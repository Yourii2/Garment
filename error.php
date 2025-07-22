<?php
require_once 'config/config.php';

$error_id = $_GET['id'] ?? 'غير محدد';
$page_title = 'خطأ في النظام';

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? "صفحة" ?> - <?= SYSTEM_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h3>حدث خطأ غير متوقع</h3>
                    <p class="text-muted">نعتذر عن هذا الخطأ. تم تسجيل المشكلة وسيتم حلها قريباً.</p>
                    <p><strong>رقم المرجع:</strong> <?= htmlspecialchars($error_id) ?></p>
                    <div class="mt-4">
                        <a href="dashboard.php" class="btn btn-primary">العودة للوحة التحكم</a>
                        <button onclick="history.back()" class="btn btn-secondary">الصفحة السابقة</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>