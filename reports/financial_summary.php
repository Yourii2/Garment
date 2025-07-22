<?php
require_once '../config/config.php';
checkLogin();

$page_title = 'الملخص المالي';

// فلاتر
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// جلب البيانات المالية
$financial_data = [];

try {
    // إجمالي المبيعات
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0) as total_sales
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE DATE(o.created_at) BETWEEN ? AND ?
        AND o.status != 'cancelled'
    ");
    $stmt->execute([$start_date, $end_date]);
    $total_sales = $stmt->fetchColumn();
    
    // تكاليف الإنتاج
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(wa.quantity_completed * wa.cost_per_unit), 0) as production_costs
        FROM worker_assignments wa
        WHERE wa.is_paid = 1 
        AND DATE(wa.completed_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $production_costs = $stmt->fetchColumn();
    
    // تكاليف المواد الخام
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(ii.quantity * ii.unit_price), 0) as material_costs
        FROM inventory_transactions it
        JOIN inventory_items ii ON it.item_id = ii.id
        WHERE it.type = 'out' 
        AND DATE(it.created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $material_costs = $stmt->fetchColumn();
    
    // المصروفات العامة
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as general_expenses
        FROM treasury_transactions
        WHERE type = 'out' 
        AND category = 'expense'
        AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $general_expenses = $stmt->fetchColumn();
    
    // حساب الأرباح
    $total_costs = $production_costs + $material_costs + $general_expenses;
    $net_profit = $total_sales - $total_costs;
    $profit_margin = $total_sales > 0 ? ($net_profit / $total_sales) * 100 : 0;
    
    $financial_data = [
        'total_sales' => $total_sales,
        'production_costs' => $production_costs,
        'material_costs' => $material_costs,
        'general_expenses' => $general_expenses,
        'total_costs' => $total_costs,
        'net_profit' => $net_profit,
        'profit_margin' => $profit_margin
    ];
    
} catch (Exception $e) {
    $error_message = 'خطأ في جلب البيانات المالية: ' . $e->getMessage();
}

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
                <h1 class="h2">الملخص المالي</h1>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>طباعة
                </button>
            </div>

            <!-- فلاتر -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">تطبيق الفلاتر</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- الملخص المالي -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h4>إجمالي المبيعات</h4>
                            <h2><?= number_format($financial_data['total_sales'], 2) ?> ج.م</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h4>إجمالي التكاليف</h4>
                            <h2><?= number_format($financial_data['total_costs'], 2) ?> ج.م</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-<?= $financial_data['net_profit'] >= 0 ? 'primary' : 'warning' ?> text-white">
                        <div class="card-body">
                            <h4>صافي الربح</h4>
                            <h2><?= number_format($financial_data['net_profit'], 2) ?> ج.م</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h4>هامش الربح</h4>
                            <h2><?= number_format($financial_data['profit_margin'], 2) ?>%</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تفصيل التكاليف -->
            <div class="card">
                <div class="card-header">
                    <h5>تفصيل التكاليف والأرباح</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>البند</th>
                                    <th>المبلغ</th>
                                    <th>النسبة من المبيعات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td><strong>إجمالي المبيعات</strong></td>
                                    <td><strong><?= number_format($financial_data['total_sales'], 2) ?> ج.م</strong></td>
                                    <td><strong>100%</strong></td>
                                </tr>
                                <tr>
                                    <td>تكاليف الإنتاج</td>
                                    <td><?= number_format($financial_data['production_costs'], 2) ?> ج.م</td>
                                    <td><?= $financial_data['total_sales'] > 0 ? number_format(($financial_data['production_costs'] / $financial_data['total_sales']) * 100, 2) : 0 ?>%</td>
                                </tr>
                                <tr>
                                    <td>تكاليف المواد الخام</td>
                                    <td><?= number_format($financial_data['material_costs'], 2) ?> ج.م</td>
                                    <td><?= $financial_data['total_sales'] > 0 ? number_format(($financial_data['material_costs'] / $financial_data['total_sales']) * 100, 2) : 0 ?>%</td>
                                </tr>
                                <tr>
                                    <td>المصروفات العامة</td>
                                    <td><?= number_format($financial_data['general_expenses'], 2) ?> ج.م</td>
                                    <td><?= $financial_data['total_sales'] > 0 ? number_format(($financial_data['general_expenses'] / $financial_data['total_sales']) * 100, 2) : 0 ?>%</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>إجمالي التكاليف</strong></td>
                                    <td><strong><?= number_format($financial_data['total_costs'], 2) ?> ج.م</strong></td>
                                    <td><strong><?= $financial_data['total_sales'] > 0 ? number_format(($financial_data['total_costs'] / $financial_data['total_sales']) * 100, 2) : 0 ?>%</strong></td>
                                </tr>
                                <tr class="table-<?= $financial_data['net_profit'] >= 0 ? 'primary' : 'warning' ?>">
                                    <td><strong>صافي الربح</strong></td>
                                    <td><strong><?= number_format($financial_data['net_profit'], 2) ?> ج.م</strong></td>
                                    <td><strong><?= number_format($financial_data['profit_margin'], 2) ?>%</strong></td>
                                </tr>
                            </tbody>
                        </table>
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