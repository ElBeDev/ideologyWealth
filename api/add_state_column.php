<?php
// Script para agregar la columna 'state' a la tabla users
require_once 'config.php';

try {
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'state'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "Agregando columna 'state' a la tabla users...\n";
        
        // Agregar la columna state después de city
        $pdo->exec("ALTER TABLE users ADD COLUMN state VARCHAR(100) DEFAULT NULL AFTER city");
        
        echo "✓ Columna 'state' agregada exitosamente.\n";
    } else {
        echo "✓ La columna 'state' ya existe en la tabla users.\n";
    }
    
    // Mostrar la estructura actual de la tabla
    echo "\nEstructura actual de campos relacionados:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field IN ('city', 'state', 'zip', 'address')");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }
    
    echo "\n✓ Script completado exitosamente.\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
