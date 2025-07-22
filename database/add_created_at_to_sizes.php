<?php
require_once '../config/config.php';

try {
    // إضافة عمود created_at إلى جدول sizes
    $pdo->exec("ALTER TABLE sizes ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "تم إضافة عمود created_at بنجاح";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>