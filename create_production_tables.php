<?php
require_once 'config/config.php';

try {
    // جدول أوامر القص
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cutting_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cutting_number VARCHAR(50) UNIQUE NOT NULL,
            product_id INT NOT NULL,
            fabric_id INT NOT NULL,
            quantity_ordered INT NOT NULL,
            fabric_used DECIMAL(10,2) NOT NULL,
            cutting_date DATE NOT NULL,
            notes TEXT,
            status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (fabric_id) REFERENCES fabric_types(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");

    // جدول توزيع القطع على العمال
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS worker_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cutting_order_id INT NOT NULL,
            worker_id INT NOT NULL,
            stage_id INT NOT NULL,
            quantity_assigned INT NOT NULL,
            quantity_completed INT DEFAULT 0,
            assigned_date DATE NOT NULL,
            completion_date DATE NULL,
            status ENUM('assigned', 'in_progress', 'completed') DEFAULT 'assigned',
            notes TEXT,
            FOREIGN KEY (cutting_order_id) REFERENCES cutting_orders(id),
            FOREIGN KEY (worker_id) REFERENCES users(id),
            FOREIGN KEY (stage_id) REFERENCES manufacturing_stages(id)
        )
    ");

    // جدول المنتجات المنتهية
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS finished_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cutting_order_id INT NOT NULL,
            quantity INT NOT NULL,
            completion_date DATE NOT NULL,
            quality_check ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cutting_order_id) REFERENCES cutting_orders(id)
        )
    ");

    echo "✅ تم إنشاء جداول الإنتاج بنجاح";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>