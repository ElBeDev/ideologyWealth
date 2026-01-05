<?php
require_once 'config.php';

$email = 'jwsicola@hotmail.com';

echo "<h2>Actualizando perfil de John W. Sicola</h2>";

try {
    // Primero verificar si las columnas existen, si no, agregarlas
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_account_number VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_beneficiary VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_routing VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_swift VARCHAR(50) DEFAULT NULL"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
        } catch (PDOException $e) {
            // Si la columna ya existe, continuar
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "<p style='color: orange;'>Nota: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<p style='color: green;'>✓ Estructura de base de datos verificada</p>";
    
    // Buscar el usuario actual
    $stmt = $pdo->prepare("SELECT id, username, email, firstname, lastname FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p style='color: red;'>✗ Usuario no encontrado</p>";
        exit;
    }
    
    echo "<h3>Usuario encontrado:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor Anterior</th></tr>";
    echo "<tr><td>ID</td><td>{$user['id']}</td></tr>";
    echo "<tr><td>Username</td><td>{$user['username']}</td></tr>";
    echo "<tr><td>Email</td><td>{$user['email']}</td></tr>";
    echo "<tr><td>Nombre</td><td>{$user['firstname']}</td></tr>";
    echo "<tr><td>Apellido</td><td>{$user['lastname']}</td></tr>";
    echo "</table>";
    
    // Actualizar el usuario con la nueva información
    $stmt = $pdo->prepare("
        UPDATE users SET 
            firstname = 'John',
            bank_account_number = '8488072554',
            bank_beneficiary = 'UNIK . John W. Sicola',
            bank_routing = '026073008',
            bank_swift = 'CMFGUS33'
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ Perfil actualizado exitosamente</p>";
    
    // Verificar los cambios
    $stmt = $pdo->prepare("
        SELECT firstname, lastname, bank_account_number, bank_beneficiary, bank_routing, bank_swift 
        FROM users WHERE email = ?
    ");
    $stmt->execute([$email]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Información Actualizada:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Nuevo Valor</th></tr>";
    echo "<tr><td>Nombre</td><td>{$updatedUser['firstname']}</td></tr>";
    echo "<tr><td>Apellido</td><td>{$updatedUser['lastname']}</td></tr>";
    echo "<tr><td>Account Number</td><td>{$updatedUser['bank_account_number']}</td></tr>";
    echo "<tr><td>Beneficiary</td><td>{$updatedUser['bank_beneficiary']}</td></tr>";
    echo "<tr><td>Routing Number</td><td>{$updatedUser['bank_routing']}</td></tr>";
    echo "<tr><td>SWIFT Code</td><td>{$updatedUser['bank_swift']}</td></tr>";
    echo "</table>";
    
    echo "<hr>";
    echo "<p style='color: blue;'>✓ El perfil ahora mostrará 'Hello, John...'</p>";
    echo "<p style='color: blue;'>✓ La información bancaria personalizada ha sido guardada</p>";
    echo "<p><a href='../dashboard.html'>Ir al Dashboard →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
