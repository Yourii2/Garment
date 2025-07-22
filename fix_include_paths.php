<?php
set_time_limit(300);

$fixed_count = 0;
$error_count = 0;

function fixIncludePaths($filepath) {
    global $fixed_count, $error_count;
    
    $content = file_get_contents($filepath);
    if ($content === false) {
        $error_count++;
        return;
    }
    
    $original_content = $content;
    $dir = dirname($filepath);
    
    // إصلاح مسارات include للملفات في المجلدات الفرعية
    if ($dir !== '.') {
        // إصلاح config path
        $content = preg_replace(
            '/require_once\s+[\'"]config\/config\.php[\'"]/',
            "require_once '../config/config.php'",
            $content
        );
        
        // إصلاح navbar path
        $content = preg_replace(
            '/include\s+[\'"]includes\/navbar\.php[\'"]/',
            "include '../includes/navbar.php'",
            $content
        );
        
        // إصلاح sidebar path
        $content = preg_replace(
            '/include\s+[\'"]includes\/sidebar\.php[\'"]/',
            "include '../includes/sidebar.php'",
            $content
        );
        
        // إصلاح footer path
        $content = preg_replace(
            '/include\s+[\'"]includes\/footer\.php[\'"]/',
            "include '../includes/footer.php'",
            $content
        );
    }
    
    if ($content !== $original_content) {
        if (file_put_contents($filepath, $content)) {
            $fixed_count++;
            echo "✅ تم إصلاح: $filepath<br>";
        } else {
            $error_count++;
            echo "❌ فشل في حفظ: $filepath<br>";
        }
    }
}

echo "<h1>إصلاح مسارات Include</h1>";

// البحث في جميع ملفات PHP
$directories = ['production', 'inventory', 'financial', 'hr', 'reports', 'admin'];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*.php');
        foreach ($files as $file) {
            fixIncludePaths($file);
        }
    }
}

echo "<hr>";
echo "<h3>النتائج:</h3>";
echo "تم إصلاح: $fixed_count ملف<br>";
echo "أخطاء: $error_count ملف<br>";

if ($fixed_count > 0) {
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
    echo "تم إصلاح مسارات include. جرب فتح الصفحات الآن.";
    echo "</div>";
}
?>