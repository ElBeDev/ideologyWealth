<?php
require_once 'config.php';

echo "<h2>Deposits System Test</h2>";

// Check if deposits table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'deposits'");
    if ($stmt->rowCount() === 0) {
        echo "<p style='color: red;'>✗ Deposits table does not exist. <a href='setup_deposits.php'>Create it first →</a></p>";
        exit;
    }
    echo "<p style='color: green;'>✓ Deposits table exists</p>";
    
    // Get total deposits count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM deposits");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>Total deposits in database: <strong>{$total}</strong></p>";
    
    // Show recent deposits
    if ($total > 0) {
        echo "<h3>Recent Deposits:</h3>";
        $stmt = $pdo->query("
            SELECT 
                d.id,
                d.trx,
                d.gateway,
                d.amount,
                d.charge,
                d.final_amount,
                d.status,
                d.created_at,
                u.username,
                u.email
            FROM deposits d
            LEFT JOIN users u ON d.user_id = u.id
            ORDER BY d.created_at DESC
            LIMIT 10
        ");
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>TRX</th>";
        echo "<th>User</th>";
        echo "<th>Gateway</th>";
        echo "<th>Amount</th>";
        echo "<th>Charge</th>";
        echo "<th>Final</th>";
        echo "<th>Status</th>";
        echo "<th>Date</th>";
        echo "</tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $statusColor = $row['status'] === 'approved' ? 'green' : ($row['status'] === 'pending' ? 'orange' : 'red');
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['trx']}</td>";
            echo "<td>{$row['username']}<br><small>{$row['email']}</small></td>";
            echo "<td>{$row['gateway']}</td>";
            echo "<td>\${$row['amount']}</td>";
            echo "<td>\${$row['charge']}</td>";
            echo "<td>\${$row['final_amount']}</td>";
            echo "<td style='color: {$statusColor}; font-weight: bold;'>{$row['status']}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show deposit statistics by status
    echo "<h3>Deposit Statistics:</h3>";
    $stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count,
            SUM(amount) as total_amount,
            SUM(final_amount) as total_final
        FROM deposits
        GROUP BY status
    ");
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Status</th><th>Count</th><th>Total Amount</th><th>Final Amount</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>\${$row['total_amount']}</td>";
        echo "<td>\${$row['total_final']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Test Deposit Creation</h3>";
    echo "<p>Go to <a href='http://62.72.7.44/deposits.html' target='_blank'>deposits.html</a> to test the deposit form.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database Error: " . $e->getMessage() . "</p>";
}
?>
