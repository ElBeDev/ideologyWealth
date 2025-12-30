<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');
    
    if (empty($email)) {
        jsonResponse(['success' => false, 'message' => 'Email or username is required'], 400);
    }
    
    // Check if user exists by email or username
    $stmt = $pdo->prepare("SELECT id, username, email, firstname, lastname FROM users WHERE email = :email OR username = :username LIMIT 1");
    $stmt->execute(['email' => $email, 'username' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store token in database
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, token, expires_at, created_at) 
        VALUES (:user_id, :token, :expires_at, NOW())
        ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at, created_at = NOW()
    ");
    $stmt->execute([
        'user_id' => $user['id'],
        'token' => $token,
        'expires_at' => $expires
    ]);
    
    // TODO: Send email with reset link
    // For now, just log the token (in production, send via email)
    error_log("Password reset token for {$user['email']}: $token");
    error_log("Reset link: " . $_SERVER['HTTP_HOST'] . "/reset-password.html?token=$token");
    
    jsoReturn token directly (no email needed)
    error_log("Password reset token for {$user['email']}: $token");
    
    jsonResponse([
        'success' => true, 
        'message' => 'Reset link generated successfully',
        'token' => $token,
        'username' => $user['username']false, 'message' => 'An error occurred. Please try again later.'], 500);
}
