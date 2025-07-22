<?php
require_once 'config/config.php';

try {
    echo "<h3>إنشاء جداول المبيعات...</h3>";
    
    // جدول المنتجات المرسلة للمبيعات
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sales_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cutting_order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity_sent INT NOT NULL,
            quality_grade ENUM('A', 'B', 'C') NOT NULL,
            send_date DATE NOT NULL,
            notes TEXT,
            sent_by INT NOT NULL,
            status ENUM('ready_for_sale', 'sold', 'returned') DEFAULT 'ready_for_sale',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cutting_order_id) REFERENCES cutting_orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (sent_by) REFERENCES users(id)
        )
    ");
    echo "✅ جدول sales_products<br>";
    
    // جدول تحويلات العمال
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS worker_transfers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            from_assignment_id INT NOT NULL,
            to_worker_id INT NOT NULL,
            quantity_transferred INT NOT NULL,
            transfer_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            created_by INT NOT NULL,
            FOREIGN KEY (from_assignment_id) REFERENCES stage_worker_assignments(id),
            FOREIGN KEY (to_worker_id) REFERENCES workers(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");
    echo "✅ جدول worker_transfers<br>";
    
    echo "<br><strong>✅ تم إنشاء جداول المبيعات بنجاح!</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>