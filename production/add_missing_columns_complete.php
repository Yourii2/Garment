<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة الأعمدة المفقودة لجدول stage_worker_assignments</h3>";
    
    // إضافة عمود completed_at
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'completed_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN completed_at TIMESTAMP NULL AFTER end_time");
        echo "✅ تم إضافة عمود completed_at<br>";
    } else {
        echo "ℹ️ عمود completed_at موجود<br>";
    }
    
    // إضافة عمود assigned_at إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'assigned_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN assigned_at TIMESTAMP NULL AFTER created_at");
        echo "✅ تم إضافة عمود assigned_at<br>";
    } else {
        echo "ℹ️ عمود assigned_at موجود<br>";
    }
    
    echo "<br><strong>✅ تم إضافة جميع الأعمدة بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>