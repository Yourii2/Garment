<?php
session_start();

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'garment_factory_system');
define('DB_USER', 'root');
define('DB_PASS', 'Bad220020!@#');

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// جلب إعدادات النظام من قاعدة البيانات
$system_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    while ($row = $stmt->fetch()) {
        $system_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // في حالة عدم وجود جدول الإعدادات بعد، استخدم القيم الافتراضية
}

// تعريف الثوابت من الإعدادات
define('SYSTEM_NAME', $system_settings['system_name'] ?? 'نظام إدارة مصنع الملابس');
define('COMPANY_NAME', $system_settings['company_name'] ?? '');
define('CURRENCY', $system_settings['currency'] ?? 'EGP');
define('CURRENCY_SYMBOL', $system_settings['currency_symbol'] ?? 'ج.م');
define('TIMEZONE', $system_settings['timezone'] ?? 'Africa/Cairo');
define('ITEMS_PER_PAGE', $system_settings['items_per_page'] ?? 20);
define('LOW_STOCK_THRESHOLD', $system_settings['low_stock_threshold'] ?? 10);

// تعيين المنطقة الزمنية
date_default_timezone_set(TIMEZONE);

// رابط الموقع الأساسي
define('BASE_URL', 'http://localhost/NewDragon');

// إعدادات الجلسة
define('SESSION_TIMEOUT', 3600); // ساعة واحدة

// دالة التحقق من تسجيل الدخول
function checkLogin() {
    global $system_settings;
    
    // التحقق من وضع الصيانة
    if (isset($system_settings['maintenance_mode']) && $system_settings['maintenance_mode'] == '1') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            include 'maintenance.php';
            exit;
        }
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// دالة التحقق من الصلاحيات
function checkPermission($permission) {
    if ($_SESSION['role'] === 'admin') {
        return true; // المدير له جميع الصلاحيات
    }
    
    if (isset($_SESSION['permissions'])) {
        $permissions = json_decode($_SESSION['permissions'], true);
        return in_array($permission, $permissions);
    }
    
    return false;
}

// دالة تنسيق العملة
function formatCurrency($amount) {
    $currency_symbols = [
        'EGP' => 'ج.م',
        'USD' => '$',
        'EUR' => '€',
        'SAR' => 'ر.س'
    ];
    
    $symbol = $currency_symbols[CURRENCY] ?? CURRENCY;
    return number_format($amount, 2) . ' ' . $symbol;
}

// دالة تنسيق التاريخ
function formatDate($date) {
    global $system_settings;
    $date_format = $system_settings['date_format'] ?? 'Y-m-d';
    return date($date_format, strtotime($date));
}

// دالة تنظيف المدخلات
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// دالة فحص معدل الطلبات (Rate Limiting)
function checkRateLimit($action, $limit = 10, $window = 60) {
    $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $current_time = time();
    
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    // تنظيف الطلبات القديمة
    $_SESSION['rate_limits'][$key] = array_filter(
        $_SESSION['rate_limits'][$key],
        function($timestamp) use ($current_time, $window) {
            return ($current_time - $timestamp) < $window;
        }
    );
    
    // فحص الحد الأقصى
    if (count($_SESSION['rate_limits'][$key]) >= $limit) {
        return false;
    }
    
    // إضافة الطلب الحالي
    $_SESSION['rate_limits'][$key][] = $current_time;
    return true;
}

// دالة تسجيل الأخطاء
function logError($message, $context = []) {
    $log_file = __DIR__ . '/../logs/error.log';
    $log_dir = dirname($log_file);
    
    // إنشاء مجلد اللوجات إذا لم يكن موجود
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'guest';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $log_entry = [
        'timestamp' => $timestamp,
        'user_id' => $user_id,
        'ip' => $ip,
        'message' => $message,
        'context' => $context
    ];
    
    $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

// دالة تسجيل النشاطات
function logActivity($action, $description, $reference_id = null, $reference_type = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, reference_id, reference_type, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $description,
            $reference_id,
            $reference_type,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        logError('Failed to log activity', [
            'action' => $action,
            'description' => $description,
            'error' => $e->getMessage()
        ]);
    }
}

// دالة فحص الصلاحيات المتقدمة
function checkPermissionAccess($permission_group) {
    if ($_SESSION['role'] === 'admin') {
        return true;
    }
    
    // قائمة الصلاحيات حسب المجموعة
    $permission_groups = [
        'inventory_management' => ['inventory_view', 'inventory_add', 'inventory_edit', 'inventory_delete'],
        'production_management' => ['production_view', 'production_add', 'production_edit', 'production_delete'],
        'financial_management' => ['financial_view', 'financial_add', 'financial_edit', 'financial_delete'],
        'hr_management' => ['hr_view', 'hr_add', 'hr_edit', 'hr_delete'],
        'reports_access' => ['reports_view', 'reports_export'],
        'system_management' => ['system_settings', 'user_management', 'backup_restore']
    ];
    
    if (!isset($permission_groups[$permission_group])) {
        return false;
    }
    
    $required_permissions = $permission_groups[$permission_group];
    
    if (isset($_SESSION['permissions'])) {
        $user_permissions = json_decode($_SESSION['permissions'], true);
        
        // فحص إذا كان المستخدم لديه أي من الصلاحيات المطلوبة
        foreach ($required_permissions as $perm) {
            if (in_array($perm, $user_permissions)) {
                return true;
            }
        }
    }
    
    return false;
}

// دالة توليد CSRF Token
function generateCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// دالة التحقق من CSRF Token
function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// دالة إنشاء جدول اللوجات إذا لم يكن موجود
function createActivityLogsTable() {
    global $pdo;
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NULL,
                action VARCHAR(100) NOT NULL,
                description TEXT,
                reference_id INT NULL,
                reference_type VARCHAR(50) NULL,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_user_id (user_id),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at)
            )
        ");
    } catch (Exception $e) {
        logError('Failed to create activity_logs table', ['error' => $e->getMessage()]);
    }
}

// دالة للحصول على المسار النسبي الصحيح
function getIncludePath($file) {
    $current_dir = dirname($_SERVER['PHP_SELF']);
    $levels = substr_count($current_dir, '/') - 1;
    $prefix = str_repeat('../', max(0, $levels));
    return $prefix . $file;
}

// إنشاء جدول اللوجات عند تحميل الملف
createActivityLogsTable();
?>


