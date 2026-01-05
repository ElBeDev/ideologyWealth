<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lifefina_bank');
define('DB_USER', 'onelife_user');
define('DB_PASS', 'OneLif3Secure2024!');
define('DB_CHARSET', 'utf8mb4');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
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
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
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
    return '317012315';
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
