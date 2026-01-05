<?php
// Script para intentar descifrar contraseñas bcrypt con patrones comunes
header('Content-Type: application/json');
require_once 'config.php';

// Patrones comunes para probar
$commonPatterns = [
    // Patrón: Nombre + "123"
    // Patrón: Nombre + "123456"
    // Patrón: Nombre + año
    // Patrón: username + números
];

try {
    // Obtener todos los usuarios con sus hashes
    $stmt = $pdo->query("SELECT id, username, firstname, lastname, email, password FROM users WHERE password IS NOT NULL");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    
    foreach ($users as $user) {
        $hash = $user['password'];
        $found = false;
        $plainPassword = null;
        
        // Generar patrones específicos para este usuario
        $patternsToTry = [
            $user['username'],
            $user['username'] . '123',
            $user['username'] . '123456',
            $user['username'] . '1234',
            $user['firstname'],
            $user['firstname'] . '123',
            $user['firstname'] . '123456',
            ucfirst(strtolower($user['firstname'])) . '123',
            ucfirst(strtolower($user['firstname'])) . '123456',
            strtolower($user['username']),
            strtolower($user['username']) . '123',
            strtolower($user['firstname']),
            strtolower($user['firstname']) . '123',
            $user['lastname'],
            strtolower($user['lastname']) . '123',
            // Patrones comunes
            'password',
            'password123',
            '123456',
            '12345678',
            'admin',
            'admin123',
        ];
        
        // Intentar cada patrón
        foreach ($patternsToTry as $pattern) {
            if (password_verify($pattern, $hash)) {
                $plainPassword = $pattern;
                $found = true;
                break;
            }
        }
        
        $results[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'password' => $found ? $plainPassword : 'NOT_FOUND',
            'hash' => $hash
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'total' => count($users),
        'found' => count(array_filter($results, function($r) { return $r['password'] !== 'NOT_FOUND'; }))
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
