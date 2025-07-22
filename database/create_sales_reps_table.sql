-- إنشاء جدول المناديب المحدث
CREATE TABLE IF NOT EXISTS sales_reps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    payment_type ENUM('salary_only', 'commission_only', 'salary_commission') NOT NULL DEFAULT 'commission_only',
    salary_type ENUM('daily', 'weekly', 'monthly') NULL,
    salary_amount DECIMAL(10,2) DEFAULT 0,
    commission_type ENUM('percentage', 'fixed_amount') NULL,
    commission_value DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
