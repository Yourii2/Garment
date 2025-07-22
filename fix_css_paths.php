<?php
echo "<h1>إصلاح مسارات CSS</h1>";

// إصلاح dashboard.php
$dashboard = file_get_contents('dashboard.php');
$dashboard = str_replace(
    'href="<?= BASE_URL ?>/assets/css/style.css"',
    'href="assets/css/style.css"',
    $dashboard
);
file_put_contents('dashboard.php', $dashboard);
echo "✅ تم إصلاح مسار CSS في dashboard.php<br>";

// إصلاح الصفحات في المجلدات الفرعية
$files_to_fix = [
    'production/manufacturing_stages.php' => '../../assets/css/style.css',
    'production/create_sales_invoice.php' => '../../../assets/css/style.css',
    'inventory/invoices.php' => '../../../assets/css/style.css'
];

foreach ($files_to_fix as $file => $correct_path) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = preg_replace(
            '/href="[^"]*assets\/css\/style\.css"/',
            'href="' . $correct_path . '"',
            $content
        );
        file_put_contents($file, $content);
        echo "✅ تم إصلاح مسار CSS في $file<br>";
    }
}

echo "<br><strong>جرب فتح الصفحات الآن</strong>";
?>