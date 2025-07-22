<?php
echo "<h1>إصلاح مسارات CSS الصحيحة</h1>";

// إصلاح production/manufacturing_stages.php
if (file_exists('production/manufacturing_stages.php')) {
    $content = file_get_contents('production/manufacturing_stages.php');
    
    // استبدال المسار الخطأ بالصحيح
    $content = str_replace(
        'href="<?= BASE_URL ?>/../../assets/css/style.css"',
        'href="<?= BASE_URL ?>/assets/css/style.css"',
        $content
    );
    
    // أو إذا كان بدون BASE_URL
    $content = str_replace(
        'href="/../../assets/css/style.css"',
        'href="../assets/css/style.css"',
        $content
    );
    
    file_put_contents('production/manufacturing_stages.php', $content);
    echo "✅ تم إصلاح manufacturing_stages.php<br>";
}

// إصلاح production/create_sales_invoice.php
if (file_exists('production/create_sales_invoice.php')) {
    $content = file_get_contents('production/create_sales_invoice.php');
    
    $content = str_replace(
        'href="../../../assets/css/style.css"',
        'href="../assets/css/style.css"',
        $content
    );
    
    file_put_contents('production/create_sales_invoice.php', $content);
    echo "✅ تم إصلاح create_sales_invoice.php<br>";
}

// إصلاح inventory/invoices.php
if (file_exists('inventory/invoices.php')) {
    $content = file_get_contents('inventory/invoices.php');
    
    $content = str_replace(
        'href="../../../assets/css/style.css"',
        'href="../../assets/css/style.css"',
        $content
    );
    
    file_put_contents('inventory/invoices.php', $content);
    echo "✅ تم إصلاح invoices.php<br>";
}

echo "<br><strong>الآن جرب فتح الصفحات - يجب أن تكون القائمة الجانبية متسقة</strong>";
?>