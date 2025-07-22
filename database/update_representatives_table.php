<?php
require_once '../config/config.php';

try {
    // إضافة الحقول الجديدة لجدول المناديب
    $alterQueries = [
        "ALTER TABLE representatives ADD COLUMN payment_type ENUM('salary_only', 'commission_only', 'salary_commission') DEFAULT 'commission_only' AFTER area",
        "ALTER TABLE representatives ADD COLUMN salary_type ENUM('daily', 'weekly', 'monthly') NULL AFTER payment_type",
        "ALTER TABLE representatives ADD COLUMN salary_amount DECIMAL(10,2) DEFAULT 0 AFTER salary_type",
        "ALTER TABLE representatives ADD COLUMN commission_type ENUM('percentage', 'fixed_amount') NULL AFTER salary_amount",
        "ALTER TABLE representatives ADD COLUMN commission_value DECIMAL(10,2) DEFAULT 0 AFTER commission_type"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "✅ تم تنفيذ: " . $query . "<br>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠️ العمود موجود مسبقاً: " . $query . "<br>";
            } else {
                echo "❌ خطأ في: " . $query . " - " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // تحديث العمود القديم commission_rate إلى النظام الجديد
    $stmt = $pdo->query("SELECT id, commission_rate FROM representatives WHERE commission_rate > 0");
    $representatives = $stmt->fetchAll();
    
    if (!empty($representatives)) {
        echo "<br><h3>تحديث البيانات القديمة:</h3>";
        $updateStmt = $pdo->prepare("UPDATE representatives SET payment_type = 'commission_only', commission_type = 'percentage', commission_value = ? WHERE id = ?");
        
        foreach ($representatives as $rep) {
            $updateStmt->execute([$rep['commission_rate'], $rep['id']]);
            echo "✅ تم تحديث المندوب رقم " . $rep['id'] . " - العمولة: " . $rep['commission_rate'] . "%<br>";
        }
    }
    
    echo "<br><br>";
    echo "<a href='../hr/representatives.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>الذهاب إلى صفحة المناديب</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ عام: " . $e->getMessage();
}
?>