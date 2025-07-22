<?php
require_once '../config/config.php';

try {
    // التحقق من وجود عمود quantity_finished أولاً
    $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'quantity_finished'");
    
    if ($stmt->rowCount() == 0) {
        // إضافة العمود إذا لم يكن موجود
        $pdo->exec("
            ALTER TABLE worker_assignments 
            ADD COLUMN quantity_finished INT DEFAULT 0
        ");
        echo "✅ تم إضافة عمود quantity_finished بنجاح";
    } else {
        echo "ℹ️ عمود quantity_finished موجود بالفعل";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
