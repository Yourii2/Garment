<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة أعمدة الكميات</h3>";
    
    // التحقق من وجود عمود current_quantity في جدول fabric_types
    $stmt = $pdo->query("SHOW COLUMNS FROM fabric_types LIKE 'current_quantity'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE fabric_types ADD COLUMN current_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
        echo "✅ تم إضافة عمود current_quantity لجدول fabric_types<br>";
    } else {
        echo "ℹ️ عمود current_quantity موجود في جدول fabric_types<br>";
    }
    
    // التحقق من وجود عمود current_quantity في جدول accessories
    $stmt = $pdo->query("SHOW COLUMNS FROM accessories LIKE 'current_quantity'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE accessories ADD COLUMN current_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
        echo "✅ تم إضافة عمود current_quantity لجدول accessories<br>";
    } else {
        echo "ℹ️ عمود current_quantity موجود في جدول accessories<br>";
    }
    
    // التحقق من وجود عمود min_quantity في جدول fabric_types
    $stmt = $pdo->query("SHOW COLUMNS FROM fabric_types LIKE 'min_quantity'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE fabric_types ADD COLUMN min_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
        echo "✅ تم إضافة عمود min_quantity لجدول fabric_types<br>";
    } else {
        echo "ℹ️ عمود min_quantity موجود في جدول fabric_types<br>";
    }
    
    // التحقق من وجود عمود min_quantity في جدول accessories
    $stmt = $pdo->query("SHOW COLUMNS FROM accessories LIKE 'min_quantity'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE accessories ADD COLUMN min_quantity DECIMAL(10,2) NOT NULL DEFAULT 0");
        echo "✅ تم إضافة عمود min_quantity لجدول accessories<br>";
    } else {
        echo "ℹ️ عمود min_quantity موجود في جدول accessories<br>";
    }
    
    // عرض هيكل الجداول للتأكد
    echo "<h4>هيكل جدول fabric_types:</h4>";
    $stmt = $pdo->query("DESCRIBE fabric_types");
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    
    echo "<h4>هيكل جدول accessories:</h4>";
    $stmt = $pdo->query("DESCRIBE accessories");
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    
    echo "<br><strong>تم إضافة جميع الأعمدة بنجاح</strong>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>