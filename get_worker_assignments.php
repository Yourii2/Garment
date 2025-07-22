<?php else: ?>
    
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? "صفحة" ?> - <?= SYSTEM_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
'config/config.php';

if (!isset($_GET['cutting_order_id'])) {
    exit('معرف الأمر مطلوب');
}

$cutting_order_id = $_GET['cutting_order_id'];

// جلب تفاصيل العمال المكلفين - تحديث الاستعلام
$stmt = $pdo->prepare("
    SELECT 
        wa.*,
        w.name as worker_name,
        ms.name as stage_name,
        co.cutting_number,
        p.name as product_name
    FROM worker_assignments wa
    JOIN workers w ON wa.worker_id = w.id
    JOIN manufacturing_stages ms ON wa.stage_id = ms.id
    JOIN cutting_orders co ON wa.cutting_order_id = co.id
    JOIN products p ON co.product_id = p.id
    WHERE wa.cutting_order_id = ?
    ORDER BY wa.assigned_date DESC
");
$stmt->execute([$cutting_order_id]);
$assignments = $stmt->fetchAll();

if (empty($assignments)): ?>
    <div class="text-center py-4">
        <i class="fas fa-users fa-3x text-muted mb-3"></i>
        <p>لا توجد مهام موزعة على العمال بعد</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>العامل</th>
                    <th>المرحلة</th>
                    <th>الكمية المكلف بها</th>
                    <th>الكمية المنجزة</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th>تاريخ التكليف</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <?php 
                    $remaining = $assignment['quantity_assigned'] - $assignment['quantity_completed'];
                    $progress = $assignment['quantity_assigned'] > 0 ? 
                        ($assignment['quantity_completed'] / $assignment['quantity_assigned']) * 100 : 0;
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($assignment['worker_name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($assignment['stage_name']) ?></td>
                        <td>
                            <span class="badge bg-primary"><?= number_format($assignment['quantity_assigned']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-success"><?= number_format($assignment['quantity_completed']) ?></span>
                        </td>
                        <td>
                            <?php if ($remaining > 0): ?>
                                <span class="badge bg-warning"><?= number_format($remaining) ?></span>
                            <?php else: ?>
                                <span class="badge bg-success">مكتمل</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status_badges = [
                                'assigned' => '<span class="badge bg-info">مكلف</span>',
                                'in_progress' => '<span class="badge bg-warning">قيد العمل</span>',
                                'completed' => '<span class="badge bg-success">مكتمل</span>'
                            ];
                            echo $status_badges[$assignment['status']] ?? '';
                            ?>
                        </td>
                        <td><?= date('Y-m-d', strtotime($assignment['assigned_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        <h6>ملخص الإنجاز:</h6>
        <?php
        $total_assigned = array_sum(array_column($assignments, 'quantity_assigned'));
        $total_completed = array_sum(array_column($assignments, 'quantity_completed'));
        $overall_progress = $total_assigned > 0 ? ($total_completed / $total_assigned) * 100 : 0;
        ?>
        <div class="progress">
            <div class="progress-bar bg-success" style="width: <?= $overall_progress ?>%">
                <?= number_format($overall_progress, 1) ?>%
            </div>
        </div>
        <small class="text-muted">
            إجمالي مكلف: <?= number_format($total_assigned) ?> | 
            إجمالي منجز: <?= number_format($total_completed) ?> | 
            متبقي: <?= number_format($total_assigned - $total_completed) ?>
        </small>
    </div>
<?php endif; ?>

</body>
</html>