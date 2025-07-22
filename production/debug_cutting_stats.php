<?php
require_once '../config/config.php';

echo "<h3>فحص إحصائيات أوامر القص</h3>";

try {
    // فحص أوامر القص
    $stmt = $pdo->query("SELECT id, cutting_number, quantity_ordered, status FROM cutting_orders WHERE status = 'active'");
    $orders = $stmt->fetchAll();
    
    echo "<h4>أوامر القص النشطة:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>رقم الأمر</th><th>الكمية المطلوبة</th><th>الحالة</th></tr>";
    
    $total_ordered = 0;
    foreach ($orders as $order) {
        echo "<tr>";
        echo "<td>{$order['id']}</td>";
        echo "<td>{$order['cutting_number']}</td>";
        echo "<td>{$order['quantity_ordered']}</td>";
        echo "<td>{$order['status']}</td>";
        echo "</tr>";
        $total_ordered += $order['quantity_ordered'];
    }
    echo "</table>";
    echo "<p><strong>إجمالي الكميات المطلوبة: {$total_ordered}</strong></p>";
    
    // فحص تخصيصات العمال
    echo "<h4>تخصيصات العمال:</h4>";
    $stmt = $pdo->query("
        SELECT 
            co.cutting_number,
            swa.status,
            SUM(swa.quantity_assigned) as assigned,
            SUM(swa.quantity_completed) as completed
        FROM cutting_orders co
        JOIN production_stages ps ON co.id = ps.cutting_order_id
        JOIN stage_worker_assignments swa ON ps.id = swa.production_stage_id
        WHERE co.status = 'active'
        GROUP BY co.id, swa.status
        ORDER BY co.cutting_number, swa.status
    ");
    $assignments = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>رقم الأمر</th><th>حالة التخصيص</th><th>الكمية المخصصة</th><th>الكمية المكتملة</th></tr>";
    
    $total_completed = 0;
    $total_in_progress = 0;
    $total_paused = 0;
    
    foreach ($assignments as $assignment) {
        echo "<tr>";
        echo "<td>{$assignment['cutting_number']}</td>";
        echo "<td>{$assignment['status']}</td>";
        echo "<td>{$assignment['assigned']}</td>";
        echo "<td>{$assignment['completed']}</td>";
        echo "</tr>";
        
        if ($assignment['status'] == 'completed') {
            $total_completed += $assignment['completed'];
        } elseif ($assignment['status'] == 'in_progress') {
            $total_in_progress += $assignment['assigned'];
        } elseif ($assignment['status'] == 'paused') {
            $total_paused += $assignment['assigned'];
        }
    }
    echo "</table>";
    
    echo "<h4>الإحصائيات النهائية:</h4>";
    echo "<p>إجمالي الكميات المطلوبة: {$total_ordered}</p>";
    echo "<p>إجمالي المكتمل: {$total_completed}</p>";
    echo "<p>إجمالي قيد العمل: {$total_in_progress}</p>";
    echo "<p>إجمالي المعلق: {$total_paused}</p>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>