<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>إعداد جداول الطلبيات</h2>";

try {
    // التحقق من وجود جدول customers أولاً
    $stmt = $pdo->query("SHOW TABLES LIKE 'customers'");
    if ($stmt->rowCount() == 0) {
        echo "<p>إنشاء جدول العملاء...</p>";
        $customers_table = "
        CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) UNIQUE,
            email VARCHAR(100),
            address TEXT,
            customer_type ENUM('individual', 'company') DEFAULT 'individual',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";
        $pdo->exec($customers_table);
        echo "✅ تم إنشاء جدول العملاء<br>";
    } else {
        echo "ℹ️ جدول العملاء موجود<br>";
    }
    
    // التحقق من وجود جدول products
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() == 0) {
        echo "<p>إنشاء جدول المنتجات...</p>";
        $products_table = "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            category_id INT,
            price DECIMAL(10,2) DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";
        $pdo->exec($products_table);
        echo "✅ تم إنشاء جدول المنتجات<br>";
        
        // إدراج منتجات تجريبية
        $sample_products = "
        INSERT INTO products (name, code, description, price) VALUES
        ('قميص رجالي', 'SHIRT-M-001', 'قميص رجالي قطني', 150.00),
        ('بنطلون جينز', 'JEANS-001', 'بنطلون جينز كلاسيكي', 200.00),
        ('فستان نسائي', 'DRESS-W-001', 'فستان نسائي أنيق', 300.00),
        ('تي شيرت', 'TSHIRT-001', 'تي شيرت قطني', 80.00),
        ('جاكيت شتوي', 'JACKET-001', 'جاكيت شتوي دافئ', 400.00);
        ";
        $pdo->exec($sample_products);
        echo "✅ تم إدراج منتجات تجريبية<br>";
    } else {
        echo "ℹ️ جدول المنتجات موجود<br>";
    }
    
    // إنشاء جدول الطلبيات الرئيسي
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        echo "<p>إنشاء جدول الطلبيات...</p>";
        $orders_table = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            customer_id INT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_address TEXT,
            status ENUM('pending', 'ready', 'in_production', 'completed', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        );
        ";
        $pdo->exec($orders_table);
        echo "✅ تم إنشاء جدول الطلبيات<br>";
    } else {
        echo "ℹ️ جدول الطلبيات موجود<br>";
    }
    
    // إنشاء جدول عناصر الطلبيات
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() == 0) {
        echo "<p>إنشاء جدول عناصر الطلبيات...</p>";
        $order_items_table = "
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );
        ";
        $pdo->exec($order_items_table);
        echo "✅ تم إنشاء جدول عناصر الطلبيات<br>";
    } else {
        echo "ℹ️ جدول عناصر الطلبيات موجود<br>";
    }
    
    echo "<br><div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>✅ تم إنشاء جميع الجداول بنجاح!</strong><br>";
    echo "يمكنك الآن استخدام نظام الطلبيات.";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='../sales/orders.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>الذهاب إلى صفحة الطلبيات</a>";
    echo "<a href='../index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>العودة للرئيسية</a>";
    echo "</div>";
    
    // عرض إحصائيات الجداول
    echo "<h3>إحصائيات الجداول:</h3>";
    $tables = ['customers', 'products', 'orders', 'order_items'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetchColumn();
            echo "- $table: $count سجل<br>";
        } catch (Exception $e) {
            echo "- $table: غير موجود<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>❌ خطأ في إنشاء الجداول:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}
?>

