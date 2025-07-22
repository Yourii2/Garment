<?php
require_once '../config/config.php';

try {
    echo "<h3>إصلاح الكميات الفارغة</h3>";
    
    // تحديث جميع القيم NULL إلى 0 في جدول fabric_types
    $result = $pdo->exec("UPDATE fabric_types SET current_quantity = 0 WHERE current_quantity IS NULL");
    echo "✅ تم تحديث $result سجل في جدول fabric_types<br>";
    
    // تحديث جميع القيم NULL إلى 0 في جدول accessories
    $result = $pdo->exec("UPDATE accessories SET current_quantity = 0 WHERE current_quantity IS NULL");
    echo "✅ تم تحديث $result سجل في جدول accessories<br>";
    
    // تحديث جميع القيم NULL إلى 0 في جدول suppliers
    $result = $pdo->exec("UPDATE suppliers SET current_balance = 0 WHERE current_balance IS NULL");
    echo "✅ تم تحديث $result سجل في جدول suppliers<br>";
    
    // تعديل الأعمدة لتكون NOT NULL مع قيمة افتراضية
    $pdo->exec("ALTER TABLE fabric_types MODIFY current_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
    echo "✅ تم تعديل عمود current_quantity في fabric_types<br>";
    
    $pdo->exec("ALTER TABLE accessories MODIFY current_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
    echo "✅ تم تعديل عمود current_quantity في accessories<br>";
    
    $pdo->exec("ALTER TABLE suppliers MODIFY current_balance DECIMAL(12,2) NOT NULL DEFAULT 0");
    echo "✅ تم تعديل عمود current_balance في suppliers<br>";
    
    // التحقق من النتائج
    echo "<h4>التحقق من النتائج:</h4>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count, SUM(current_quantity) as total FROM fabric_types");
    $result = $stmt->fetch();
    echo "عدد الأقمشة: {$result['count']}, إجمالي الكميات: {$result['total']}<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count, SUM(current_quantity) as total FROM accessories");
    $result = $stmt->fetch();
    echo "عدد الإكسسوارات: {$result['count']}, إجمالي الكميات: {$result['total']}<br>";
    
    echo "<br><strong>تم إصلاح جميع الأعمدة بنجاح</strong>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>