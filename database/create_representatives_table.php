<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جدول المناديب</h3>";
    
    // إنشاء جدول representatives
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS representatives (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    echo "✅ تم إنشاء جدول representatives<br>";
    
    // إضافة بعض المناديب التجريبيين
    $stmt = $pdo->query("SELECT COUNT(*) FROM representatives");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO representatives (name, phone) VALUES 
            ('أحمد محمد', '01234567890'),
            ('محمد علي', '01234567891'),
            ('علي أحمد', '01234567892')
        ");
        echo "✅ تم إضافة مناديب تجريبيين<br>";
    }
    
    echo "<br><strong>✅ تم إعداد جدول المناديب بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
