<?php
require_once '../config/config.php';

try {
    // التحقق من وجود الأعمدة وإضافتها إذا لم تكن موجودة
    
    // فحص عمود unit_cost
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_movements LIKE 'unit_cost'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inventory_movements ADD COLUMN unit_cost DECIMAL(10,2) DEFAULT 0");
        echo "تم إضافة عمود unit_cost<br>";
    }
    
    // فحص عمود total_cost
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_movements LIKE 'total_cost'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inventory_movements ADD COLUMN total_cost DECIMAL(10,2) DEFAULT 0");
        echo "تم إضافة عمود total_cost<br>";
    }
    
    // فحص عمود reference_type
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_movements LIKE 'reference_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inventory_movements ADD COLUMN reference_type VARCHAR(50) DEFAULT NULL");
        echo "تم إضافة عمود reference_type<br>";
    }
    
    // فحص عمود reference_id
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_movements LIKE 'reference_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inventory_movements ADD COLUMN reference_id INT DEFAULT NULL");
        echo "تم إضافة عمود reference_id<br>";
    }
    
    // فحص عمود branch_id
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_movements LIKE 'branch_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inventory_movements ADD COLUMN branch_id INT DEFAULT NULL");
        $pdo->exec("ALTER TABLE inventory_movements ADD FOREIGN KEY (branch_id) REFERENCES branches(id)");
        echo "تم إضافة عمود branch_id<br>";
    }
    
    echo "تم تحديث جدول inventory_movements بنجاح";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
