<?php
require_once 'config/config.php';
checkLogin();

// جلب الإحصائيات من قاعدة البيانات
$stats = [
    'total_orders' => 0,
    'total_fabrics' => 0,
    'total_accessories' => 0,
    'total_low_stock' => 0
];

try {
    // إحصائيات الأقمشة
    $stmt = $pdo->query("SELECT COUNT(*) as total_fabrics FROM fabric_types WHERE is_active = 1");
    $result = $stmt->fetch();
    $stats['total_fabrics'] = $result ? ($result['total_fabrics'] ?: 0) : 0;
    
} catch (Exception $e) {
    // في حالة الخطأ، استخدم القيم الافتراضية
}

$page_title = 'لوحة التحكم - ' . SYSTEM_NAME;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/unified-style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="page-header">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        مرحباً بك في لوحة التحكم
                    </h1>
                </div>

                <!-- الإحصائيات -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stats-label mb-1">إجمالي الأقمشة</div>
                                        <div class="stats-number"><?= number_format($stats['total_fabrics'] ?? 0) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cut stats-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
