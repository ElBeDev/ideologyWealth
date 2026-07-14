-- Script para agregar la columna 'state' a la tabla users si no existe
-- Este script es seguro ejecutar múltiples veces (idempotente)

-- Agregar columna state después de city
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS state VARCHAR(100) DEFAULT NULL AFTER city;

-- Verificar la estructura de la tabla
DESCRIBE users;
