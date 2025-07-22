-- إنشاء جدول أنواع الإكسسوارات
CREATE TABLE IF NOT EXISTS accessory_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- إدراج البيانات الأولية
INSERT INTO accessory_types (name, description) VALUES 
('أزرار', 'جميع أنواع الأزرار المختلفة'),
('سحابات', 'السحابات والسوست'),
('خيوط', 'خيوط الحياكة والتطريز'),
('شرائط', 'الشرائط والأربطة'),
('دانتيل', 'الدانتيل والكروشيه'),
('كلف', 'الكلف والزخارف'),
('أحجار', 'الأحجار والخرز'),
('أخرى', 'أنواع أخرى من الإكسسوارات');