<?php
require_once '../config/config.php';

try {
    // جدول فواتير المخزون
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inventory_invoices (
            id INT PRIMARY KEY AUTO_INCREMENT,
            invoice_number VARCHAR(50) UNIQUE NOT NULL,
            invoice_type ENUM('purchase', 'return', 'damage') NOT NULL,
            supplier_id INT NULL,
            branch_id INT NULL,
            invoice_date DATE NOT NULL,
            total_amount DECIMAL(12,2) DEFAULT 0,
            notes TEXT,
            user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
            FOREIGN KEY (branch_id) REFERENCES branches(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // جدول تفاصيل فواتير المخزون
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inventory_invoice_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            invoice_id INT,
            fabric_id INT NULL,
            accessory_id INT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            unit_cost DECIMAL(10,2) DEFAULT 0,
            total_cost DECIMAL(10,2) DEFAULT 0,
            notes TEXT,
            FOREIGN KEY (invoice_id) REFERENCES inventory_invoices(id) ON DELETE CASCADE,
            FOREIGN KEY (fabric_id) REFERENCES fabric_types(id),
            FOREIGN KEY (accessory_id) REFERENCES accessories(id)
        )
    ");

    echo "تم إنشاء جداول الفواتير بنجاح";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
