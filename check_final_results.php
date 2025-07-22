<?php
/**
 * فحص النتائج النهائية بعد تشغيل جميع سكربتات الإصلاح
 */

set_time_limit(60);

$directories = ['.', 'production', 'inventory', 'financial', 'hr', 'reports', 'admin', 'warehouse', 'sales'];
$excluded_files = ['config.php', 'database.php', 'functions.php', 'permissions.php', 'fix_all_pages.php', 'fix_all_pages_v2.php', 'fix_all_pages_v3.php', 'debug_files.php', 'install.php', 'setup.php', 'check_final_results.php'];

function scanDirectory($dir) {
    $files = [];
    if (!is_dir($dir)) return $files;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

// جمع جميع الملفات
$all_files = [];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $files = scanDirectory($dir);
        foreach ($files as $file) {
            if (!in_array(basename($file), $excluded_files)) {
                $all_files[] = $file;
            }
        }
    }
}

$total_files = count($all_files);
$fixed_files = 0;
$ui_files_without_html = 0;
$ui_files_with_html = 0;
$non_ui_files = 0;

echo "<!DOCTYPE html>";
echo '<html lang="ar" dir="rtl">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>فحص النتائج النهائية</title>';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">';
echo '</head>';
echo '<body class="container mt-4">';

echo "<h1>📊 تقرير النتائج النهائية</h1>";
echo "<div class='alert alert-info'>إجمالي الملفات: <strong>$total_files</strong></div>";

echo "<h3>🔍 تحليل تفصيلي:</h3>";

foreach ($all_files as $file) {
    $content = file_get_contents($file);
    
    // فحص عناصر UI
    $hasUIElements = (
        preg_match('/<html/i', $content) ||
        preg_match('/<!DOCTYPE/i', $content) ||
        preg_match('/<head>/i', $content) ||
        preg_match('/<body>/i', $content) ||
        preg_match('/bootstrap/i', $content) ||
        preg_match('/container-fluid/i', $content) ||
        preg_match('/include.*navbar/i', $content) ||
        preg_match('/include.*sidebar/i', $content) ||
        preg_match('/include.*header/i', $content) ||
        preg_match('/btn btn-/i', $content) ||
        preg_match('/class=".*col-/i', $content) ||
        preg_match('/fa-[a-z-]+/i', $content)
    );
    
    if (!$hasUIElements) {
        $non_ui_files++;
        continue;
    }
    
    // فحص إذا كان محدث بـ lang="ar" dir="rtl"
    $isFixed = preg_match('/<html[^>]*lang="ar"[^>]*dir="rtl"/i', $content);
    
    if ($isFixed) {
        $fixed_files++;
        $ui_files_with_html++;
    } else {
        // فحص إذا كان يحتوي على HTML structure
        $hasHTMLStructure = preg_match('/<html/i', $content);
        
        if ($hasHTMLStructure) {
            echo "<div class='alert alert-warning'>⚠️ <strong>$file</strong> - يحتوي على HTML لكن غير محدث</div>";
        } else {
            $ui_files_without_html++;
            echo "<div class='alert alert-danger'>❌ <strong>$file</strong> - يحتوي على UI elements لكن بدون HTML structure</div>";
        }
    }
}

echo "<div class='row mt-4'>";
echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='card-title text-success'>✅ ملفات محدثة</h5>";
echo "<h2 class='text-success'>$fixed_files</h2>";
echo "</div></div></div>";

echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='card-title text-danger'>❌ UI بدون HTML</h5>";
echo "<h2 class='text-danger'>$ui_files_without_html</h2>";
echo "</div></div></div>";

echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='card-title text-primary'>📄 UI مع HTML</h5>";
echo "<h2 class='text-primary'>$ui_files_with_html</h2>";
echo "</div></div></div>";

echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='card-title text-muted'>📝 ملفات عادية</h5>";
echo "<h2 class='text-muted'>$non_ui_files</h2>";
echo "</div></div></div>";

echo "</div>";

$success_rate = round(($fixed_files / ($fixed_files + $ui_files_without_html)) * 100, 1);
echo "<div class='alert alert-success mt-4'>";
echo "<h4>📈 معدل النجاح: $success_rate%</h4>";
echo "<p>تم إصلاح $fixed_files من " . ($fixed_files + $ui_files_without_html) . " ملف يحتوي على عناصر UI</p>";
echo "</div>";

if ($ui_files_without_html > 0) {
    echo "<div class='alert alert-warning'>";
    echo "<h5>🔧 يحتاج إصلاح إضافي:</h5>";
    echo "<p>يوجد $ui_files_without_html ملف يحتوي على عناصر UI لكن بدون HTML structure. قد تحتاج لتشغيل سكربت إصلاح متخصص.</p>";
    echo "</div>";
}

echo "</body></html>";
?>