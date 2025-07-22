<?php
require_once '../config/config.php';

echo "<h2>إنشاء جدول أوامر الإنتاج</h2>";

try {
    // التحقق من وجود الجدول
    $stmt = $pdo->query("SHOW TABLES LIKE 'production_orders'");
    
    if ($stmt->rowCount() == 0) {
        echo "<p>جاري إنشاء جدول production_orders...</p>";
        
        // إنشاء جدول أوامر الإنتاج
        $sql = "
            CREATE TABLE production_orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(50) UNIQUE NOT NULL,
                product_id INT,
                total_quantity INT NOT NULL,
                fabric_id INT,
                fabric_quantity_used DECIMAL(10,2),
                status ENUM('cutting', 'manufacturing', 'completed', 'cancelled') DEFAULT 'cutting',
                start_date DATE,
                target_completion_date DATE,
                actual_completion_date DATE,
                notes TEXT,
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($sql);
        echo "<div style='color: green;'>✅ تم إنشاء جدول production_orders بنجاح</div>";
        
        // إدراج بيانات تجريبية
        $pdo->exec("
            INSERT INTO production_orders 
            (order_number, total_quantity, status, start_date, target_completion_date, created_by) 
            VALUES 
            ('PRD202400001', 100, 'cutting', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1),
            ('PRD202400002', 50, 'manufacturing', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 1)
        ");
        echo "<div style='color: blue;'>ℹ️ تم إدراج بيانات تجريبية</div>";
        
    } else {
        echo "<div style='color: orange;'>ℹ️ جدول production_orders موجود بالفعل</div>";
    }
    
    echo "<br><a href='production_orders.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>الذهاب إلى صفحة أوامر الإنتاج</a>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
