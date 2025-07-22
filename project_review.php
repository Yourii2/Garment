<?php
require_once 'config/config.php';

echo "<h2>مراجعة شاملة للمشروع</h2>";

// 1. فحص الجداول الموجودة
echo "<h3>1. الجداول الموجودة:</h3>";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll();
foreach ($tables as $table) {
    $table_name = array_values($table)[0];
    $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM `$table_name`");
    $stmt2->execute();
    $count = $stmt2->fetchColumn();
    echo "- $table_name ($count سجل)<br>";
}

// 2. فحص ملفات المشروع
echo "<h3>2. ملفات المشروع:</h3>";
$directories = [
    'inventory' => 'ملفات المخزون',
    'production' => 'ملفات الإنتاج', 
    'financial' => 'ملفات المالية',
    'hr' => 'ملفات الموارد البشرية',
    'reports' => 'ملفات التقارير',
    'config' => 'ملفات الإعدادات',
    'includes' => 'ملفات مشتركة',
    'assets' => 'ملفات الأصول'
];

foreach ($directories as $dir => $desc) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        $php_files = array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });
        echo "- $desc ($dir): " . count($php_files) . " ملف PHP<br>";
    }
}

// 3. فحص الصفحات الرئيسية
echo "<h3>3. الصفحات الرئيسية:</h3>";
$main_files = [
    'index.php' => 'الصفحة الرئيسية',
    'dashboard.php' => 'لوحة التحكم',
    'login.php' => 'تسجيل الدخول',
    'setup.php' => 'إعداد النظام'
];

foreach ($main_files as $file => $desc) {
    echo "- $desc: " . (file_exists($file) ? '✅ موجود' : '❌ غير موجود') . "<br>";
}

// 4. فحص آخر التحديثات
echo "<h3>4. آخر الملفات المحدثة:</h3>";
$all_files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $all_files[$file->getPathname()] = $file->getMTime();
    }
}
arsort($all_files);
$recent_files = array_slice($all_files, 0, 10, true);
foreach ($recent_files as $file => $time) {
    echo "- $file (" . date('Y-m-d H:i:s', $time) . ")<br>";
}
?>