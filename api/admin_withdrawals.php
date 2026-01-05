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

// GET - Get all withdrawals
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $status = $_GET['status'] ?? 'all';
        
        $sql = "
            SELECT w.*, u.username, u.firstname, u.lastname, u.email 
            FROM withdrawals w 
            LEFT JOIN users u ON w.user_id = u.id
        ";
        
        if ($status !== 'all') {
            $sql .= " WHERE w.status = :status";
        }
        
        $sql .= " ORDER BY w.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        
        if ($status !== 'all') {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        $withdrawals = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'withdrawals' => $withdrawals
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST - Approve or reject withdrawal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? null;
    $withdrawal_id = $input['id'] ?? null;
    
    if (!$action || !$withdrawal_id) {
        echo json_encode(['success' => false, 'message' => 'Action and withdrawal ID are required']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get withdrawal details
        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ?");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch();
        
        if (!$withdrawal) {
            echo json_encode(['success' => false, 'message' => 'Withdrawal not found']);
            exit;
        }
        
        if ($withdrawal['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Withdrawal already processed']);
            exit;
        }
        
        if ($action === 'approve') {
            // Update withdrawal status
            $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$withdrawal_id]);
            
            // Balance was already deducted when withdrawal was created
            // Just mark as approved
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Withdrawal approved'
            ]);
            
        } elseif ($action === 'reject') {
            // Update withdrawal status
            $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'rejected', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$withdrawal_id]);
            
            // Return balance to user (add back the deducted amount)
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$withdrawal['final_amount'], $withdrawal['user_id']]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Withdrawal rejected and balance returned to user'
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
