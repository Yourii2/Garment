<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة منتج جديد
if (isset($_POST['add_product'])) {
    try {
        $pdo->beginTransaction();
        
        $name = $_POST['name'];
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $fabric_consumption = $_POST['fabric_consumption'] ?? 0;
        $estimated_cost = $_POST['estimated_cost'] ?? 0;
        $selling_price = $_POST['selling_price'] ?? 0;
        
        // توليد الكود التلقائي
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM products");
        $result = $stmt->fetch();
        $newId = ($result['max_id'] ?? 0) + 1;
        $code = 'P' . $newId;
        
        // إدراج المنتج
        $stmt = $pdo->prepare("INSERT INTO products (name, code, description, category, fabric_consumption, estimated_cost, selling_price, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$name, $code, $description, $category, $fabric_consumption, $estimated_cost, $selling_price]);
        
        if ($result) {
            $product_id = $pdo->lastInsertId();
            
            // إضافة مراحل المنتج
            if (!empty($_POST['stages'])) {
                foreach ($_POST['stages'] as $stage_id) {
                    $stmt = $pdo->prepare("INSERT INTO product_stages (product_id, stage_id, sort_order) VALUES (?, ?, ?)");
                    $stmt->execute([$product_id, $stage_id, 0]);
                }
            }
            
            $pdo->commit();
            $_SESSION['success_message'] = 'تم إضافة المنتج بنجاح بالكود: ' . $code;
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: products.php');
    exit;
}

// معالجة التعديل
if (isset($_POST['edit_product'])) {
    try {
        $pdo->beginTransaction();
        
        $id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $fabric_consumption = $_POST['fabric_consumption'] ?? 0;
        $estimated_cost = $_POST['estimated_cost'] ?? 0;
        $selling_price = $_POST['selling_price'] ?? 0;
        
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, category = ?, fabric_consumption = ?, estimated_cost = ?, selling_price = ? WHERE id = ?");
        $result = $stmt->execute([$name, $description, $category, $fabric_consumption, $estimated_cost, $selling_price, $id]);
        
        if ($result) {
            // حذف المراحل القديمة وإضافة الجديدة
            $stmt = $pdo->prepare("DELETE FROM product_stages WHERE product_id = ?");
            $stmt->execute([$id]);
            
            if (!empty($_POST['stages'])) {
                foreach ($_POST['stages'] as $stage_id) {
                    $stmt = $pdo->prepare("INSERT INTO product_stages (product_id, stage_id, sort_order) VALUES (?, ?, ?)");
                    $stmt->execute([$id, $stage_id, 0]);
                }
            }
            
            $pdo->commit();
            $_SESSION['success_message'] = 'تم تحديث المنتج بنجاح';
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: products.php');
    exit;
}

// معالجة الحذف
if (isset($_POST['delete_product'])) {
    try {
        $id = $_POST['product_id'];
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم حذف المنتج بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: products.php');
    exit;
}

// جلب المنتجات
$stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

// جلب بيانات الأقمشة
$stmt = $pdo->query("SELECT id, name, cost_per_unit FROM fabric_types ORDER BY name");
$fabrics = $stmt->fetchAll();

// جلب مراحل التصنيع
$stmt = $pdo->query("SELECT id, name, is_paid, cost_per_unit FROM manufacturing_stages ORDER BY sort_order, name");
$stages = $stmt->fetchAll();

$page_title = 'منتجات التصنيع';
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
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tshirt text-primary me-2"></i>
                    منتجات التصنيع
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>إضافة منتج جديد
                </button>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>قائمة المنتجات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>الكود</th>
                                    <th>اسم المنتج</th>
                                    <th>الفئة</th>
                                    <th>استهلاك القماش</th>
                                    <th>التكلفة المقدرة</th>
                                    <th>سعر البيع</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد منتجات مسجلة
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($product['code']) ?></strong></td>
                                            <td>
                                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                                <?php if ($product['description']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($product['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($product['category']) ?></td>
                                            <td><?= number_format($product['fabric_consumption'], 2) ?> متر</td>
                                            <td><?= number_format($product['estimated_cost'], 2) ?> ج.م</td>
                                            <td><?= number_format($product['selling_price'], 2) ?> ج.م</td>
                                            <td>
                                                <?php if ($product['is_active']): ?>
                                                    <span class="badge bg-success">نشط</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">غير نشط</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="viewProduct(<?= $product['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">
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

<!-- Modal إضافة منتج جديد -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>إضافة منتج جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المنتج *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الفئة</label>
                                <input type="text" name="category" class="form-control" placeholder="مثال: قمصان، بناطيل، فساتين">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">نوع القماش</label>
                                <select name="fabric_type_id" id="fabric_type_id" class="form-select" onchange="calculateEstimatedCost()">
                                    <option value="">اختر نوع القماش</option>
                                    <?php foreach ($fabrics as $fabric): ?>
                                        <option value="<?= $fabric['id'] ?>" data-cost="<?= $fabric['cost_per_unit'] ?>">
                                            <?= htmlspecialchars($fabric['name']) ?> - <?= $fabric['cost_per_unit'] ?> ج.م/متر
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">استهلاك القماش (متر)</label>
                                <input type="number" name="fabric_consumption" id="fabric_consumption" class="form-control" step="0.01" value="0" onchange="calculateEstimatedCost()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">التكلفة المقدرة (محسوبة تلقائ)</label>
                                <input type="number" name="estimated_cost" id="estimated_cost" class="form-control" step="0.01" value="0" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">سعر البيع</label>
                                <input type="number" name="selling_price" class="form-control" step="0.01" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مراحل الإنتاج</label>
                        <div class="row">
                            <?php foreach ($stages as $stage): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input stage-checkbox" type="checkbox" name="stages[]" 
                                               value="<?= $stage['id'] ?>" id="stage_<?= $stage['id'] ?>" 
                                               data-cost="<?= $stage['cost_per_unit'] ?>" 
                                               data-paid="<?= $stage['is_paid'] ?>"
                                               onchange="calculateEstimatedCost()">
                                        <label class="form-check-label" for="stage_<?= $stage['id'] ?>">
                                            <?= htmlspecialchars($stage['name']) ?>
                                            <?php if ($stage['is_paid']): ?>
                                                <span class="text-muted">(<?= $stage['cost_per_unit'] ?> ج.م/قطعة)</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_product" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تعديل منتج -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>تعديل المنتج
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editProductForm">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المنتج *</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الفئة</label>
                                <input type="text" name="category" id="edit_category" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">نوع القماش</label>
                                <select name="fabric_type_id" id="edit_fabric_type_id" class="form-select" onchange="calculateEditEstimatedCost()">
                                    <option value="">اختر نوع القماش</option>
                                    <?php foreach ($fabrics as $fabric): ?>
                                        <option value="<?= $fabric['id'] ?>" data-cost="<?= $fabric['cost_per_unit'] ?>">
                                            <?= htmlspecialchars($fabric['name']) ?> - <?= $fabric['cost_per_unit'] ?> ج.م/متر
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">استهلاك القماش (متر)</label>
                                <input type="number" name="fabric_consumption" id="edit_fabric_consumption" class="form-control" step="0.01" onchange="calculateEditEstimatedCost()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">التكلفة المقدرة (محسوبة تلقائ)</label>
                                <input type="number" name="estimated_cost" id="edit_estimated_cost" class="form-control" step="0.01" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">سعر البيع</label>
                                <input type="number" name="selling_price" id="edit_selling_price" class="form-control" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مراحل الإنتاج</label>
                        <div class="row" id="edit_stages_container">
                            <?php foreach ($stages as $stage): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="stages[]" value="<?= $stage['id'] ?>" id="edit_stage_<?= $stage['id'] ?>" data-paid="<?= $stage['is_paid'] ?>" data-cost="<?= $stage['cost_per_unit'] ?>" onchange="calculateEditEstimatedCost()">
                                        <label class="form-check-label" for="edit_stage_<?= $stage['id'] ?>">
                                            <?= htmlspecialchars($stage['name']) ?>
                                            <?php if ($stage['is_paid']): ?>
                                                <span class="text-muted">(<?= $stage['cost_per_unit'] ?> ج.م/قطعة)</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="edit_product" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تأكيد الحذف -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>تأكيد الحذف
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف المنتج: <strong id="delete_product_name"></strong>؟</p>
                <p class="text-muted">لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" id="deleteProductForm">
                    <input type="hidden" name="product_id" id="delete_product_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="delete_product" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>حذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_category').value = product.category || '';
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('edit_fabric_consumption').value = product.fabric_consumption;
    document.getElementById('edit_estimated_cost').value = product.estimated_cost;
    document.getElementById('edit_selling_price').value = product.selling_price;
    
    // جلب مراحل المنتج
    fetch(`get_product_stages.php?id=${product.id}`)
        .then(response => response.json())
        .then(stages => {
            // إلغاء تحديد جميع المراحل
            document.querySelectorAll('#edit_stages_container input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            stages.forEach(stage => {
                const checkbox = document.getElementById(`edit_stage_${stage.stage_id}`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        });
    
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}

function deleteProduct(id, name) {
    document.getElementById('delete_product_id').value = id;
    document.getElementById('delete_product_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteProductModal')).show();
}

function viewProduct(id) {
    // يمكن إضافة صفحة عرض تفاصيل المنتج
    window.location.href = `product_details.php?id=${id}`;
}

function calculateEstimatedCost() {
    let totalCost = 0;
    
    // حساب تكلفة القماش
    const fabricSelect = document.getElementById('fabric_type_id');
    const fabricConsumption = parseFloat(document.getElementById('fabric_consumption').value) || 0;
    
    if (fabricSelect.value) {
        const fabricCost = parseFloat(fabricSelect.options[fabricSelect.selectedIndex].dataset.cost) || 0;
        totalCost += fabricCost * fabricConsumption;
    }
    
    // حساب تكلفة مراحل التصنيع المدفوعة
    const stageCheckboxes = document.querySelectorAll('.stage-checkbox:checked');
    stageCheckboxes.forEach(checkbox => {
        const isPaid = checkbox.dataset.paid === '1';
        if (isPaid) {
            const stageCost = parseFloat(checkbox.dataset.cost) || 0;
            totalCost += stageCost;
        }
    });
    
    document.getElementById('estimated_cost').value = totalCost.toFixed(2);
}

function calculateEditEstimatedCost() {
    let totalCost = 0;
    
    // حساب تكلفة القماش
    const fabricSelect = document.getElementById('edit_fabric_type_id');
    const fabricConsumption = parseFloat(document.getElementById('edit_fabric_consumption').value) || 0;
    
    if (fabricSelect.value) {
        const fabricCost = parseFloat(fabricSelect.options[fabricSelect.selectedIndex].dataset.cost) || 0;
        totalCost += fabricCost * fabricConsumption;
    }
    
    // حساب تكلفة مراحل التصنيع المدفوعة
    const stageCheckboxes = document.querySelectorAll('#edit_stages_container .form-check-input:checked');
    stageCheckboxes.forEach(checkbox => {
        const isPaid = checkbox.dataset.paid === '1';
        if (isPaid) {
            const stageCost = parseFloat(checkbox.dataset.cost) || 0;
            totalCost += stageCost;
        }
    });
    
    document.getElementById('edit_estimated_cost').value = totalCost.toFixed(2);
}
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>