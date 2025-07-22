<?php
require_once '../config/config.php';

try {
    echo "<h3>فحص مراحل المنتجات</h3>";
    
    // فحص المنتجات ومراحلها
    $stmt = $pdo->query("
        SELECT p.id, p.name, COUNT(ps.id) as stages_count
        FROM products p
        LEFT JOIN product_stages ps ON p.id = ps.product_id
        GROUP BY p.id, p.name
        ORDER BY p.id
    ");
    $products = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>معرف المنتج</th><th>اسم المنتج</th><th>عدد المراحل</th><th>الإجراء</th></tr>";
    
    foreach ($products as $product) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>{$product['stages_count']}</td>";
        
        if ($product['stages_count'] == 0) {
            echo "<td style='color: red;'>لا توجد مراحل - يحتاج إضافة</td>";
        } else {
            echo "<td style='color: green;'>جيد</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // إضافة مراحل افتراضية للمنتجات التي لا تحتوي على مراحل
    echo "<br><h4>إضافة مراحل افتراضية:</h4>";
    
    $stmt = $pdo->query("SELECT id FROM manufacturing_stages ORDER BY id LIMIT 3");
    $default_stages = $stmt->fetchAll();
    
    if (!empty($default_stages)) {
        $stmt = $pdo->query("SELECT id FROM products WHERE id NOT IN (SELECT DISTINCT product_id FROM product_stages WHERE product_id IS NOT NULL)");
        $products_without_stages = $stmt->fetchAll();
        
        foreach ($products_without_stages as $product) {
            $order = 1;
            foreach ($default_stages as $stage) {
                $insert_stmt = $pdo->prepare("INSERT INTO product_stages (product_id, stage_id, stage_order) VALUES (?, ?, ?)");
                $insert_stmt->execute([$product['id'], $stage['id'], $order]);
                $order++;
            }
            echo "✅ تم إضافة مراحل للمنتج رقم {$product['id']}<br>";
        }
    }
    
    echo "<br><strong>✅ تم الانتهاء من فحص وإصلاح مراحل المنتجات</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>