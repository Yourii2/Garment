<?php
require_once '../config/config.php';

try {
    echo "<h3>إصلاح الأعمدة المفقودة</h3>";
    
    // إضافة عمود description لجدول sizes إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM sizes LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sizes ADD COLUMN description TEXT NULL AFTER code");
        echo "✅ تم إضافة عمود description لجدول sizes<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول sizes<br>";
    }
    
    // إضافة عمود sort_order لجدول sizes إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM sizes LIKE 'sort_order'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sizes ADD COLUMN sort_order INT DEFAULT 0 AFTER description");
        echo "✅ تم إضافة عمود sort_order لجدول sizes<br>";
    } else {
        echo "ℹ️ عمود sort_order موجود في جدول sizes<br>";
    }
    
    // إضافة عمود description لجدول branches إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM branches LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE branches ADD COLUMN description TEXT NULL AFTER address");
        echo "✅ تم إضافة عمود description لجدول branches<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول branches<br>";
    }
    
    // إضافة عمود description لجدول fabric_types إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM fabric_types LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE fabric_types ADD COLUMN description TEXT NULL AFTER name");
        echo "✅ تم إضافة عمود description لجدول fabric_types<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول fabric_types<br>";
    }
    
    // إضافة عمود description لجدول accessories إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM accessories LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE accessories ADD COLUMN description TEXT NULL AFTER name");
        echo "✅ تم إضافة عمود description لجدول accessories<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول accessories<br>";
    }
    
    // إضافة عمود description لجدول products إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL AFTER name");
        echo "✅ تم إضافة عمود description لجدول products<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول products<br>";
    }
    
    // إضافة عمود description لجدول manufacturing_stages إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM manufacturing_stages LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE manufacturing_stages ADD COLUMN description TEXT NULL AFTER name");
        echo "✅ تم إضافة عمود description لجدول manufacturing_stages<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول manufacturing_stages<br>";
    }
    
    // إضافة عمود specialization لجدول workers إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM workers LIKE 'specialization'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE workers ADD COLUMN specialization VARCHAR(100) NULL AFTER salary_amount");
        echo "✅ تم إضافة عمود specialization لجدول workers<br>";
    } else {
        echo "ℹ️ عمود specialization موجود في جدول workers<br>";
    }
    
    // إضافة عمود description لجدول workers إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM workers LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE workers ADD COLUMN description TEXT NULL AFTER specialization");
        echo "✅ تم إضافة عمود description لجدول workers<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول workers<br>";
    }
    
    // إضافة عمود notes لجدول customers إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM customers LIKE 'notes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN notes TEXT NULL AFTER customer_type");
        echo "✅ تم إضافة عمود notes لجدول customers<br>";
    } else {
        echo "ℹ️ عمود notes موجود في جدول customers<br>";
    }
    
    // إضافة عمود description لجدول customers إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM customers LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN description TEXT NULL AFTER notes");
        echo "✅ تم إضافة عمود description لجدول customers<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول customers<br>";
    }
    
    // إضافة عمود notes لجدول suppliers إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM suppliers LIKE 'notes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE suppliers ADD COLUMN notes TEXT NULL AFTER current_balance");
        echo "✅ تم إضافة عمود notes لجدول suppliers<br>";
    } else {
        echo "ℹ️ عمود notes موجود في جدول suppliers<br>";
    }
    
    // إضافة عمود description لجدول suppliers إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM suppliers LIKE 'description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE suppliers ADD COLUMN description TEXT NULL AFTER notes");
        echo "✅ تم إضافة عمود description لجدول suppliers<br>";
    } else {
        echo "ℹ️ عمود description موجود في جدول suppliers<br>";
    }
    
    // التحقق من وجود جدول system_settings وإضافة الإعدادات المطلوبة
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    if ($stmt->rowCount() == 0) {
        // إنشاء جدول system_settings
        $pdo->exec("
            CREATE TABLE system_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "✅ تم إنشاء جدول system_settings<br>";
        
        // إضافة الإعدادات الأساسية
        $default_settings = [
            ['system_name', 'نظام إدارة مصنع الملابس', 'اسم النظام'],
            ['company_name', 'مصنع الملابس', 'اسم الشركة'],
            ['currency', 'EGP', 'العملة المستخدمة'],
            ['currency_symbol', 'ج.م', 'رمز العملة'],
            ['timezone', 'Africa/Cairo', 'المنطقة الزمنية'],
            ['items_per_page', '20', 'عدد العناصر في الصفحة'],
            ['low_stock_threshold', '10', 'حد التنبيه للمخزون المنخفض']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        foreach ($default_settings as $setting) {
            $stmt->execute($setting);
        }
        echo "✅ تم إضافة الإعدادات الأساسية<br>";
    } else {
        echo "ℹ️ جدول system_settings موجود<br>";
        
        // إضافة currency_symbol إذا لم يكن موجود
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = 'currency_symbol'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES ('currency_symbol', 'ج.م', 'رمز العملة')");
            $stmt->execute();
            echo "✅ تم إضافة إعداد currency_symbol<br>";
        }
    }
    
    echo "<br><strong>✅ تم إصلاح جميع الأعمدة المفقودة بنجاح</strong>";
    echo "<br><a href='../dashboard.php' class='btn btn-primary'>العودة للوحة التحكم</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
    echo "<br>تفاصيل الخطأ: " . $e->getTraceAsString();
}
$page_title = 'Fix missing columns';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? "صفحة" ?> - <?= SYSTEM_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    

<?php include '../includes/footer.php'; ?>
</body>
</html>