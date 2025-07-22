<?php
require_once '../config/config.php';

echo "<h3>تشخيص المهام المكتملة</h3>";

try {
    // فحص جميع المهام
    echo "<h4>1. جميع المهام في النظام:</h4>";
    $stmt = $pdo->query("
        SELECT 
            swa.id,
            swa.status,
            swa.quantity_assigned,
            swa.quantity_completed,
            w.name as worker_name,
            ms.name as stage_name
        FROM stage_worker_assignments swa
        LEFT JOIN workers w ON swa.worker_id = w.id
        LEFT JOIN production_stages ps ON swa.production_stage_id = ps.id
        LEFT JOIN manufacturing_stages ms ON ps.stage_id = ms.id
        ORDER BY swa.id DESC
    ");
    $all_tasks = $stmt->fetchAll();
    
    if (empty($all_tasks)) {
        echo "❌ لا توجد مهام في النظام<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>الحالة</th><th>العامل</th><th>المرحلة</th><th>الكمية المخصصة</th><th>الكمية المكتملة</th></tr>";
        foreach ($all_tasks as $task) {
            echo "<tr>";
            echo "<td>{$task['id']}</td>";
            echo "<td>{$task['status']}</td>";
            echo "<td>{$task['worker_name']}</td>";
            echo "<td>{$task['stage_name']}</td>";
            echo "<td>{$task['quantity_assigned']}</td>";
            echo "<td>{$task['quantity_completed']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // فحص المهام المكتملة فقط
    echo "<h4>2. المهام بحالة 'completed':</h4>";
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM stage_worker_assignments 
        WHERE status = 'completed'
    ");
    $completed_count = $stmt->fetch()['count'];
    echo "عدد المهام المكتملة: {$completed_count}<br>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
