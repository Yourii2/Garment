-- إضافة عمود accessory_id إلى جدول inventory_movements
ALTER TABLE inventory_movements 
ADD COLUMN accessory_id INT NULL AFTER fabric_id;

-- إضافة المفتاح الخارجي
ALTER TABLE inventory_movements 
ADD CONSTRAINT fk_inventory_movements_accessory 
FOREIGN KEY (accessory_id) REFERENCES accessories(id) 
ON DELETE SET NULL;

-- التأكد من وجود الجداول المطلوبة
CREATE TABLE IF NOT EXISTS fabric_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(50),
    color VARCHAR(50),
    unit VARCHAR(20) DEFAULT 'متر',
    cost_per_unit DECIMAL(10,2),
    current_quantity DECIMAL(10,2) DEFAULT 0,
    min_quantity DECIMAL(10,2) DEFAULT 0,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

CREATE TABLE IF NOT EXISTS accessories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(50),
    unit VARCHAR(20) DEFAULT 'قطعة',
    cost_per_unit DECIMAL(10,2),
    current_quantity DECIMAL(10,2) DEFAULT 0,
    min_quantity DECIMAL(10,2) DEFAULT 0,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);
