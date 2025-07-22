<?php
require_once 'config/config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// إعادة توجيه إلى لوحة التحكم
header('Location: dashboard.php');
exit;
?>