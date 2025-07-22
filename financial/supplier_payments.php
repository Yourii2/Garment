<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة دفعة جديدة
if (isset($_POST['add_payment'])) {
    try {
        $supplier_id = $_POST['supplier_id'];
        $treasury_id = $_POST['treasury_id'];
        $amount = floatval($_POST['amount']);
        $payment_type = $_POST['payment_type'];
        $notes = $_POST['notes'] ?? '';
        
        if ($amount <= 0) {
            throw new Exception('يجب أن يكون المبلغ أكبر من صفر');
        }
        
        $pdo->beginTransaction();
        
        // فحص رصيد الخزينة
        $stmt = $pdo->prepare("SELECT current_balance FROM treasuries WHERE id = ?");
        $stmt->execute([$treasury_id]);
        $balance = $stmt->fetchColumn();
        
        if ($balance < $amount) {
            throw new Exception('الرصيد غير كافي في الخزينة');
        }
        
        // خصم من الخزينة
        $stmt = $pdo->prepare("UPDATE treasuries SET current_balance = current_balance - ? WHERE id = ?");
        $stmt->execute([$amount, $treasury_id]);
        
        // تسجيل المعاملة
        $stmt = $pdo->prepare("
            INSERT INTO treasury_transactions 
            (treasury_id, amount, type, description, user_id, created_at) 
            VALUES (?, ?, 'supplier_payment', ?, ?, NOW())
        ");
        $description = "دفعة لمورد - " . $payment_type . " - " . $notes;
        $stmt->execute([$treasury_id, $amount, $description, $_SESSION['user_id']]);
        
        // تسجيل في جدول دفعات الموردين
        $stmt = $pdo->prepare("
            INSERT INTO supplier_payments 
            (supplier_id, treasury_id, amount, payment_type, notes, paid_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$supplier_id, $treasury_id, $amount, $payment_type, $notes, $_SESSION['user_id']]);
        
        $pdo->commit();
        $_SESSION['success_message'] = 'تم تسجيل الدفعة بنجاح';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: supplier_payments.php');
    exit;
}

// جلب الموردين
$stmt = $pdo->query("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC");
$suppliers = $stmt->fetchAll();

// جلب الخزائن النشطة
$stmt = $pdo->query("SELECT * FROM treasuries WHERE is_active = 1 ORDER BY name ASC");
$treasuries = $stmt->fetchAll();

// جلب الدفعات
$stmt = $pdo->query("
    SELECT sp.*, s.name as supplier_name, t.name as treasury_name, u.full_name as paid_by_name
    FROM supplier_payments sp
    LEFT JOIN suppliers s ON sp.supplier_id = s.id
    LEFT JOIN treasuries t ON sp.treasury_id = t.id
    LEFT JOIN users u ON sp.paid_by = u.id
    ORDER BY sp.created_at DESC
    LIMIT 50
");
$payments = $stmt->fetchAll();

$page_title = 'دفعات الموردين';
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
                <h1 class="h2">
                    <i class="fas fa-truck me-2"></i>دفعات الموردين
                </h1>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    <i class="fas fa-plus me-1"></i>إضافة دفعة
                </button>
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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>المورد</th>
                                    <th>المبلغ</th>
                                    <th>نوع الدفع</th>
                                    <th>الخزينة</th>
                                    <th>الدافع</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($payment['supplier_name']) ?></td>
                                        <td>
                                            <span class="badge bg-warning fs-6">
                                                <?= number_format($payment['amount'], 2) ?> ج.م
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($payment['payment_type']) ?></td>
                                        <td><?= htmlspecialchars($payment['treasury_name']) ?></td>
                                        <td><?= htmlspecialchars($payment['paid_by_name']) ?></td>
                                        <td><?= htmlspecialchars($payment['notes']) ?></td>
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

<!-- Modal إضافة دفعة -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة دفعة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">المورد *</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">اختر المورد</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>">
                                    <?= htmlspecialchars($supplier['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الخزينة *</label>
                        <select name="treasury_id" class="form-select" required>
                            <option value="">اختر الخزينة</option>
                            <?php foreach ($treasuries as $treasury): ?>
                                <option value="<?= $treasury['id'] ?>">
                                    <?= htmlspecialchars($treasury['name']) ?> 
                                    (<?= number_format($treasury['current_balance'], 2) ?> ج.م)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع الدفع *</label>
                        <select name="payment_type" class="form-select" required>
                            <option value="">اختر النوع</option>
                            <option value="نقدي">نقدي</option>
                            <option value="شيك">شيك</option>
                            <option value="تحويل بنكي">تحويل بنكي</option>
                            <option value="دفع إلكتروني">دفع إلكتروني</option>
                            <option value="دفعة مقدمة">دفعة مقدمة</option>
                            <option value="تسوية حساب">تسوية حساب</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_payment" class="btn btn-warning">تسجيل الدفعة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include '../includes/footer.php'; ?>
</body>
</html>

</body>
</html>