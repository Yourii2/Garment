<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جداول المخزون</h3>";
    
    // جدول أنواع الأقمشة
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fabric_types (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إنشاء جدول fabric_types<br>";
    
    // جدول الأقمشة
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fabrics (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            type_id INT,
            color VARCHAR(50),
            width DECIMAL(8,2),
            weight DECIMAL(8,2),
            composition VARCHAR(200),
            supplier_id INT,
            cost_per_meter DECIMAL(10,2) NOT NULL,
            selling_price_per_meter DECIMAL(10,2) NOT NULL,
            stock_quantity DECIMAL(10,2) DEFAULT 0,
            minimum_stock DECIMAL(10,2) DEFAULT 0,
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (type_id) REFERENCES fabric_types(id),
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إنشاء جدول fabrics<br>";
    
    // جدول الإكسسوارات
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS accessories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            type_id INT,
            color VARCHAR(50),
            size VARCHAR(50),
            material VARCHAR(100),
            supplier_id INT,
            cost_per_unit DECIMAL(10,2) NOT NULL,
            selling_price_per_unit DECIMAL(10,2) NOT NULL,
            stock_quantity INT DEFAULT 0,
            minimum_stock INT DEFAULT 0,
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (type_id) REFERENCES accessory_types(id),
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إنشاء جدول accessories<br>";
    
    echo "<br><strong>تم إنشاء جميع الجداول بنجاح</strong>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>