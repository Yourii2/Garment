<?php
require_once '../config/config.php';
checkLogin();

$product_code = $_GET['code'] ?? '';
$product_data = null;
$production_history = [];

if ($product_code) {
    // البحث عن المنتج
    $stmt = $pdo->prepare("SELECT * FROM products WHERE code = ?");
    $stmt->execute([$product_code]);
    $product_data = $stmt->fetch();
    
    if ($product_data) {
        // جلب تاريخ الإنتاج الكامل
        $stmt = $pdo->prepare("
            SELECT 
                co.cutting_number,
                co.quantity as cutting_quantity,
                co.created_at as cutting_date,
                ps.stage_name,
                ps.status as stage_status,
                ps.started_at,
                ps.completed_at,
                ps.worker_name,
                ps.notes as stage_notes,
                TIMESTAMPDIFF(MINUTE, ps.started_at, ps.completed_at) as duration_minutes
            FROM cutting_orders co
            LEFT JOIN production_stages ps ON co.id = ps.cutting_order_id
            WHERE co.product_id = ?
            ORDER BY co.created_at DESC, ps.stage_order ASC
        ");
        $stmt->execute([$product_data['id']]);
        $production_history = $stmt->fetchAll();
    }
}

$page_title = 'تتبع المنتج';
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
                    <i class="fas fa-search me-2"></i>تتبع المنتج
                </h1>
            </div>

            <!-- نموذج البحث -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-barcode me-2"></i>البحث بالباركود
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">كود المنتج / الباركود</label>
                                    <input type="text" name="code" class="form-control" 
                                           value="<?= htmlspecialchars($product_code) ?>" 
                                           placeholder="امسح الباركود أو أدخل كود المنتج" autofocus>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block w-100">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($product_code && !$product_data): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                لم يتم العثور على منتج بهذا الكود: <?= htmlspecialchars($product_code) ?>
            </div>
            <?php endif; ?>

            <?php if ($product_data): ?>
            <!-- معلومات المنتج -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>معلومات المنتج
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>اسم المنتج:</strong><br>
                            <?= htmlspecialchars($product_data['name']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>الكود:</strong><br>
                            <?= htmlspecialchars($product_data['code']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>الوصف:</strong><br>
                            <?= htmlspecialchars($product_data['description'] ?? 'غير محدد') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تاريخ الإنتاج -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>تاريخ الإنتاج والمراحل
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($production_history)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        لا توجد بيانات إنتاج لهذا المنتج حتى الآن
                    </div>
                    <?php else: ?>
                    <div class="timeline">
                        <?php 
                        $current_cutting_order = null;
                        foreach ($production_history as $record): 
                            if ($current_cutting_order !== $record['cutting_number']):
                                $current_cutting_order = $record['cutting_number'];
                        ?>
                        <!-- بداية أمر قص جديد -->
                        <div class="timeline-item mb-4">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-cut me-2"></i>
                                            أمر القص: <?= htmlspecialchars($record['cutting_number']) ?>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>الكمية:</strong> <?= $record['cutting_quantity'] ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>تاريخ القص:</strong> <?= date('Y-m-d H:i', strtotime($record['cutting_date'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($record['stage_name']): ?>
                        <!-- مرحلة إنتاج -->
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-<?= $record['stage_status'] === 'completed' ? 'success' : ($record['stage_status'] === 'in_progress' ? 'warning' : 'secondary') ?>"></div>
                            <div class="timeline-content">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($record['stage_name']) ?></h6>
                                            <span class="badge bg-<?= $record['stage_status'] === 'completed' ? 'success' : ($record['stage_status'] === 'in_progress' ? 'warning' : 'secondary') ?>">
                                                <?= $record['stage_status'] === 'completed' ? 'مكتمل' : ($record['stage_status'] === 'in_progress' ? 'قيد التنفيذ' : 'في الانتظار') ?>
                                            </span>
                                        </div>
                                        
                                        <div class="row">
                                            <?php if ($record['worker_name']): ?>
                                            <div class="col-md-3">
                                                <small class="text-muted">العامل:</small><br>
                                                <?= htmlspecialchars($record['worker_name']) ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($record['started_at']): ?>
                                            <div class="col-md-3">
                                                <small class="text-muted">بداية العمل:</small><br>
                                                <?= date('Y-m-d H:i', strtotime($record['started_at'])) ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($record['completed_at']): ?>
                                            <div class="col-md-3">
                                                <small class="text-muted">انتهاء العمل:</small><br>
                                                <?= date('Y-m-d H:i', strtotime($record['completed_at'])) ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($record['duration_minutes']): ?>
                                            <div class="col-md-3">
                                                <small class="text-muted">المدة المستغرقة:</small><br>
                                                <?= floor($record['duration_minutes'] / 60) ?>س <?= $record['duration_minutes'] % 60 ?>د
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($record['stage_notes']): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">ملاحظات:</small><br>
                                            <?= nl2br(htmlspecialchars($record['stage_notes'])) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 10px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    margin-left: 20px;
}
</style>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>