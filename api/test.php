<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM users');
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'API funcionando correctamente',
        'database' => 'Conectada',
        'total_usuarios' => $result['total'],
        'php_version' => phpversion(),
        'server_time' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
