<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جداول الخزائن</h3>";
    
    // جدول الخزائن
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS treasuries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            current_balance DECIMAL(15,2) DEFAULT 0.00,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_active (is_active)
        )
    ");
    
    // جدول معاملات الخزائن
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS treasury_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            treasury_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            type ENUM('income', 'expense', 'transfer_in', 'transfer_out') NOT NULL,
            description TEXT,
            reference_type VARCHAR(50),
            reference_id INT,
            from_treasury_id INT NULL,
            to_treasury_id INT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (treasury_id) REFERENCES treasuries(id),
            FOREIGN KEY (from_treasury_id) REFERENCES treasuries(id),
            FOREIGN KEY (to_treasury_id) REFERENCES treasuries(id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX idx_treasury_id (treasury_id),
            INDEX idx_type (type),
            INDEX idx_created_at (created_at)
        )
    ");
    
    // إدراج خزينة افتراضية
    $stmt = $pdo->query("SELECT COUNT(*) FROM treasuries");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO treasuries (name, description, current_balance) 
            VALUES ('الخزينة الرئيسية', 'الخزينة الافتراضية للنظام', 0.00)
        ");
    }
    
    echo "✅ تم إنشاء جداول الخزائن بنجاح<br>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>