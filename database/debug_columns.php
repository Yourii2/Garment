<?php
require_once '../config/config.php';

echo "<h3>فحص الأعمدة في الجداول</h3>";

$tables_to_check = ['sizes', 'branches', 'fabric_types', 'accessories', 'products', 'manufacturing_stages', 'workers', 'customers', 'suppliers'];

foreach ($tables_to_check as $table) {
    try {
        echo "<h4>جدول $table:</h4>";
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
        }
        echo "<br>";
        
    } catch (Exception $e) {
        echo "❌ خطأ في جدول $table: " . $e->getMessage() . "<br><br>";
    }
}
?>