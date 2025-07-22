<?php
require_once '../config/config.php';

try {
    // إنشاء جدول تسجيل النشاطات
    $sql = "
    CREATE TABLE IF NOT EXISTS activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        activity VARCHAR(255) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        level ENUM('info', 'warning', 'error') DEFAULT 'info',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        INDEX idx_level (level),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ تم إنشاء جدول activity_logs بنجاح<br>";
    
    // إنشاء جدول إعدادات النظام إذا لم يكن موجود
    $sql = "
    CREATE TABLE IF NOT EXISTS system_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ تم إنشاء جدول system_settings بنجاح<br>";
    
    // إدراج إعدادات افتراضية
    $default_settings = [
        ['company_name', 'شركة الملابس المتطورة', 'اسم الشركة'],
        ['company_address', '', 'عنوان الشركة'],
        ['company_phone', '', 'هاتف الشركة'],
        ['company_email', '', 'بريد الشركة الإلكتروني'],
        ['tax_rate', '14', 'معدل الضريبة %'],
        ['currency', 'جنيه مصري', 'العملة'],
        ['currency_symbol', 'ج.م', 'رمز العملة'],
        ['backup_frequency', 'daily', 'تكرار النسخ الاحتياطية'],
        ['low_stock_threshold', '10', 'حد التنبيه للمخزون المنخفض']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    foreach ($default_settings as $setting) {
        $stmt->execute($setting);
    }
    echo "✅ تم إدراج الإعدادات الافتراضية<br>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>