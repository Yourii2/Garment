<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة فئة جديدة
if (isset($_POST['add_category'])) {
    try {
        $name = $_POST['name'];
        $description = $_POST['description'] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO fabric_categories (name, description, created_at) VALUES (?, ?, NOW())");
        $result = $stmt->execute([$name, $description]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم إضافة الفئة بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: fabric_categories.php');
    exit;
}

// معالجة تحديث فئة
if (isset($_POST['update_category'])) {
    try {
        $id = $_POST['category_id'];
        $name = $_POST['name'];
        $description = $_POST['description'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE fabric_categories SET name = ?, description = ? WHERE id = ?");
        $result = $stmt->execute([$name, $description, $id]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم تحديث الفئة بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: fabric_categories.php');
    exit;
}

// معالجة حذف فئة
if (isset($_POST['delete_category'])) {
    try {
        $id = $_POST['category_id'];
        
        // التحقق من وجود أقمشة مرتبطة بهذه الفئة
        $fabrics_count = 0;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM fabric_types WHERE category_id = ?");
            $stmt->execute([$id]);
            $fabrics_count = $stmt->fetchColumn();
        } catch (Exception $e) {
            // عمود category_id غير موجود، يمكن المتابعة
            $fabrics_count = 0;
        }
        
        // التحقق من وجود فواتير مرتبطة بأقمشة هذه الفئة
        $invoices_count = 0;
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM inventory_invoice_items iii 
                INNER JOIN fabric_types ft ON iii.fabric_id = ft.id 
                WHERE ft.category_id = ?
            ");
            $stmt->execute([$id]);
            $invoices_count = $stmt->fetchColumn();
        } catch (Exception $e) {
            // الجداول غير موجودة أو العمود غير موجود
            $invoices_count = 0;
        }
        
        if ($fabrics_count > 0) {
            $_SESSION['error_message'] = 'لا يمكن حذف هذه الفئة لوجود ' . $fabrics_count . ' قماش مرتبط بها';
        } elseif ($invoices_count > 0) {
            $_SESSION['error_message'] = 'لا يمكن حذف هذه الفئة لوجود فواتير مرتبطة بأقمشتها';
        } else {
            $stmt = $pdo->prepare("DELETE FROM fabric_categories WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $_SESSION['success_message'] = 'تم حذف الفئة بنجاح';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: fabric_categories.php');
    exit;
}

// جلب الفئات
$stmt = $pdo->query("SELECT * FROM fabric_categories ORDER BY name");
$categories = $stmt->fetchAll();

include '../includes/header.php';
$page_title = 'Fabric categories';
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
                    <i class="fas fa-tags me-2"></i>أنواع الأقمشة
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus me-1"></i>إضافة فئة جديدة
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

            <!-- جدول الفئات -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>قائمة فئات الأقمشة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>الاسم</th>
                                    <th>الوصف</th>
                                    <th>عدد الأقمشة</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد فئات مسجلة
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                    <?php
                                    // حساب عدد الأقمشة في هذه الفئة
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM fabric_types WHERE category_id = ?");
                                    $stmt->execute([$category['id']]);
                                    $fabric_count = $stmt->fetchColumn();
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category['name']) ?></td>
                                        <td><?= htmlspecialchars($category['description']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= $fabric_count ?></span>
                                        </td>
                                        <td><?= date('Y-m-d', strtotime($category['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-info" onclick="viewCategoryFabrics(<?= $category['id'] ?>)" title="عرض الأقمشة">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $category['id'] ?>" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDeleteCategory(<?= $category['id'] ?>, '<?= addslashes($category['name']) ?>')" title="حذف">
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

<!-- Modal إضافة فئة -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة فئة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الفئة</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_category" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals تعديل الفئات -->
<?php foreach ($categories as $category): ?>
<div class="modal fade" id="editCategoryModal<?= $category['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الفئة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الفئة</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($category['description']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="update_category" class="btn btn-primary">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal عرض أقمشة الفئة -->
<div class="modal fade" id="categoryFabricsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">أقمشة الفئة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="categoryFabricsContent">
                    <!-- سيتم تحميل المحتوى عبر AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewCategoryFabrics(categoryId) {
    $('#categoryFabricsModal').modal('show');
    $('#categoryFabricsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>');
    
    fetch('ajax/get_category_fabrics.php?category_id=' + categoryId)
        .then(response => response.text())
        .then(data => {
            $('#categoryFabricsContent').html(data);
        })
        .catch(error => {
            $('#categoryFabricsContent').html('<div class="alert alert-danger">خطأ في تحميل البيانات</div>');
        });
}

function confirmDeleteCategory(id, name) {
    if (confirm('هل أنت متأكد من حذف الفئة "' + name + '"؟\nتأكد من عدم وجود أقمشة أو فواتير مرتبطة بهذه الفئة.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="category_id" value="' + id + '"><input type="hidden" name="delete_category" value="1">';
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