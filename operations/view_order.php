<?php
require_once '../config/config.php';
checkLogin();

$order_id = $_GET['id'] ?? 0;

// جلب بيانات الطلبية
$stmt = $pdo->prepare("
    SELECT o.*, 
           SUM(COALESCE(oi.quantity * oi.unit_price, 0)) as products_total,
           (SUM(COALESCE(oi.quantity * oi.unit_price, 0)) + COALESCE(o.shipping_cost, 0)) as order_total
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error_message'] = 'الطلبية غير موجودة';
    header('Location: orders.php');
    exit;
}

// جلب منتجات الطلبية
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.product_code
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$page_title = 'تفاصيل الطلبية';
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
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-file-alt me-2"></i>تفاصيل الطلبية: <?= htmlspecialchars($order['order_number']) ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button onclick="printInvoice(<?= $order['id'] ?>)" class="btn btn-success me-2">
                        <i class="fas fa-print me-1"></i>طباعة الفاتورة
                    </button>
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- معلومات العميل -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user me-2"></i>معلومات العميل</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>الاسم:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                            <p><strong>الهاتف:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                            <p><strong>العنوان:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- معلومات الطلبية -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle me-2"></i>معلومات الطلبية</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>رقم الطلبية:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
                            <p><strong>التاريخ:</strong> <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></p>
                            <p><strong>الحالة:</strong> 
                                <span class="badge bg-warning"><?= $order['status'] ?></span>
                            </p>
                            <p><strong>الإجمالي:</strong> 
                                <span class="text-success fw-bold"><?= number_format($order['order_total'], 2) ?> ج.م</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- منتجات الطلبية -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-shopping-cart me-2"></i>منتجات الطلبية</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>سعر الوحدة</th>
                                    <th>المجموع</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= number_format($item['unit_price'], 2) ?> ج.م</td>
                                        <td><?= number_format($item['quantity'] * $item['unit_price'], 2) ?> ج.م</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">مجموع المنتجات:</th>
                                    <th><?= number_format($order['products_total'], 2) ?> ج.م</th>
                                </tr>
                                <?php if ($order['shipping_cost'] > 0): ?>
                                <tr>
                                    <th colspan="3">مصروفات الشحن:</th>
                                    <th><?= number_format($order['shipping_cost'], 2) ?> ج.م</th>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-success">
                                    <th colspan="3">الإجمالي النهائي:</th>
                                    <th><?= number_format($order['order_total'], 2) ?> ج.م</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($order['notes']): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-sticky-note me-2"></i>ملاحظات</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
function printInvoice(orderId) {
    window.open('print_invoice.php?id=' + orderId, '_blank', 'width=400,height=600');
}
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>