<?php
require_once '../config/config.php';
checkLogin();
checkPermissionAccess('production_management');

$page_title = 'مراحل الإنتاج';

// معالجة إضافة مرحلة جديدة
if (isset($_POST['start_production']) && $_POST['start_production']) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'رمز الأمان غير صحيح';
        header('Location: stages.php');
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $cutting_order_id = $_POST['cutting_order_id'];
        $stage_id = $_POST['stage_id'];
        $worker_id = $_POST['worker_id'];
        $quantity = $_POST['quantity'];
        $notes = $_POST['notes'] ?? '';
        $cost_per_unit = $_POST['cost_per_unit'] ?? 0;
        $is_paid = isset($_POST['is_paid']) ? 1 : 0;
        
        // التحقق من الكمية المتاحة
        $stmt = $pdo->prepare("
            SELECT 
                co.quantity_ordered,
                COALESCE(SUM(swa.quantity_assigned), 0) as total_assigned
            FROM cutting_orders co
            LEFT JOIN production_stages ps ON co.id = ps.cutting_order_id
            LEFT JOIN stage_worker_assignments swa ON ps.id = swa.production_stage_id
            WHERE co.id = ?
            GROUP BY co.id, co.quantity_ordered
        ");
        $stmt->execute([$cutting_order_id]);
        $order_data = $stmt->fetch();
        
        $available_quantity = $order_data['quantity_ordered'] - $order_data['total_assigned'];
        
        if ($quantity > $available_quantity) {
            throw new Exception("الكمية المطلوبة ({$quantity}) أكبر من المتاحة ({$available_quantity})");
        }
        
        // البحث عن مرحلة إنتاج موجودة أو إنشاء جديدة
        $stmt = $pdo->prepare("
            SELECT id FROM production_stages 
            WHERE cutting_order_id = ? AND stage_id = ?
        ");
        $stmt->execute([$cutting_order_id, $stage_id]);
        $existing_stage = $stmt->fetch();
        
        if ($existing_stage) {
            $production_stage_id = $existing_stage['id'];
        } else {
            // إنشاء مرحلة إنتاج جديدة
            $stmt = $pdo->prepare("
                INSERT INTO production_stages 
                (cutting_order_id, stage_id, stage_order, quantity_required, status) 
                VALUES (?, ?, 1, ?, 'in_progress')
            ");
            $stmt->execute([$cutting_order_id, $stage_id, $quantity]);
            $production_stage_id = $pdo->lastInsertId();
        }
        
        // تخصيص العامل - بدون الأعمدة المفقودة مؤقت
        $stmt = $pdo->prepare("
            INSERT INTO stage_worker_assignments 
            (production_stage_id, worker_id, quantity_assigned, start_time, status, notes) 
            VALUES (?, ?, ?, NOW(), 'assigned', ?)
        ");
        $stmt->execute([$production_stage_id, $worker_id, $quantity, $notes]);
        
        $assignment_id = $pdo->lastInsertId();
        
        // تحديث التكلفة إذا كانت الأعمدة موجودة
        try {
            $stmt = $pdo->prepare("
                UPDATE stage_worker_assignments 
                SET cost_per_unit = ?, is_paid = ?, total_cost = ?
                WHERE id = ?
            ");
            $total_cost = $is_paid ? ($quantity * $cost_per_unit) : 0;
            $stmt->execute([$cost_per_unit, $is_paid, $total_cost, $assignment_id]);
        } catch (Exception $e) {
            // الأعمدة غير موجودة، تجاهل الخطأ
        }
        
        // تحديث رصيد العامل إذا كانت المرحلة مدفوعة
        if ($is_paid && $cost_per_unit > 0) {
            $total_amount = $quantity * $cost_per_unit;
            $stmt = $pdo->prepare("
                UPDATE workers 
                SET current_balance = COALESCE(current_balance, 0) + ?
                WHERE id = ?
            ");
            $stmt->execute([$total_amount, $worker_id]);
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = "تم بدء مرحلة الإنتاج بنجاح وتخصيص {$quantity} قطعة للعامل";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: stages.php');
    exit;
}

// معالجة بدء العمل
if (isset($_POST['start_work'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'رمز الأمان غير صحيح';
        header('Location: stages.php');
        exit;
    }
    
    try {
        $assignment_id = $_POST['assignment_id'];
        
        $stmt = $pdo->prepare("
            UPDATE stage_worker_assignments 
            SET status = 'in_progress', start_time = NOW() 
            WHERE id = ? AND status = 'assigned'
        ");
        $result = $stmt->execute([$assignment_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'تم بدء العمل بنجاح';
        } else {
            $_SESSION['error_message'] = 'لم يتم العثور على المهمة أو أنها بدأت بالفعل';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: stages.php');
    exit;
}

// معالجة إكمال العمل
if (isset($_POST['complete_work'])) {
    try {
        $assignment_id = $_POST['assignment_id'];
        $quantity_completed = $_POST['quantity_completed'];
        
        $stmt = $pdo->prepare("
            UPDATE stage_worker_assignments 
            SET status = 'completed', 
                quantity_completed = ?,
                end_time = NOW()
            WHERE id = ? AND status = 'in_progress'
        ");
        $result = $stmt->execute([$quantity_completed, $assignment_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'تم إكمال العمل بنجاح';
        } else {
            $_SESSION['error_message'] = 'لم يتم العثور على المهمة أو أنها مكتملة بالفعل';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: stages.php');
    exit;
}

// معالجة إنهاء الإنتاج
if (isset($_POST['finish_production'])) {
    try {
        $pdo->beginTransaction();
        
        $assignment_id = $_POST['finish_assignment_id'];
        $finished_quantity = $_POST['finished_quantity'];
        
        // جلب معلومات المهمة
        $stmt = $pdo->prepare("
            SELECT swa.*, ps.cutting_order_id, co.product_id
            FROM stage_worker_assignments swa
            INNER JOIN production_stages ps ON swa.production_stage_id = ps.id
            INNER JOIN cutting_orders co ON ps.cutting_order_id = co.id
            WHERE swa.id = ? AND swa.status = 'completed'
        ");
        $stmt->execute([$assignment_id]);
        $assignment = $stmt->fetch();
        
        if (!$assignment) {
            throw new Exception("المهمة غير موجودة أو غير مكتملة");
        }
        
        $available_for_finish = $assignment['quantity_completed'] - ($assignment['quantity_finished'] ?? 0);
        
        if ($finished_quantity > $available_for_finish) {
            throw new Exception("الكمية المطلوب إنهاؤها أكبر من المتاحة");
        }
        
        // تحديث كمية الإنهاء
        $stmt = $pdo->prepare("
            UPDATE stage_worker_assignments 
            SET quantity_finished = COALESCE(quantity_finished, 0) + ?
            WHERE id = ?
        ");
        $stmt->execute([$finished_quantity, $assignment_id]);
        
        // إضافة للمنتجات الجاهزة للمبيعات
        $stmt = $pdo->prepare("
            INSERT INTO sales_products 
            (cutting_order_id, product_id, quantity_sent, quality_grade, send_date, notes, sent_by, status)
            VALUES (?, ?, ?, 'A', CURDATE(), 'تم إنهاء الإنتاج', ?, 'ready_for_sale')
        ");
        $stmt->execute([
            $assignment['cutting_order_id'],
            $assignment['product_id'],
            $finished_quantity,
            $_SESSION['user_id']
        ]);
        
        $pdo->commit();
        $_SESSION['success_message'] = "تم إنهاء {$finished_quantity} قطعة وإرسالها للمبيعات بنجاح";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: stages.php');
    exit;
}

// معالجة نقل إلى مرحلة جديدة
if (isset($_POST['transfer_to_stage'])) {
    try {
        $pdo->beginTransaction();
        
        $assignment_id = $_POST['transfer_assignment_id'];
        $new_worker_id = $_POST['new_worker_id'];
        $new_stage_id = $_POST['new_stage_id'];
        $transfer_quantity = $_POST['transfer_quantity'];
        $is_paid = isset($_POST['transfer_is_paid']);
        $cost_per_unit = $is_paid ? floatval($_POST['transfer_cost_per_unit']) : 0;
        $notes = $_POST['transfer_notes'] ?? '';
        
        // جلب معلومات المهمة الأصلية
        $stmt = $pdo->prepare("
            SELECT swa.*, ps.cutting_order_id 
            FROM stage_worker_assignments swa
            INNER JOIN production_stages ps ON swa.production_stage_id = ps.id
            WHERE swa.id = ? AND swa.status = 'completed'
        ");
        $stmt->execute([$assignment_id]);
        $original_assignment = $stmt->fetch();
        
        if (!$original_assignment) {
            throw new Exception("المهمة غير موجودة أو غير مكتملة");
        }
        
        // التحقق من الكمية المتاحة
        $available = $original_assignment['quantity_completed'] - 
                    ($original_assignment['quantity_transferred'] ?? 0) - 
                    ($original_assignment['quantity_finished'] ?? 0);
        
        if ($transfer_quantity > $available) {
            throw new Exception("الكمية المطلوب نقلها أكبر من المتاحة");
        }
        
        // البحث عن مرحلة إنتاج موجودة أو إنشاء جديدة
        $stmt = $pdo->prepare("
            SELECT id FROM production_stages 
            WHERE cutting_order_id = ? AND stage_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$original_assignment['cutting_order_id'], $new_stage_id]);
        $existing_stage = $stmt->fetch();
        
        if ($existing_stage) {
            $new_production_stage_id = $existing_stage['id'];
        } else {
            // الحصول على ترتيب المرحلة من جدول product_stages
            $stmt = $pdo->prepare("
                SELECT ps.stage_order 
                FROM product_stages ps
                INNER JOIN cutting_orders co ON ps.product_id = co.product_id
                WHERE co.id = ? AND ps.stage_id = ?
                LIMIT 1
            ");
            $stmt->execute([$original_assignment['cutting_order_id'], $new_stage_id]);
            $stage_info = $stmt->fetch();
            $stage_order = $stage_info ? $stage_info['stage_order'] : 1;
            
            // إنشاء مرحلة إنتاج جديدة
            $stmt = $pdo->prepare("
                INSERT INTO production_stages (cutting_order_id, stage_id, stage_order, quantity_required, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $original_assignment['cutting_order_id'], 
                $new_stage_id, 
                $stage_order,
                $transfer_quantity
            ]);
            $new_production_stage_id = $pdo->lastInsertId();
        }
        
        // إنشاء مهمة جديدة للعامل الجديد
        $total_cost = $is_paid ? ($transfer_quantity * $cost_per_unit) : 0;
        
        $stmt = $pdo->prepare("
            INSERT INTO stage_worker_assignments 
            (production_stage_id, worker_id, quantity_assigned, start_time, status, notes, cost_per_unit, is_paid, total_cost)
            VALUES (?, ?, ?, NOW(), 'assigned', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $new_production_stage_id, 
            $new_worker_id, 
            $transfer_quantity, 
            $notes,
            $cost_per_unit,
            $is_paid ? 1 : 0,
            $total_cost
        ]);
        
        // تحديث المهمة الأصلية
        $stmt = $pdo->prepare("
            UPDATE stage_worker_assignments 
            SET quantity_transferred = COALESCE(quantity_transferred, 0) + ?
            WHERE id = ?
        ");
        $stmt->execute([$transfer_quantity, $assignment_id]);
        
        $pdo->commit();
        $_SESSION['success_message'] = "تم نقل {$transfer_quantity} قطعة إلى المرحلة الجديدة بنجاح";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: stages.php');
    exit;
}

// جلب أوامر القص مع الكمية المتاحة الصحيحة
try {
    $stmt = $pdo->query("
        SELECT 
            co.id,
            co.cutting_number,
            co.quantity_ordered,
            p.name as product_name,
            COALESCE(SUM(swa.quantity_assigned), 0) as total_assigned,
            (co.quantity_ordered - COALESCE(SUM(swa.quantity_assigned), 0)) as available_quantity
        FROM cutting_orders co
        INNER JOIN products p ON co.product_id = p.id
        LEFT JOIN production_stages ps ON co.id = ps.cutting_order_id
        LEFT JOIN stage_worker_assignments swa ON ps.id = swa.production_stage_id
        WHERE co.status = 'active'
        GROUP BY co.id, co.cutting_number, co.quantity_ordered, p.name
        HAVING available_quantity > 0
        ORDER BY co.id DESC
    ");
    $available_orders = $stmt->fetchAll();
} catch (Exception $e) {
    $available_orders = [];
    $_SESSION['error_message'] = 'خطأ في جلب أوامر القص: ' . $e->getMessage();
}

// جلب العمال
try {
    $stmt = $pdo->query("SELECT id, name FROM workers WHERE is_active = 1 ORDER BY name");
    $workers = $stmt->fetchAll();
} catch (Exception $e) {
    $workers = [];
}

// جلب مراحل التصنيع
try {
    $stmt = $pdo->query("SELECT id, name, is_paid, cost_per_unit FROM manufacturing_stages WHERE is_active = 1 ORDER BY sort_order, name");
    $stages = $stmt->fetchAll();
} catch (Exception $e) {
    $stages = [];
}

// جلب المهام الحالية
try {
    $stmt = $pdo->query("
        SELECT 
            swa.id,
            swa.worker_id,
            swa.quantity_assigned,
            swa.quantity_completed,
            swa.status,
            swa.start_time,
            swa.notes,
            w.name as worker_name,
            ms.name as stage_name,
            co.cutting_number,
            p.name as product_name
        FROM stage_worker_assignments swa
        INNER JOIN workers w ON swa.worker_id = w.id
        INNER JOIN production_stages ps ON swa.production_stage_id = ps.id
        INNER JOIN manufacturing_stages ms ON ps.stage_id = ms.id
        INNER JOIN cutting_orders co ON ps.cutting_order_id = co.id
        INNER JOIN products p ON co.product_id = p.id
        WHERE swa.status IN ('assigned', 'in_progress')
        ORDER BY swa.start_time DESC
    ");
    $current_assignments = $stmt->fetchAll();
    
    $assigned_tasks = array_filter($current_assignments, function($a) { return $a['status'] === 'assigned'; });
    $in_progress_tasks = array_filter($current_assignments, function($a) { return $a['status'] === 'in_progress'; });
    $completed_tasks = []; // سيتم إضافتها لاحق<|im_start|>
    
} catch (Exception $e) {
    $current_assignments = [];
    $assigned_tasks = [];
    $in_progress_tasks = [];
    $completed_tasks = [];
}

// تقسيم المهام حسب الحالة
$assigned_tasks = [];
$in_progress_tasks = [];
$completed_tasks_display = [];

foreach ($current_assignments as $assignment) {
    if ($assignment['status'] == 'assigned') {
        $assigned_tasks[] = $assignment;
    } elseif ($assignment['status'] == 'in_progress') {
        $in_progress_tasks[] = $assignment;
    }
}

// جلب المهام المكتملة التي لم تنته من الإنتاج
try {
    $stmt = $pdo->query("
        SELECT 
            swa.id,
            swa.quantity_assigned,
            swa.quantity_completed,
            COALESCE(swa.quantity_transferred, 0) as quantity_transferred,
            COALESCE(swa.quantity_finished, 0) as quantity_finished,
            w.name as worker_name,
            ms.name as stage_name,
            co.cutting_number,
            p.name as product_name,
            swa.notes,
            swa.end_time
        FROM stage_worker_assignments swa
        LEFT JOIN workers w ON swa.worker_id = w.id
        LEFT JOIN production_stages ps ON swa.production_stage_id = ps.id
        LEFT JOIN manufacturing_stages ms ON ps.stage_id = ms.id
        LEFT JOIN cutting_orders co ON ps.cutting_order_id = co.id
        LEFT JOIN products p ON co.product_id = p.id
        WHERE swa.status = 'completed'
        AND (swa.quantity_completed > COALESCE(swa.quantity_finished, 0))
        ORDER BY swa.id DESC
    ");
    $completed_tasks_display = $stmt->fetchAll();
    
    // حساب الكميات المتاحة
    foreach ($completed_tasks_display as &$task) {
        $task['available_for_transfer'] = $task['quantity_completed'] - $task['quantity_transferred'] - $task['quantity_finished'];
        $task['available_for_finish'] = $task['quantity_completed'] - $task['quantity_finished'];
    }
    
} catch (Exception $e) {
    $completed_tasks_display = [];
    error_log("خطأ في جلب المهام المكتملة: " . $e->getMessage());
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/../../assets/css/style.css" rel="stylesheet">
    <style>
        .main-content { margin-top: 60px; }
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
                        <i class="fas fa-tasks me-2"></i>مراحل الإنتاج
                    </h1>
                    <div>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#startProductionModal">
                            <i class="fas fa-play me-1"></i>بدء مرحلة إنتاج جديدة
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

                <!-- إحصائيات سريعة -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">مهام جديدة</h5>
                                <h2><?= count($assigned_tasks) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">قيد التنفيذ</h5>
                                <h2><?= count($in_progress_tasks) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">مكتملة</h5>
                                <h2><?= count($completed_tasks_display) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">أوامر متاحة</h5>
                                <h2><?= count($available_orders) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويبات المهام -->
                <ul class="nav nav-tabs" id="tasksTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button">
                            <i class="fas fa-clock me-1"></i>مهام جديدة (<?= count($assigned_tasks) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress" type="button">
                            <i class="fas fa-cog me-1"></i>قيد التنفيذ (<?= count($in_progress_tasks) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button">
                            <i class="fas fa-check me-1"></i>مكتملة (<?= count($completed_tasks_display) ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="tasksTabContent">
                    <!-- المهام الجديدة -->
                    <div class="tab-pane fade show active" id="assigned" role="tabpanel">
                        <div class="row mt-3">
                            <?php if (empty($assigned_tasks)): ?>
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">لا توجد مهام جديدة</h5>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($assigned_tasks as $task): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card mb-3" style="border-left: 4px solid #0d6efd;">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($task['worker_name']) ?>
                                                </h6>
                                                <span class="badge bg-primary">جديد</span>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-1"><strong>الأمر:</strong> <?= htmlspecialchars($task['cutting_number']) ?></p>
                                                <p class="mb-1"><strong>المنتج:</strong> <?= htmlspecialchars($task['product_name']) ?></p>
                                                <p class="mb-1"><strong>المرحلة:</strong> 
                                                    <span class="badge bg-info"><?= htmlspecialchars($task['stage_name']) ?></span>
                                                </p>
                                                <p class="mb-1"><strong>الكمية:</strong> <?= number_format($task['quantity_assigned']) ?> قطعة</p>
                                                <p class="mb-1"><strong>تاريخ التوزيع:</strong> <?= date('Y-m-d', strtotime($task['assigned_date'])) ?></p>
                                                
                                                <div class="d-grid gap-2 mt-3">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <input type="hidden" name="assignment_id" value="<?= $task['id'] ?>">
                                                        <button type="submit" name="start_work" class="btn btn-warning btn-sm w-100">
                                                            <i class="fas fa-play me-1"></i>بدء العمل
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- المهام قيد التنفيذ -->
                    <div class="tab-pane fade" id="progress" role="tabpanel">
                        <div class="row mt-3">
                            <?php if (empty($in_progress_tasks)): ?>
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">لا توجد مهام قيد التنفيذ</h5>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($in_progress_tasks as $task): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card mb-3" style="border-left: 4px solid #ffc107;">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($task['worker_name']) ?>
                                                </h6>
                                                <span class="badge bg-warning">قيد التنفيذ</span>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-1"><strong>الأمر:</strong> <?= htmlspecialchars($task['cutting_number']) ?></p>
                                                <p class="mb-1"><strong>المنتج:</strong> <?= htmlspecialchars($task['product_name']) ?></p>
                                                <p class="mb-1"><strong>المرحلة:</strong> 
                                                    <span class="badge bg-info"><?= htmlspecialchars($task['stage_name']) ?></span>
                                                </p>
                                                <p class="mb-1"><strong>الكمية:</strong> <?= number_format($task['quantity_assigned']) ?> قطعة</p>
                                                
                                                <div class="d-grid gap-2 mt-3">
                                                    <button type="button" class="btn btn-success btn-sm" 
                                                            onclick="completeAssignment(<?= $task['id'] ?>, <?= $task['quantity_assigned'] ?>)">
                                                        <i class="fas fa-check me-1"></i>إكمال المهمة
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- المهام المكتملة -->
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                        <div class="row mt-3">
                            <?php foreach ($completed_tasks_display as $task): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card mb-3" style="border-left: 4px solid #198754;">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($task['worker_name']) ?>
                                            </h6>
                                            <span class="badge bg-success">مكتمل</span>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>الأمر:</strong> <?= htmlspecialchars($task['cutting_number']) ?></p>
                                            <p class="mb-1"><strong>المنتج:</strong> <?= htmlspecialchars($task['product_name']) ?></p>
                                            <p class="mb-1"><strong>المرحلة:</strong> 
                                                <span class="badge bg-info"><?= htmlspecialchars($task['stage_name']) ?></span>
                                            </p>
                                            <p class="mb-1"><strong>الكمية المكتملة:</strong> <?= number_format($task['quantity_completed']) ?> قطعة</p>
                                            <p class="mb-1"><strong>المتاحة للنقل:</strong> <?= number_format($task['available_for_transfer']) ?> قطعة</p>
                                            
                                            <div class="d-grid gap-2 mt-3">
                                                <?php if ($task['available_for_finish'] > 0): ?>
                                                    <button type="button" class="btn btn-primary btn-sm" 
                                                            onclick="finishProduction(<?= $task['id'] ?>, <?= $task['available_for_finish'] ?>)">
                                                        <i class="fas fa-flag-checkered me-1"></i>إنهاء الإنتاج (<?= $task['available_for_finish'] ?>)
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($task['available_for_transfer'] > 0): ?>
                                                    <button type="button" class="btn btn-warning btn-sm" 
                                                            onclick="transferWorker(<?= $task['id'] ?>, '<?= htmlspecialchars($task['worker_name']) ?>', '<?= htmlspecialchars($task['product_name']) ?>', '<?= htmlspecialchars($task['stage_name']) ?>', <?= $task['available_for_transfer'] ?>)">
                                                        <i class="fas fa-exchange-alt me-1"></i>نقل لمرحلة أخرى
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- مودال توزيع مهمة جديدة -->
    <div class="modal fade" id="assignTaskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">توزيع مهمة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">أمر القص <span class="text-danger">*</span></label>
                                    <select name="cutting_order_id" class="form-select" required onchange="updateAvailableQuantity()">
                                        <option value="">اختر أمر القص</option>
                                        <?php foreach ($available_orders as $order): ?>
                                            <option value="<?= $order['id'] ?>" data-available="<?= $order['available_quantity'] ?>">
                                                <?= htmlspecialchars($order['cutting_number']) ?> - <?= htmlspecialchars($order['product_name']) ?>
                                                (متاح: <?= $order['available_quantity'] ?> قطعة)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">العامل <span class="text-danger">*</span></label>
                                    <select name="worker_id" class="form-select" required>
                                        <option value="">اختر العامل (العدد: <?= count($workers) ?>)</option>
                                        <?php 
                                        if (!empty($workers)) {
                                            foreach ($workers as $worker) {
                                                echo '<option value="' . $worker['id'] . '">';
                                                echo htmlspecialchars($worker['name']) . ' (ID: ' . $worker['id'] . ')';
                                                echo '</option>';
                                            }
                                        } else {
                                            echo '<option value="" disabled>لا يوجد عمال متاحين</option>';
                                        }
                                        ?>
                                    </select>
                                    <small class="text-muted">تشخيص: <?= var_export($workers, true) ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">مرحلة التصنيع <span class="text-danger">*</span></label>
                                    <select name="stage_id" class="form-select" required>
                                        <option value="">اختر المرحلة</option>
                                        <?php foreach ($stages as $stage): ?>
                                            <option value="<?= $stage['id'] ?>"><?= htmlspecialchars($stage['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity_assigned" class="form-control" min="1" required>
                                    <div class="form-text" id="available-quantity-text">اختر أمر القص أولاً</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="assignment_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="assign_task" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>توزيع المهمة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال تحديث حالة المهمة -->
    <div class="modal fade" id="updateAssignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تحديث حالة المهمة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="assignment_id" id="update_assignment_id">
                        <input type="hidden" name="new_status" id="update_new_status">
                        
                        <div id="update-assignment-info" class="alert alert-info">
                            جاري تحميل معلومات المهمة...
                        </div>
                        
                        <div class="mb-3" id="completed-quantity-section" style="display: none;">
                            <label class="form-label">الكمية المكتملة <span class="text-danger">*</span></label>
                            <input type="number" name="completed_quantity" id="completed_quantity" class="form-control" min="1">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="update_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="update_assignment" class="btn btn-primary" id="update-submit-btn">
                            <i class="fas fa-save me-1"></i>تحديث
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال إنهاء التصنيع -->
    <div class="modal fade" id="finishProductionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إنهاء تصنيع المنتج</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="finish_assignment_id" id="finish_assignment_id">
                        
                        <div id="finish-assignment-info" class="alert alert-info">
                            جاري تحميل معلومات المهمة...
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">الكمية المنتهية <span class="text-danger">*</span></label>
                            <input type="number" name="finished_quantity" id="finished_quantity" class="form-control" min="1" required>
                            <div id="finish-quantity-validation" class="form-text"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">فحص الجودة</label>
                            <select name="quality_check" class="form-select" required>
                                <option value="passed">مطابق للمواصفات</option>
                                <option value="pending">في انتظار الفحص</option>
                                <option value="failed">غير مطابق</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات الإنهاء</label>
                            <textarea name="finish_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="finish_production" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i>إنهاء التصنيع
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال نقل لعامل آخر -->
    <div class="modal fade" id="transferWorkerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt me-2"></i>نقل إلى مرحلة أخرى
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="transfer_assignment_id" id="transfer_assignment_id">
                    
                    <div class="modal-body">
                        <div class="alert alert-info" id="transfer-assignment-info">
                            <!-- معلومات المهمة الحالية -->
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">العامل الجديد <span class="text-danger">*</span></label>
                                    <select name="new_worker_id" class="form-select" required>
                                        <option value="">اختر العامل</option>
                                        <?php foreach ($workers as $worker): ?>
                                            <option value="<?= $worker['id'] ?>">
                                                <?= htmlspecialchars($worker['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">المرحلة الجديدة <span class="text-danger">*</span></label>
                                    <select name="new_stage_id" class="form-select" required onchange="updateTransferStageInfo()">
                                        <option value="">اختر المرحلة</option>
                                        <?php foreach ($stages as $stage): ?>
                                            <option value="<?= $stage['id'] ?>" 
                                                    data-paid="<?= $stage['is_paid'] ?>" 
                                                    data-cost="<?= $stage['cost_per_unit'] ?>">
                                                <?= htmlspecialchars($stage['name']) ?>
                                                <?php if ($stage['is_paid']): ?>
                                                    (مدفوعة - <?= $stage['cost_per_unit'] ?> ج.م/قطعة)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">الكمية المراد نقلها <span class="text-danger">*</span></label>
                                    <input type="number" name="transfer_quantity" id="transfer_quantity" class="form-control" min="1" required onchange="calculateTransferTotal()">
                                    <div class="form-text" id="transfer-available-text">المتاحة للنقل: 0</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">هل المرحلة الجديدة مدفوعة؟</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="transfer_is_paid" id="transfer_is_paid" onchange="toggleTransferPayment()">
                                        <label class="form-check-label" for="transfer_is_paid">
                                            مرحلة مدفوعة الأجر
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3" id="transfer_cost_section" style="display: none;">
                                    <label class="form-label">التكلفة لكل قطعة (ج.م) <span class="text-danger">*</span></label>
                                    <input type="number" name="transfer_cost_per_unit" id="transfer_cost_per_unit" class="form-control" step="0.01" min="0" onchange="calculateTransferTotal()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea name="transfer_notes" class="form-control" rows="2" placeholder="ملاحظات إضافية..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div id="transfer_cost_summary" class="alert alert-success" style="display: none;">
                            <h6><i class="fas fa-calculator me-1"></i>ملخص التكلفة:</h6>
                            <div id="transfer_cost_details"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="transfer_to_stage" class="btn btn-warning">
                            <i class="fas fa-exchange-alt me-1"></i>نقل إلى المرحلة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال بدء مرحلة إنتاج -->
    <div class="modal fade" id="startProductionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">بدء مرحلة إنتاج جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="startProductionForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="start_production" value="1">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            اختر أمر القص والعامل ومرحلة التصنيع لبدء العمل
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">أمر القص <span class="text-danger">*</span></label>
                                    <select name="cutting_order_id" class="form-select" required onchange="updateAvailableQuantityStart()">
                                        <option value="">اختر أمر القص (العدد: <?= count($available_orders) ?>)</option>
                                        <?php foreach ($available_orders as $order): ?>
                                            <option value="<?= $order['id'] ?>" data-available="<?= $order['available_quantity'] ?>" data-product="<?= htmlspecialchars($order['product_name']) ?>">
                                                <?= htmlspecialchars($order['cutting_number']) ?> - <?= htmlspecialchars($order['product_name']) ?>
                                                (متاح: <?= $order['available_quantity'] ?> قطعة)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">العامل <span class="text-danger">*</span></label>
                                    <select name="worker_id" class="form-select" required>
                                        <option value="">اختر العامل</option>
                                        <?php 
                                        // تشخيص مباشر
                                        $debug_stmt = $pdo->query("SELECT id, name FROM workers WHERE is_active = 1");
                                        $debug_workers = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($debug_workers as $worker): ?>
                                            <option value="<?= $worker['id'] ?>">
                                                <?= htmlspecialchars($worker['name']) ?> (ID: <?= $worker['id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="text-muted small">
                                        عدد العمال المتاحين: <?= count($debug_workers) ?>
                                        <?php if (!empty($debug_workers)): ?>
                                            - أول عامل: <?= $debug_workers[0]['name'] ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">مرحلة التصنيع <span class="text-danger">*</span></label>
                                    <select name="stage_id" class="form-select" required onchange="updateStageInfoStart()">
                                        <option value="">اختر المرحلة</option>
                                        <?php foreach ($stages as $stage): ?>
                                            <option value="<?= $stage['id'] ?>" 
                                                    data-paid="<?= $stage['is_paid'] ?>" 
                                                    data-cost="<?= $stage['cost_per_uniit'] ?>">
                                                <?= htmlspecialchars($stage['name']) ?>
                                                <?php if ($stage['is_paid']): ?>
                                                    (مدفوعة - <?= $stage['cost_per_unit'] ?> ج.م/قطعة)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="start_quantity" class="form-control" min="1" required onchange="calculateTotalStart()">
                                    <div class="form-text" id="available-quantity-start">اختر أمر القص أولاً</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">هل المرحلة مدفوعة الأجر؟</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid_start" onchange="togglePaymentStart()">
                                        <label class="form-check-label" for="is_paid_start">
                                            مرحلة مدفوعة الأجر
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3" id="cost_section_start" style="display: none;">
                                    <label class="form-label">التكلفة لكل قطعة (ج.م) <span class="text-danger">*</span></label>
                                    <input type="number" name="cost_per_unit" id="cost_per_unit_start" class="form-control" step="0.01" min="0" onchange="calculateTotalStart()">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="أي ملاحظات إضافية..."></textarea>
                        </div>
                        
                        <div id="cost_summary_start" class="alert alert-success" style="display: none;">
                            <h6><i class="fas fa-calculator me-1"></i>ملخص التكلفة:</h6>
                            <div id="cost_details_start"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="start_production_stage" class="btn btn-success">
                            <i class="fas fa-play me-1"></i>بدء مرحلة الإنتاج
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let maxFinishQuantity = 0;

    function updateAvailableQuantity() {
        const select = document.querySelector('select[name="cutting_order_id"]');
        const quantityInput = document.querySelector('input[name="quantity_assigned"]');
        const textDiv = document.getElementById('available-quantity-text');
        
        if (select.value) {
            const available = select.options[select.selectedIndex].dataset.available;
            quantityInput.max = available;
            quantityInput.value = available;
            textDiv.innerHTML = `الكمية المتاحة: ${available} قطعة`;
            textDiv.className = 'form-text text-success';
        } else {
            quantityInput.max = '';
            quantityInput.value = '';
            textDiv.innerHTML = 'اختر أمر القص أولاً';
            textDiv.className = 'form-text';
        }
    }

    function updateAssignment(assignmentId, newStatus, maxQuantity) {
        // إنشاء نموذج مخفي وإرساله
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        // إضافة الحقول المطلوبة
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?>';
        form.appendChild(csrfInput);
        
        const assignmentInput = document.createElement('input');
        assignmentInput.type = 'hidden';
        assignmentInput.name = 'assignment_id';
        assignmentInput.value = assignmentId;
        form.appendChild(assignmentInput);
        
        if (newStatus === 'in_progress') {
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'start_work';
            actionInput.value = '1';
            form.appendChild(actionInput);
        } else if (newStatus === 'completed') {
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'complete_work';
            actionInput.value = '1';
            form.appendChild(actionInput);
            
            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = 'quantity_completed';
            quantityInput.value = maxQuantity;
            form.appendChild(quantityInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }

    function completeAssignment(assignmentId, maxQuantity) {
        updateAssignment(assignmentId, 'completed', maxQuantity);
    }

    function finishProduction(assignmentId, availableQuantity) {
        maxFinishQuantity = availableQuantity;
        document.getElementById('finish_assignment_id').value = assignmentId;
        document.getElementById('finished_quantity').max = availableQuantity;
        document.getElementById('finished_quantity').value = availableQuantity;
        
        document.getElementById('finish-assignment-info').innerHTML = `
            <h6><i class="fas fa-info-circle me-1"></i>معلومات المهمة</h6>
            <strong>الكمية المتاحة للإنهاء:</strong> ${availableQuantity} قطعة
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('finishProductionModal'));
        modal.show();
    }

    function validateFinishQuantity() {
        const quantityInput = document.getElementById('finished_quantity');
        const quantityValidation = document.getElementById('finish-quantity-validation');
        const requestedQuantity = parseInt(quantityInput.value) || 0;
        
        if (requestedQuantity > maxFinishQuantity) {
            quantityValidation.innerHTML = `<span class="text-danger">الكمية المطلوبة أكبر من المتاحة (${maxFinishQuantity})</span>`;
            quantityInput.setCustomValidity('الكمية المطلوبة أكبر من المتاحة');
            return false;
        } else if (requestedQuantity > 0) {
            quantityValidation.innerHTML = `<span class="text-success">سيتم إنهاء ${requestedQuantity} قطعة</span>`;
            quantityInput.setCustomValidity('');
            return true;
        } else {
            quantityValidation.innerHTML = '<span class="text-muted">أدخل الكمية المطلوب إنهاؤها</span>';
            quantityInput.setCustomValidity('');
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const finishQuantityInput = document.getElementById('finished_quantity');
        if (finishQuantityInput) {
            finishQuantityInput.addEventListener('input', validateFinishQuantity);
        }
    });

    function transferWorker(assignmentId, workerName, productName, stageName, availableQty) {
        document.getElementById('transfer_assignment_id').value = assignmentId;
        document.getElementById('transfer_quantity').max = availableQty;
        document.getElementById('transfer_quantity').value = availableQty;
        document.getElementById('transfer-available-text').textContent = `المتاحة للنقل: ${availableQty}`;
        
        document.getElementById('transfer-assignment-info').innerHTML = `
            <strong>العامل الحالي:</strong> ${workerName}<br>
            <strong>المنتج:</strong> ${productName}<br>
            <strong>المرحلة الحالية:</strong> ${stageName}<br>
            <strong>الكمية المتاحة للنقل:</strong> ${availableQty} قطعة
        `;
        
        new bootstrap.Modal(document.getElementById('transferWorkerModal')).show();
    }

    function updateAvailableQuantityStart() {
        const select = document.querySelector('select[name="cutting_order_id"]');
        const selectedOption = select.options[select.selectedIndex];
        const quantityInput = document.getElementById('start_quantity');
        const availableText = document.getElementById('available-quantity-start');
        
        if (selectedOption.value) {
            const available = selectedOption.dataset.available;
            const product = selectedOption.dataset.product;
            
            quantityInput.max = available;
            quantityInput.value = available;
            availableText.innerHTML = `الكمية المتاحة: ${available} قطعة من ${product}`;
            availableText.className = 'form-text text-success';
        } else {
            quantityInput.max = '';
            quantityInput.value = '';
            availableText.innerHTML = 'اختر أمر القص أولاً';
            availableText.className = 'form-text text-muted';
        }
        
        calculateTotalStart();
    }

    function updateStageInfoStart() {
        const select = document.querySelector('select[name="stage_id"]');
        const selectedOption = select.options[select.selectedIndex];
        const isPaidCheckbox = document.getElementById('is_paid_start');
        const costInput = document.getElementById('cost_per_unit_start');
        
        if (selectedOption.value) {
            const isPaid = selectedOption.dataset.paid == '1';
            const defaultCost = selectedOption.dataset.cost || '0';
            
            isPaidCheckbox.checked = isPaid;
            costInput.value = isPaid ? defaultCost : '0';
            
            togglePaymentStart();
        }
        
        calculateTotalStart();
    }

    function togglePaymentStart() {
        const isPaidCheckbox = document.getElementById('is_paid_start');
        const costSection = document.getElementById('cost_section_start');
        const costInput = document.getElementById('cost_per_unit_start');
        
        if (isPaidCheckbox.checked) {
            costSection.style.display = 'block';
            costInput.required = true;
        } else {
            costSection.style.display = 'none';
            costInput.required = false;
            costInput.value = '0';
        }
        
        calculateTotalStart();
    }

    function calculateTotalStart() {
        const quantity = parseInt(document.getElementById('start_quantity').value) || 0;
        const costPerUnit = parseFloat(document.getElementById('cost_per_unit_start').value) || 0;
        const isPaid = document.getElementById('is_paid_start').checked;
        const costSummary = document.getElementById('cost_summary_start');
        const costDetails = document.getElementById('cost_details_start');
        
        if (quantity > 0 && isPaid && costPerUnit > 0) {
            const totalCost = quantity * costPerUnit;
            costDetails.innerHTML = `
                <strong>الكمية:</strong> ${quantity} قطعة<br>
                <strong>التكلفة لكل قطعة:</strong> ${costPerUnit} ج.م<br>
                <strong>إجمالي التكلفة:</strong> ${totalCost.toFixed(2)} ج.م
            `;
            costSummary.style.display = 'block';
        } else {
            costSummary.style.display = 'none';
        }
    }

    function updateTransferStageInfo() {
        const stageSelect = document.querySelector('select[name="new_stage_id"]');
        const isPaidCheckbox = document.getElementById('transfer_is_paid');
        const costInput = document.getElementById('transfer_cost_per_unit');
        
        if (stageSelect.value) {
            const option = stageSelect.options[stageSelect.selectedIndex];
            const isPaid = option.dataset.paid === '1';
            const defaultCost = parseFloat(option.dataset.cost) || 0;
            
            isPaidCheckbox.checked = isPaid;
            costInput.value = defaultCost;
            
            toggleTransferPayment();
            calculateTransferTotal();
        }
    }

    function toggleTransferPayment() {
        const isPaidCheckbox = document.getElementById('transfer_is_paid');
        const costSection = document.getElementById('transfer_cost_section');
        const costInput = document.getElementById('transfer_cost_per_unit');
        
        if (isPaidCheckbox.checked) {
            costSection.style.display = 'block';
            costInput.required = true;
        } else {
            costSection.style.display = 'none';
            costInput.required = false;
            costInput.value = '0';
        }
        
        calculateTransferTotal();
    }

    function calculateTransferTotal() {
        const quantity = parseInt(document.getElementById('transfer_quantity').value) || 0;
        const costPerUnit = parseFloat(document.getElementById('transfer_cost_per_unit').value) || 0;
        const isPaid = document.getElementById('transfer_is_paid').checked;
        const costSummary = document.getElementById('transfer_cost_summary');
        const costDetails = document.getElementById('transfer_cost_details');
        
        if (quantity > 0 && isPaid && costPerUnit > 0) {
            const totalCost = quantity * costPerUnit;
            costDetails.innerHTML = `
                <strong>الكمية:</strong> ${quantity} قطعة<br>
                <strong>التكلفة لكل قطعة:</strong> ${costPerUnit} ج.م<br>
                <strong>إجمالي التكلفة:</strong> ${totalCost.toFixed(2)} ج.م
            `;
            costSummary.style.display = 'block';
        } else {
            costSummary.style.display = 'none';
        }
    }
    </script>

    <?php include '../includes/footer.php'; ?>

