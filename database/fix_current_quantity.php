<?php
require_once '../config/config.php';

try {
    // تحديث جميع القيم NULL إلى 0 في جدول fabric_types
    $pdo->exec("UPDATE fabric_types SET current_quantity = 0 WHERE current_quantity IS NULL");
    echo "✅ تم تحديث القيم الفارغة في جدول fabric_types<br>";
    
    // تحديث جميع القيم NULL إلى 0 في جدول accessories
    $pdo->exec("UPDATE accessories SET current_quantity = 0 WHERE current_quantity IS NULL");
    echo "✅ تم تحديث القيم الفارغة في جدول accessories<br>";
    
    // تحديث جميع القيم NULL إلى 0 في جدول suppliers
    $pdo->exec("UPDATE suppliers SET current_balance = 0 WHERE current_balance IS NULL");
    echo "✅ تم تحديث القيم الفارغة في جدول suppliers<br>";
    
    // تعديل الأعمدة لتكون NOT NULL مع قيمة افتراضية
    $pdo->exec("ALTER TABLE fabric_types MODIFY current_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
    echo "✅ تم تعديل عمود current_quantity في fabric_types<br>";
    
    $pdo->exec("ALTER TABLE accessories MODIFY current_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
    echo "✅ تم تعديل عمود current_quantity في accessories<br>";
    
    $pdo->exec("ALTER TABLE suppliers MODIFY current_balance DECIMAL(12,2) NOT NULL DEFAULT 0");
    echo "✅ تم تعديل عمود current_balance في suppliers<br>";
    
    echo "<br>تم إصلاح جميع الأعمدة بنجاح";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>