<?php
require_once '../config/config.php';
checkLogin();

// معالجة إرسال منتج للمبيعات
if (isset($_POST['send_to_sales'])) {
    try {
        $cutting_order_id = $_POST['cutting_order_id'];
        $quantity_to_send = $_POST['quantity_to_send'];
        $quality_grade = $_POST['quality_grade'];
        $notes = $_POST['notes'] ?? '';
        
        $pdo->beginTransaction();
        
        // التحقق من الكمية المكتملة المتاحة
        $stmt = $pdo->prepare("
            SELECT co.*, p.name as product_name,
                   COALESCE(SUM(swa.quantity_completed), 0) as total_completed,
                   COALESCE(SUM(sp.quantity_sent), 0) as total_sent
            FROM cutting_orders co
            JOIN products p ON co.product_id = p.id
            LEFT JOIN production_stages ps ON co.id = ps.cutting_order_id
            LEFT JOIN stage_worker_assignments swa ON ps.id = swa.production_stage_id
            LEFT JOIN sales_products sp ON co.id = sp.cutting_order_id
            WHERE co.id = ?
            GROUP BY co.id
        ");
        $stmt->execute([$cutting_order_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception('أمر القص غير موجود');
        }
        
        $available_quantity = $order['total_completed'] - $order['total_sent'];
        
        if ($quantity_to_send > $available_quantity) {
            throw new Exception("الكمية المطلوبة ($quantity_to_send) أكبر من المتاحة ($available_quantity)");
        }
        
        // إدراج في جدول المنتجات الجاهزة للمبيعات
        $stmt = $pdo->prepare("
            INSERT INTO sales_products 
            (cutting_order_id, product_id, quantity_sent, quality_grade, send_date, notes, sent_by, status)
            VALUES (?, ?, ?, ?, CURDATE(), ?, ?, 'ready_for_sale')
        ");
        $stmt->execute([
            $cutting_order_id,
            $order['product_id'],
            $quantity_to_send,
            $quality_grade,
            $notes,
            $_SESSION['user_id']
        ]);
        
        // تحديث حالة أمر القص إذا تم إرسال كامل الكمية
        if ($quantity_to_send == $available_quantity) {
            $stmt = $pdo->prepare("UPDATE cutting_orders SET status = 'completed' WHERE id = ?");
            $stmt->execute([$cutting_order_id]);
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = "تم إرسال $quantity_to_send قطعة من {$order['product_name']} للمبيعات بنجاح";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: send_to_sales.php');
    exit;
}

// جلب أوامر القص المكتملة
try {
    $stmt = $pdo->query("
        SELECT co.*, p.name as product_name, p.code as product_code,
               COALESCE(SUM(swa.quantity_completed), 0) as total_completed,
               COALESCE(SUM(sp.quantity_sent), 0) as total_sent
        FROM cutting_orders co
        JOIN products p ON co.product_id = p.id
        LEFT JOIN production_stages ps ON co.id = ps.cutting_order_id
        LEFT JOIN stage_worker_assignments swa ON ps.id = swa.production_stage_id
        LEFT JOIN sales_products sp ON co.id = sp.cutting_order_id
        WHERE co.status = 'active'
        GROUP BY co.id
        ORDER BY co.cutting_date DESC
    ");
    $completed_orders = $stmt->fetchAll();
} catch (Exception $e) {
    $completed_orders = [];
    $_SESSION['error_message'] = 'خطأ في جلب البيانات: ' . $e->getMessage();
}

// جلب المنتجات المرسلة للمبيعات
try {
    // التحقق من وجود الجدول أولاً
    $stmt = $pdo->query("SHOW TABLES LIKE 'sales_products'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("
            SELECT sp.*, co.cutting_number, p.name as product_name, p.code as product_code,
                   u.username as sent_by_name
            FROM sales_products sp
            JOIN cutting_orders co ON sp.cutting_order_id = co.id
            JOIN products p ON sp.product_id = p.id
            JOIN users u ON sp.sent_by = u.id
            ORDER BY sp.send_date DESC, sp.created_at DESC
            LIMIT 50
        ");
        $sales_products = $stmt->fetchAll();
    } else {
        $sales_products = [];
        $_SESSION['info_message'] = 'جدول المبيعات غير موجود. قم بتشغيل create_missing_tables.php أولاً.';
    }
} catch (Exception $e) {
    $sales_products = [];
    $_SESSION['error_message'] = 'خطأ في جلب بيانات المبيعات: ' . $e->getMessage();
}

$page_title = 'إرسال للمبيعات';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/../../assets/css/style.css" rel="stylesheet">
    <style>
        .main-content { margin-top: 60px; }
        .quality-badge {
            font-size: 0.8em;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-shipping-fast me-2"></i><?= $page_title ?>
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

                <!-- المنتجات الجاهزة للإرسال -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-box-open me-2"></i>المنتجات الجاهزة للإرسال
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>رقم الأمر</th>
                                        <th>المنتج</th>
                                        <th>الكمية المكتملة</th>
                                        <th>المرسل للمبيعات</th>
                                        <th>المتاح للإرسال</th>
                                        <th>تاريخ القص</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($completed_orders)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                لا توجد منتجات جاهزة للإرسال
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($completed_orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($order['cutting_number']) ?></strong>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($order['product_name']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($order['product_code']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?= $order['total_completed'] ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $order['total_sent'] ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $order['available_quantity'] ?></span>
                                                </td>
                                                <td>
                                                    <?= date('Y-m-d', strtotime($order['cutting_date'])) ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="sendToSales(<?= $order['id'] ?>, '<?= htmlspecialchars($order['product_name']) ?>', <?= $order['available_quantity'] ?>)">
                                                        <i class="fas fa-paper-plane me-1"></i>إرسال
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- المنتجات المرسلة للمبيعات -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>سجل المنتجات المرسلة للمبيعات
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>رقم الأمر</th>
                                        <th>المنتج</th>
                                        <th>الكمية</th>
                                        <th>درجة الجودة</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>المرسل بواسطة</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sales_products)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                لا توجد منتجات مرسلة للمبيعات
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sales_products as $product): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($product['cutting_number']) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($product['product_name']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($product['product_code']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $product['quantity_sent'] ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $quality_class = match($product['quality_grade']) {
                                                        'A' => 'bg-success',
                                                        'B' => 'bg-warning',
                                                        'C' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge quality-badge <?= $quality_class ?>">
                                                        درجة <?= $product['quality_grade'] ?>
                                                    </span>
                                                </td>
                                                <td><?= date('Y-m-d', strtotime($product['send_date'])) ?></td>
                                                <td><?= htmlspecialchars($product['sent_by_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $product['status'] ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- مودال إرسال للمبيعات -->
    <div class="modal fade" id="sendToSalesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إرسال منتج للمبيعات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="cutting_order_id" id="send_cutting_order_id">
                        
                        <div id="send-product-info" class="alert alert-info">
                            جاري تحميل معلومات المنتج...
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">الكمية المراد إرسالها *</label>
                            <input type="number" name="quantity_to_send" class="form-control" min="1" required>
                            <div class="form-text">الكمية المتاحة: <span id="max_quantity">0</span></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">درجة الجودة *</label>
                            <select name="quality_grade" class="form-select" required>
                                <option value="">اختر درجة الجودة</option>
                                <option value="A">درجة A - ممتاز</option>
                                <option value="B">درجة B - جيد</option>
                                <option value="C">درجة C - مقبول</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="ملاحظات حول جودة المنتج أو تفاصيل أخرى..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="send_to_sales" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i>إرسال للمبيعات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function sendToSales(cuttingOrderId, productName, availableQuantity) {
        document.getElementById('send_cutting_order_id').value = cuttingOrderId;
        document.getElementById('max_quantity').textContent = availableQuantity;
        document.querySelector('[name="quantity_to_send"]').max = availableQuantity;
        
        document.getElementById('send-product-info').innerHTML = `
            <strong>المنتج:</strong> ${productName}<br>
            <strong>الكمية المتاحة للإرسال:</strong> ${availableQuantity}
        `;
        
        new bootstrap.Modal(document.getElementById('sendToSalesModal')).show();
    }
    </script>
    <script>
    // إعادة تهيئة Bootstrap dropdowns
    document.addEventListener('DOMContentLoaded', function() {
        // تأكد من تحميل Bootstrap
        if (typeof bootstrap !== 'undefined') {
            // إعادة تهيئة جميع القوائم المنسدلة
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        }
    });
    </script>
</body>
</html>




