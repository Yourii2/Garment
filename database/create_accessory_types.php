<?php
require_once '../config/config.php';

try {
    // إنشاء جدول أنواع الإكسسوارات
    $create_table = "
    CREATE TABLE IF NOT EXISTS accessory_types (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_table);
    echo "✅ تم إنشاء جدول accessory_types بنجاح<br>";
    
    // التحقق من وجود بيانات
    $stmt = $pdo->query("SELECT COUNT(*) FROM accessory_types");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // إدراج بيانات أولية
        $insert_data = "
        INSERT INTO accessory_types (name, description) VALUES 
        ('أزرار', 'جميع أنواع الأزرار المختلفة'),
        ('سحابات', 'السحابات والسوست'),
        ('خيوط', 'خيوط الحياكة والتطريز'),
        ('شرائط', 'الشرائط والأربطة'),
        ('دانتيل', 'الدانتيل والكروشيه'),
        ('كلف', 'الكلف والزخارف'),
        ('أحجار', 'الأحجار والخرز'),
        ('أخرى', 'أنواع أخرى من الإكسسوارات')";
        
        $pdo->exec($insert_data);
        echo "✅ تم إدراج البيانات الأولية بنجاح<br>";
    } else {
        echo "ℹ️ الجدول يحتوي على $count نوع من الإكسسوارات<br>";
    }
    
    // عرض البيانات الموجودة
    echo "<h3>أنواع الإكسسوارات الموجودة:</h3>";
    $stmt = $pdo->query("SELECT * FROM accessory_types ORDER BY name");
    $types = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>الاسم</th><th>الوصف</th><th>تاريخ الإضافة</th></tr>";
    foreach ($types as $type) {
        echo "<tr>";
        echo "<td>" . $type['id'] . "</td>";
        echo "<td>" . htmlspecialchars($type['name']) . "</td>";
        echo "<td>" . htmlspecialchars($type['description']) . "</td>";
        echo "<td>" . $type['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><a href='../inventory/accessory_types.php'>الذهاب إلى صفحة أنواع الإكسسوارات</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>