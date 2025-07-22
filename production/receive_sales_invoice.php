<?php
require_once '../config/config.php';
checkLogin();

$invoice_id = $_GET['id'] ?? 0;

// معالجة تأكيد الاستلام
if (isset($_POST['confirm_receipt'])) {
    try {
        $pdo->beginTransaction();
        
        $items = $_POST['items'] ?? [];
        
        foreach ($items as $item_id => $item_data) {
            $quantity_received = intval($item_data['quantity_received']);
            
            // تحديث عنصر الفاتورة
            $stmt = $pdo->prepare("
                UPDATE sales_invoice_items 
                SET quantity_received = ?, 
                    status = CASE 
                        WHEN ? = quantity_sent THEN 'received'
                        WHEN ? > 0 THEN 'partial'
                        ELSE 'pending'
                    END
                WHERE id = ?
            ");
            $stmt->execute([$quantity_received, $quantity_received, $quantity_received, $item_id]);
        }
        
        // تحديث حالة الفاتورة
        $stmt = $pdo->prepare("
            UPDATE sales_invoices 
            SET status = 'confirmed', 
                confirmed_by = ?, 
                confirmed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $invoice_id]);
        
        $pdo->commit();
        $_SESSION['success_message'] = 'تم تأكيد استلام الفاتورة بنجاح';
        header("Location: view_sales_invoice.php?id={$invoice_id}");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
}

// جلب بيانات الفاتورة
$stmt = $pdo->prepare("
    SELECT si.*, u.username as created_by_name
    FROM sales_invoices si
    LEFT JOIN users u ON si.created_by = u.id
    WHERE si.id = ? AND si.status = 'pending'
");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    $_SESSION['error_message'] = 'الفاتورة غير موجودة أو تم تأكيدها مسبقاً';
    header('Location: sales_invoices.php');
    exit;
}

// جلب عناصر الفاتورة
$stmt = $pdo->prepare("
    SELECT sii.*, co.cutting_number, p.name as product_name, p.code as product_code
    FROM sales_invoice_items sii
    JOIN cutting_orders co ON sii.cutting_order_id = co.id
    JOIN products p ON sii.product_id = p.id
    WHERE sii.invoice_id = ?
    ORDER BY sii.id
");
$stmt->execute([$invoice_id]);
$invoice_items = $stmt->fetchAll();

$page_title = 'استلام فاتورة رقم ' . $invoice['invoice_number'];
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
                    <i class="fas fa-clipboard-check me-2"></i><?= $page_title ?>
                </h1>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>معلومات الفاتورة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>رقم الفاتورة:</strong> <?= $invoice['invoice_number'] ?></p>
                                <p><strong>تاريخ الفاتورة:</strong> <?= date('Y-m-d', strtotime($invoice['invoice_date'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>عدد المنتجات:</strong> <?= $invoice['total_items'] ?></p>
                                <p><strong>إجمالي الكمية:</strong> <?= number_format($invoice['total_quantity']) ?> قطعة</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check-square me-2"></i>تأكيد استلام المنتجات
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الكمية المرسلة</th>
                                        <th>الكمية المستلمة</th>
                                        <th>درجة الجودة</th>
                                        <th>ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoice_items as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($item['cutting_number']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= number_format($item['quantity_sent']) ?></span>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       name="items[<?= $item['id'] ?>][quantity_received]" 
                                                       class="form-control" 
                                                       min="0" 
                                                       max="<?= $item['quantity_sent'] ?>"
                                                       value="<?= $item['quantity_sent'] ?>"
                                                       required>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $item['quality_grade'] == 'A' ? 'success' : ($item['quality_grade'] == 'B' ? 'warning' : 'danger') ?>">
                                                    <?= $item['quality_grade'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $item['notes'] ? htmlspecialchars($item['notes']) : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 mb-4">
                    <a href="sales_invoices.php" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i>إلغاء
                    </a>
                    <button type="submit" name="confirm_receipt" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>تأكيد الاستلام
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>