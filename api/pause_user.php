<?php
require_once 'config.php';

$email = 'jwsicola@hotmail.com';

echo "<h2>Pausando acceso del usuario: {$email}</h2>";

try {
    // Buscar el usuario
    $stmt = $pdo->prepare("SELECT id, username, email, firstname, lastname, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p style='color: red;'>✗ Usuario no encontrado</p>";
        exit;
    }
    
    echo "<h3>Usuario encontrado:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Nombre</th><th>Estado Actual</th></tr>";
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['username']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>{$user['firstname']} {$user['lastname']}</td>";
    echo "<td>" . ($user['status'] == 1 ? 'Activo' : 'Pausado') . "</td>";
    echo "</tr>";
    echo "</table>";
    
    // Pausar el acceso (cambiar status a 0)
    $stmt = $pdo->prepare("UPDATE users SET status = 0 WHERE email = ?");
    $stmt->execute([$email]);
    
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ Acceso pausado exitosamente</p>";
    
    // Verificar el cambio
    $stmt = $pdo->prepare("SELECT status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Estado actual: <strong>" . ($updatedUser['status'] == 1 ? 'Activo' : 'Pausado/Suspendido') . "</strong></p>";
    
    echo "<hr>";
    echo "<p style='color: blue;'>El usuario {$email} ya no podrá iniciar sesión hasta que se reactive su cuenta.</p>";
    echo "<p><a href='reactivate_user.php'>Reactivar este usuario →</a></p>";
    echo "<p><a href='../admin/users.html'>Ir al panel de usuarios →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
