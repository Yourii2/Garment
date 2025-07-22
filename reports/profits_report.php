<?php
require_once '../config/config.php';
checkLogin();

$page_title = 'تقرير الأرباح حسب التاريخ';

// معالجة الفلاتر
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'daily';

// جلب بيانات الأرباح
$profits_data = [];
$total_revenue = 0;
$total_costs = 0;
$total_profit = 0;

try {
    // حساب الإيرادات والتكاليف حسب الفترة المحددة
    $date_format = match($group_by) {
        'daily' => '%Y-%m-%d',
        'weekly' => '%Y-%u',
        'monthly' => '%Y-%m',
        'yearly' => '%Y',
        default => '%Y-%m-%d'
    };
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, ?) as period,
            SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) as revenue,
            SUM(CASE WHEN type = 'cost' THEN amount ELSE 0 END) as costs,
            SUM(CASE WHEN type = 'revenue' THEN amount ELSE -amount END) as profit
        FROM (
            -- الإيرادات من المبيعات
            SELECT created_at, 'revenue' as type, total_amount as amount
            FROM sales 
            WHERE created_at BETWEEN ? AND ?
            
            UNION ALL
            
            -- تكاليف المواد الخام
            SELECT created_at, 'cost' as type, total_cost as amount
            FROM inventory_invoices 
            WHERE created_at BETWEEN ? AND ?
            
            UNION ALL
            
            -- تكاليف الإنتاج
            SELECT created_at, 'cost' as type, fabric_cost_per_unit * total_quantity as amount
            FROM production_orders 
            WHERE created_at BETWEEN ? AND ?
            
            UNION ALL
            
            -- المصروفات
            SELECT created_at, 'cost' as type, amount
            FROM expenses 
            WHERE created_at BETWEEN ? AND ?
        ) as financial_data
        GROUP BY period
        ORDER BY period
    ");
    
    $stmt->execute([
        $date_format, 
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date
    ]);
    
    $profits_data = $stmt->fetchAll();
    
    // حساب الإجماليات
    foreach ($profits_data as $row) {
        $total_revenue += $row['revenue'];
        $total_costs += $row['costs'];
        $total_profit += $row['profit'];
    }
    
} catch (Exception $e) {
    $error_message = 'خطأ في جلب بيانات الأرباح: ' . $e->getMessage();
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
                <h1 class="h2">تقرير الأرباح حسب التاريخ</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>طباعة
                    </button>
                </div>
            </div>

            <!-- فلاتر التقرير -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">فلاتر التقرير</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="group_by" class="form-label">تجميع حسب</label>
                            <select class="form-select" id="group_by" name="group_by">
                                <option value="daily" <?= $group_by === 'daily' ? 'selected' : '' ?>>يومي</option>
                                <option value="weekly" <?= $group_by === 'weekly' ? 'selected' : '' ?>>أسبوعي</option>
                                <option value="monthly" <?= $group_by === 'monthly' ? 'selected' : '' ?>>شهري</option>
                                <option value="yearly" <?= $group_by === 'yearly' ? 'selected' : '' ?>>سنوي</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">
                                <i class="fas fa-search me-2"></i>تطبيق الفلاتر
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ملخص الأرباح -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">إجمالي الإيرادات</h4>
                                    <h2><?= number_format($total_revenue, 2) ?> ج.م</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-arrow-up fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">إجمالي التكاليف</h4>
                                    <h2><?= number_format($total_costs, 2) ?> ج.م</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-arrow-down fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white <?= $total_profit >= 0 ? 'bg-primary' : 'bg-warning' ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">صافي الربح</h4>
                                    <h2><?= number_format($total_profit, 2) ?> ج.م</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول تفاصيل الأرباح -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">تفاصيل الأرباح حسب الفترة</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?>
                    </div>
                    <?php elseif (empty($profits_data)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>لا توجد بيانات للفترة المحددة
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>الفترة</th>
                                    <th>الإيرادات</th>
                                    <th>التكاليف</th>
                                    <th>صافي الربح</th>
                                    <th>هامش الربح %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($profits_data as $row): 
                                    $profit_margin = $row['revenue'] > 0 ? ($row['profit'] / $row['revenue']) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= $row['period'] ?></td>
                                    <td class="text-success"><?= number_format($row['revenue'], 2) ?> ج.م</td>
                                    <td class="text-danger"><?= number_format($row['costs'], 2) ?> ج.م</td>
                                    <td class="<?= $row['profit'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                                        <?= number_format($row['profit'], 2) ?> ج.م
                                    </td>
                                    <td class="<?= $profit_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($profit_margin, 1) ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>الإجمالي</th>
                                    <th class="text-success"><?= number_format($total_revenue, 2) ?> ج.م</th>
                                    <th class="text-danger"><?= number_format($total_costs, 2) ?> ج.م</th>
                                    <th class="<?= $total_profit >= 0 ? 'text-primary' : 'text-warning' ?>">
                                        <?= number_format($total_profit, 2) ?> ج.م
                                    </th>
                                    <th class="<?= $total_revenue > 0 && ($total_profit / $total_revenue) * 100 >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= $total_revenue > 0 ? number_format(($total_profit / $total_revenue) * 100, 1) : 0 ?>%
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
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