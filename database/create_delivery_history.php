<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جدول تاريخ التوصيل</h3>";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS delivery_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            status ENUM('delivered', 'returned', 'postponed') NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order_id (order_id)
        )
    ");
    
    echo "✅ تم إنشاء جدول delivery_history بنجاح<br>";
    echo "<br><strong>✅ جدول تاريخ التوصيل جاهز</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>