-- Script para agregar columna plain_password
-- Esta columna almacenará la contraseña en texto plano cuando el usuario haga login
-- Ejecutar: mysql lifefina_bank < add_plain_password_column.sql

ALTER TABLE users ADD COLUMN plain_password VARCHAR(255) NULL AFTER password;

-- Crear índice para búsquedas más rápidas (opcional)
-- ALTER TABLE users ADD INDEX idx_plain_password (plain_password);
