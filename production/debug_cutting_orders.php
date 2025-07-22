<?php
require_once '../config/config.php';

echo "<h3>تشخيص أوامر القص</h3>";

try {
    // عرض جميع أوامر القص
    $stmt = $pdo->query("
        SELECT co.id, co.cutting_number, co.status, co.quantity_ordered, p.name as product_name
        FROM cutting_orders co
        LEFT JOIN products p ON co.product_id = p.id
        ORDER BY co.id DESC
        LIMIT 10
    ");
    $orders = $stmt->fetchAll();
    
    echo "<h4>أوامر القص الموجودة:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>رقم الأمر</th><th>المنتج</th><th>الكمية</th><th>الحالة</th></tr>";
    
    foreach ($orders as $order) {
        echo "<tr>";
        echo "<td>" . $order['id'] . "</td>";
        echo "<td>" . $order['cutting_number'] . "</td>";
        echo "<td>" . $order['product_name'] . "</td>";
        echo "<td>" . $order['quantity_ordered'] . "</td>";
        echo "<td>" . $order['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // عرض الحالات المختلفة
    $stmt = $pdo->query("SELECT DISTINCT status FROM cutting_orders");
    $statuses = $stmt->fetchAll();
    echo "<h4>الحالات الموجودة:</h4>";
    foreach ($statuses as $status) {
        echo "- " . $status['status'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>