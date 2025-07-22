<?php
require_once '../config/config.php';

try {
    echo "<h3>إصلاح جدول production_stages</h3>";
    
    // فحص هيكل الجدول الحالي
    $stmt = $pdo->query("DESCRIBE production_stages");
    $columns = $stmt->fetchAll();
    
    echo "<h4>الأعمدة الحالية:</h4>";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}<br>";
    }
    
    // التحقق من وجود عمود status وتعديله إذا لزم الأمر
    $has_status = false;
    $has_stage_order = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] == 'status') {
            $has_status = true;
        }
        if ($column['Field'] == 'stage_order') {
            $has_stage_order = true;
        }
    }
    
    if (!$has_status) {
        // إضافة عمود status إذا لم يكن موجود
        $pdo->exec("ALTER TABLE production_stages ADD COLUMN status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending'");
        echo "✅ تم إضافة عمود status<br>";
    } else {
        // تحديث عمود status ليتضمن القيم الصحيحة
        $pdo->exec("ALTER TABLE production_stages MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending'");
        echo "✅ تم تحديث عمود status<br>";
    }
    
    // إضافة قيمة افتراضية لعمود stage_order إذا لم تكن موجودة
    if ($has_stage_order) {
        $pdo->exec("ALTER TABLE production_stages MODIFY COLUMN stage_order INT DEFAULT 1");
        echo "✅ تم تحديث عمود stage_order ليحتوي على قيمة افتراضية<br>";
        
        // تحديث القيم الفارغة
        $pdo->exec("UPDATE production_stages SET stage_order = 1 WHERE stage_order IS NULL OR stage_order = 0");
        echo "✅ تم تحديث القيم الفارغة في stage_order<br>";
    }
    
    echo "<br><strong>✅ تم إصلاح الجدول بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
