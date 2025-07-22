<?php
require_once 'config/config.php';

echo "<h2>اختبار الاتصال بقاعدة البيانات</h2>";

try {
    // اختبار الاتصال
    echo "✅ الاتصال بقاعدة البيانات ناجح<br>";
    
    // فحص الجداول الموجودة
    echo "<h3>الجداول الموجودة:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
    // فحص جدول fabric_types
    echo "<h3>محتويات جدول fabric_types:</h3>";
    $stmt = $pdo->query("SELECT * FROM fabric_types ORDER BY id DESC LIMIT 5");
    $fabrics = $stmt->fetchAll();
    if (empty($fabrics)) {
        echo "❌ الجدول فارغ<br>";
    } else {
        foreach ($fabrics as $fabric) {
            echo "ID: {$fabric['id']}, الاسم: {$fabric['name']}, الكود: {$fabric['code']}<br>";
        }
    }
    
    // فحص جدول accessories
    echo "<h3>محتويات جدول accessories:</h3>";
    $stmt = $pdo->query("SELECT * FROM accessories ORDER BY id DESC LIMIT 5");
    $accessories = $stmt->fetchAll();
    if (empty($accessories)) {
        echo "❌ الجدول فارغ<br>";
    } else {
        foreach ($accessories as $accessory) {
            echo "ID: {$accessory['id']}, الاسم: {$accessory['name']}, الكود: {$accessory['code']}<br>";
        }
    }
    
    // اختبار إدراج مباشر
    echo "<h3>اختبار إدراج مباشر:</h3>";
    $testName = "قماش تجريبي " . date('Y-m-d H:i:s');
    $testCode = "TEST" . time();
    
    $stmt = $pdo->prepare("INSERT INTO fabric_types (name, code, unit, current_quantity, created_at) VALUES (?, ?, 'متر', 0, NOW())");
    $result = $stmt->execute([$testName, $testCode]);
    
    if ($result) {
        echo "✅ تم إدراج البيانات التجريبية بنجاح - ID: " . $pdo->lastInsertId() . "<br>";
    } else {
        echo "❌ فشل في إدراج البيانات التجريبية<br>";
        print_r($pdo->errorInfo());
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "<br>";
}
?>