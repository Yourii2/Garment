<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>تشخيص مشاكل الصفحات</h1>";

// فحص الصفحات الأساسية
$test_pages = [
    'dashboard.php',
    'production/manufacturing_stages.php',
    'inventory/invoices.php',
    'production/create_sales_invoice.php'
];

foreach ($test_pages as $page) {
    echo "<h3>فحص: $page</h3>";
    
    if (!file_exists($page)) {
        echo "<div style='color: red;'>❌ الملف غير موجود</div>";
        continue;
    }
    
    // فحص syntax errors
    $output = shell_exec("php -l $page 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "<div style='color: green;'>✅ لا توجد أخطاء syntax</div>";
    } else {
        echo "<div style='color: red;'>❌ خطأ syntax: $output</div>";
    }
    
    // فحص المحتوى
    $content = file_get_contents($page);
    
    // فحص include paths
    if (preg_match_all('/include\s+[\'"]([^\'\"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $include_path) {
            $full_path = dirname($page) . '/' . $include_path;
            if (!file_exists($full_path)) {
                echo "<div style='color: red;'>❌ ملف include مفقود: $include_path</div>";
            }
        }
    }
    
    // فحص require_once paths
    if (preg_match_all('/require_once\s+[\'"]([^\'\"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $require_path) {
            $full_path = dirname($page) . '/' . $require_path;
            if (!file_exists($full_path)) {
                echo "<div style='color: red;'>❌ ملف require مفقود: $require_path</div>";
            }
        }
    }
    
    echo "<hr>";
}

// فحص الملفات الأساسية
echo "<h3>فحص الملفات الأساسية:</h3>";
$essential_files = [
    'config/config.php',
    'includes/navbar.php',
    'includes/sidebar.php',
    'includes/footer.php'
];

foreach ($essential_files as $file) {
    if (file_exists($file)) {
        echo "<div style='color: green;'>✅ $file موجود</div>";
    } else {
        echo "<div style='color: red;'>❌ $file مفقود</div>";
    }
}
?>