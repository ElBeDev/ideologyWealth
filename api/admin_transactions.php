<?php
/**
 * API para gestionar transacciones desde el panel de administración
 * Permite crear, editar y cambiar estado de transacciones de usuarios
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// GET - Obtener transacciones de un usuario
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'user_id es requerido']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, trx, date, details, account_number, amount, type, status, post_balance, description, created_at
            FROM transactions 
            WHERE user_id = ? 
            ORDER BY date DESC, id DESC
        ");
        $stmt->execute([$user_id]);
        $transactions = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'transactions' => $transactions
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// POST - Crear o actualizar transacción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? 'create';
    
    try {
        if ($action === 'create') {
            // Crear nueva transacción
            $user_id = $input['user_id'] ?? null;
            $details = $input['details'] ?? '';
            $amount = floatval($input['amount'] ?? 0);
            $type = $input['type'] ?? 'credit'; // credit o debit
            $status = $input['status'] ?? 'pending'; // pending, completed, failed
            $date = $input['date'] ?? date('Y-m-d H:i:s');
            $account_number = $input['account_number'] ?? '';
            $description = $input['description'] ?? '';
            
            if (!$user_id || !$details || $amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'user_id, details y amount son requeridos']);
                exit;
            }
            
            // Generar TRX único
            $trx = 'TRX' . time() . rand(100, 999);
            
            // Obtener balance actual del usuario
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $current_balance = $user ? floatval($user['balance']) : 0;
            
            // Calcular post_balance según el tipo y estado
            $post_balance = null;
            if ($status === 'completed') {
                if ($type === 'credit') {
                    $post_balance = $current_balance + $amount;
                } else {
                    $post_balance = $current_balance - $amount;
                }
            }
            
            // Insertar transacción
            $stmt = $pdo->prepare("
                INSERT INTO transactions (user_id, trx, date, details, account_number, amount, type, status, post_balance, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $trx, $date, $details, $account_number, $amount, $type, $status, $post_balance, $description]);
            
            $transaction_id = $pdo->lastInsertId();
            
            // Si está completada y es credit, actualizar balance del usuario
            if ($status === 'completed' && $type === 'credit') {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$amount, $user_id]);
            } elseif ($status === 'completed' && $type === 'debit') {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$amount, $user_id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Transacción creada exitosamente',
                'transaction_id' => $transaction_id,
                'trx' => $trx
            ]);
            
        } elseif ($action === 'update_status') {
            // Actualizar estado de transacción existente
            $transaction_id = $input['transaction_id'] ?? null;
            $new_status = $input['status'] ?? null;
            
            if (!$transaction_id || !$new_status) {
                echo json_encode(['success' => false, 'message' => 'transaction_id y status son requeridos']);
                exit;
            }
            
            // Validar status
            $valid_statuses = ['pending', 'completed', 'failed', 'approved', 'rejected'];
            if (!in_array($new_status, $valid_statuses)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido. Use: pending, completed, failed']);
                exit;
            }
            
            // Obtener transacción actual
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
            $stmt->execute([$transaction_id]);
            $transaction = $stmt->fetch();
            
            if (!$transaction) {
                echo json_encode(['success' => false, 'message' => 'Transacción no encontrada']);
                exit;
            }
            
            $old_status = $transaction['status'];
            $user_id = $transaction['user_id'];
            $amount = floatval($transaction['amount']);
            $type = $transaction['type'];
            
            // Obtener balance actual
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $current_balance = floatval($user['balance']);
            
            // Calcular nuevo post_balance
            $post_balance = $transaction['post_balance']; // keep existing value by default
            
            // Si pasa a completed, aplicar al balance
            if ($new_status === 'completed' && $old_status !== 'completed') {
                if ($type === 'credit') {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$amount, $user_id]);
                    $post_balance = $current_balance + $amount;
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $stmt->execute([$amount, $user_id]);
                    $post_balance = $current_balance - $amount;
                }
            }
            // Si estaba completed y pasa a otro estado, revertir
            elseif ($old_status === 'completed' && $new_status !== 'completed') {
                if ($type === 'credit') {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $stmt->execute([$amount, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$amount, $user_id]);
                }
            }
            
            // Actualizar estado (y TRX si se proporcionó)
            $new_trx = trim($input['trx'] ?? '');
            if ($new_trx) {
                $stmt = $pdo->prepare("UPDATE transactions SET status = ?, post_balance = ?, trx = ? WHERE id = ?");
                $stmt->execute([$new_status, $post_balance, $new_trx, $transaction_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE transactions SET status = ?, post_balance = ? WHERE id = ?");
                $stmt->execute([$new_status, $post_balance, $transaction_id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado a: ' . $new_status,
                'old_status' => $old_status,
                'new_status' => $new_status
            ]);
            
        } elseif ($action === 'update') {
            // Actualizar transacción completa
            $transaction_id = $input['transaction_id'] ?? null;
            
            if (!$transaction_id) {
                echo json_encode(['success' => false, 'message' => 'transaction_id es requerido']);
                exit;
            }
            
            $updates = [];
            $params = [];
            
            if (isset($input['details'])) {
                $updates[] = "details = ?";
                $params[] = $input['details'];
            }
            if (isset($input['amount'])) {
                $updates[] = "amount = ?";
                $params[] = floatval($input['amount']);
            }
            if (isset($input['date'])) {
                $updates[] = "date = ?";
                $params[] = $input['date'];
            }
            if (isset($input['account_number'])) {
                $updates[] = "account_number = ?";
                $params[] = $input['account_number'];
            }
            if (isset($input['description'])) {
                $updates[] = "description = ?";
                $params[] = $input['description'];
            }
            if (isset($input['type'])) {
                $updates[] = "type = ?";
                $params[] = $input['type'];
            }
            
            if (empty($updates)) {
                echo json_encode(['success' => false, 'message' => 'No hay campos para actualizar']);
                exit;
            }
            
            $params[] = $transaction_id;
            $sql = "UPDATE transactions SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                'success' => true,
                'message' => 'Transacción actualizada'
            ]);
            
        } elseif ($action === 'delete') {
            // Eliminar transacción
            $transaction_id = $input['transaction_id'] ?? null;
            
            if (!$transaction_id) {
                echo json_encode(['success' => false, 'message' => 'transaction_id es requerido']);
                exit;
            }
            
            // Verificar si estaba completada para revertir balance
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
            $stmt->execute([$transaction_id]);
            $transaction = $stmt->fetch();
            
            if ($transaction && $transaction['status'] === 'completed') {
                $user_id = $transaction['user_id'];
                $amount = floatval($transaction['amount']);
                $type = $transaction['type'];
                
                // Revertir balance
                if ($type === 'credit') {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                }
                $stmt->execute([$amount, $user_id]);
            }
            
            $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
            $stmt->execute([$transaction_id]);

            // Also delete matching deposit record (linked by trx + user_id)
            if ($transaction && $transaction['trx']) {
                $txTrx  = $transaction['trx'];
                $userId = $transaction['user_id'];
                // Scenario A: same trx (admin manual add)
                // Scenario B: transaction trx = 'DEP' + deposit trx (deposit approve flow)
                $depositTrx = (substr($txTrx, 0, 3) === 'DEP') ? substr($txTrx, 3) : $txTrx;
                $stmt = $pdo->prepare("DELETE FROM deposits WHERE user_id = ? AND (trx = ? OR trx = ?)");
                $stmt->execute([$userId, $txTrx, $depositTrx]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Transacción eliminada'
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no soportado']);
