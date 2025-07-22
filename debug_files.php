<?php
/**
 * سكربت تشخيص الملفات لمعرفة سبب عدم التحديث
 */

// فحص ملف واحد كمثال
$test_file = 'reports/production_report.php';

if (file_exists($test_file)) {
    $content = file_get_contents($test_file);
    
    echo "<h3>تشخيص الملف: $test_file</h3>";
    echo "<h4>محتوى الملف (أول 1000 حرف):</h4>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "</pre>";
    
    echo "<h4>فحص HTML:</h4>";
    echo "يحتوي على &lt;html: " . (preg_match('/<html/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على &lt;!DOCTYPE: " . (preg_match('/<!DOCTYPE/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على &lt;head&gt;: " . (preg_match('/<head>/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على &lt;body&gt;: " . (preg_match('/<body>/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على bootstrap: " . (preg_match('/bootstrap.*css/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على container-fluid: " . (preg_match('/container-fluid/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على include.*sidebar: " . (preg_match('/include.*sidebar/i', $content) ? 'نعم' : 'لا') . "<br>";
    
    echo "<h4>فحص التحديث:</h4>";
    echo "محدث بالفعل (lang=\"ar\" dir=\"rtl\"): " . (preg_match('/<html[^>]*lang="ar"[^>]*dir="rtl"/i', $content) ? 'نعم' : 'لا') . "<br>";
    
    // فحص أكثر تفصيلاً
    if (preg_match('/<html[^>]*>/i', $content, $matches)) {
        echo "HTML tag الموجود: " . htmlspecialchars($matches[0]) . "<br>";
    }
    
} else {
    echo "الملف غير موجود: $test_file";
}

echo "<hr>";

// فحص ملف آخر
$test_file2 = 'inventory/accessories.php';

if (file_exists($test_file2)) {
    $content = file_get_contents($test_file2);
    
    echo "<h3>تشخيص الملف: $test_file2</h3>";
    echo "<h4>محتوى الملف (أول 1000 حرف):</h4>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "</pre>";
    
    echo "<h4>فحص HTML:</h4>";
    echo "يحتوي على &lt;html: " . (preg_match('/<html/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على &lt;!DOCTYPE: " . (preg_match('/<!DOCTYPE/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على &lt;head&gt;: " . (preg_match('/<head>/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على &lt;body&gt;: " . (preg_match('/<body>/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على bootstrap: " . (preg_match('/bootstrap.*css/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على container-fluid: " . (preg_match('/container-fluid/i', $content) ? 'نعم' : 'لا') . "<br>";
    echo "يحتوي على include.*sidebar: " . (preg_match('/include.*sidebar/i', $content) ? 'نعم' : 'لا') . "<br>";
    
    echo "<h4>فحص التحديث:</h4>";
    echo "محدث بالفعل (lang=\"ar\" dir=\"rtl\"): " . (preg_match('/<html[^>]*lang="ar"[^>]*dir="rtl"/i', $content) ? 'نعم' : 'لا') . "<br>";
    
    // فحص أكثر تفصيلاً
    if (preg_match('/<html[^>]*>/i', $content, $matches)) {
        echo "HTML tag الموجود: " . htmlspecialchars($matches[0]) . "<br>";
    }
    
} else {
    echo "الملف غير موجود: $test_file2";
}
?>