<?php
require_once '../config/config.php';

try {
    // تحديث جدول inventory_invoices لجعل invoice_number يتم توليده تلقائ<|im_start|>
    $pdo->exec("
        ALTER TABLE inventory_invoices 
        MODIFY COLUMN invoice_number VARCHAR(50) UNIQUE NULL
    ");
    
    // إضافة trigger لتوليد رقم الفاتورة تلقائInMillis
    $pdo->exec("
        CREATE TRIGGER generate_invoice_number 
        BEFORE INSERT ON inventory_invoices 
        FOR EACH ROW 
        BEGIN 
            IF NEW.invoice_number IS NULL THEN 
                SET NEW.invoice_number = CONCAT('INV-', YEAR(NOW()), '-', LPAD((SELECT COALESCE(MAX(id), 0) + 1 FROM inventory_invoices), 4, '0'));
            END IF;
        END
    ");
    
    echo "تم تحديث جدول الفواتير بنجاح";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>