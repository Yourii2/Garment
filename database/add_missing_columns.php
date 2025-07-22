<?php
require_once '../config/config.php';

try {
    echo "<h3>إضافة الأعمدة المفقودة</h3>";
    
    // إضافة عمود quantity_defective
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'quantity_defective'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN quantity_defective INT DEFAULT 0 AFTER quantity_completed");
        echo "✅ تم إضافة عمود quantity_defective<br>";
    } else {
        echo "ℹ️ عمود quantity_defective موجود<br>";
    }
    
    // إضافة عمود quantity_transferred
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_worker_assignments LIKE 'quantity_transferred'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE stage_worker_assignments ADD COLUMN quantity_transferred INT DEFAULT 0 AFTER quantity_defective");
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
    
    // التحقق من وجود جدول worker_assignments وإضافة الأعمدة المفقودة
    $stmt = $pdo->query("SHOW TABLES LIKE 'worker_assignments'");
    if ($stmt->rowCount() > 0) {
        // إضافة الأعمدة المفقودة لجدول worker_assignments
        $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'quantity_defective'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN quantity_defective INT DEFAULT 0 AFTER quantity_completed");
            echo "✅ تم إضافة عمود quantity_defective لجدول worker_assignments<br>";
        }
        
        $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'quantity_transferred'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN quantity_transferred INT DEFAULT 0 AFTER quantity_defective");
            echo "✅ تم إضافة عمود quantity_transferred لجدول worker_assignments<br>";
        }
        
        $stmt = $pdo->query("SHOW COLUMNS FROM worker_assignments LIKE 'quantity_finished'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE worker_assignments ADD COLUMN quantity_finished INT DEFAULT 0 AFTER quantity_transferred");
            echo "✅ تم إضافة عمود quantity_finished لجدول worker_assignments<br>";
        }
    }
    
    echo "<br><strong>✅ تم إضافة جميع الأعمدة المفقودة بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>