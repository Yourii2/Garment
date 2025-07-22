<?php
require_once '../config/config.php';

try {
    // التحقق من وجود الأعمدة وإضافتها إذا لم تكن موجودة
    
    // فحص عمود location
    $stmt = $pdo->query("SHOW COLUMNS FROM branches LIKE 'location'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE branches ADD COLUMN location VARCHAR(255) DEFAULT NULL");
        echo "تم إضافة عمود location<br>";
    }
    
    // فحص عمود manager
    $stmt = $pdo->query("SHOW COLUMNS FROM branches LIKE 'manager'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE branches ADD COLUMN manager VARCHAR(100) DEFAULT NULL");
        echo "تم إضافة عمود manager<br>";
    }
    
    // فحص عمود phone
    $stmt = $pdo->query("SHOW COLUMNS FROM branches LIKE 'phone'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE branches ADD COLUMN phone VARCHAR(20) DEFAULT NULL");
        echo "تم إضافة عمود phone<br>";
    }
    
    // فحص عمود created_at
    $stmt = $pdo->query("SHOW COLUMNS FROM branches LIKE 'created_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE branches ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "تم إضافة عمود created_at<br>";
    }
    
    echo "تم تحديث جدول branches بنجاح";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>