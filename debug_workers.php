<?php
require_once 'config/config.php';

echo "<h3>فحص العمال في النظام</h3>";

try {
    // فحص العمال في جدول workers
    $stmt = $pdo->query("
        SELECT id, name, phone, is_active 
        FROM workers 
        ORDER BY name
    ");
    $workers = $stmt->fetchAll();
    
    echo "<h4>العمال في جدول workers:</h4>";
    if (empty($workers)) {
        echo "❌ لا يوجد عمال في جدول workers<br>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>الاسم</th><th>الهاتف</th><th>نشط</th></tr>";
        foreach ($workers as $worker) {
            echo "<tr>";
            echo "<td>{$worker['id']}</td>";
            echo "<td>{$worker['name']}</td>";
            echo "<td>{$worker['phone']}</td>";
            echo "<td>" . ($worker['is_active'] ? 'نعم' : 'لا') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // اختبار استعلام get_product_workers
    echo "<h4>اختبار استعلام العمال:</h4>";
    $stmt = $pdo->query("
        SELECT id, name as full_name 
        FROM workers 
        WHERE is_active = 1
        ORDER BY name
    ");
    $workers = $stmt->fetchAll();
    
    echo "عدد العمال المسترجعين: " . count($workers) . "<br>";
    echo "JSON: " . json_encode($workers) . "<br>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
