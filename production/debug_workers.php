<?php
require_once '../config/config.php';

echo "<h3>تشخيص مشكلة العمال</h3>";

// التحقق من وجود جدول workers
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'workers'");
    if ($stmt->rowCount() == 0) {
        echo "❌ جدول workers غير موجود<br>";
        exit;
    }
    echo "✅ جدول workers موجود<br>";
    
    // عرض بنية الجدول
    $stmt = $pdo->query("DESCRIBE workers");
    $columns = $stmt->fetchAll();
    echo "<h4>أعمدة جدول workers:</h4>";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
    
    // عرض جميع البيانات
    $stmt = $pdo->query("SELECT * FROM workers");
    $workers = $stmt->fetchAll();
    echo "<h4>جميع العمال في الجدول:</h4>";
    foreach ($workers as $worker) {
        echo "ID: " . $worker['id'] . " - الاسم: " . $worker['name'] . " - نشط: " . ($worker['is_active'] ?? 'غير محدد') . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>