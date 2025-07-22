<?php
require_once '../config/config.php';

try {
    echo "<h3>إصلاح جدول المقاسات</h3>";
    
    // التحقق من وجود عمود description
    $stmt = $pdo->query("SHOW COLUMNS FROM sizes LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sizes ADD COLUMN description TEXT NULL AFTER code");
        echo "✅ تم إضافة عمود description<br>";
    } else {
        echo "ℹ️ عمود description موجود<br>";
    }
    
    // التحقق من وجود عمود sort_order
    $stmt = $pdo->query("SHOW COLUMNS FROM sizes LIKE 'sort_order'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sizes ADD COLUMN sort_order INT DEFAULT 0 AFTER description");
        echo "✅ تم إضافة عمود sort_order<br>";
    } else {
        echo "ℹ️ عمود sort_order موجود<br>";
    }
    
    // التحقق من وجود عمود is_active
    $stmt = $pdo->query("SHOW COLUMNS FROM sizes LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sizes ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER sort_order");
        echo "✅ تم إضافة عمود is_active<br>";
    } else {
        echo "ℹ️ عمود is_active موجود<br>";
    }
    
    // التحقق من وجود عمود created_at
    $stmt = $pdo->query("SHOW COLUMNS FROM sizes LIKE 'created_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sizes ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active");
        echo "✅ تم إضافة عمود created_at<br>";
    } else {
        echo "ℹ️ عمود created_at موجود<br>";
    }
    
    // عرض هيكل الجدول النهائي
    echo "<h4>هيكل جدول sizes النهائي:</h4>";
    $stmt = $pdo->query("DESCRIBE sizes");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>العمود</th><th>النوع</th><th>Null</th><th>Key</th><th>Default</th></tr>";
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
    
    echo "<br><br>";
    echo "<a href='../inventory/sizes.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>الذهاب إلى صفحة المقاسات</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>