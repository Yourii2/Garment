<?php
require_once '../config/config.php';

try {
    // إضافة إعداد رمز العملة إذا لم يكن موجوداً
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO system_settings (setting_key, setting_value, description) 
        VALUES ('currency_symbol', 'ج.م', 'رمز العملة المستخدم في النظام')
    ");
    $stmt->execute();
    
    echo "تم إضافة إعداد رمز العملة بنجاح";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>