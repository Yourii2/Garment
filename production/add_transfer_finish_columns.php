<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة أعمدة النقل والإنهاء</h3>";
    
    // إضافة عمود quantity_transferred
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'quantity_transferred'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN quantity_transferred INT DEFAULT 0 AFTER quantity_completed");
        echo "✅ تم إضافة عمود quantity_transferred<br>";
    } else {
        echo "ℹ️ عمود quantity_transferred موجود<br>";
    }
    
    // إضافة عمود quantity_finished
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'quantity_finished'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN quantity_finished INT DEFAULT 0 AFTER quantity_transferred");
        echo "✅ تم إضافة عمود quantity_finished<br>";
    } else {
        echo "ℹ️ عمود quantity_finished موجود<br>";
    }
    
    echo "<br><strong>✅ تم إضافة جميع الأعمدة بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>