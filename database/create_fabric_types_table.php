<?php
require_once '../config/config.php';

try {
    // إنشاء جدول أنواع الأقمشة
    $create_table = "
    CREATE TABLE IF NOT EXISTS fabric_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_table);
    echo "✅ تم إنشاء جدول fabric_categories بنجاح<br>";
    
    // التحقق من وجود بيانات
    $stmt = $pdo->query("SELECT COUNT(*) FROM fabric_categories");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // إدراج بيانات أولية
        $insert_data = "
        INSERT INTO fabric_categories (name, description) VALUES 
        ('قطن', 'أقمشة القطن الطبيعية'),
        ('حرير', 'أقمشة الحرير الطبيعي والصناعي'),
        ('صوف', 'أقمشة الصوف والخامات الدافئة'),
        ('كتان', 'أقمشة الكتان الطبيعية'),
        ('بوليستر', 'الأقمشة الصناعية'),
        ('مخلوط', 'الأقمشة المخلوطة'),
        ('دانتيل', 'أقمشة الدانتيل والتول'),
        ('جينز', 'أقمشة الجينز والدنيم'),
        ('شيفون', 'أقمشة الشيفون الخفيفة'),
        ('ساتان', 'أقمشة الساتان اللامعة')";
        
        $pdo->exec($insert_data);
        echo "✅ تم إدراج البيانات الأولية بنجاح<br>";
    } else {
        echo "ℹ️ الجدول يحتوي على $count نوع من الأقمشة<br>";
    }
    
    echo "<br><a href='../inventory/fabric_categories.php'>الذهاب إلى صفحة أنواع الأقمشة</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>