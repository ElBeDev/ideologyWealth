-- Create deposits table
CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trx VARCHAR(50) UNIQUE NOT NULL,
    gateway VARCHAR(100) NOT NULL,
    amount DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
    charge DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
    final_amount DECIMAL(28, 8) NOT NULL DEFAULT 0.00000000,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
