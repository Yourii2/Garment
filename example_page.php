<?php
require_once '../config/config.php';
checkLogin();

$page_title = "عنوان الصفحة - " . SYSTEM_NAME;

include '../includes/header.php';
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

    <!-- عنوان الصفحة -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-5 pb-3 mb-4 border-bottom">
    <h1 class="h2">
        <i class="fas fa-icon me-2"></i>عنوان الصفحة
    </h1>
    <button type="button" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>إضافة جديد
    </button>
</div>

<!-- محتوى الصفحة هنا -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>المحتوى
        </h5>
    </div>
    <div class="card-body">
        <!-- المحتوى -->
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>