<?php
require_once '../config/config.php';

echo "<h3>إصلاح جدول حركات المخزون</h3>";

try {
    // فحص عمود type
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_movements LIKE 'type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inventory_movements ADD COLUMN type ENUM('in', 'out') NOT NULL AFTER id");
        echo "✅ تم إضافة عمود type<br>";
    } else {
        echo "ℹ️ عمود type موجود<br>";
    }
    
    // تحديث البيانات الموجودة إذا كانت فارغة
    $stmt = $pdo->query("SELECT COUNT(*) FROM inventory_movements WHERE type IS NULL OR type = ''");
    $null_count = $stmt->fetchColumn();
    
    if ($null_count > 0) {
        // تعيين قيمة افتراضية للحركات الفارغة
        $pdo->exec("UPDATE inventory_movements SET type = 'out' WHERE type IS NULL OR type = ''");
        echo "✅ تم تحديث $null_count حركة بقيمة افتراضية<br>";
    }
    
    // عرض عينة من البيانات
    echo "<h4>عينة من حركات المخزون:</h4>";
    $stmt = $pdo->query("
        SELECT im.id, im.type, im.quantity, 
               COALESCE(ft.name, a.name, 'غير محدد') as item_name,
               im.created_at
        FROM inventory_movements im
        LEFT JOIN fabric_types ft ON im.fabric_id = ft.id
        LEFT JOIN accessories a ON im.accessory_id = a.id
        ORDER BY im.created_at DESC 
        LIMIT 5
    ");
    $movements = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>النوع</th><th>الصنف</th><th>الكمية</th><th>التاريخ</th></tr>";
    foreach ($movements as $movement) {
        echo "<tr>";
        echo "<td>" . $movement['id'] . "</td>";
        echo "<td>" . ($movement['type'] === 'in' ? 'إدخال' : 'إخراج') . "</td>";
        echo "<td>" . htmlspecialchars($movement['item_name']) . "</td>";
        echo "<td>" . $movement['quantity'] . "</td>";
        echo "<td>" . $movement['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><strong>✅ تم إصلاح جدول حركات المخزون</strong>";
    echo "<br><a href='../dashboard.php'>العودة للوحة التحكم</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>