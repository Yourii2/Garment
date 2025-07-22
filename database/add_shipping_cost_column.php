<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة عمود مصروفات الشحن</h3>";
    
    // فحص بنية جدول orders أولاً
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    echo "<h4>أعمدة جدول orders الحالية:</h4>";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
    }
    
    // فحص وجود عمود shipping_cost
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'shipping_cost'");
    if ($stmt->rowCount() == 0) {
        // إضافة العمود بعد آخر عمود موجود
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(10,2) DEFAULT 0 AFTER notes");
        echo "✅ تم إضافة عمود shipping_cost<br>";
    } else {
        echo "ℹ️ عمود shipping_cost موجود<br>";
    }
    
    // فحص وجود عمود unit_price في order_items
    $stmt = $pdo->query("SHOW COLUMNS FROM order_items LIKE 'unit_price'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN unit_price DECIMAL(10,2) DEFAULT 0 AFTER quantity");
        echo "✅ تم إضافة عمود unit_price في order_items<br>";
    } else {
        echo "ℹ️ عمود unit_price موجود في order_items<br>";
    }
    
    // إضافة عمود delivery_notes إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'delivery_notes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN delivery_notes TEXT AFTER shipping_cost");
        echo "✅ تم إضافة عمود delivery_notes<br>";
    } else {
        echo "ℹ️ عمود delivery_notes موجود<br>";
    }
    
    // إضافة عمود representative_id إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'representative_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN representative_id INT NULL AFTER customer_address");
        echo "✅ تم إضافة عمود representative_id<br>";
    } else {
        echo "ℹ️ عمود representative_id موجود<br>";
    }
    
    echo "<br><strong>✅ تم إعداد جميع الأعمدة المطلوبة</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
