<?php
echo "<h1>تطبيق الستايل الموحد على جميع الصفحات</h1>";

// قائمة الصفحات المراد تحديثها
$pages = [
    'dashboard.php',
    'production/manufacturing_stages.php',
    'inventory/invoices.php',
    'production/view_sales_invoice.php',
    'production/sales_invoices.php'
];

foreach ($pages as $page) {
    if (file_exists($page)) {
        $content = file_get_contents($page);
        
        // استبدال رابط CSS القديم بالجديد
        $old_css = 'href="[^"]*style\.css"';
        $new_css = 'href="' . (strpos($page, '/') ? '../' : '') . 'assets/css/unified-style.css"';
        
        $content = preg_replace('/' . $old_css . '/', $new_css, $content);
        
        // إضافة Bootstrap إذا لم يكن موجود
        if (strpos($content, 'bootstrap@5.1.3') === false) {
            $bootstrap_css = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">';
            $content = str_replace('<link href="https://cdnjs.cloudflare.com', $bootstrap_css . "\n    " . '<link href="https://cdnjs.cloudflare.com', $content);
        }
        
        file_put_contents($page, $content);
        echo "✅ تم تحديث $page<br>";
    } else {
        echo "❌ $page غير موجود<br>";
    }
}

echo "<br><strong>تم تطبيق الستايل الموحد على جميع الصفحات!</strong>";
?>