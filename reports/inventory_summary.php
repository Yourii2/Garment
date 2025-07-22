<?php
require_once '../config/config.php';
checkLogin();

$page_title = 'ملخص المخزون العام';

// جلب بيانات المخزون
$inventory_data = [];
$total_value = 0;
$low_stock_count = 0;

try {
    $stmt = $pdo->query("
        SELECT 
            i.id,
            i.item_name,
            i.item_code,
            i.category,
            i.current_stock,
            i.minimum_stock,
            i.unit_price,
            i.unit,
            (i.current_stock * i.unit_price) as total_value,
            CASE 
                WHEN i.current_stock <= i.minimum_stock THEN 'منخفض'
                WHEN i.current_stock <= (i.minimum_stock * 1.5) THEN 'تحذير'
                ELSE 'طبيعي'
            END as stock_status
        FROM inventory_items i
        ORDER BY i.category, i.item_name
    ");
    
    $inventory_data = $stmt->fetchAll();
    
    foreach ($inventory_data as $item) {
        $total_value += $item['total_value'];
        if ($item['stock_status'] === 'منخفض') {
            $low_stock_count++;
        }
    }
    
} catch (Exception $e) {
    $error_message = 'خطأ في جلب بيانات المخزون: ' . $e->getMessage();
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
                <h1 class="h2">ملخص المخزون العام</h1>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>طباعة
                </button>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h4>إجمالي الأصناف</h4>
                            <h2><?= count($inventory_data) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h4>قيمة المخزون</h4>
                            <h2><?= number_format($total_value, 2) ?> ج.م</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h4>مخزون منخفض</h4>
                            <h2><?= $low_stock_count ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول المخزون -->
            <div class="card">
                <div class="card-header">
                    <h5>تفاصيل المخزون</h5>
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
                                    <th>سعر الوحدة</th>
                                    <th>القيمة الإجمالية</th>
                                    <th>حالة المخزون</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory_data as $item): ?>
                                <tr>
                                    <td><?= $item['item_code'] ?></td>
                                    <td><?= $item['item_name'] ?></td>
                                    <td><?= $item['category'] ?></td>
                                    <td><?= $item['current_stock'] ?> <?= $item['unit'] ?></td>
                                    <td><?= $item['minimum_stock'] ?></td>
                                    <td><?= number_format($item['unit_price'], 2) ?> ج.م</td>
                                    <td><?= number_format($item['total_value'], 2) ?> ج.م</td>
                                    <td>
                                        <span class="badge bg-<?= $item['stock_status'] === 'منخفض' ? 'danger' : ($item['stock_status'] === 'تحذير' ? 'warning' : 'success') ?>">
                                            <?= $item['stock_status'] ?>
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