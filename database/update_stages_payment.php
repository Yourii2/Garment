<?php
require_once '../config/config.php';

try {
    echo "<h3>تحديث جدول المراحل لإضافة معلومات الدفع</h3>";
    
    // إضافة حقل هل المرحلة مدفوعة الأجر
    $pdo->exec("
        ALTER TABLE manufacturing_stages 
        ADD COLUMN is_paid BOOLEAN DEFAULT TRUE AFTER description
    ");
    echo "✅ تم إضافة حقل is_paid<br>";
    
    // إضافة حقل تكلفة القطعة الواحدة
    $pdo->exec("
        ALTER TABLE manufacturing_stages 
        ADD COLUMN cost_per_unit DECIMAL(10,2) DEFAULT 0 AFTER is_paid
    ");
    echo "✅ تم إضافة حقل cost_per_unit<br>";
    
    // تحديث المراحل الموجودة بقيم افتراضية
    $pdo->exec("
        UPDATE manufacturing_stages 
        SET is_paid = TRUE, cost_per_unit = 1.00 
        WHERE cost_per_unit = 0
    ");
    echo "✅ تم تحديث المراحل الموجودة بقيم افتراضية<br>";
    
    echo "<br><strong>✅ تم تحديث جدول المراحل بنجاح</strong>";
    
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "⚠️ الحقول موجودة مسبق<|im_start|>فذ<br>";
    } else {
        echo "❌ خطأ: " . $e->getMessage();
    }
}
?>