<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة أعمدة التكلفة المفقودة</h3>";
    
    // فحص وإضافة عمود cost_per_unit
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'cost_per_unit'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN cost_per_unit DECIMAL(10,2) DEFAULT 0 AFTER notes");
        echo "✅ تم إضافة عمود cost_per_unit<br>";
    } else {
        echo "ℹ️ عمود cost_per_unit موجود<br>";
    }
    
    // فحص وإضافة عمود is_paid
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'is_paid'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN is_paid BOOLEAN DEFAULT FALSE AFTER cost_per_unit");
        echo "✅ تم إضافة عمود is_paid<br>";
    } else {
        echo "ℹ️ عمود is_paid موجود<br>";
    }
    
    // فحص وإضافة عمود total_cost
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'total_cost'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN total_cost DECIMAL(10,2) DEFAULT 0 AFTER is_paid");
        echo "✅ تم إضافة عمود total_cost<br>";
    } else {
        echo "ℹ️ عمود total_cost موجود<br>";
    }
    
    echo "<br><strong>تم إضافة جميع الأعمدة بنجاح</strong>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>