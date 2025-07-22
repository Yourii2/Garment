<?php
require_once 'config/config.php';

echo "<h3>فحص هيكل الجداول</h3>";

// فحص جدول fabric_types
echo "<h4>جدول fabric_types:</h4>";
try {
    $stmt = $pdo->query("DESCRIBE fabric_types");
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM fabric_types");
    $count = $stmt->fetchColumn();
    echo "عدد السجلات: $count<br><br>";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "<br><br>";
}

// فحص جدول accessories
echo "<h4>جدول accessories:</h4>";
try {
    $stmt = $pdo->query("DESCRIBE accessories");
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM accessories");
    $count = $stmt->fetchColumn();
    echo "عدد السجلات: $count<br><br>";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "<br><br>";
}

// فحص الجداول الموجودة
echo "<h4>جميع الجداول:</h4>";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll();
foreach ($tables as $table) {
    echo "- " . array_values($table)[0] . "<br>";
}
?>