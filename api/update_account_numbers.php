<?php
require_once 'config.php';

echo "<h2>Updating all account numbers to 317012315...</h2>";

try {
    $stmt = $pdo->prepare("UPDATE users SET account_number = '317012315'");
    $stmt->execute();
    
    $count = $stmt->rowCount();
    
    echo "<p style='color: green;'>✓ Updated {$count} users successfully!</p>";
    
    // Show all users with their new account numbers
    $stmt = $pdo->query("SELECT id, username, email, account_number FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users) {
        echo "<h3>Current Users:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Account Number</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td><strong>{$user['account_number']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<p style='color: blue;'>All users now have account number: <strong>317012315</strong></p>";
    echo "<p><a href='../dashboard.html'>Go to Dashboard →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
