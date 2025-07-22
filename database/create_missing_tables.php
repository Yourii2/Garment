<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء الجداول المفقودة...</h3>";
    
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
            INDEX idx_cutting_order (cutting_order_id),
            INDEX idx_product (product_id),
            INDEX idx_sent_by (sent_by)
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
            INDEX idx_from_assignment (from_assignment_id),
            INDEX idx_to_worker (to_worker_id)
        )
    ");
    echo "✅ جدول worker_transfers<br>";
    
    // تحديث جدول manufacturing_stages لإضافة حقل is_paid
    $stmt = $pdo->query("SHOW COLUMNS FROM manufacturing_stages LIKE 'is_paid'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE manufacturing_stages ADD COLUMN is_paid BOOLEAN DEFAULT TRUE AFTER description");
        echo "✅ تم إضافة عمود is_paid لجدول manufacturing_stages<br>";
    } else {
        echo "ℹ️ عمود is_paid موجود في manufacturing_stages<br>";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM manufacturing_stages LIKE 'default_cost'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE manufacturing_stages ADD COLUMN default_cost DECIMAL(10,2) DEFAULT 0 AFTER is_paid");
        echo "✅ تم إضافة عمود default_cost لجدول manufacturing_stages<br>";
    } else {
        echo "ℹ️ عمود default_cost موجود في manufacturing_stages<br>";
    }
    
    // تحديث جدول stage_worker_assignments لإضافة معلومات الأجر
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'is_paid'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN is_paid BOOLEAN DEFAULT TRUE AFTER status");
        echo "✅ تم إضافة عمود is_paid لجدول stage_worker_assignments<br>";
    } else {
        echo "ℹ️ عمود is_paid موجود في stage_worker_assignments<br>";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'cost_per_piece'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN cost_per_piece DECIMAL(10,2) DEFAULT 0 AFTER is_paid");
        echo "✅ تم إضافة عمود cost_per_piece لجدول stage_worker_assignments<br>";
    } else {
        echo "ℹ️ عمود cost_per_piece موجود في stage_worker_assignments<br>";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'total_cost'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN total_cost DECIMAL(10,2) DEFAULT 0 AFTER cost_per_piece");
        echo "✅ تم إضافة عمود total_cost لجدول stage_worker_assignments<br>";
    } else {
        echo "ℹ️ عمود total_cost موجود في stage_worker_assignments<br>";
    }
    
    // إنشاء جدول production_stages إذا لم يكن موجود
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS production_stages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cutting_order_id INT NOT NULL,
            stage_id INT NOT NULL,
            quantity_required INT NOT NULL,
            status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_cutting_order (cutting_order_id),
            INDEX idx_stage (stage_id),
            UNIQUE KEY unique_cutting_stage (cutting_order_id, stage_id)
        )
    ");
    echo "✅ جدول production_stages<br>";
    
    echo "<br><strong>✅ تم إنشاء وتحديث الجداول بنجاح!</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>


