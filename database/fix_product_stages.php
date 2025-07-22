<?php
require_once '../config/config.php';

try {
    echo "<h3>إصلاح جدول product_stages</h3>";
    
    // إضافة عمود stage_order إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM product_stages LIKE 'stage_order'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE product_stages ADD COLUMN stage_order INT DEFAULT 1 AFTER stage_id");
        echo "✅ تم إضافة عمود stage_order<br>";
        
        // تحديث الترتيب للمراحل الموجودة
        $pdo->exec("UPDATE product_stages SET stage_order = id");
        echo "✅ تم تحديث ترتيب المراحل الموجودة<br>";
    } else {
        echo "ℹ️ عمود stage_order موجود<br>";
    }
    
    // إضافة عمود product_code في جدول products إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'product_code'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN product_code VARCHAR(50) AFTER name");
        echo "✅ تم إضافة عمود product_code<br>";
        
        // تحديث الكود للمنتجات الموجودة
        $stmt = $pdo->query("SELECT id, code FROM products WHERE product_code IS NULL");
        $products = $stmt->fetchAll();
        foreach ($products as $product) {
            $code = $product['code'] ?? 'PRD' . str_pad($product['id'], 4, '0', STR_PAD_LEFT);
            $update_stmt = $pdo->prepare("UPDATE products SET product_code = ? WHERE id = ?");
            $update_stmt->execute([$code, $product['id']]);
        }
        echo "✅ تم تحديث أكواد المنتجات الموجودة<br>";
    } else {
        echo "ℹ️ عمود product_code موجود<br>";
    }
    
    echo "<br><strong>✅ تم إصلاح الجداول بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>