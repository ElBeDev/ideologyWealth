<?php
require_once 'config.php';

echo "<h2>Creating transfers table...</h2>";

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS transfers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        trx VARCHAR(50) UNIQUE NOT NULL,
        beneficiary_name VARCHAR(255) NOT NULL,
        account_number VARCHAR(100) NOT NULL,
        routing_number VARCHAR(50) NOT NULL,
        swift_code VARCHAR(50) DEFAULT NULL,
        bank_name VARCHAR(255) DEFAULT NULL,
        amount DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
        charge DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
        final_amount DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
        purpose TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        admin_note TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at),
        INDEX idx_trx (trx)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Transfers table created successfully!</p>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'transfers'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Table 'transfers' confirmed in database</p>";
        
        echo "<h3>Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE transfers");
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
    echo "<p><a href='../admin/transfers.html'>Go to Admin Transfers →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
