-- Script para crear tabla de transacciones (si no existe)
-- y verificar el usuario Kurt Stoops

-- Crear tabla de transacciones si no existe
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `trx` varchar(50) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `details` varchar(255) NOT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `status` enum('pending','approved','completed','rejected') NOT NULL DEFAULT 'pending',
  `post_balance` decimal(15,2) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verificar el usuario Kurt Stoops
SELECT 
    id,
    username,
    CONCAT(firstname, ' ', lastname) as full_name,
    email,
    balance
FROM users 
WHERE LOWER(CONCAT(firstname, ' ', lastname)) = 'kurt stoops'
   OR LOWER(username) LIKE '%kurt%stoops%'
LIMIT 1;

-- NOTA: La transacción de HILTON se mostrará automáticamente 
-- para Kurt Stoops a través de la API (api/transactions.php)
-- NO es necesario insertarla en la base de datos.
-- El código PHP la genera dinámicamente solo para ese usuario.
