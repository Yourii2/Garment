<?php
require_once 'config/config.php';

echo "<h3>فحص القيم الحالية</h3>";

// فحص قيم fabric_types
echo "<h4>قيم جدول fabric_types:</h4>";
$stmt = $pdo->query("SELECT id, name, current_quantity, min_quantity FROM fabric_types");
$fabrics = $stmt->fetchAll();
foreach ($fabrics as $fabric) {
    echo "- {$fabric['name']}: الكمية الحالية = {$fabric['current_quantity']}, الحد الأدنى = {$fabric['min_quantity']}<br>";
}

// فحص قيم accessories
echo "<h4>قيم جدول accessories:</h4>";
$stmt = $pdo->query("SELECT id, name, current_quantity, min_quantity FROM accessories");
$accessories = $stmt->fetchAll();
foreach ($accessories as $accessory) {
    echo "- {$accessory['name']}: الكمية الحالية = {$accessory['current_quantity']}, الحد الأدنى = {$accessory['min_quantity']}<br>";
}

// تحديث القيم الفارغة
echo "<h4>تحديث القيم الفارغة:</h4>";
$result1 = $pdo->exec("UPDATE fabric_types SET current_quantity = 0 WHERE current_quantity IS NULL");
$result2 = $pdo->exec("UPDATE fabric_types SET min_quantity = 0 WHERE min_quantity IS NULL");
$result3 = $pdo->exec("UPDATE accessories SET current_quantity = 0 WHERE current_quantity IS NULL");
$result4 = $pdo->exec("UPDATE accessories SET min_quantity = 0 WHERE min_quantity IS NULL");

echo "تم تحديث $result1 سجل في fabric_types (current_quantity)<br>";
echo "تم تحديث $result2 سجل في fabric_types (min_quantity)<br>";
echo "تم تحديث $result3 سجل في accessories (current_quantity)<br>";
echo "تم تحديث $result4 سجل في accessories (min_quantity)<br>";
?>