<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة عمود description إلى جدول branches</h3>";
    
    // فحص وجود عمود description
    $stmt = $pdo->query("SHOW COLUMNS FROM branches LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE branches ADD COLUMN description TEXT NULL AFTER address");
        echo "✅ تم إضافة عمود description<br>";
    } else {
        echo "ℹ️ عمود description موجود<br>";
    }
    
    echo "<br><strong>✅ تم الانتهاء</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>