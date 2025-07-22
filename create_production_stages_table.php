<?php
require_once 'config/config.php';

try {
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
    
    echo "✅ تم إنشاء جداول مراحل الإنتاج بنجاح";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>