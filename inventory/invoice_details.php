<?php
require_once '../config/config.php';
checkLogin();

$invoice_id = $_GET['id'] ?? 0;

// جلب بيانات الفاتورة
$stmt = $pdo->prepare("
    SELECT i.*, s.name as supplier_name, b.name as branch_name, u.full_name as user_name
    FROM inventory_invoices i 
    LEFT JOIN suppliers s ON i.supplier_id = s.id 
    LEFT JOIN branches b ON i.branch_id = b.id 
    LEFT JOIN users u ON i.user_id = u.id
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    $_SESSION['error_message'] = 'الفاتورة غير موجودة';
    header('Location: invoices.php');
    exit;
}

// جلب أصناف الفاتورة
$stmt = $pdo->prepare("
    SELECT ii.*, f.name as fabric_name, f.code as fabric_code, a.name as accessory_name, a.code as accessory_code
    FROM inventory_invoice_items ii
    LEFT JOIN fabric_types f ON ii.fabric_id = f.id
    LEFT JOIN accessories a ON ii.accessory_id = a.id
    WHERE ii.invoice_id = ?
");
$stmt->execute([$invoice_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الفاتورة - <?= SYSTEM_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-5 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-file-invoice me-2"></i>
                        تفاصيل الفاتورة رقم: <?= htmlspecialchars($invoice['invoice_number']) ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="print_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-success" target="_blank">
                                <i class="fas fa-print me-1"></i>
                                طباعة
                            </a>
                            <a href="export_invoice_pdf.php?id=<?= $invoice['id'] ?>" class="btn btn-danger">
                                <i class="fas fa-file-pdf me-1"></i>
                                تصدير PDF
                            </a>
                            <a href="export_invoice_excel.php?id=<?= $invoice['id'] ?>" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i>
                                تصدير Excel
                            </a>
                        </div>
                        <a href="invoices.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للفواتير
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>بيانات الفاتورة</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>رقم الفاتورة:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></p>
                                        <p><strong>النوع:</strong> 
                                            <?php if ($invoice['invoice_type'] === 'purchase'): ?>
                                                <span class="badge bg-success">شراء</span>
                                            <?php elseif ($invoice['invoice_type'] === 'return'): ?>
                                                <span class="badge bg-warning">مرتجع</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">تالف</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>المورد:</strong> <?= htmlspecialchars($invoice['supplier_name'] ?? 'غير محدد') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>المخزن:</strong> <?= htmlspecialchars($invoice['branch_name'] ?? 'غير محدد') ?></p>
                                        <p><strong>التاريخ:</strong> <?= date('Y-m-d', strtotime($invoice['invoice_date'])) ?></p>
                                        <p><strong>المستخدم:</strong> <?= htmlspecialchars($invoice['user_name']) ?></p>
                                    </div>
                                </div>
                                <?php if ($invoice['notes']): ?>
                                    <p><strong>ملاحظات:</strong> <?= htmlspecialchars($invoice['notes']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>ملخص الفاتورة</h5>
                            </div>
                            <div class="card-body">
                                <h4 class="text-primary">الإجمالي: <?= number_format($invoice['total_amount'], 2) ?> <?= CURRENCY_SYMBOL ?></h4>
                                <p class="text-muted">عدد الأصناف: <?= count($items) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5>أصناف الفاتورة</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>الصنف</th>
                                        <th>الكود</th>
                                        <th>الكمية</th>
                                        <th>سعر الوحدة</th>
                                        <th>الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if ($item['fabric_id']): ?>
                                                    <i class="fas fa-cut text-primary me-1"></i>
                                                    <?= htmlspecialchars($item['fabric_name']) ?>
                                                <?php else: ?>
                                                    <i class="fas fa-cog text-success me-1"></i>
                                                    <?= htmlspecialchars($item['accessory_name']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($item['fabric_code'] ?? $item['accessory_code']) ?></td>
                                            <td><?= number_format($item['quantity'], 2) ?></td>
                                            <td><?= number_format($item['unit_cost'], 2) ?> <?= CURRENCY_SYMBOL ?></td>
                                            <td><?= number_format($item['total_cost'], 2) ?> <?= CURRENCY_SYMBOL ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <th colspan="4">الإجمالي الكلي</th>
                                        <th><?= number_format($invoice['total_amount'], 2) ?> <?= CURRENCY_SYMBOL ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // طباعة تلقائية إذا كان هناك معامل print
        <?php if (isset($_GET['print']) && $_GET['print'] == '1'): ?>
        if (confirm('هل تريد طباعة الفاتورة الآن؟')) {
            window.open('print_invoice.php?id=<?= $invoice['id'] ?>', '_blank');
        }
        <?php endif; ?>
    </script>
</body>
</html>



