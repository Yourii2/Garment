<?php
// معالج الأخطاء المحسن
function handleDatabaseError($e, $operation = 'عملية قاعدة البيانات') {
    $error_id = uniqid();
    error_log("[$error_id] Database Error in $operation: " . $e->getMessage() . " - File: " . $e->getFile() . " - Line: " . $e->getLine());
    
    return [
        'success' => false,
        'message' => "خطأ في $operation. رقم المرجع: $error_id",
        'error_id' => $error_id
    ];
}

function logError($message, $context = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'user_id' => $_SESSION['user_id'] ?? 'غير محدد',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'غير محدد',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'غير محدد'
    ];
    
    error_log(json_encode($log_entry, JSON_UNESCAPED_UNICODE));
}

function handleException($exception) {
    $error_id = uniqid();
    logError("Uncaught Exception [$error_id]", [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (ini_get('display_errors')) {
        echo "<div class='alert alert-danger'>خطأ غير متوقع. رقم المرجع: $error_id</div>";
    } else {
        header('Location: /error.php?id=' . $error_id);
    }
}

set_exception_handler('handleException');

function validateDatabaseConnection($pdo) {
    try {
        $pdo->query('SELECT 1');
        return true;
    } catch (Exception $e) {
        logError('Database connection failed', ['error' => $e->getMessage()]);
        return false;
    }
}
?>