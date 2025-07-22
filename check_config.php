<?php
echo "<h2>فحص إعدادات قاعدة البيانات</h2>";

// فحص ملف config.php
if (file_exists('config/config.php')) {
    echo "✅ ملف config.php موجود<br>";
    
    // قراءة محتويات الملف
    $config_content = file_get_contents('config/config.php');
    
    // البحث عن إعدادات قاعدة البيانات
    if (strpos($config_content, 'DB_HOST') !== false) {
        echo "✅ إعدادات قاعدة البيانات موجودة<br>";
    } else {
        echo "❌ إعدادات قاعدة البيانات غير موجودة<br>";
    }
    
    // محاولة الاتصال المباشر
    try {
        $host = 'localhost';
        $dbname = 'garment_factory_system';
        $username = 'root';
        $password = 'Bad220020!@#';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ الاتصال المباشر بقاعدة البيانات ناجح<br>";
        
        // فحص قاعدة البيانات المحددة
        $stmt = $pdo->query("SELECT DATABASE()");
        $current_db = $stmt->fetchColumn();
        echo "قاعدة البيانات الحالية: " . $current_db . "<br>";
        
    } catch (Exception $e) {
        echo "❌ فشل الاتصال المباشر: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "❌ ملف config.php غير موجود<br>";
}
?>