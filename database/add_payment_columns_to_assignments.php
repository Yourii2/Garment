<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة أعمدة الدفع لجدول worker_assignments</h3>";
    
    // إضافة عمود is_paid
    $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'is_paid'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN is_paid BOOLEAN DEFAULT FALSE AFTER notes");
        echo "✅ تم إضافة عمود is_paid<br>";
    } else {
        echo "ℹ️ عمود is_paid موجود<br>";
    }
    
    // إضافة عمود cost_per_unit
    $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'cost_per_unit'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN cost_per_unit DECIMAL(10,2) DEFAULT 0 AFTER is_paid");
        echo "✅ تم إضافة عمود cost_per_unit<br>";
    } else {
        echo "ℹ️ عمود cost_per_unit موجود<br>";
    }
    
    echo "<br><strong>✅ تم إضافة أعمدة الدفع بنجاح</strong>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>