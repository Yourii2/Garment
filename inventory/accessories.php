<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة إكسسوار جديد
if (isset($_POST['add_accessory'])) {
    try {
        $name = $_POST['name'];
        $type = $_POST['type'] ?? '';
        $unit = $_POST['unit'] ?? 'قطعة';
        $cost_per_unit = $_POST['cost_per_unit'] ?? 0;
        $min_quantity = $_POST['min_quantity'] ?? 0;
        
        // توليد الكود التلقائي
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM accessories");
        $result = $stmt->fetch();
        $newId = ($result['max_id'] ?? 0) + 1;
        $code = 'A' . $newId;
        
        $stmt = $pdo->prepare("INSERT INTO accessories (name, code, type, unit, cost_per_unit, min_quantity, current_quantity, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
        $result = $stmt->execute([$name, $code, $type, $unit, $cost_per_unit, $min_quantity]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم إضافة الإكسسوار بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: accessories.php');
    exit;
}

// معالجة تحديث إكسسوار
if (isset($_POST['update_accessory'])) {
    try {
        $id = $_POST['accessory_id'];
        $name = $_POST['name'];
        $type = $_POST['type'] ?? '';
        $unit = $_POST['unit'] ?? 'قطعة';
        $cost_per_unit = $_POST['cost_per_unit'] ?? 0;
        $min_quantity = $_POST['min_quantity'] ?? 0;
        
        $stmt = $pdo->prepare("UPDATE accessories SET name = ?, type = ?, unit = ?, cost_per_unit = ?, min_quantity = ? WHERE id = ?");
        $result = $stmt->execute([$name, $type, $unit, $cost_per_unit, $min_quantity, $id]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم تحديث الإكسسوار بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: accessories.php');
    exit;
}

// معالجة حذف إكسسوار
if (isset($_POST['delete_accessory'])) {
    try {
        $id = $_POST['accessory_id'];
        
        $stmt = $pdo->prepare("DELETE FROM accessories WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم حذف الإكسسوار بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: accessories.php');
    exit;
}

// جلب الإكسسوارات
$stmt = $pdo->query("SELECT * FROM accessories ORDER BY id DESC");
$accessories = $stmt->fetchAll();

// جلب أنواع الإكسسوارات
$stmt = $pdo->query("SELECT * FROM accessory_types ORDER BY name");
$accessory_types = $stmt->fetchAll();

include '../includes/header.php';
$page_title = 'Accessories';
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
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-puzzle-piece me-2"></i>إدارة الإكسسوارات
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccessoryModal">
                    <i class="fas fa-plus me-1"></i>إضافة إكسسوار جديد
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

            <!-- جدول الإكسسوارات -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>قائمة الإكسسوارات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>الكود</th>
                                    <th>الاسم</th>
                                    <th>النوع</th>
                                    <th>الوحدة</th>
                                    <th>الكمية الحالية</th>
                                    <th>التكلفة/الوحدة</th>
                                    <th>الحد الأدنى</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($accessories)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد إكسسوارات مسجلة
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($accessories as $accessory): ?>
                                    <tr>
                                        <td><code><?= $accessory['code'] ?></code></td>
                                        <td><?= htmlspecialchars($accessory['name']) ?></td>
                                        <td><?= htmlspecialchars($accessory['type']) ?></td>
                                        <td><?= htmlspecialchars($accessory['unit']) ?></td>
                                        <td>
                                            <span class="badge <?= $accessory['current_quantity'] <= $accessory['min_quantity'] ? 'bg-danger' : 'bg-success' ?>">
                                                <?= $accessory['current_quantity'] ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($accessory['cost_per_unit'], 2) ?> ج.م</td>
                                        <td><?= $accessory['min_quantity'] ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-info" onclick="viewAccessoryTransactions(<?= $accessory['id'] ?>)" title="عرض المعاملات">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAccessoryModal<?= $accessory['id'] ?>" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDeleteAccessory(<?= $accessory['id'] ?>, '<?= addslashes($accessory['name']) ?>')" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

<!-- Modal إضافة إكسسوار -->
<div class="modal fade" id="addAccessoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة إكسسوار جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الإكسسوار</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">النوع</label>
                        <select class="form-select" name="type">
                            <option value="">اختر النوع</option>
                            <?php foreach ($accessory_types as $type): ?>
                                <option value="<?= $type['name'] ?>"><?= $type['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوحدة</label>
                        <select class="form-select" name="unit">
                            <option value="قطعة">قطعة</option>
                            <option value="متر">متر</option>
                            <option value="كيلو">كيلو</option>
                            <option value="عبوة">عبوة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التكلفة لكل وحدة</label>
                        <input type="number" step="0.01" class="form-control" name="cost_per_unit" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحد الأدنى للكمية</label>
                        <input type="number" class="form-control" name="min_quantity" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_accessory" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals تعديل الإكسسوارات -->
<?php foreach ($accessories as $accessory): ?>
<div class="modal fade" id="editAccessoryModal<?= $accessory['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الإكسسوار</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accessory_id" value="<?= $accessory['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الإكسسوار</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($accessory['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">النوع</label>
                        <select class="form-select" name="type">
                            <option value="">اختر النوع</option>
                            <?php foreach ($accessory_types as $type): ?>
                                <option value="<?= $type['name'] ?>" <?= $accessory['type'] == $type['name'] ? 'selected' : '' ?>>
                                    <?= $type['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوحدة</label>
                        <select class="form-select" name="unit">
                            <option value="قطعة" <?= $accessory['unit'] == 'قطعة' ? 'selected' : '' ?>>قطعة</option>
                            <option value="متر" <?= $accessory['unit'] == 'متر' ? 'selected' : '' ?>>متر</option>
                            <option value="كيلو" <?= $accessory['unit'] == 'كيلو' ? 'selected' : '' ?>>كيلو</option>
                            <option value="عبوة" <?= $accessory['unit'] == 'عبوة' ? 'selected' : '' ?>>عبوة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التكلفة لكل وحدة</label>
                        <input type="number" step="0.01" class="form-control" name="cost_per_unit" value="<?= $accessory['cost_per_unit'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحد الأدنى للكمية</label>
                        <input type="number" class="form-control" name="min_quantity" value="<?= $accessory['min_quantity'] ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="update_accessory" class="btn btn-primary">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- مودال عرض المعاملات -->
<div class="modal fade" id="accessoryTransactionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">معاملات الإكسسوار</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="accessoryTransactionsContent">
                <!-- سيتم تحميل المحتوى هنا -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewAccessoryTransactions(accessoryId) {
    const modal = new bootstrap.Modal(document.getElementById('accessoryTransactionsModal'));
    modal.show();
    
    document.getElementById('accessoryTransactionsContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>';
    
    fetch('ajax/get_accessory_transactions.php?accessory_id=' + accessoryId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('accessoryTransactionsContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('accessoryTransactionsContent').innerHTML = '<div class="alert alert-danger">خطأ في تحميل البيانات</div>';
        });
}

function confirmDeleteAccessory(id, name) {
    if (confirm('هل أنت متأكد من حذف الإكسسوار "' + name + '"؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="accessory_id" value="' + id + '"><input type="hidden" name="delete_accessory" value="1">';
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