<?php
require_once '../config/config.php';

try {
    echo "<h3>تحديث جدول تخصيصات العمال لإضافة معلومات الأجر</h3>";
    
    // إضافة حقل هل المهمة مدفوعة الأجر
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'is_paid'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN is_paid BOOLEAN DEFAULT TRUE AFTER status");
        echo "✅ تم إضافة حقل is_paid<br>";
    } else {
        echo "ℹ️ حقل is_paid موجود<br>";
    }
    
    // إضافة حقل تكلفة القطعة الواحدة
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'cost_per_piece'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN cost_per_piece DECIMAL(10,2) DEFAULT 0 AFTER is_paid");
        echo "✅ تم إضافة حقل cost_per_piece<br>";
    } else {
        echo "ℹ️ حقل cost_per_piece موجود<br>";
    }
    
    echo "<br><strong>✅ تم تحديث جدول تخصيصات العمال بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
