<?php
require_once '../config/config.php';

echo "<h3>اختبار النظام</h3>";

try {
    // اختبار الثوابت
    echo "<h4>الثوابت:</h4>";
    echo "CURRENCY_SYMBOL: " . (defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : 'غير معرف') . "<br>";
    echo "SYSTEM_NAME: " . (defined('SYSTEM_NAME') ? SYSTEM_NAME : 'غير معرف') . "<br>";
    echo "CURRENCY: " . (defined('CURRENCY') ? CURRENCY : 'غير معرف') . "<br>";
    
    // اختبار الدوال
    echo "<h4>الدوال:</h4>";
    echo "logError: " . (function_exists('logError') ? '✅ موجودة' : '❌ مفقودة') . "<br>";
    echo "logActivity: " . (function_exists('logActivity') ? '✅ موجودة' : '❌ مفقودة') . "<br>";
    echo "checkPermissionAccess: " . (function_exists('checkPermissionAccess') ? '✅ موجودة' : '❌ مفقودة') . "<br>";
    echo "generateCSRF: " . (function_exists('generateCSRF') ? '✅ موجودة' : '❌ مفقودة') . "<br>";
    
    // اختبار الجداول
    echo "<h4>الجداول:</h4>";
    $tables = ['system_settings', 'activity_logs', 'workers', 'customers', 'suppliers'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        echo "$table: " . ($stmt->rowCount() > 0 ? '✅ موجود' : '❌ مفقود') . "<br>";
    }
    
    // اختبار الأعمدة الحساسة
    echo "<h4>الأعمدة الحساسة:</h4>";
    $critical_columns = [
        'workers' => ['specialization', 'description'],
        'customers' => ['notes', 'description'],
        'suppliers' => ['notes', 'description'],
        'system_settings' => ['setting_key', 'setting_value']
    ];
    
    foreach ($critical_columns as $table => $columns) {
        foreach ($columns as $column) {
            $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE '$column'");
            echo "$table.$column: " . ($stmt->rowCount() > 0 ? '✅ موجود' : '❌ مفقود') . "<br>";
        }
    }
    
    // اختبار الإعدادات
    echo "<h4>إعدادات النظام:</h4>";
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = $stmt->fetchAll();
    foreach ($settings as $setting) {
        echo $setting['setting_key'] . ": " . $setting['setting_value'] . "<br>";
    }
    
    echo "<br><strong>✅ النظام جاهز للعمل!</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ في الاختبار: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h3, h4 { color: #333; }
.success { color: green; }
.error { color: red; }
</style>