<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جداول فواتير المبيعات...</h3>";
    
    // جدول فواتير الإرسال
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sales_invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_number VARCHAR(50) UNIQUE NOT NULL,
            invoice_date DATE NOT NULL,
            total_quantity INT NOT NULL DEFAULT 0,
            total_items INT NOT NULL DEFAULT 0,
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            created_by INT NOT NULL,
            confirmed_by INT NULL,
            confirmed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id),
            FOREIGN KEY (confirmed_by) REFERENCES users(id)
        )
    ");
    echo "✅ جدول sales_invoices<br>";
    
    // جدول عناصر الفاتورة
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sales_invoice_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT NOT NULL,
            cutting_order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity_sent INT NOT NULL,
            quantity_received INT DEFAULT 0,
            quality_grade ENUM('A', 'B', 'C') NOT NULL,
            notes TEXT,
            status ENUM('pending', 'received', 'partial') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (invoice_id) REFERENCES sales_invoices(id) ON DELETE CASCADE,
            FOREIGN KEY (cutting_order_id) REFERENCES cutting_orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )
    ");
    echo "✅ جدول sales_invoice_items<br>";
    
    // جدول الإشعارات
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type ENUM('sales_invoice', 'inventory_alert', 'production_update') NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            reference_id INT NULL,
            reference_type VARCHAR(50) NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✅ جدول notifications<br>";
    
    echo "<br><strong>✅ تم إنشاء جميع الجداول بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>