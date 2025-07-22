<?php
require_once 'config/config.php';

echo "<h3>اختبار API العمال</h3>";

try {
    // اختبار جدول workers
    $stmt = $pdo->query("SELECT id, name, is_active FROM workers ORDER BY name");
    $workers = $stmt->fetchAll();
    
    echo "<h4>العمال في قاعدة البيانات:</h4>";
    foreach ($workers as $worker) {
        echo "ID: {$worker['id']}, الاسم: {$worker['name']}, نشط: " . ($worker['is_active'] ? 'نعم' : 'لا') . "<br>";
    }
    
    // اختبار استعلام get_product_workers
    echo "<h4>اختبار get_product_workers.php:</h4>";
    $stmt = $pdo->query("SELECT id, name as full_name FROM workers WHERE is_active = 1 ORDER BY name");
    $workers = $stmt->fetchAll();
    
    echo "عدد العمال: " . count($workers) . "<br>";
    echo "JSON: " . json_encode($workers, JSON_UNESCAPED_UNICODE) . "<br>";
    
    // اختبار مباشر للملف
    echo "<h4>اختبار مباشر للملف:</h4>";
    echo "<a href='production/get_product_workers.php?product_id=1' target='_blank'>اختبار get_product_workers.php</a><br>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>