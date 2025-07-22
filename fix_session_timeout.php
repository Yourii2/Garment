<?php
echo "<h1>إصلاح SESSION_TIMEOUT</h1>";

$config_file = 'config/config.php';
$content = file_get_contents($config_file);

// البحث عن مكان إضافة SESSION_TIMEOUT
if (strpos($content, 'SESSION_TIMEOUT') === false) {
    // إضافة SESSION_TIMEOUT بعد تعريف BASE_URL
    $search = "define('BASE_URL', 'http://localhost/NewDragon');";
    $replace = "define('BASE_URL', 'http://localhost/NewDragon');

// إعدادات الجلسة
define('SESSION_TIMEOUT', 3600); // ساعة واحدة";
    
    $content = str_replace($search, $replace, $content);
    
    if (file_put_contents($config_file, $content)) {
        echo "✅ تم إضافة SESSION_TIMEOUT إلى config.php<br>";
    } else {
        echo "❌ فشل في تحديث config.php<br>";
    }
} else {
    echo "✅ SESSION_TIMEOUT موجود بالفعل<br>";
}

// التحقق من وجود دوال أخرى مطلوبة
$required_functions = ['cleanInput', 'validateCSRF', 'checkRateLimit', 'generateCSRF'];
$missing_functions = [];

foreach ($required_functions as $func) {
    if (strpos($content, "function $func") === false) {
        $missing_functions[] = $func;
    }
}

if (!empty($missing_functions)) {
    echo "<br>❌ دوال مفقودة: " . implode(', ', $missing_functions) . "<br>";
    
    // إضافة الدوال المفقودة
    $functions_code = "

// دالة تنظيف المدخلات
function cleanInput(\$input) {
    return htmlspecialchars(trim(\$input), ENT_QUOTES, 'UTF-8');
}

// دالة التحقق من CSRF Token
function validateCSRF(\$token) {
    return isset(\$_SESSION['csrf_token']) && hash_equals(\$_SESSION['csrf_token'], \$token);
}

// دالة توليد CSRF Token
function generateCSRF() {
    if (!isset(\$_SESSION['csrf_token'])) {
        \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return \$_SESSION['csrf_token'];
}

// دالة فحص معدل الطلبات
function checkRateLimit(\$action, \$limit = 10, \$window = 60) {
    \$key = \$action . '_' . (\$_SERVER['REMOTE_ADDR'] ?? 'unknown');
    \$current_time = time();
    
    if (!isset(\$_SESSION['rate_limits'][\$key])) {
        \$_SESSION['rate_limits'][\$key] = [];
    }
    
    // تنظيف الطلبات القديمة
    \$_SESSION['rate_limits'][\$key] = array_filter(
        \$_SESSION['rate_limits'][\$key],
        function(\$timestamp) use (\$current_time, \$window) {
            return (\$current_time - \$timestamp) < \$window;
        }
    );
    
    // فحص الحد الأقصى
    if (count(\$_SESSION['rate_limits'][\$key]) >= \$limit) {
        return false;
    }
    
    // إضافة الطلب الحالي
    \$_SESSION['rate_limits'][\$key][] = \$current_time;
    return true;
}
";
    
    $content .= $functions_code;
    
    if (file_put_contents($config_file, $content)) {
        echo "✅ تم إضافة الدوال المفقودة<br>";
    } else {
        echo "❌ فشل في إضافة الدوال<br>";
    }
} else {
    echo "✅ جميع الدوال المطلوبة موجودة<br>";
}

echo "<br><strong>جرب فتح login.php الآن</strong>";
?>