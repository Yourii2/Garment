<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جداول الإدارة المالية...</h3>";
    
    // جدول تحصيلات العملاء
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS customer_collections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            treasury_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            collection_type VARCHAR(50) NOT NULL,
            notes TEXT,
            collected_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            INDEX idx_treasury (treasury_id),
            INDEX idx_collected_by (collected_by)
        )
    ");
    echo "✅ جدول customer_collections<br>";
    
    // جدول دفعات الموردين
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS supplier_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
            treasury_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_type VARCHAR(50) NOT NULL,
            notes TEXT,
            paid_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_supplier (supplier_id),
            INDEX idx_treasury (treasury_id),
            INDEX idx_paid_by (paid_by)
        )
    ");
    echo "✅ جدول supplier_payments<br>";
    
    // جدول مسحوبات الموظفين
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_withdrawals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            treasury_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            withdrawal_type VARCHAR(50) NOT NULL,
            notes TEXT,
            approved_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id),
            INDEX idx_treasury (treasury_id),
            INDEX idx_approved_by (approved_by)
        )
    ");
    echo "✅ جدول employee_withdrawals<br>";
    
    // جدول مسحوبات العمال
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS worker_withdrawals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            worker_id INT NOT NULL,
            treasury_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            withdrawal_type VARCHAR(50) NOT NULL,
            notes TEXT,
            approved_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_worker (worker_id),
            INDEX idx_treasury (treasury_id),
            INDEX idx_approved_by (approved_by)
        )
    ");
    echo "✅ جدول worker_withdrawals<br>";
    
    // جدول مسحوبات المناديب
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS representative_withdrawals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            representative_id INT NOT NULL,
            treasury_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            withdrawal_type VARCHAR(50) NOT NULL,
            notes TEXT,
            approved_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_representative (representative_id),
            INDEX idx_treasury (treasury_id),
            INDEX idx_approved_by (approved_by)
        )
    ");
    echo "✅ جدول representative_withdrawals<br>";
    
    // تحديث جدول treasury_transactions لإضافة أعمدة التحويل
    try {
        // فحص وجود العمود أولاً
        $stmt = $pdo->query("SHOW COLUMNS FROM treasury_transactions LIKE 'from_treasury_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE treasury_transactions ADD COLUMN from_treasury_id INT NULL");
            echo "✅ إضافة عمود from_treasury_id<br>";
        } else {
            echo "✅ عمود from_treasury_id موجود بالفعل<br>";
        }
        
        $stmt = $pdo->query("SHOW COLUMNS FROM treasury_transactions LIKE 'to_treasury_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE treasury_transactions ADD COLUMN to_treasury_id INT NULL");
            echo "✅ إضافة عمود to_treasury_id<br>";
        } else {
            echo "✅ عمود to_treasury_id موجود بالفعل<br>";
        }
    } catch (Exception $e) {
        echo "⚠️ تحديث treasury_transactions: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><strong>✅ تم إنشاء جميع جداول الإدارة المالية بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>

