<?php
require_once '../config/config.php';

echo "<h3>فحص الجداول المطلوبة</h3>";

try {
    // فحص جدول fabric_types
    $stmt = $pdo->query("SHOW TABLES LIKE 'fabric_types'");
    if ($stmt->rowCount() == 0) {
        echo "❌ جدول fabric_types مفقود - سيتم إنشاؤه<br>";
        $pdo->exec("
            CREATE TABLE fabric_types (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                code VARCHAR(50) UNIQUE,
                type VARCHAR(50),
                color VARCHAR(50),
                unit VARCHAR(20) DEFAULT 'متر',
                cost_per_unit DECIMAL(10,2) DEFAULT 0,
                current_quantity DECIMAL(10,2) DEFAULT 0,
                min_quantity DECIMAL(10,2) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                branch_id INT,
                category_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ تم إنشاء جدول fabric_types<br>";
    } else {
        echo "✅ جدول fabric_types موجود<br>";
    }
    
    // فحص جدول accessories
    $stmt = $pdo->query("SHOW TABLES LIKE 'accessories'");
    if ($stmt->rowCount() == 0) {
        echo "❌ جدول accessories مفقود - سيتم إنشاؤه<br>";
        $pdo->exec("
            CREATE TABLE accessories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                code VARCHAR(50) UNIQUE,
                type VARCHAR(50),
                unit VARCHAR(20) DEFAULT 'قطعة',
                cost_per_unit DECIMAL(10,2) DEFAULT 0,
                current_quantity DECIMAL(10,2) DEFAULT 0,
                min_quantity DECIMAL(10,2) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                branch_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ تم إنشاء جدول accessories<br>";
    } else {
        echo "✅ جدول accessories موجود<br>";
    }
    
    // فحص جدول inventory_movements
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_movements'");
    if ($stmt->rowCount() == 0) {
        echo "❌ جدول inventory_movements مفقود - سيتم إنشاؤه<br>";
        $pdo->exec("
            CREATE TABLE inventory_movements (
                id INT PRIMARY KEY AUTO_INCREMENT,
                type ENUM('in', 'out') NOT NULL,
                fabric_id INT NULL,
                accessory_id INT NULL,
                quantity DECIMAL(10,2) NOT NULL,
                notes TEXT,
                user_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fabric_id) REFERENCES fabric_types(id),
                FOREIGN KEY (accessory_id) REFERENCES accessories(id)
            )
        ");
        echo "✅ تم إنشاء جدول inventory_movements<br>";
    } else {
        echo "✅ جدول inventory_movements موجود<br>";
    }
    
    // إضافة عمود is_active إذا لم يكن موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM fabric_types LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE fabric_types ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        echo "✅ تم إضافة عمود is_active لجدول fabric_types<br>";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM accessories LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE accessories ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        echo "✅ تم إضافة عمود is_active لجدول accessories<br>";
    }
    
    // إضافة بيانات تجريبية
    $stmt = $pdo->query("SELECT COUNT(*) FROM fabric_types");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO fabric_types (name, code, type, color, unit, current_quantity, min_quantity) VALUES
            ('قطن أبيض', 'FAB001', 'قطن', 'أبيض', 'متر', 100, 20),
            ('حرير أزرق', 'FAB002', 'حرير', 'أزرق', 'متر', 50, 10),
            ('صوف رمادي', 'FAB003', 'صوف', 'رمادي', 'متر', 75, 15)
        ");
        echo "✅ تم إضافة بيانات تجريبية للأقمشة<br>";
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM accessories");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO accessories (name, code, type, unit, current_quantity, min_quantity) VALUES
            ('أزرار بيضاء', 'ACC001', 'أزرار', 'قطعة', 500, 100),
            ('سحاب أسود', 'ACC002', 'سحاب', 'قطعة', 200, 50),
            ('خيط أبيض', 'ACC003', 'خيط', 'بكرة', 150, 30)
        ");
        echo "✅ تم إضافة بيانات تجريبية للإكسسوارات<br>";
    }
    
    echo "<br><strong>✅ جميع الجداول جاهزة!</strong>";
    echo "<br><a href='../dashboard.php'>العودة للوحة التحكم</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>