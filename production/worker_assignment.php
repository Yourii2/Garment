<?php
require_once '../config/config.php';
checkLogin();

$cutting_order_id = $_GET['cutting_order_id'] ?? 0;

// معالجة تخصيص عامل
if (isset($_POST['assign_worker'])) {
    try {
        $pdo->beginTransaction();
        
        $stage_id = $_POST['stage_id'];
        $worker_id = $_POST['worker_id'];
        $quantity = $_POST['quantity'];
        $notes = $_POST['notes'] ?? '';
        
        // التحقق من الكمية المتاحة
        $stmt = $pdo->prepare("
            SELECT 
                ps.quantity_required,
                COALESCE(SUM(swa.quantity_assigned), 0) as already_assigned
            FROM production_stages ps
            LEFT JOIN stage_worker_assignments swa ON ps.id = swa.production_stage_id
            WHERE ps.id = ?
            GROUP BY ps.id
        ");
        $stmt->execute([$stage_id]);
        $stage_info = $stmt->fetch();
        
        $available_quantity = $stage_info['quantity_required'] - $stage_info['already_assigned'];
        
        if ($quantity > $available_quantity) {
            throw new Exception("الكمية المطلوبة ({$quantity}) أكبر من المتاحة ({$available_quantity})");
        }
        
        // تخصيص العامل
        $stmt = $pdo->prepare("
            INSERT INTO stage_worker_assignments 
            (production_stage_id, worker_id, quantity_assigned, notes, status) 
            VALUES (?, ?, ?, ?, 'assigned')
        ");
        $stmt->execute([$stage_id, $worker_id, $quantity, $notes]);
        
        // تحديث الكمية المخصصة في المرحلة
        $stmt = $pdo->prepare("
            UPDATE production_stages 
            SET quantity_assigned = quantity_assigned + ?,
                status = CASE 
                    WHEN quantity_assigned + ? >= quantity_required THEN 'in_progress'
                    ELSE status 
                END
            WHERE id = ?
        ");
        $stmt->execute([$quantity, $quantity, $stage_id]);
        
        $pdo->commit();
        $_SESSION['success_message'] = 'تم تخصيص العامل بنجاح';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header("Location: worker_assignment.php?cutting_order_id={$cutting_order_id}");
    exit;
}

// جلب تفاصيل أمر القص
try {
    $stmt = $pdo->prepare("
        SELECT co.*, p.name as product_name
        FROM cutting_orders co
        JOIN products p ON co.product_id = p.id
        WHERE co.id = ?
    ");
    $stmt->execute([$cutting_order_id]);
    $cutting_order = $stmt->fetch();
    
    if (!$cutting_order) {
        $_SESSION['error_message'] = 'أمر القص غير موجود';
        header('Location: cutting.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    header('Location: cutting.php');
    exit;
}

// جلب المراحل المتاحة للتخصيص
try {
    $stmt = $pdo->prepare("
        SELECT 
            ps.*,
            ms.name as stage_name,
            (ps.quantity_required - ps.quantity_assigned) as available_quantity
        FROM production_stages ps
        JOIN manufacturing_stages ms ON ps.stage_id = ms.id
        WHERE ps.cutting_order_id = ? 
        AND ps.quantity_assigned < ps.quantity_required
        ORDER BY ps.stage_order
    ");
    $stmt->execute([$cutting_order_id]);
    $available_stages = $stmt->fetchAll();
} catch (Exception $e) {
    $available_stages = [];
}

// جلب العمال المتاحين
try {
    $stmt = $pdo->query("
        SELECT id, name 
        FROM workers 
        WHERE is_active = 1 
        ORDER BY name
    ");
    $workers = $stmt->fetchAll();
} catch (Exception $e) {
    $workers = [];
}

// جلب التخصيصات الحالية
try {
    $stmt = $pdo->prepare("
        SELECT 
            swa.*,
            w.name as worker_name,
            ps.stage_order,
            ms.name as stage_name,
            ps.quantity_required
        FROM stage_worker_assignments swa
        JOIN workers w ON swa.worker_id = w.id
        JOIN production_stages ps ON swa.production_stage_id = ps.id
        JOIN manufacturing_stages ms ON ps.stage_id = ms.id
        WHERE ps.cutting_order_id = ?
        ORDER BY ps.stage_order, swa.created_at
    ");
    $stmt->execute([$cutting_order_id]);
    $current_assignments = $stmt->fetchAll();
} catch (Exception $e) {
    $current_assignments = [];
}

$page_title = 'تخصيص العمال - ' . $cutting_order['cutting_number'];
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
        .assignment-card { border-left: 4px solid #007bff; }
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
                        <i class="fas fa-user-plus me-2"></i><?= $page_title ?>
                    </h1>
                    <div>
                        <a href="production_stages_detail.php?cutting_order_id=<?= $cutting_order_id ?>" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-right me-1"></i>العودة للمراحل
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignWorkerModal">
                            <i class="fas fa-plus me-1"></i>تخصيص عامل جديد
                        </button>
                    </div>
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

                <!-- معلومات أمر القص -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>معلومات أمر القص
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>رقم الأمر:</strong> <?= htmlspecialchars($cutting_order['cutting_number']) ?>
                            </div>
                            <div class="col-md-4">
                                <strong>المنتج:</strong> <?= htmlspecialchars($cutting_order['product_name']) ?>
                            </div>
                            <div class="col-md-4">
                                <strong>الكمية:</strong> <?= number_format($cutting_order['quantity_ordered']) ?> قطعة
                            </div>
                        </div>
                    </div>
                </div>

                <!-- التخصيصات الحالية -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>التخصيصات الحالية
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($current_assignments)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-user-slash fa-3x mb-3 d-block"></i>
                                لا توجد تخصيصات حالياً
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($current_assignments as $assignment): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card assignment-card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?= htmlspecialchars($assignment['worker_name']) ?>
                                                    </h6>
                                                    <span class="badge bg-<?= 
                                                        $assignment['status'] == 'completed' ? 'success' : 
                                                        ($assignment['status'] == 'in_progress' ? 'warning' : 'secondary') 
                                                    ?>">
                                                        <?= 
                                                            $assignment['status'] == 'completed' ? 'مكتمل' : 
                                                            ($assignment['status'] == 'in_progress' ? 'قيد العمل' : 'مخصص') 
                                                        ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="small text-muted mb-2">
                                                    <strong>المرحلة:</strong> <?= htmlspecialchars($assignment['stage_name']) ?>
                                                    <span class="badge bg-light text-dark ms-1"><?= $assignment['stage_order'] ?></span>
                                                </div>
                                                
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <div class="text-muted small">مخصص</div>
                                                        <div class="fw-bold"><?= number_format($assignment['quantity_assigned']) ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-muted small">مكتمل</div>
                                                        <div class="fw-bold text-success"><?= number_format($assignment['quantity_completed']) ?></div>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($assignment['start_time']): ?>
                                                    <div class="small text-muted mt-2">
                                                        <i class="fas fa-clock me-1"></i>
                                                        بدء: <?= date('Y-m-d H:i', strtotime($assignment['start_time'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary w-100" 
                                                            onclick="updateWorkerProgress(<?= $assignment['id'] ?>)">
                                                        <i class="fas fa-edit me-1"></i>تحديث التقدم
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- مودال تخصيص عامل -->
    <div class="modal fade" id="assignWorkerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تخصيص عامل جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">المرحلة</label>
                            <select name="stage_id" class="form-select" required onchange="updateAvailableQuantity(this)">
                                <option value="">اختر المرحلة</option>
                                <?php foreach ($available_stages as $stage): ?>
                                    <option value="<?= $stage['id'] ?>" data-available="<?= $stage['available_quantity'] ?>">
                                        <?= htmlspecialchars($stage['stage_name']) ?> 
                                        (متاح: <?= number_format($stage['available_quantity']) ?> قطعة)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">العامل</label>
                            <select name="worker_id" class="form-select" required>
                                <option value="">اختر العامل</option>
                                <?php foreach ($workers as $worker): ?>
                                    <option value="<?= $worker['id'] ?>">
                                        <?= htmlspecialchars($worker['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">الكمية المخصصة</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                            <div class="form-text" id="availableQuantityText">اختر المرحلة أولاً</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="assign_worker" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>تخصيص العامل
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function updateAvailableQuantity(select) {
        const selectedOption = select.options[select.selectedIndex];
        const available = selectedOption.getAttribute('data-available');
        const quantityInput = document.querySelector('input[name="quantity"]');
        const helpText = document.getElementById('availableQuantityText');
        
        if (available) {
            quantityInput.max = available;
            helpText.textContent = `الكمية المتاحة: ${available} قطعة`;
            helpText.className = 'form-text text-success';
        } else {
            quantityInput.max = '';
            helpText.textContent = 'اختر المرحلة أولاً';
            helpText.className = 'form-text';
        }
    }
    
    function updateWorkerProgress(assignmentId) {
        window.location.href = `update_worker_progress.php?assignment_id=${assignmentId}`;
    }
    </script>
</body>
</html>