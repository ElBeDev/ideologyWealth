<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
header('Content-Type: application/json');
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Auth via session OR X-Auth-Token header
$user_id = null;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
} else {
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? null;
    if ($token) {
        $st = $pdo->prepare("SELECT id FROM users WHERE auth_token = ? LIMIT 1");
        $st->execute([$token]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) $user_id = $row['id'];
    }
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $current_password = $input['current_password'] ?? '';
    $new_password = $input['new_password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode([
            'success' => false,
            'message' => 'New passwords do not match'
        ]);
        exit;
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters'
        ]);
        exit;
    }
    
    try {
        // Get current user
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
            exit;
        }
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Current password is incorrect'
            ]);
            exit;
        }
        
        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password + plain_password for admin reference
        $stmt = $pdo->prepare("UPDATE users SET password = ?, plain_password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_password_hash, $new_password, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
