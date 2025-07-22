<?php
require_once 'config/config.php';

try {
    // إنشاء جدول عمليات القص
    $sql = "CREATE TABLE IF NOT EXISTS cutting_operations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cutting_number VARCHAR(50) UNIQUE NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        fabric_consumption_per_unit DECIMAL(10,2) NOT NULL,
        total_fabric_used DECIMAL(10,2) NOT NULL,
        unit_cost DECIMAL(10,2) NOT NULL,
        total_cost DECIMAL(10,2) NOT NULL,
        notes TEXT,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    $pdo->exec($sql);
    echo "✅ تم إنشاء جدول عمليات القص بنجاح";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
