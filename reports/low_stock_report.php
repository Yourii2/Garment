<?php
require_once '../config/config.php';
checkLogin();

$page_title = 'تقرير المخزون المنخفض';

// جلب الأصناف ذات المخزون المنخفض
$low_stock_items = [];

try {
    $stmt = $pdo->query("
        SELECT 
            item_name,
            item_code,
            category,
            current_stock,
            minimum_stock,
            unit,
            unit_price,
            supplier_name,
            (minimum_stock - current_stock) as shortage_quantity,
            ((minimum_stock - current_stock) * unit_price) as shortage_value
        FROM inventory_items 
        WHERE current_stock <= minimum_stock
        ORDER BY (current_stock / minimum_stock) ASC
    ");
    
    $low_stock_items = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error_message = 'خطأ في جلب بيانات المخزون المنخفض: ' . $e->getMessage();
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
                <h1 class="h2">تقرير المخزون المنخفض</h1>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>طباعة
                </button>
            </div>

            <?php if (empty($low_stock_items)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                جميع الأصناف في المخزون ضمن الحد الآمن
            </div>
            <?php else: ?>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                يوجد <?= count($low_stock_items) ?> صنف يحتاج إعادة تموين
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>الأصناف التي تحتاج إعادة تموين</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>كود الصنف</th>
                                    <th>اسم الصنف</th>
                                    <th>الفئة</th>
                                    <th>الكمية الحالية</th>
                                    <th>الحد الأدنى</th>
                                    <th>النقص</th>
                                    <th>قيمة النقص</th>
                                    <th>المورد</th>
                                    <th>الأولوية</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock_items as $item): 
                                    $priority_ratio = $item['current_stock'] / $item['minimum_stock'];
                                    $priority_class = $priority_ratio <= 0.5 ? 'danger' : ($priority_ratio <= 0.8 ? 'warning' : 'info');
                                    $priority_text = $priority_ratio <= 0.5 ? 'عاجل' : ($priority_ratio <= 0.8 ? 'مهم' : 'عادي');
                                ?>
                                <tr>
                                    <td><?= $item['item_code'] ?></td>
                                    <td><?= $item['item_name'] ?></td>
                                    <td><?= $item['category'] ?></td>
                                    <td><?= $item['current_stock'] ?> <?= $item['unit'] ?></td>
                                    <td><?= $item['minimum_stock'] ?> <?= $item['unit'] ?></td>
                                    <td class="text-danger"><?= $item['shortage_quantity'] ?> <?= $item['unit'] ?></td>
                                    <td class="text-danger"><?= number_format($item['shortage_value'], 2) ?> ج.م</td>
                                    <td><?= $item['supplier_name'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $priority_class ?>"><?= $priority_text ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>