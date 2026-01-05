<?php
require_once 'config.php';

echo "<h2>Creating withdrawals table...</h2>";

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS withdrawals (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        trx VARCHAR(50) UNIQUE NOT NULL,
        gateway VARCHAR(100) NOT NULL,
        amount DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
        charge DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
        final_amount DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        transaction_id VARCHAR(255) DEFAULT NULL,
        account_details TEXT DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Withdrawals table created successfully!</p>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'withdrawals'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Table 'withdrawals' confirmed in database</p>";
        
        echo "<h3>Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE withdrawals");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<p><a href='../deposits.html'>Go to Deposits →</a></p>";
    echo "<p><a href='../withdrawals.html'>Go to Withdrawals →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
