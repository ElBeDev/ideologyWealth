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
        
        $sql = "
            SELECT d.*, u.username, u.firstname, u.lastname, u.email 
            FROM deposits d 
            LEFT JOIN users u ON d.user_id = u.id
        ";
        
        if ($status !== 'all') {
            $sql .= " WHERE d.status = :status";
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        
        if ($status !== 'all') {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        $deposits = $stmt->fetchAll();
        
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
