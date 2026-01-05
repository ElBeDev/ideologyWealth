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
// For now, we'll allow access if a valid admin token is sent or skip check for testing
// You can add header authentication here if needed

// GET - Dashboard statistics
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $total_users = $stmt->fetch()['count'];
        
        // Total deposits amount
        $stmt = $pdo->query("SELECT COALESCE(SUM(final_amount), 0) as total FROM deposits WHERE status = 'approved'");
        $total_deposits = $stmt->fetch()['total'];
        
        // Pending deposits count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM deposits WHERE status = 'pending'");
        $pending_deposits = $stmt->fetch()['count'];
        
        // Pending withdrawals count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending'");
        $pending_withdrawals = $stmt->fetch()['count'];
        
        // Recent deposits with user info
        $stmt = $pdo->query("
            SELECT d.*, u.username, u.firstname, u.lastname 
            FROM deposits d 
            LEFT JOIN users u ON d.user_id = u.id 
            ORDER BY d.created_at DESC 
            LIMIT 10
        ");
        $recent_deposits = $stmt->fetchAll();
        
        // Recent withdrawals with user info
        $stmt = $pdo->query("
            SELECT w.*, u.username, u.firstname, u.lastname 
            FROM withdrawals w 
            LEFT JOIN users u ON w.user_id = u.id 
            ORDER BY w.created_at DESC 
            LIMIT 10
        ");
        $recent_withdrawals = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_users' => $total_users,
                'total_deposits' => $total_deposits,
                'pending_deposits' => $pending_deposits,
                'pending_withdrawals' => $pending_withdrawals
            ],
            'recent_deposits' => $recent_deposits,
            'recent_withdrawals' => $recent_withdrawals
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
?>
