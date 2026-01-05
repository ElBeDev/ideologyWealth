<?php
require_once 'config.php';

$email = 'jwsicola@hotmail.com';

echo "<h2>Reactivando acceso del usuario: {$email}</h2>";

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
    
    // Reactivar el acceso (cambiar status a 1)
    $stmt = $pdo->prepare("UPDATE users SET status = 1 WHERE email = ?");
    $stmt->execute([$email]);
    
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ Acceso reactivado exitosamente</p>";
    
    // Verificar el cambio
    $stmt = $pdo->prepare("SELECT status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Estado actual: <strong>" . ($updatedUser['status'] == 1 ? 'Activo' : 'Pausado/Suspendido') . "</strong></p>";
    
    echo "<hr>";
    echo "<p style='color: blue;'>El usuario {$email} ahora puede iniciar sesión normalmente.</p>";
    echo "<p><a href='pause_user.php'>Pausar nuevamente este usuario →</a></p>";
    echo "<p><a href='../admin/users.html'>Ir al panel de usuarios →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
