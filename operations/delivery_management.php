<?php
require_once '../config/config.php';
checkLogin();

// معالجة تعيين مندوب للطلبيات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_representative'])) {
    try {
        $representative_id = $_POST['representative_id'];
        $order_ids = $_POST['order_ids'] ?? [];
        
        if (!empty($order_ids) && $representative_id) {
            $pdo->beginTransaction();
            
            // تحديث الطلبيات المحددة
            $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET representative_id = ?, 
                    status = 'ready_for_delivery',
                    updated_at = CURRENT_TIMESTAMP
                WHERE id IN ($placeholders)
            ");
            
            $params = array_merge([$representative_id], $order_ids);
            $stmt->execute($params);
            
            $pdo->commit();
            $_SESSION['success_message'] = 'تم تعيين المندوب للطلبيات المحددة بنجاح';
        } else {
            $_SESSION['error_message'] = 'يرجى اختيار مندوب وطلبيات';
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: delivery_management.php');
    exit;
}

// معالجة إزالة مندوب من الطلبية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_representative'])) {
    try {
        $order_id = $_POST['order_id'];
        
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET representative_id = NULL, 
                status = 'pending',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        $stmt->execute([$order_id]);
        $_SESSION['success_message'] = 'تم إلغاء تعيين المندوب وإرجاع الطلبية للانتظار';
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: delivery_management.php');
    exit;
}

// إضافة عمود representative_id لجدول orders إذا لم يكن موجود
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'representative_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN representative_id INT NULL AFTER customer_address");
        $pdo->exec("ALTER TABLE orders ADD INDEX idx_representative_id (representative_id)");
    }
} catch (Exception $e) {
    // العمود موجود بالفعل
}

// جلب المناديب
$stmt = $pdo->query("SELECT * FROM representatives ORDER BY name ASC");
$representatives = $stmt->fetchAll();

// جلب الطلبيات غير المعينة لمناديب
$stmt = $pdo->query("
    SELECT o.*, 
           COUNT(oi.id) as products_count,
           SUM(oi.quantity) as total_quantity,
           SUM(COALESCE(oi.quantity * oi.unit_price, 0)) as products_total,
           COALESCE(o.shipping_cost, 0) as shipping_cost,
           (SUM(COALESCE(oi.quantity * oi.unit_price, 0)) + COALESCE(o.shipping_cost, 0)) as order_total
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.representative_id IS NULL 
    AND o.status IN ('pending', 'ready', 'completed')
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$unassigned_orders = $stmt->fetchAll();

// جلب الطلبيات المعينة لمناديب
$stmt = $pdo->query("
    SELECT o.*, r.name as representative_name,
           COUNT(oi.id) as products_count,
           SUM(oi.quantity) as total_quantity,
           SUM(COALESCE(oi.quantity * oi.unit_price, 0)) as products_total,
           COALESCE(o.shipping_cost, 0) as shipping_cost,
           (SUM(COALESCE(oi.quantity * oi.unit_price, 0)) + COALESCE(o.shipping_cost, 0)) as order_total
    FROM orders o
    LEFT JOIN representatives r ON o.representative_id = r.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.representative_id IS NOT NULL
    GROUP BY o.id
    ORDER BY r.name ASC, o.created_at DESC
");
$assigned_orders = $stmt->fetchAll();

$page_title = 'إدارة التوصيل';
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
                    <i class="fas fa-truck me-2"></i>إدارة التوصيل
                </h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- الطلبيات غير المعينة -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>الطلبيات غير المعينة لمناديب
                        <span class="badge bg-warning ms-2"><?= count($unassigned_orders) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($unassigned_orders)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3 d-block"></i>
                            جميع الطلبيات معينة لمناديب
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">اختيار المندوب</label>
                                    <select name="representative_id" class="form-select" required>
                                        <option value="">اختر المندوب</option>
                                        <?php foreach ($representatives as $rep): ?>
                                            <option value="<?= $rep['id'] ?>"><?= htmlspecialchars($rep['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" name="assign_representative" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-1"></i>تعيين المندوب للطلبيات المحددة
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                                            </th>
                                            <th>رقم الطلبية</th>
                                            <th>العميل</th>
                                            <th>الهاتف</th>
                                            <th>العنوان</th>
                                            <th>عدد المنتجات</th>
                                            <th>إجمالي المبلغ</th>
                                            <th>الحالة</th>
                                            <th>تاريخ الإنشاء</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($unassigned_orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="order_ids[]" value="<?= $order['id'] ?>" class="order-checkbox">
                                                </td>
                                                <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                                                <td><?= htmlspecialchars($order['customer_address']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $order['products_count'] ?></span>
                                                    <small class="text-muted">(<?= $order['total_quantity'] ?> قطعة)</small>
                                                </td>
                                                <td>
                                                    <strong class="text-success"><?= number_format($order['order_total'], 2) ?> ج.م</strong>
                                                    <?php if ($order['shipping_cost'] > 0): ?>
                                                        <br><small class="text-muted">شحن: <?= number_format($order['shipping_cost'], 2) ?> ج.م</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning"><?= $order['status'] ?></span>
                                                </td>
                                                <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- الطلبيات المعينة -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-check me-2"></i>الطلبيات المعينة للمناديب
                        <span class="badge bg-success ms-2"><?= count($assigned_orders) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assigned_orders)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            لا توجد طلبيات معينة للمناديب
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>رقم الطلبية</th>
                                        <th>العميل</th>
                                        <th>الهاتف</th>
                                        <th>العنوان</th>
                                        <th>المندوب</th>
                                        <th>عدد المنتجات</th>
                                        <th>إجمالي المبلغ</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assigned_orders as $order): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                                            <td><?= htmlspecialchars($order['customer_address']) ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?= htmlspecialchars($order['representative_name']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $order['products_count'] ?></span>
                                                <small class="text-muted">(<?= $order['total_quantity'] ?> قطعة)</small>
                                            </td>
                                            <td>
                                                <strong class="text-success"><?= number_format($order['order_total'], 2) ?> ج.م</strong>
                                                <?php if ($order['shipping_cost'] > 0): ?>
                                                    <br><small class="text-muted">شحن: <?= number_format($order['shipping_cost'], 2) ?> ج.م</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'pending' => 'warning',
                                                    'ready' => 'info',
                                                    'in_production' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    'ready_for_delivery' => 'info',
                                                    'out_for_delivery' => 'warning',
                                                    'delivered' => 'success',
                                                    'returned' => 'danger'
                                                ];
                                                $status_names = [
                                                    'pending' => 'في الانتظار',
                                                    'ready' => 'جاهز',
                                                    'in_production' => 'قيد الإنتاج',
                                                    'completed' => 'مكتمل',
                                                    'cancelled' => 'ملغي',
                                                    'ready_for_delivery' => 'جاهز للتوصيل',
                                                    'out_for_delivery' => 'في الطريق',
                                                    'delivered' => 'تم التوصيل',
                                                    'returned' => 'مرتجع'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $status_colors[$order['status']] ?? 'secondary' ?>">
                                                    <?= $status_names[$order['status']] ?? $order['status'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" onclick="removeRepresentative(<?= $order['id'] ?>)">
                                                    <i class="fas fa-times"></i> إلغاء
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function removeRepresentative(orderId) {
    if (confirm('هل أنت متأكد من إلغاء تعيين المندوب لهذه الطلبية؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="remove_representative" value="1">
            <input type="hidden" name="order_id" value="${orderId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>