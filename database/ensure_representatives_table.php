<?php
require_once '../config/config.php';

try {
    echo "<h3>التأكد من وجود جدول المناديب</h3>";
    
    // فحص وجود الجدول
    $stmt = $pdo->query("SHOW TABLES LIKE 'representatives'");
    if ($stmt->rowCount() == 0) {
        // إنشاء الجدول
        $pdo->exec("
            CREATE TABLE representatives (
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
        
        // إضافة مناديب تجريبيين
        $pdo->exec("
            INSERT INTO representatives (name, phone) VALUES 
            ('مندوب التوصيل الأول', '01000000001'),
            ('مندوب التوصيل الثاني', '01000000002')
        ");
        echo "✅ تم إضافة مناديب تجريبيين<br>";
    } else {
        echo "ℹ️ جدول representatives موجود<br>";
    }
    
    // عدد المناديب
    $stmt = $pdo->query("SELECT COUNT(*) FROM representatives");
    $count = $stmt->fetchColumn();
    echo "عدد المناديب: $count<br>";
    
    echo "<br><strong>✅ جدول المناديب جاهز</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>