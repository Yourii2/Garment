<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة خزينة جديدة
if (isset($_POST['add_treasury'])) {
    try {
        $name = $_POST['name'];
        $description = $_POST['description'] ?? '';
        $initial_balance = floatval($_POST['initial_balance'] ?? 0);
        
        $pdo->beginTransaction();
        
        // إضافة الخزينة
        $stmt = $pdo->prepare("INSERT INTO treasuries (name, description, current_balance, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$name, $description, $initial_balance]);
        
        if ($result && $initial_balance > 0) {
            $treasury_id = $pdo->lastInsertId();
            
            // إضافة معاملة الرصيد الافتتاحي
            $stmt = $pdo->prepare("
                INSERT INTO treasury_transactions 
                (treasury_id, amount, type, description, user_id, created_at) 
                VALUES (?, ?, 'income', 'رصيد افتتاحي', ?, NOW())
            ");
            $stmt->execute([$treasury_id, $initial_balance, $_SESSION['user_id']]);
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = 'تم إضافة الخزينة بنجاح';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: treasuries.php');
    exit;
}

// معالجة حذف خزينة
if (isset($_POST['delete_treasury'])) {
    try {
        $id = $_POST['treasury_id'];
        
        // فحص إذا كانت الخزينة فارغة
        $stmt = $pdo->prepare("SELECT current_balance FROM treasuries WHERE id = ?");
        $stmt->execute([$id]);
        $balance = $stmt->fetchColumn();
        
        if ($balance != 0) {
            $_SESSION['error_message'] = 'لا يمكن حذف الخزينة لأنها تحتوي على رصيد';
        } else {
            // حذف المعاملات أولاً
            $stmt = $pdo->prepare("DELETE FROM treasury_transactions WHERE treasury_id = ?");
            $stmt->execute([$id]);
            
            // حذف الخزينة
            $stmt = $pdo->prepare("DELETE FROM treasuries WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $_SESSION['success_message'] = 'تم حذف الخزينة بنجاح';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: treasuries.php');
    exit;
}

// جلب الخزائن
$stmt = $pdo->query("
    SELECT t.*, 
           COUNT(tt.id) as transactions_count,
           (SELECT COUNT(*) FROM treasury_transactions WHERE treasury_id = t.id AND type IN ('income', 'transfer_in')) as income_count,
           (SELECT COUNT(*) FROM treasury_transactions WHERE treasury_id = t.id AND type IN ('expense', 'transfer_out')) as expense_count
    FROM treasuries t
    LEFT JOIN treasury_transactions tt ON t.id = tt.treasury_id
    WHERE t.is_active = 1
    GROUP BY t.id
    ORDER BY t.name ASC
");
$treasuries = $stmt->fetchAll();

$page_title = 'إدارة الخزائن';
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
                    <i class="fas fa-cash-register me-2"></i>إدارة الخزائن
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTreasuryModal">
                    <i class="fas fa-plus me-1"></i>إضافة خزينة جديدة
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
                                    <th>اسم الخزينة</th>
                                    <th>الوصف</th>
                                    <th>الرصيد الحالي</th>
                                    <th>عدد المعاملات</th>
                                    <th>الواردات</th>
                                    <th>المصروفات</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($treasuries as $treasury): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($treasury['name']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($treasury['description']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $treasury['current_balance'] >= 0 ? 'success' : 'danger' ?> fs-6">
                                                <?= number_format($treasury['current_balance'], 2) ?> ج.م
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $treasury['transactions_count'] ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= $treasury['income_count'] ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger"><?= $treasury['expense_count'] ?></span>
                                        </td>
                                        <td><?= date('Y-m-d', strtotime($treasury['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewTransactions(<?= $treasury['id'] ?>)">
                                                <i class="fas fa-list"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editTreasury(<?= $treasury['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($treasury['current_balance'] == 0): ?>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteTreasury(<?= $treasury['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
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

<!-- Modal إضافة خزينة -->
<div class="modal fade" id="addTreasuryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة خزينة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الخزينة *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرصيد الافتتاحي</label>
                        <input type="number" name="initial_balance" class="form-control" step="0.01" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_treasury" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewTransactions(id) {
    window.location.href = 'treasury_transactions.php?id=' + id;
}

function editTreasury(id) {
    alert('سيتم إضافة وظيفة التعديل قريباً');
}

function deleteTreasury(id) {
    if (confirm('هل أنت متأكد من حذف هذه الخزينة؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="treasury_id" value="${id}">
            <input type="hidden" name="delete_treasury" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>