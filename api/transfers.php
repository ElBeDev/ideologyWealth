<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle POST requests (update transfer status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $transferId = filter_var($_POST['transfer_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$transferId) {
            jsonResponse(['success' => false, 'message' => 'Invalid transfer ID'], 400);
        }
        try {
            $stmt = $pdo->prepare("SELECT user_id, final_amount, status FROM transfers WHERE id = ?");
            $stmt->execute([$transferId]);
            $transfer = $stmt->fetch();
            if (!$transfer) {
                jsonResponse(['success' => false, 'message' => 'Transfer not found'], 404);
            }
            // Return balance if pending (was already deducted on creation)
            if ($transfer['status'] === 'pending') {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$transfer['final_amount'], $transfer['user_id']]);
            }
            $stmt = $pdo->prepare("DELETE FROM transfers WHERE id = ?");
            $stmt->execute([$transferId]);
            jsonResponse(['success' => true, 'message' => 'Transfer deleted']);
        } catch (PDOException $e) {
            error_log("Transfer delete error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    if ($action === 'updateStatus') {
        $transferId = filter_var($_POST['transfer_id'] ?? 0, FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        
        if (!$transferId || !in_array($status, ['approved', 'rejected'])) {
            jsonResponse(['success' => false, 'message' => 'Invalid parameters'], 400);
        }
        
        try {
            $stmt = $pdo->prepare("
                UPDATE transfers 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $transferId]);
            
            // Balance was already deducted when transfer was created.
            // On reject: return it to the user.
            if ($status === 'rejected') {
                $stmt = $pdo->prepare("
                    SELECT user_id, final_amount FROM transfers WHERE id = ?
                ");
                $stmt->execute([$transferId]);
                $transfer = $stmt->fetch();

                if ($transfer) {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$transfer['final_amount'], $transfer['user_id']]);
                }
            }
            
            jsonResponse([
                'success' => true, 
                'message' => 'Transfer ' . $status . ' successfully'
            ]);
        } catch (PDOException $e) {
            error_log("Transfer update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Database error'], 500);
        }
    }
    
    jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

// Handle GET requests (fetch transfers)
try {
    $status = $_GET['status'] ?? 'all';
    
    // Base query
    $sql = "
        SELECT 
            t.id,
            t.trx,
            t.beneficiary_name,
            t.account_number,
            t.routing_number,
            t.swift_code,
            t.bank_name,
            t.amount,
            t.charge,
            t.final_amount,
            t.purpose,
            t.status,
            t.created_at as date,
            u.username,
            u.email
        FROM transfers t
        INNER JOIN users u ON t.user_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filter by status
    if ($status !== 'all') {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }
    
    // Search filter
    if (!empty($search)) {
        $sql .= " AND (t.trx LIKE ? OR u.username LIKE ? OR t.beneficiary_name LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Date range filter
    if (!empty($dateRange)) {
        $dates = explode(' - ', $dateRange);
        if (count($dates) === 2) {
            $sql .= " AND DATE(t.created_at) BETWEEN ? AND ?";
            $params[] = trim($dates[0]);
            $params[] = trim($dates[1]);
        }
    }
    
    // Order by newest first
    $sql .= " ORDER BY t.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transfers = $stmt->fetchAll();
    
    // Get counts for badges
    $countStmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM transfers
    ");
    $counts = $countStmt->fetch();
    
    jsonResponse([
        'success' => true,
        'data' => $transfers,
        'counts' => $counts
    ]);
    
} catch (PDOException $e) {
    error_log("Transfer fetch error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error'], 500);
}
?>
