<?php
require_once '../config/config.php';
checkLogin();

$page_title = 'تحليل العيوب';

// فلاتر
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// جلب تحليل العيوب حسب المرحلة
$defects_by_stage = [];
$defects_by_worker = [];
$defects_by_product = [];

try {
    // العيوب حسب المرحلة
    $stmt = $pdo->prepare("
        SELECT 
            ms.name as stage_name,
            SUM(wa.quantity_completed) as total_produced,
            SUM(wa.quantity_defective) as total_defective,
            COUNT(wa.id) as assignments_count,
            CASE 
                WHEN SUM(wa.quantity_completed) > 0 
                THEN (SUM(wa.quantity_defective) / SUM(wa.quantity_completed)) * 100 
                ELSE 0 
            END as defect_rate
        FROM worker_assignments wa
        JOIN manufacturing_stages ms ON wa.stage_id = ms.id
        WHERE wa.status = 'completed' 
        AND DATE(wa.completed_at) BETWEEN ? AND ?
        GROUP BY ms.id, ms.name
        ORDER BY defect_rate DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $defects_by_stage = $stmt->fetchAll();
    
    // العيوب حسب العامل
    $stmt = $pdo->prepare("
        SELECT 
            w.name as worker_name,
            SUM(wa.quantity_completed) as total_produced,
            SUM(wa.quantity_defective) as total_defective,
            COUNT(wa.id) as assignments_count,
            CASE 
                WHEN SUM(wa.quantity_completed) > 0 
                THEN (SUM(wa.quantity_defective) / SUM(wa.quantity_completed)) * 100 
                ELSE 0 
            END as defect_rate
        FROM worker_assignments wa
        JOIN workers w ON wa.worker_id = w.id
        WHERE wa.status = 'completed' 
        AND DATE(wa.completed_at) BETWEEN ? AND ?
        GROUP BY w.id, w.name
        HAVING total_produced > 0
        ORDER BY defect_rate DESC
        LIMIT 10
    ");
    $stmt->execute([$start_date, $end_date]);
    $defects_by_worker = $stmt->fetchAll();
    
    // العيوب حسب المنتج
    $stmt = $pdo->prepare("
        SELECT 
            p.name as product_name,
            SUM(wa.quantity_completed) as total_produced,
            SUM(wa.quantity_defective) as total_defective,
            CASE 
                WHEN SUM(wa.quantity_completed) > 0 
                THEN (SUM(wa.quantity_defective) / SUM(wa.quantity_completed)) * 100 
                ELSE 0 
            END as defect_rate
        FROM worker_assignments wa
        JOIN cutting_orders co ON wa.cutting_order_id = co.id
        JOIN products p ON co.product_id = p.id
        WHERE wa.status = 'completed' 
        AND DATE(wa.completed_at) BETWEEN ? AND ?
        GROUP BY p.id, p.name
        HAVING total_produced > 0
        ORDER BY defect_rate DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $defects_by_product = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error_message = 'خطأ في جلب تحليل العيوب: ' . $e->getMessage();
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
                <h1 class="h2">تحليل العيوب</h1>
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

            <!-- العيوب حسب المرحلة -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>العيوب حسب المرحلة</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>المرحلة</th>
                                    <th>إجمالي المنتج</th>
                                    <th>إجمالي المعيب</th>
                                    <th>معدل العيوب</th>
                                    <th>عدد المهام</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($defects_by_stage as $stage): ?>
                                <tr>
                                    <td><?= $stage['stage_name'] ?></td>
                                    <td><?= $stage['total_produced'] ?></td>
                                    <td><?= $stage['total_defective'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $stage['defect_rate'] <= 2 ? 'success' : ($stage['defect_rate'] <= 5 ? 'warning' : 'danger') ?>">
                                            <?= number_format($stage['defect_rate'], 2) ?>%
                                        </span>
                                    </td>
                                    <td><?= $stage['assignments_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- العيوب حسب العامل -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>أعلى 10 عمال في معدل العيوب</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>العامل</th>
                                    <th>إجمالي المنتج</th>
                                    <th>إجمالي المعيب</th>
                                    <th>معدل العيوب</th>
                                    <th>عدد المهام</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($defects_by_worker as $worker): ?>
                                <tr>
                                    <td><?= $worker['worker_name'] ?></td>
                                    <td><?= $worker['total_produced'] ?></td>
                                    <td><?= $worker['total_defective'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $worker['defect_rate'] <= 2 ? 'success' : ($worker['defect_rate'] <= 5 ? 'warning' : 'danger') ?>">
                                            <?= number_format($worker['defect_rate'], 2) ?>%
                                        </span>
                                    </td>
                                    <td><?= $worker['assignments_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- العيوب حسب المنتج -->
            <div class="card">
                <div class="card-header">
                    <h5>العيوب حسب المنتج</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>إجمالي المنتج</th>
                                    <th>إجمالي المعيب</th>
                                    <th>معدل العيوب</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($defects_by_product as $product): ?>
                                <tr>
                                    <td><?= $product['product_name'] ?></td>
                                    <td><?= $product['total_produced'] ?></td>
                                    <td><?= $product['total_defective'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $product['defect_rate'] <= 2 ? 'success' : ($product['defect_rate'] <= 5 ? 'warning' : 'danger') ?>">
                                            <?= number_format($product['defect_rate'], 2) ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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