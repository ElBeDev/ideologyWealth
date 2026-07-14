<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once 'config.php';

// Ensure login_logs table exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            username VARCHAR(100),
            ip_address VARCHAR(45),
            user_agent TEXT,
            device_type VARCHAR(50),
            browser VARCHAR(100),
            os VARCHAR(100),
            location_country VARCHAR(100),
            location_city VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (PDOException $e) {
    error_log("login_logs table creation error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id  = $_GET['user_id'] ?? null;
    $limit    = min((int)($_GET['limit'] ?? 100), 500);

    try {
        if ($user_id) {
            $stmt = $pdo->prepare("
                SELECT l.*, u.firstname, u.lastname, u.email
                FROM login_logs l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.user_id = ?
                ORDER BY l.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$user_id, $limit]);
        } else {
            $stmt = $pdo->prepare("
                SELECT l.*, u.firstname, u.lastname, u.email
                FROM login_logs l
                LEFT JOIN users u ON l.user_id = u.id
                ORDER BY l.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
        }

        $logs = $stmt->fetchAll();
        echo json_encode(['success' => true, 'logs' => $logs]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
