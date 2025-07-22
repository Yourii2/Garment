<?php
require_once '../config/config.php';

try {
    // التحقق من وجود جدول sizes
    $stmt = $pdo->query("SHOW TABLES LIKE 'sizes'");
    if ($stmt->rowCount() == 0) {
        echo "جدول sizes غير موجود. سيتم إنشاؤه الآن...<br>";
        
        $create_table = "
        CREATE TABLE sizes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            code VARCHAR(20) UNIQUE NOT NULL,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($create_table);
        echo "تم إنشاء جدول sizes بنجاح<br>";
    } else {
        echo "جدول sizes موجود<br>";
    }
    
    // عرض بنية الجدول
    $stmt = $pdo->query("DESCRIBE sizes");
    $columns = $stmt->fetchAll();
    
    echo "<h3>بنية جدول sizes:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>العمود</th><th>النوع</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // عرض البيانات الموجودة
    $stmt = $pdo->query("SELECT * FROM sizes");
    $sizes = $stmt->fetchAll();
    
    echo "<h3>البيانات الموجودة (" . count($sizes) . " مقاس):</h3>";
    if (count($sizes) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>الاسم</th><th>الكود</th><th>الترتيب</th><th>نشط</th></tr>";
        foreach ($sizes as $size) {
            echo "<tr>";
            echo "<td>" . $size['id'] . "</td>";
            echo "<td>" . $size['name'] . "</td>";
            echo "<td>" . $size['code'] . "</td>";
            echo "<td>" . $size['sort_order'] . "</td>";
            echo "<td>" . ($size['is_active'] ? 'نعم' : 'لا') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "لا توجد مقاسات";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>