-- إنشاء جدول معاملات العمال
CREATE TABLE IF NOT EXISTS worker_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    worker_id INT NOT NULL,
    transaction_type ENUM('salary', 'bonus', 'deduction', 'advance', 'overtime', 'piece_work') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transaction_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;