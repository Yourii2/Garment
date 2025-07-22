<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// إعدادات قاعدة البيانات الافتراضية
$default_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'garment_factory_system',
    'charset' => 'utf8mb4'
];

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// معالجة الخطوات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        // خطوة قاعدة البيانات
        $db_config = [
            'host' => $_POST['db_host'] ?? 'localhost',
            'username' => $_POST['db_username'] ?? 'root',
            'password' => $_POST['db_password'] ?? '',
            'dbname' => $_POST['db_name'] ?? 'garment_factory_system',
            'charset' => 'utf8mb4'
        ];
        
        try {
            // اختبار الاتصال
            $pdo = new PDO(
                "mysql:host={$db_config['host']};charset={$db_config['charset']}", 
                $db_config['username'], 
                $db_config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // إنشاء قاعدة البيانات
            $pdo->exec("CREATE DATABASE IF NOT EXISTS {$db_config['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // حفظ إعدادات قاعدة البيانات
            $_SESSION['db_config'] = $db_config;
            $step = 2;
            
        } catch (Exception $e) {
            $errors[] = 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage();
        }
    }
    
    if ($step == 2) {
        // خطوة إعدادات النظام
        $system_config = [
            'system_name' => $_POST['system_name'] ?? 'نظام إدارة مصنع الملابس',
            'company_name' => $_POST['company_name'] ?? 'شركة الملابس المتطورة',
            'country' => $_POST['country'] ?? 'مصر',
            'currency' => $_POST['currency'] ?? 'جنيه مصري',
            'currency_symbol' => $_POST['currency_symbol'] ?? 'ج.م',
            'timezone' => $_POST['timezone'] ?? 'Africa/Cairo',
            'language' => $_POST['language'] ?? 'ar',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];
        
        $_SESSION['system_config'] = $system_config;
        $step = 3;
    }
    
    if ($step == 3) {
        // خطوة المستخدم الإداري
        $admin_config = [
            'username' => $_POST['admin_username'] ?? 'admin',
            'password' => $_POST['admin_password'] ?? 'admin123',
            'full_name' => $_POST['admin_name'] ?? 'مدير النظام',
            'email' => $_POST['admin_email'] ?? '',
            'phone' => $_POST['admin_phone'] ?? ''
        ];
        
        if (strlen($admin_config['password']) < 6) {
            $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } else {
            $_SESSION['admin_config'] = $admin_config;
            $step = 4;
        }
    }
    
    if ($step == 4) {
        // تنفيذ التثبيت
        try {
            $db_config = $_SESSION['db_config'];
            $system_config = $_SESSION['system_config'];
            $admin_config = $_SESSION['admin_config'];
            
            // الاتصال بقاعدة البيانات
            $pdo = new PDO(
                "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}", 
                $db_config['username'], 
                $db_config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // إنشاء الجداول
            $sql = "
            -- جدول إعدادات النظام
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
            
            -- جدول المستخدمين
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
            
            -- جدول أنواع الأقمشة
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            -- جدول الإكسسوارات
            CREATE TABLE IF NOT EXISTS accessories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                code VARCHAR(50) UNIQUE NOT NULL,
                type VARCHAR(50),
                unit VARCHAR(20) DEFAULT 'قطعة',
                cost_per_unit DECIMAL(10,2),
                current_quantity DECIMAL(10,2) DEFAULT 0,
                min_quantity DECIMAL(10,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            -- جدول المقاسات
            CREATE TABLE IF NOT EXISTS sizes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                code VARCHAR(20) UNIQUE NOT NULL,
                sort_order INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            -- جدول حركات المخزون
            CREATE TABLE IF NOT EXISTS inventory_movements (
                id INT PRIMARY KEY AUTO_INCREMENT,
                fabric_id INT NULL,
                accessory_id INT NULL,
                movement_type ENUM('in', 'out') NOT NULL,
                quantity DECIMAL(10,2) NOT NULL,
                unit_cost DECIMAL(10,2),
                total_cost DECIMAL(10,2),
                notes TEXT,
                user_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fabric_id) REFERENCES fabric_types(id),
                FOREIGN KEY (accessory_id) REFERENCES accessories(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            ";
            
            // تنفيذ الاستعلامات
            $statements = explode(';', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // إدراج إعدادات النظام
            $settings = [
                'system_name' => $system_config['system_name'],
                'company_name' => $system_config['company_name'],
                'country' => $system_config['country'],
                'currency' => $system_config['currency'],
                'currency_symbol' => $system_config['currency_symbol'],
                'timezone' => $system_config['timezone'],
                'language' => $system_config['language'],
                'phone' => $system_config['phone'],
                'email' => $system_config['email'],
                'address' => $system_config['address'],
                'logo' => '',
                'version' => '1.0.0'
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            
            // إنشاء المستخدم الإداري
            $admin_password = password_hash($admin_config['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, 'admin') ON DUPLICATE KEY UPDATE password = ?, full_name = ?, email = ?, phone = ?");
            $stmt->execute([
                $admin_config['username'], 
                $admin_password, 
                $admin_config['full_name'],
                $admin_config['email'],
                $admin_config['phone'],
                $admin_password,
                $admin_config['full_name'],
                $admin_config['email'],
                $admin_config['phone']
            ]);
            
            // إدراج المقاسات الافتراضية
            $default_sizes = [
                ['صغير', 'XS', 1],
                ['صغير', 'S', 2],
                ['متوسط', 'M', 3],
                ['كبير', 'L', 4],
                ['كبير', 'XL', 5],
                ['كبير', 'XXL', 6]
            ];
            
            foreach ($default_sizes as $size) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO sizes (name, code, sort_order) VALUES (?, ?, ?)");
                $stmt->execute($size);
            }
            
            // إنشاء ملف config.php
            $config_content = "<?php
// إعدادات قاعدة البيانات
define('DB_HOST', '{$db_config['host']}');
define('DB_NAME', '{$db_config['dbname']}');
define('DB_USER', '{$db_config['username']}');
define('DB_PASS', '{$db_config['password']}');
define('DB_CHARSET', '{$db_config['charset']}');

// إعدادات النظام
define('SYSTEM_NAME', '{$system_config['system_name']}');
define('COMPANY_NAME', '{$system_config['company_name']}');
define('COUNTRY', '{$system_config['country']}');
define('CURRENCY', '{$system_config['currency']}');
define('CURRENCY_SYMBOL', '{$system_config['currency_symbol']}');
define('TIMEZONE', '{$system_config['timezone']}');
define('BASE_URL', '/NewDragon');

// تعيين المنطقة الزمنية
date_default_timezone_set(TIMEZONE);

// بدء الجلسة
session_start();

// اتصال قاعدة البيانات
try {
    \$pdo = new PDO(
        \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException \$e) {
    die(\"خطأ في الاتصال بقاعدة البيانات: \" . \$e->getMessage());
}

// دالة جلب إعدادات النظام
function getSystemSettings() {
    global \$pdo;
    static \$settings = null;
    
    if (\$settings === null) {
        \$stmt = \$pdo->query(\"SELECT setting_key, setting_value FROM system_settings\");
        \$settings = [];
        while (\$row = \$stmt->fetch()) {
            \$settings[\$row['setting_key']] = \$row['setting_value'];
        }
    }
    
    return \$settings;
}

// دالة فحص تسجيل الدخول
function checkLogin() {
    if (!isset(\$_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// دالة فحص الصلاحيات
function checkPermission(\$permission) {
    if (\$_SESSION['role'] === 'admin') {
        return true;
    }
    
    \$permissions = json_decode(\$_SESSION['permissions'] ?? '[]', true);
    return in_array(\$permission, \$permissions);
}

// دالة تسجيل النشاطات
function logActivity(\$activity, \$details = '') {
    global \$pdo;
    try {
        \$stmt = \$pdo->prepare(\"INSERT INTO activity_logs (user_id, activity, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())\");
        \$stmt->execute([\$_SESSION['user_id'] ?? 0, \$activity, \$details, \$_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception \$e) {
        // تجاهل أخطاء تسجيل النشاطات
    }
}
?>";
            
            file_put_contents('config/config.php', $config_content);
            
            $step = 5;
            $success[] = 'تم التثبيت بنجاح!';
            
        } catch (Exception $e) {
            $errors[] = 'خطأ في التثبيت: ' . $e->getMessage();
        }
    }
}

session_start();
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
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-card { 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            max-width: 800px;
            margin: 2rem auto;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step-item {
            display: flex;
            align-items: center;
            margin: 0 1rem;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 0.5rem;
        }
        .step-number.active {
            background: #007bff;
            color: white;
        }
        .step-number.completed {
            background: #28a745;
            color: white;
        }
        .step-number.pending {
            background: #e9ecef;
            color: #6c757d;
        }
        .form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card p-5">
            <div class="text-center mb-4">
                <i class="fas fa-industry fa-3x text-primary mb-3"></i>
                <h2>تثبيت نظام إدارة مصنع الملابس</h2>
                <p class="text-muted">الإصدار 1.0.0</p>
            </div>

            <!-- مؤشر الخطوات -->
            <div class="step-indicator">
                <div class="step-item">
                    <div class="step-number <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : 'pending' ?>">1</div>
                    <span>قاعدة البيانات</span>
                </div>
                <div class="step-item">
                    <div class="step-number <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : 'pending' ?>">2</div>
                    <span>إعدادات النظام</span>
                </div>
                <div class="step-item">
                    <div class="step-number <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : 'pending' ?>">3</div>
                    <span>المستخدم الإداري</span>
                </div>
                <div class="step-item">
                    <div class="step-number <?= $step >= 4 ? ($step > 4 ? 'completed' : 'active') : 'pending' ?>">4</div>
                    <span>التثبيت</span>
                </div>
            </div>

            <!-- عرض الأخطاء -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- عرض رسائل النجاح -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <ul class="mb-0">
                        <?php foreach ($success as $msg): ?>
                            <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <!-- خطوة 1: إعدادات قاعدة البيانات -->
                <form method="POST">
                    <div class="form-section">
                        <h4><i class="fas fa-database text-primary me-2"></i>إعدادات قاعدة البيانات</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">خادم قاعدة البيانات</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">اسم قاعدة البيانات</label>
                                <input type="text" class="form-control" name="db_name" value="garment_factory_system" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" class="form-control" name="db_username" value="root" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="db_password">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">التالي <i class="fas fa-arrow-left ms-2"></i></button>
                </form>

            <?php elseif ($step == 2): ?>
                <!-- خطوة 2: إعدادات النظام -->
                <form method="POST">
                    <div class="form-section">
                        <h4><i class="fas fa-cog text-primary me-2"></i>إعدادات النظام</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">اسم النظام</label>
                                <input type="text" class="form-control" name="system_name" value="نظام إدارة مصنع الملابس" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">اسم الشركة</label>
                                <input type="text" class="form-control" name="company_name" value="شركة الملابس المتطورة" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">الدولة</label>
                                <select class="form-control" name="country" required>
                                    <option value="مصر">مصر</option>
                                    <option value="السعودية">السعودية</option>
                                    <option value="الإمارات">الإمارات</option>
                                    <option value="الكويت">الكويت</option>
                                    <option value="قطر">قطر</option>
                                    <option value="البحرين">البحرين</option>
                                    <option value="عمان">عمان</option>
                                    <option value="الأردن">الأردن</option>
                                    <option value="لبنان">لبنان</option>
                                    <option value="سوريا">سوريا</option>
                                    <option value="العراق">العراق</option>
                                    <option value="المغرب">المغرب</option>
                                    <option value="الجزائر">الجزائر</option>
                                    <option value="تونس">تونس</option>
                                    <option value="ليبيا">ليبيا</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">العملة</label>
                                <select class="form-control" name="currency" required>
                                    <option value="جنيه مصري">جنيه مصري</option>
                                    <option value="ريال سعودي">ريال سعودي</option>
                                    <option value="درهم إماراتي">درهم إماراتي</option>
                                    <option value="دينار كويتي">دينار كويتي</option>
                                    <option value="ريال قطري">ريال قطري</option>
                                    <option value="دولار أمريكي">دولار أمريكي</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">رمز العملة</label>
                                <input type="text" class="form-control" name="currency_symbol" value="ج.م" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">المنطقة الزمنية</label>
                                <select class="form-control" name="timezone" required>
                                    <option value="Africa/Cairo">القاهرة (GMT+2)</option>
                                    <option value="Asia/Riyadh">الرياض (GMT+3)</option>
                                    <option value="Asia/Dubai">دبي (GMT+4)</option>
                                    <option value="Asia/Kuwait">الكويت (GMT+3)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">العنوان</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">التالي <i class="fas fa-arrow-left ms-2"></i></button>
                </form>

            <?php elseif ($step == 3): ?>
                <!-- خطوة 3: المستخدم الإداري -->
                <form method="POST">
                    <div class="form-section">
                        <h4><i class="fas fa-user-shield text-primary me-2"></i>إنشاء المستخدم الإداري</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" class="form-control" name="admin_username" value="admin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="admin_password" value="admin123" required>
                                <small class="text-muted">6 أحرف على الأقل</small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" name="admin_name" value="مدير النظام" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="admin_email">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" class="form-control" name="admin_phone">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">التالي <i class="fas fa-arrow-left ms-2"></i></button>
                </form>

            <?php elseif ($step == 4): ?>
                <!-- خطوة 4: تأكيد التثبيت -->
                <div class="form-section">
                    <h4><i class="fas fa-check-circle text-success me-2"></i>تأكيد التثبيت</h4>
                    <p>سيتم الآن تثبيت النظام بالإعدادات التالية:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>إعدادات قاعدة البيانات:</h6>
                            <ul>
                                <li>الخادم: <?= htmlspecialchars($_SESSION['db_config']['host']) ?></li>
                                <li>قاعدة البيانات: <?= htmlspecialchars($_SESSION['db_config']['dbname']) ?></li>
                                <li>المستخدم: <?= htmlspecialchars($_SESSION['db_config']['username']) ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>إعدادات النظام:</h6>
                            <ul>
                                <li>اسم النظام: <?= htmlspecialchars($_SESSION['system_config']['system_name']) ?></li>
                                <li>الشركة: <?= htmlspecialchars($_SESSION['system_config']['company_name']) ?></li>
                                <li>الدولة: <?= htmlspecialchars($_SESSION['system_config']['country']) ?></li>
                                <li>العملة: <?= htmlspecialchars($_SESSION['system_config']['currency']) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <form method="POST">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-rocket me-2"></i>بدء التثبيت
                    </button>
                </form>

            <?php elseif ($step == 5): ?>
                <!-- خطوة 5: اكتمال التثبيت -->
                <div class="text-center">
                    <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                    <h3 class="text-success">تم التثبيت بنجاح!</h3>
                    <p class="lead">تم تثبيت نظام إدارة مصنع الملابس بنجاح</p>
                    
                    <div class="alert alert-info">
                        <h5>بيانات تسجيل الدخول:</h5>
                        <p><strong>اسم المستخدم:</strong> <?= htmlspecialchars($_SESSION['admin_config']['username'] ?? 'admin') ?></p>
                        <p><strong>كلمة المرور:</strong> <?= htmlspecialchars($_SESSION['admin_config']['password'] ?? 'admin123') ?></p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="login.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

