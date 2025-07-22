<?php
require_once '../config/config.php';

// تحقق من المهام المكتملة
$stmt = $pdo->query("
    SELECT 
        wa.id,
        wa.quantity_completed,
        wa.quantity_transferred,
        wa.quantity_finished,
        w.full_name as worker_name,
        ms.name as stage_name,
        co.cutting_number
    FROM worker_assignments wa
    JOIN workers w ON wa.worker_id = w.id
    JOIN manufacturing_stages ms ON wa.stage_id = ms.id
    JOIN cutting_orders co ON wa.cutting_order_id = co.id
    WHERE wa.status = 'completed'
    ORDER BY wa.id DESC
    LIMIT 10
");

echo "<h3>المهام المكتملة:</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>العامل</th><th>المرحلة</th><th>الأمر</th><th>مكتمل</th><th>منقول</th><th>منتهي</th><th>متاح للنقل</th><th>متاح للإنهاء</th></tr>";

while ($row = $stmt->fetch()) {
    $available_transfer = $row['quantity_completed'] - ($row['quantity_transferred'] ?? 0);
    $available_finish = $row['quantity_completed'] - ($row['quantity_finished'] ?? 0);
    
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['worker_name']}</td>";
    echo "<td>{$row['stage_name']}</td>";
    echo "<td>{$row['cutting_number']}</td>";
    echo "<td>{$row['quantity_completed']}</td>";
    echo "<td>" . ($row['quantity_transferred'] ?? 0) . "</td>";
    echo "<td>" . ($row['quantity_finished'] ?? 0) . "</td>";
    echo "<td>{$available_transfer}</td>";
    echo "<td>{$available_finish}</td>";
    echo "</tr>";
}
echo "</table>";
?>