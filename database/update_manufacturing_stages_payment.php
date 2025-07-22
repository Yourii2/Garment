<?php
require_once '../config/config.php';

try {
    echo "<h3>تحديث جدول مراحل التصنيع لإضافة معلومات الدفع</h3>";
    
    // إضافة حقل هل المرحلة مدفوعة الأجر
    $stmt = $pdo->query("SHOW COLUMNS FROM manufacturing_stages LIKE 'is_paid'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE manufacturing_stages ADD COLUMN is_paid BOOLEAN DEFAULT TRUE AFTER description");
        echo "✅ تم إضافة حقل is_paid<br>";
    } else {
        echo "ℹ️ حقل is_paid موجود<br>";
    }
    
    // إضافة حقل تكلفة القطعة الواحدة الافتراضية
    $stmt = $pdo->query("SHOW COLUMNS FROM manufacturing_stages LIKE 'default_cost'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE manufacturing_stages ADD COLUMN default_cost DECIMAL(10,2) DEFAULT 0 AFTER is_paid");
        echo "✅ تم إضافة حقل default_cost<br>";
    } else {
        echo "ℹ️ حقل default_cost موجود<br>";
    }
    
    // تحديث المراحل الموجودة بقيم افتراضية
    $pdo->exec("UPDATE manufacturing_stages SET is_paid = TRUE, default_cost = 1.00 WHERE default_cost = 0");
    echo "✅ تم تحديث المراحل الموجودة بقيم افتراضية<br>";
    
    echo "<br><strong>✅ تم تحديث جدول مراحل التصنيع بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>