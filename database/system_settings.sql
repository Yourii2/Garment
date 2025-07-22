-- جدول إعدادات النظام
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- إدراج الإعدادات الافتراضية
INSERT INTO system_settings (setting_key, setting_value) VALUES
('system_name', 'نظام إدارة مصنع الملابس'),
('company_name', ''),
('company_address', ''),
('company_phone', ''),
('company_email', ''),
('currency', 'EGP'),
('timezone', 'Africa/Cairo'),
('date_format', 'Y-m-d'),
('language', 'ar'),
('items_per_page', '20'),
('low_stock_threshold', '10'),
('backup_frequency', 'daily'),
('email_notifications', '1'),
('sms_notifications', '0'),
('auto_backup', '1'),
('maintenance_mode', '0')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);