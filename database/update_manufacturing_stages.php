<?php
require_once '../config/config.php';

try {
    // التحقق من وجود عمود created_at وإضافته إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM manufacturing_stages LIKE 'created_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE manufacturing_stages ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "تم إضافة عمود created_at إلى جدول manufacturing_stages<br>";
    } else {
        echo "عمود created_at موجود بالفعل في جدول manufacturing_stages<br>";
    }
    
    // التحقق من وجود عمود updated_at وإضافته إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM manufacturing_stages LIKE 'updated_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE manufacturing_stages ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "تم إضافة عمود updated_at إلى جدول manufacturing_stages<br>";
    } else {
        echo "عمود updated_at موجود بالفعل في جدول manufacturing_stages<br>";
    }
    
    echo "<br>تم تحديث جدول manufacturing_stages بنجاح";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>