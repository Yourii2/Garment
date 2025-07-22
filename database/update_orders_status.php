<?php
require_once '../config/config.php';

try {
    echo "<h3>تحديث عمود status في جدول orders</h3>";
    
    // تحديث عمود status ليشمل حالات التوصيل الجديدة
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
    
    // عرض القيم المسموحة الجديدة
    echo "<br><strong>القيم المسموحة الآن:</strong><br>";
    echo "- pending (في الانتظار)<br>";
    echo "- ready (جاهز)<br>";
    echo "- in_production (قيد الإنتاج)<br>";
    echo "- completed (مكتمل)<br>";
    echo "- cancelled (ملغي)<br>";
    echo "- ready_for_delivery (جاهز للتوصيل)<br>";
    echo "- out_for_delivery (في الطريق)<br>";
    echo "- delivered (تم التوصيل)<br>";
    echo "- returned (مرتجع)<br>";
    
    echo "<br><strong>✅ تم التحديث بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>