<?php
require_once '../config/config.php';

// التحقق من وجود العمال
$stmt = $pdo->query("SELECT COUNT(*) as count FROM workers WHERE is_active = 1");
$count = $stmt->fetch()['count'];

echo "عدد العمال النشطين: " . $count . "<br>";

if ($count == 0) {
    echo "لا يوجد عمال نشطين. إضافة عامل تجريبي...<br>";
    $pdo->exec("INSERT INTO workers (name, phone, is_active) VALUES ('عامل تجريبي', '123456789', 1)");
    echo "تم إضافة عامل تجريبي<br>";
}

$stmt = $pdo->query("SELECT id, name FROM workers WHERE is_active = 1");
$workers = $stmt->fetchAll();

echo "<h3>قائمة العمال:</h3>";
foreach ($workers as $worker) {
    echo "ID: " . $worker['id'] . " - الاسم: " . $worker['name'] . "<br>";
}
?>