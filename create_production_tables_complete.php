<?php
require_once 'config/config.php';

try {
    echo "<h3>إنشاء جداول الإنتاج...</h3>";
    
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
    echo "✅ جدول cutting_orders<br>";

    // جدول مراحل الإنتاج لكل أمر قص
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS production_stages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cutting_order_id INT NOT NULL,
            stage_id INT NOT NULL,
            stage_order INT NOT NULL,
            quantity_required INT NOT NULL,
            quantity_assigned INT DEFAULT 0,
            quantity_completed INT DEFAULT 0,
            status ENUM('pending', 'in_progress', 'completed', 'paused') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (cutting_order_id) REFERENCES cutting_orders(id),
            FOREIGN KEY (stage_id) REFERENCES manufacturing_stages(id)
        )
    ");
    echo "✅ جدول production_stages<br>";
    
    // جدول تخصيص العمال للمراحل
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS stage_worker_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            production_stage_id INT NOT NULL,
            worker_id INT NOT NULL,
            quantity_assigned INT NOT NULL,
            quantity_completed INT DEFAULT 0,
            start_time TIMESTAMP NULL,
            end_time TIMESTAMP NULL,
            status ENUM('assigned', 'in_progress', 'completed', 'paused') DEFAULT 'assigned',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (production_stage_id) REFERENCES production_stages(id),
            FOREIGN KEY (worker_id) REFERENCES workers(id)
        )
    ");
    echo "✅ جدول stage_worker_assignments<br>";

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
    echo "✅ جدول finished_products<br>";

    // جدول أوامر الإنتاج
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS production_orders (
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
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (fabric_id) REFERENCES fabric_types(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");
    echo "✅ جدول production_orders<br>";

    echo "<br><strong>✅ تم إنشاء جميع جداول الإنتاج بنجاح!</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>