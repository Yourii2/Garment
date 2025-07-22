<?php
// ملف تثبيت نظام إدارة مصنع الملابس
error_reporting(E_ALL);
ini_set('display_errors', 1);

// إعدادات قاعدة البيانات
$db_config = [
    'host' => 'localhost',
    'dbname' => 'garment_factory_system',
    'username' => 'root',
    'password' => 'Bad220020!@#',
    'charset' => 'utf8mb4'
];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت نظام إدارة مصنع الملابس</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .install-card { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .step { padding: 20px; margin: 10px 0; border-radius: 10px; }
        .step.success { background: #d4edda; border: 1px solid #c3e6cb; }
        .step.error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .step.warning { background: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="install-card p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-industry fa-3x text-primary mb-3"></i>
                        <h2>تثبيت نظام إدارة مصنع الملابس</h2>
                        <p class="text-muted">الإصدار 1.0.0</p>
                    </div>

                    <?php
                    try {
                        echo '<div class="step">';
                        echo '<h5><i class="fas fa-database text-info"></i> خطوة 1: التحقق من قاعدة البيانات</h5>';
                        
                        // الاتصال بـ MySQL
                        $pdo = new PDO(
                            "mysql:host={$db_config['host']};charset={$db_config['charset']}", 
                            $db_config['username'], 
                            $db_config['password']
                        );
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        // استخدام CREATE DATABASE IF NOT EXISTS
                        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$db_config['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        echo '<p class="text-success"><i class="fas fa-check"></i> تم التأكد من وجود قاعدة البيانات</p>';
                        echo '</div>';
                        
                        // الاتصال بقاعدة البيانات
                        $pdo = new PDO(
                            "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}", 
                            $db_config['username'], 
                            $db_config['password']
                        );
                        
                        echo '<div class="step">';
                        echo '<h5><i class="fas fa-table text-info"></i> خطوة 2: إنشاء الجداول</h5>';
                        
                        // التحقق من وجود ملف schema.sql
                        if (file_exists('database/schema.sql')) {
                            $sql = file_get_contents('database/schema.sql');
                            
                            // إزالة أوامر إنشاء قاعدة البيانات من الملف
                            $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
                            $sql = preg_replace('/USE.*?;/i', '', $sql);
                            
                            // تنظيف الملف من التعليقات والأسطر الفارغة
                            $lines = explode("\n", $sql);
                            $clean_sql = '';
                            
                            foreach ($lines as $line) {
                                $line = trim($line);
                                // تجاهل التعليقات والأسطر الفارغة
                                if (!empty($line) && !preg_match('/^\s*--/', $line)) {
                                    $clean_sql .= $line . "\n";
                                }
                            }
                            
                            // تقسيم الاستعلامات
                            $statements = explode(';', $clean_sql);
                            $created_tables = 0;
                            
                            foreach ($statements as $statement) {
                                $statement = trim($statement);
                                if (!empty($statement)) {
                                    try {
                                        $pdo->exec($statement);
                                        if (stripos($statement, 'CREATE TABLE') !== false) {
                                            $created_tables++;
                                        }
                                    } catch (PDOException $e) {
                                        // تجاهل أخطاء الجداول الموجودة مسبق<|im_start|>
                                        if (strpos($e->getMessage(), 'already exists') === false && 
                                            strpos($e->getMessage(), 'Duplicate entry') === false) {
                                            echo '<p class="text-warning">تحذير: ' . $e->getMessage() . '</p>';
                                        }
                                    }
                                }
                            }
                            
                            echo '<p class="text-success"><i class="fas fa-check"></i> تم إنشاء ' . $created_tables . ' جدول بنجاح</p>';
                        } else {
                            echo '<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> ملف schema.sql غير موجود، سيتم إنشاء الجداول الأساسية</p>';
                            
                            // إنشاء الجداول الأساسية
                            $basic_tables = "
                            CREATE TABLE IF NOT EXISTS users (
                                id INT PRIMARY KEY AUTO_INCREMENT,
                                username VARCHAR(50) UNIQUE NOT NULL,
                                password VARCHAR(255) NOT NULL,
                                full_name VARCHAR(100) NOT NULL,
                                email VARCHAR(100),
                                phone VARCHAR(20),
                                role ENUM('admin', 'supervisor', 'accountant', 'worker', 'sales_rep', 'limited_user') NOT NULL,
                                permissions JSON,
                                is_active BOOLEAN DEFAULT TRUE,
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                            );
                            
                            CREATE TABLE IF NOT EXISTS sizes (
                                id INT PRIMARY KEY AUTO_INCREMENT,
                                name VARCHAR(50) NOT NULL,
                                code VARCHAR(20) UNIQUE NOT NULL,
                                sort_order INT DEFAULT 0,
                                is_active BOOLEAN DEFAULT TRUE
                            );
                            
                            INSERT IGNORE INTO sizes (name, code, sort_order) VALUES 
                            ('صغير', 'S', 1), ('متوسط', 'M', 2), ('كبير', 'L', 3), 
                            ('كبير<|im_start|>', 'XL', 4), ('كبير3', 'XXL', 5);
                            ";
                            
                            $statements = explode(';', $basic_tables);
                            foreach ($statements as $statement) {
                                $statement = trim($statement);
                                if (!empty($statement)) {
                                    $pdo->exec($statement);
                                }
                            }
                            echo '<p class="text-success"><i class="fas fa-check"></i> تم إنشاء الجداول الأساسية</p>';
                        }
                        echo '</div>';
                        
                        echo '<div class="step">';
                        echo '<h5><i class="fas fa-user-plus text-info"></i> خطوة 3: إنشاء المستخدم الافتراضي</h5>';
                        
                        // التحقق من وجود المستخدم الافتراضي
                        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            echo '<p class="text-info"><i class="fas fa-info-circle"></i> المستخدم الافتراضي موجود مسبق<|im_start|></p>';
                            
                            // تحديث كلمة المرور للتأكد
                            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
                            $stmt->execute([$admin_password]);
                            echo '<p class="text-success"><i class="fas fa-check"></i> تم تحديث كلمة المرور</p>';
                        } else {
                            // إنشاء المستخدم الافتراضي
                            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("
                                INSERT INTO users (username, password, full_name, role, is_active) 
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmt->execute(['admin', $admin_password, 'مدير النظام', 'admin', 1]);
                            echo '<p class="text-success"><i class="fas fa-check"></i> تم إنشاء المستخدم الافتراضي</p>';
                        }
                        
                        // التحقق من إنشاء المستخدم
                        $stmt = $pdo->prepare("SELECT username, full_name, role FROM users WHERE username = 'admin'");
                        $stmt->execute();
                        $admin_user = $stmt->fetch();
                        
                        if ($admin_user) {
                            echo '<p class="text-success"><i class="fas fa-user-check"></i> تم التحقق من المستخدم: ' . $admin_user['full_name'] . '</p>';
                        } else {
                            echo '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> فشل في إنشاء المستخدم!</p>';
                        }
                        echo '</div>';
                        
                        echo '<div class="step success">';
                        echo '<h4><i class="fas fa-check-circle text-success"></i> تم التثبيت بنجاح!</h4>';
                        echo '<hr>';
                        echo '<h5>بيانات تسجيل الدخول:</h5>';
                        echo '<div class="row">';
                        echo '<div class="col-md-6">';
                        echo '<p><strong>اسم المستخدم:</strong> <code class="bg-light p-2 rounded">admin</code></p>';
                        echo '</div>';
                        echo '<div class="col-md-6">';
                        echo '<p><strong>كلمة المرور:</strong> <code class="bg-light p-2 rounded">admin123</code></p>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="text-center mt-4">';
                        echo '<a href="login.php" class="btn btn-primary btn-lg">';
                        echo '<i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول إلى النظام';
                        echo '</a>';
                        echo '</div>';
                        echo '</div>';
                        
                    } catch (PDOException $e) {
                        echo '<div class="step error">';
                        echo '<h5><i class="fas fa-times-circle text-danger"></i> خطأ في التثبيت</h5>';
                        echo '<p class="text-danger">خطأ: ' . $e->getMessage() . '</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>






