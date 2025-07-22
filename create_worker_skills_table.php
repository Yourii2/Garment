<?php
require_once 'config/config.php';

try {
    echo "<h3>إنشاء جدول مهارات العمال</h3>";
    
    // إنشاء جدول worker_skills
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS worker_skills (
            id INT PRIMARY KEY AUTO_INCREMENT,
            worker_id INT NOT NULL,
            stage_id INT NOT NULL,
            skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (worker_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (stage_id) REFERENCES manufacturing_stages(id) ON DELETE CASCADE,
            UNIQUE KEY unique_worker_stage (worker_id, stage_id)
        )
    ");
    echo "✅ تم إنشاء جدول worker_skills<br>";
    
    // إضافة مهارات افتراضية لجميع العمال في جميع المراحل
    $stmt = $pdo->query("
        INSERT IGNORE INTO worker_skills (worker_id, stage_id)
        SELECT u.id, ms.id
        FROM users u
        CROSS JOIN manufacturing_stages ms
        WHERE u.role IN ('worker', 'supervisor') 
        AND u.is_active = 1
        AND ms.is_active = 1
    ");
    
    $affected = $stmt->rowCount();
    echo "✅ تم إضافة {$affected} مهارة للعمال<br>";
    
    echo "<br><strong>✅ تم إعداد مهارات العمال بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>