<?php
require_once '../config/config.php';

try {
    // إضافة عمود current_quantity لجدول fabric_types إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM fabric_types LIKE 'current_quantity'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE fabric_types ADD COLUMN current_quantity DECIMAL(10,2) DEFAULT 0");
        echo "✅ تم إضافة عمود current_quantity لجدول fabric_types<br>";
    } else {
        echo "ℹ️ عمود current_quantity موجود في جدول fabric_types<br>";
    }
    
    // إضافة عمود current_quantity لجدول accessories إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM accessories LIKE 'current_quantity'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE accessories ADD COLUMN current_quantity DECIMAL(10,2) DEFAULT 0");
        echo "✅ تم إضافة عمود current_quantity لجدول accessories<br>";
    } else {
        echo "ℹ️ عمود current_quantity موجود في جدول accessories<br>";
    }
    
    // إضافة عمود current_balance لجدول suppliers إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM suppliers LIKE 'current_balance'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE suppliers ADD COLUMN current_balance DECIMAL(12,2) DEFAULT 0");
        echo "✅ تم إضافة عمود current_balance لجدول suppliers<br>";
    } else {
        echo "ℹ️ عمود current_balance موجود في جدول suppliers<br>";
    }
    
    echo "<br>تم التحديث بنجاح";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>