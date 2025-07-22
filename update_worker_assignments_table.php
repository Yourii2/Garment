<?php
require_once 'config/config.php';

try {
    echo "<h3>تحديث جدول worker_assignments</h3>";
    
    // التحقق من وجود عمود started_at
    $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'started_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN started_at TIMESTAMP NULL");
        echo "✅ تم إضافة عمود started_at<br>";
    } else {
        echo "ℹ️ عمود started_at موجود<br>";
    }
    
    // التحقق من وجود عمود completed_at
    $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'completed_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN completed_at TIMESTAMP NULL");
        echo "✅ تم إضافة عمود completed_at<br>";
    } else {
        echo "ℹ️ عمود completed_at موجود<br>";
    }
    
    // التحقق من وجود عمود quantity_defective
    $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'quantity_defective'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN quantity_defective INT DEFAULT 0");
        echo "✅ تم إضافة عمود quantity_defective<br>";
    } else {
        echo "ℹ️ عمود quantity_defective موجود<br>";
    }
    
    // التحقق من وجود عمود quantity_transferred
    $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'quantity_transferred'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN quantity_transferred INT DEFAULT 0");
        echo "✅ تم إضافة عمود quantity_transferred<br>";
    } else {
        echo "ℹ️ عمود quantity_transferred موجود<br>";
    }

    echo "<br><strong>✅ تم تحديث جدول worker_assignments بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
