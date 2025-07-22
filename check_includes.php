<?php
echo "<h1>فحص ملفات includes</h1>";

$include_files = [
    'includes/navbar.php',
    'includes/sidebar.php', 
    'includes/footer.php'
];

foreach ($include_files as $file) {
    echo "<h3>محتوى: $file</h3>";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
        echo "<hr>";
    } else {
        echo "الملف غير موجود<br><hr>";
    }
}
?>