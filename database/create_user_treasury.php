<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جدول خزينة المستخدم</h3>";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_treasury (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_id INT NULL,
            amount DECIMAL(10,2) NOT NULL,
            type ENUM('income', 'expense') NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_order_id (order_id)
        )
    ");
    
    echo "✅ تم إنشاء جدول user_treasury بنجاح<br>";
    echo "<br><strong>✅ جدول الخزينة جاهز</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>