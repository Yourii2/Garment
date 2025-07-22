<?php
require_once 'config/config.php';

try {
    echo "<h3>إضافة عمال تجريبيين</h3>";
    
    $workers = [
        ['username' => 'worker1', 'full_name' => 'أحمد محمد', 'role' => 'worker'],
        ['username' => 'worker2', 'full_name' => 'محمد علي', 'role' => 'worker'],
        ['username' => 'supervisor1', 'full_name' => 'خالد أحمد', 'role' => 'supervisor']
    ];
    
    foreach ($workers as $worker) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (username, password, full_name, role, is_active) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $worker['username'],
            password_hash('123456', PASSWORD_DEFAULT),
            $worker['full_name'],
            $worker['role']
        ]);
        echo "✅ تم إضافة: {$worker['full_name']}<br>";
    }
    
    echo "<br><strong>✅ تم إضافة العمال بنجاح</strong>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>