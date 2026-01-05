<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;
    
    if (!$username || !$password) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit;
    }
    
    // Hardcoded admin credentials for now - you can add to database later
    $admin_username = 'admin';
    $admin_password_hash = password_hash('admin123', PASSWORD_BCRYPT); // Change this password!
    
    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        // Return success without session
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'admin' => [
                'username' => $username,
                'role' => 'administrator'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
?>
