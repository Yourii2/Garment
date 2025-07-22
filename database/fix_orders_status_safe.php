<?php
require_once '../config/config.php';

try {
    echo "<h3>إصلاح عمود status في جدول orders بطريقة آمنة</h3>";
    
    // أولاً: فحص القيم الموجودة
    $stmt = $pdo->query("SELECT DISTINCT status FROM orders");
    $existing_statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "القيم الموجودة حال<|im_start|>: " . implode(', ', $existing_statuses) . "<br><br>";
    
    // تنظيف القيم غير الصحيحة
    $pdo->exec("UPDATE orders SET status = 'pending' WHERE status NOT IN ('pending', 'ready', 'in_production', 'completed', 'cancelled')");
    echo "✅ تم تنظيف القيم غير الصحيحة<br>";
    
    // الآن تحديث العمود بأمان
    $pdo->exec("
        ALTER TABLE orders 
        MODIFY COLUMN status ENUM(
            'pending', 
            'ready', 
            'in_production', 
            'completed', 
            'cancelled',
            'ready_for_delivery',
            'out_for_delivery', 
            'delivered', 
            'returned'
        ) DEFAULT 'pending'
    ");
    
    echo "✅ تم تحديث عمود status بنجاح<br>";
    
    // التحقق من النتيجة
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    $column_info = $stmt->fetch();
    echo "<br>تعريف العمود الجديد: " . $column_info['Type'] . "<br>";
    
    echo "<br><strong>✅ تم الإصلاح بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>