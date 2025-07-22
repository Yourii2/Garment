<?php
// دوال الأمان المحسنة
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCSRF() {
    return $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function checkPermissionAccess($required_permission) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
    
    if ($_SESSION['role'] !== 'admin' && !checkPermission($required_permission)) {
        $_SESSION['error_message'] = 'ليس لديك صلاحية للوصول لهذه الصفحة';
        header('Location: ../dashboard.php');
        exit;
    }
}

function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'], $max_size = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطأ في رفع الملف'];
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'حجم الملف كبير جداً'];
    }
    
    return ['success' => true];
}
?>