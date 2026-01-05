<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $_SESSION['user_id'];
    $firstname = trim($input['firstname'] ?? '');
    $lastname = trim($input['lastname'] ?? '');
    $mobile = trim($input['mobile'] ?? '');
    $country_code = trim($input['country_code'] ?? '');
    $city = trim($input['city'] ?? '');
    $zip = trim($input['zip'] ?? '');
    $address = trim($input['address'] ?? '');
    
    // Validation
    if (empty($firstname) || empty($lastname) || empty($mobile) || empty($city) || empty($zip) || empty($address)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET firstname = ?, 
                lastname = ?, 
                mobile = ?, 
                country_code = ?, 
                city = ?, 
                zip = ?, 
                address = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $firstname,
            $lastname,
            $mobile,
            $country_code,
            $city,
            $zip,
            $address,
            $user_id
        ]);
        
        // Get updated user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            unset($user['password']);
            $_SESSION['user_data'] = $user;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
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
