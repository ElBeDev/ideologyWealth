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

// GET - Get all deposits
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $date_range = $_GET['date_range'] ?? '';

        $sql = "
            SELECT d.*, 'deposit' AS source, u.username, u.firstname, u.lastname, u.email 
            FROM deposits d 
            LEFT JOIN users u ON d.user_id = u.id
        ";
        $where = [];
        $params = [];

        if ($status !== 'all') {
            $where[] = "d.status = ?";
            $params[] = $status;
        }
        if ($search) {
            $where[] = "(d.trx LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }
        if ($date_range && strpos($date_range, ' - ') !== false) {
            [$dateFrom, $dateTo] = explode(' - ', $date_range, 2);
            $where[] = "DATE(d.created_at) BETWEEN ? AND ?";
            $params[] = trim($dateFrom); $params[] = trim($dateTo);
        }
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $deposits = $stmt->fetchAll();

        // Also pull credit transactions not already linked to a deposits record
        try {
            $txSql = "
                SELECT
                    t.id,
                    t.trx,
                    'transaction' AS source,
                    'Manual (Admin)' AS gateway,
                    t.amount,
                    0 AS charge,
                    t.amount AS final_amount,
                    t.status,
                    t.details AS notes,
                    t.date AS created_at,
                    t.date AS updated_at,
                    u2.username, u2.firstname, u2.lastname, u2.email,
                    t.user_id
                FROM transactions t
                LEFT JOIN users u2 ON t.user_id = u2.id
                WHERE t.type = 'credit'
                  AND NOT EXISTS (
                      SELECT 1 FROM deposits d2
                      WHERE d2.user_id = t.user_id
                        AND (d2.trx = t.trx COLLATE utf8mb4_unicode_ci OR d2.trx = REPLACE(t.trx, 'DEP', '') COLLATE utf8mb4_unicode_ci)
                  )
            ";
            $txWhere = [];
            $txParams = [];
            if ($status !== 'all') {
                $txWhere[] = "t.status = ?";
                $txParams[] = ($status === 'approved') ? 'completed' : $status;
            }
            if ($search) {
                $txWhere[] = "(t.trx LIKE ? OR u2.username LIKE ? OR u2.email LIKE ?)";
                $txParams[] = "%$search%"; $txParams[] = "%$search%"; $txParams[] = "%$search%";
            }
            if ($txWhere) {
                $txSql .= " AND " . implode(" AND ", $txWhere);
            }
            $txSql .= " ORDER BY t.date DESC";
            $txStmt = $pdo->prepare($txSql);
            $txStmt->execute($txParams);
            $txDeposits = $txStmt->fetchAll();

            $deposits = array_merge($deposits, $txDeposits);
            usort($deposits, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        } catch (PDOException $e) {
            // transactions table may not exist yet
        }

        echo json_encode([
            'success' => true,
            'deposits' => $deposits
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST - Approve or reject deposit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? null;
    $deposit_id = $input['id'] ?? null;
    
    if (!$action || !$deposit_id) {
        echo json_encode(['success' => false, 'message' => 'Action and deposit ID are required']);
        exit;
    }
    
    // Handle 'delete' action
    if ($action === 'delete') {
        $stmt = $pdo->prepare("SELECT * FROM deposits WHERE id = ?");
        $stmt->execute([$deposit_id]);
        $deposit = $stmt->fetch();

        if (!$deposit) {
            echo json_encode(['success' => false, 'message' => 'Deposit not found']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Revert balance if deposit was approved
            if ($deposit['status'] === 'approved') {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                $stmt->execute([floatval($deposit['final_amount'] ?: $deposit['amount']), $deposit['user_id']]);
            }

            // Delete the deposit record
            $stmt = $pdo->prepare("DELETE FROM deposits WHERE id = ?");
            $stmt->execute([$deposit_id]);

            // Delete matching transaction(s) directly (no second balance reversal)
            // Scenario A: same trx (admin manual add)
            // Scenario B: transactions.trx = 'DEP' + deposits.trx (deposit approve flow)
            $depositTrx = $deposit['trx'];
            $stmt = $pdo->prepare("DELETE FROM transactions WHERE user_id = ? AND (trx = ? OR trx = ?)");
            $stmt->execute([$deposit['user_id'], $depositTrx, 'DEP' . $depositTrx]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Deposit deleted successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Handle 'update' action (edit TRX ID and/or date)
    if ($action === 'update') {
        $new_trx  = trim($input['trx']  ?? '');
        $new_date = trim($input['date'] ?? '');
        $source   = trim($input['source'] ?? 'deposit');

        if (!$new_trx) {
            echo json_encode(['success' => false, 'message' => 'TRX ID is required']);
            exit;
        }

        $dateVal = $new_date
            ? date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $new_date)))
            : null;

        try {
            if ($source === 'transaction') {
                $stmt = $pdo->prepare("UPDATE transactions SET trx = ?, date = ? WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE deposits SET trx = ?, created_at = ?, updated_at = NOW() WHERE id = ?");
            }
            $stmt->execute([$new_trx, $dateVal, $deposit_id]);
            echo json_encode(['success' => true, 'message' => 'Deposit updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        // Get deposit details
        $stmt = $pdo->prepare("SELECT * FROM deposits WHERE id = ?");
        $stmt->execute([$deposit_id]);
        $deposit = $stmt->fetch();
        
        if (!$deposit) {
            echo json_encode(['success' => false, 'message' => 'Deposit not found']);
            exit;
        }
        
        if ($deposit['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Deposit already processed']);
            exit;
        }
        
        if ($action === 'approve') {
            // Update deposit status
            $stmt = $pdo->prepare("UPDATE deposits SET status = 'approved', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$deposit_id]);
            
            // Add balance to user
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$deposit['final_amount'], $deposit['user_id']]);
            
            // Get new balance for post_balance
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$deposit['user_id']]);
            $newBalance = $stmt->fetchColumn();
            
            // Create transaction record so it shows in user's movements
            try {
                $trx = 'DEP' . $deposit['trx'];
                $gateway = $deposit['gateway'] ?? 'Deposit';
                // Only insert if not already recorded
                $chk = $pdo->prepare("SELECT id FROM transactions WHERE trx = ? LIMIT 1");
                $chk->execute([$trx]);
                if (!$chk->fetch()) {
                    $stmt = $pdo->prepare("
                        INSERT INTO transactions (user_id, trx, date, details, account_number, amount, type, status, post_balance, description, created_at)
                        VALUES (?, ?, NOW(), ?, '', ?, 'credit', 'completed', ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $deposit['user_id'],
                        $trx,
                        'Deposit - ' . $gateway,
                        floatval($deposit['final_amount']),
                        floatval($newBalance),
                        'Deposit approved via ' . $gateway
                    ]);
                }
            } catch (PDOException $e) {
                // Transactions table may not exist yet — log but don't fail
                error_log("Could not create transaction record for deposit: " . $e->getMessage());
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Deposit approved and balance added to user'
            ]);
            
        } elseif ($action === 'reject') {
            // Update deposit status
            $stmt = $pdo->prepare("UPDATE deposits SET status = 'rejected', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$deposit_id]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Deposit rejected'
            ]);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
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
