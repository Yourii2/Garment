<?php
require_once '../config/config.php';
checkLogin();

// جلب جميع الفواتير
$stmt = $pdo->query("
    SELECT si.*, u.username as created_by_name
    FROM sales_invoices si
    LEFT JOIN users u ON si.created_by = u.id
    ORDER BY si.created_at DESC
");
$invoices = $stmt->fetchAll();

$page_title = 'فواتير الإرسال للمبيعات';
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
    <link href="../assets/css/unified-style.css" rel="stylesheet">
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
    <link href="../assets/css/unified-style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-file-invoice me-2"></i><?= $page_title ?>
                </h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>تاريخ الفاتورة</th>
                                    <th>عدد المنتجات</th>
                                    <th>إجمالي الكمية</th>
                                    <th>الحالة</th>
                                    <th>تم الإنشاء بواسطة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($invoices)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد فواتير إرسال
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($invoice['invoice_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= $invoice['total_items'] ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= number_format($invoice['total_quantity']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $invoice['status'] == 'confirmed' ? 'success' : ($invoice['status'] == 'cancelled' ? 'danger' : 'warning') ?>">
                                                    <?= $invoice['status'] == 'confirmed' ? 'مؤكدة' : ($invoice['status'] == 'cancelled' ? 'ملغية' : 'في الانتظار') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($invoice['created_by_name']) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="view_sales_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($invoice['status'] == 'pending'): ?>
                                                        <a href="receive_sales_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-outline-success">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
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

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>