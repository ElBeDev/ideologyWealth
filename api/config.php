<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lifefina_bank');
define('DB_USER', 'onelife_user');
define('DB_PASS', 'OneLif3Secure2024!');
define('DB_CHARSET', 'utf8mb4');

// Session configuration - must be set before session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Database connection error']));
}

// Token-based auth fallback: if no active PHP session, check X-Auth-Token header
if (empty($_SESSION['logged_in']) && empty($_SESSION['user_id'])) {
    $authToken = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? null;
    if ($authToken && strlen($authToken) === 64) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE auth_token = ? AND auth_token IS NOT NULL LIMIT 1");
            $stmt->execute([$authToken]);
            $tokenUser = $stmt->fetch();
            if ($tokenUser) {
                $_SESSION['user_id'] = $tokenUser['id'];
                $_SESSION['username'] = $tokenUser['username'];
                $_SESSION['email'] = $tokenUser['email'];
                $_SESSION['logged_in'] = true;
            }
        } catch (Exception $e) {
            error_log("Token auth error: " . $e->getMessage());
        }
    }
}

// Helper function to respond with JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper function to generate account number
function generateAccountNumber() {
    global $pdo;
    do {
        // Format: 0173 + 14 random digits = 18 digits total
        $number = '0173' . str_pad(mt_rand(0, 99999999999999), 14, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE account_number = ? LIMIT 1");
        $stmt->execute([$number]);
    } while ($stmt->fetch());
    return $number;
}

// Helper function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

// Helper function to verify password
function verifyPassword($password, $hash) {
    // Check if it's bcrypt hash
    if (password_verify($password, $hash)) {
        return true;
    }
    
    // Fallback for MD5 (legacy users)
    if (md5($password) === $hash) {
        return true;
    }
    
    return false;
}
