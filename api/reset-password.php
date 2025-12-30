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
    $token = trim($data['token'] ?? '');
    $password = trim($data['password'] ?? '');
    
    if (empty($token)) {
        jsonResponse(['success' => false, 'message' => 'Token is required'], 400);
    }
    
    if (empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Password is required'], 400);
    }
    
    if (strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
    }
    
    // Check if token is valid and not expired
    $stmt = $pdo->prepare("
        SELECT user_id 
        FROM password_resets 
        WHERE token = :token 
        AND expires_at > NOW() 
        AND used = 0
        LIMIT 1
    ");
    $stmt->execute(['token' => $token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        jsonResponse(['success' => false, 'message' => 'Invalid or expired reset token'], 400);
    }
    
    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Update user password
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
    $stmt->execute([
        'password' => $hashedPassword,
        'user_id' => $reset['user_id']
    ]);
    
    // Mark token as used
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = :token");
    $stmt->execute(['token' => $token]);
    
    jsonResponse([
        'success' => true, 
        'message' => 'Password reset successfully! You can now login with your new password.'
    ]);
    
} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred. Please try again later.'], 500);
}
