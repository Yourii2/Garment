<?php
require_once 'config/config.php';

// تسجيل نشاط تسجيل الخروج
if (isset($_SESSION['user_id'])) {
    logActivity('user_logout', 'تسجيل خروج');
}

// تنظيف الجلسة
session_unset();
session_destroy();

// إنشاء جلسة جديدة للرسائل
session_start();
$_SESSION['success_message'] = 'تم تسجيل الخروج بنجاح';

header('Location: login.php');
exit;
?>