<?php
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
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

// Authenticate via session OR X-Auth-Token header
$user_id = null;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
} else {
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? null;
    if ($token) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE auth_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) $user_id = $row['id'];
    }
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $mobile = trim($input['mobile'] ?? '');
    $email  = trim($input['email']  ?? '');
    
    // Validation
    if (empty($mobile) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email and mobile are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET email = ?, mobile = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$email, $mobile, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        
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
