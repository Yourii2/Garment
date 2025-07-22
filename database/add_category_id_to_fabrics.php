<?php
require_once '../config/config.php';

try {
    // التحقق من وجود عمود category_id
    $stmt = $pdo->query("SHOW COLUMNS FROM fabric_types LIKE 'category_id'");
    if ($stmt->rowCount() == 0) {
        // إضافة عمود category_id إلى جدول fabric_types
        $pdo->exec("ALTER TABLE fabric_types ADD COLUMN category_id INT NULL");
        echo "✅ تم إضافة عمود category_id بنجاح<br>";
        
        // إضافة مفتاح خارجي
        $pdo->exec("ALTER TABLE fabric_types ADD FOREIGN KEY (category_id) REFERENCES fabric_categories(id) ON DELETE SET NULL");
        echo "✅ تم إضافة المفتاح الخارجي بنجاح<br>";
    } else {
        echo "ℹ️ عمود category_id موجود بالفعل<br>";
    }
    
    echo "<br><a href='../inventory/fabrics.php'>الذهاب إلى صفحة الأقمشة</a>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
