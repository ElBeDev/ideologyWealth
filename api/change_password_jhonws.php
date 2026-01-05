<?php
require_once 'config.php';

echo "<h2>Changing password for user: JhonWS</h2>";

$username = 'JhonWS';
$newPassword = '123456789';

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "<p style='color: red;'>✗ User 'JhonWS' not found in database</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ User found:</p>";
    echo "<ul>";
    echo "<li>ID: {$user['id']}</li>";
    echo "<li>Username: {$user['username']}</li>";
    echo "<li>Email: {$user['email']}</li>";
    echo "</ul>";
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE username = ?");
    $stmt->execute([$hashedPassword, $username]);
    
    echo "<p style='color: green; font-weight: bold;'>✓ Password updated successfully!</p>";
    echo "<p>New password: <strong>$newPassword</strong></p>";
    echo "<p style='color: #666; font-size: 14px;'>The password has been securely hashed and stored in the database.</p>";
    
    echo "<hr>";
    echo "<p><a href='../admin/login.html'>Go to Admin Login →</a></p>";
    echo "<p><a href='../login.html'>Go to User Login →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
