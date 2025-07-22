<?php
require_once 'config/config.php';

// فحص هيكل الجداول
echo "<h3>فحص جدول inventory_movements:</h3>";
$stmt = $pdo->query("DESCRIBE inventory_movements");
$columns = $stmt->fetchAll();
foreach ($columns as $column) {
    echo $column['Field'] . " - " . $column['Type'] . "<br>";
}

echo "<h3>فحص جدول fabric_types:</h3>";
$stmt = $pdo->query("DESCRIBE fabric_types");
$columns = $stmt->fetchAll();
foreach ($columns as $column) {
    echo $column['Field'] . " - " . $column['Type'] . "<br>";
}

echo "<h3>فحص جدول accessories:</h3>";
$stmt = $pdo->query("DESCRIBE accessories");
$columns = $stmt->fetchAll();
foreach ($columns as $column) {
    echo $column['Field'] . " - " . $column['Type'] . "<br>";
}

// فحص البيانات الموجودة
echo "<h3>عدد الأقمشة:</h3>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM fabric_types");
$count = $stmt->fetch();
echo "عدد الأقمشة: " . $count['count'] . "<br>";

echo "<h3>عدد الإكسسوارات:</h3>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM accessories");
$count = $stmt->fetch();
echo "عدد الإكسسوارات: " . $count['count'] . "<br>";

echo "<h3>عدد حركات المخزون:</h3>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_movements");
$count = $stmt->fetch();
echo "عدد الحركات: " . $count['count'] . "<br>";

// اختبار إدراج بسيط
echo "<h3>اختبار إدراج قماش:</h3>";
try {
    $stmt = $pdo->prepare("INSERT INTO fabric_types (name, code, unit, current_quantity, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute(['قماش تجريبي', 'TEST001', 'متر', 0]);
    if ($result) {
        echo "تم إدراج القماش التجريبي بنجاح<br>";
        echo "ID: " . $pdo->lastInsertId() . "<br>";
    } else {
        echo "فشل في إدراج القماش<br>";
    }
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "<br>";
}
?>