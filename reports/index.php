<?php
require_once '../config/config.php';
checkLogin();

$page_title = 'التقارير';
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
    <link href="<?= BASE_URL ?>/../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? "صفحة" ?> - <?= SYSTEM_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">التقارير</h1>
            </div>

            <!-- تقارير المخزون -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-boxes me-2"></i>تقارير المخزون</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="inventory_summary.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-bar me-2"></i>ملخص المخزون العام
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="fabric_report.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-cut me-2"></i>تقرير الأقمشة
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="low_stock_report.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-exclamation-triangle me-2"></i>المخزون المنخفض
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- التقارير المالية -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-money-bill-wave me-2"></i>التقارير المالية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="profits_report.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-chart-line me-2"></i>تقرير الأرباح
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="treasury_report.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-vault me-2"></i>تقرير الخزائن
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="financial_summary.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-calculator me-2"></i>الملخص المالي
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تقارير المبيعات -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-shopping-cart me-2"></i>تقارير المبيعات</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="sales_report.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-chart-area me-2"></i>تقرير المبيعات
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="customer_sales_report.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-users me-2"></i>مبيعات العملاء
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تقارير الإنتاج -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-industry me-2"></i>تقارير الإنتاج</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="production_report.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-cogs me-2"></i>تقرير الإنتاج
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="worker_productivity_report.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-user-hard-hat me-2"></i>إنتاجية العمال
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تقارير الجودة -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-medal me-2"></i>تقارير الجودة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="quality_report.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-star me-2"></i>تقرير الجودة
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="defects_analysis_report.php" class="btn btn-outline-danger w-100">
                                <i class="fas fa-bug me-2"></i>تحليل العيوب
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تقارير الموارد البشرية -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-users-cog me-2"></i>تقارير الموارد البشرية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="employee_report.php" class="btn btn-outline-dark w-100">
                                <i class="fas fa-id-badge me-2"></i>تقرير الموظفين
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="worker_accounts_report.php" class="btn btn-outline-dark w-100">
                                <i class="fas fa-wallet me-2"></i>حسابات العمال
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- التحليلات المتقدمة -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-analytics me-2"></i>التحليلات المتقدمة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="performance_dashboard.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-tachometer-alt me-2"></i>لوحة الأداء
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="monthly_summary.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-alt me-2"></i>الملخص الشهري
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>