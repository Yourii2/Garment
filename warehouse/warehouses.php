<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة مخزن جديد
if (isset($_POST['add_warehouse'])) {
    try {
        $name = $_POST['name'];
        $location = $_POST['location'] ?? '';
        $manager = $_POST['manager'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO branches (name, type, address, created_at) VALUES (?, 'warehouse', ?, NOW())");
        $result = $stmt->execute([$name, $location]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم إضافة المخزن بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: warehouses.php');
    exit;
}

// معالجة تعديل مخزن
if (isset($_POST['edit_warehouse'])) {
    try {
        $id = $_POST['warehouse_id'];
        $name = $_POST['name'];
        $location = $_POST['location'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE branches SET name = ?, address = ?, updated_at = NOW() WHERE id = ? AND type = 'warehouse'");
        $result = $stmt->execute([$name, $location, $id]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم تحديث المخزن بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: warehouses.php');
    exit;
}

// معالجة حذف مخزن
if (isset($_POST['delete_warehouse'])) {
    try {
        $id = $_POST['warehouse_id'];
        
        // فحص إذا كان المخزن فارغ
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM fabric_types WHERE branch_id = ?");
        $stmt->execute([$id]);
        $fabric_count = $stmt->fetchColumn();
        
        if ($fabric_count > 0) {
            $_SESSION['error_message'] = 'لا يمكن حذف المخزن لأنه يحتوي على أقمشة';
        } else {
            $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ? AND type = 'warehouse'");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $_SESSION['success_message'] = 'تم حذف المخزن بنجاح';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: warehouses.php');
    exit;
}

// جلب المخازن مع إحصائيات شاملة
$stmt = $pdo->query("
    SELECT b.*, 
           COUNT(DISTINCT ft.id) as fabric_count,
           COUNT(DISTINCT a.id) as accessory_count,
           COUNT(DISTINCT po.id) as production_count,
           COUNT(DISTINCT fi.id) as finished_count,
           SUM(COALESCE(ft.current_quantity, 0)) as total_fabric_quantity,
           SUM(COALESCE(a.current_quantity, 0)) as total_accessory_quantity
    FROM branches b
    LEFT JOIN fabric_types ft ON b.id = ft.branch_id
    LEFT JOIN accessories a ON b.id = a.branch_id
    LEFT JOIN production_orders po ON b.id = po.branch_id AND po.status IN ('pending', 'in_progress')
    LEFT JOIN finished_inventory fi ON b.id = fi.branch_id AND fi.status = 'in_stock'
    WHERE b.type = 'warehouse'
    GROUP BY b.id
    ORDER BY b.name ASC
");
$warehouses = $stmt->fetchAll();

$page_title = 'إدارة المخازن';
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
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-warehouse me-2"></i>إدارة المخازن
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                    <i class="fas fa-plus me-1"></i>إضافة مخزن جديد
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
                                    <th>اسم المخزن</th>
                                    <th>الموقع</th>
                                    <th>الأقمشة</th>
                                    <th>الإكسسوارات</th>
                                    <th>قيد التصنيع</th>
                                    <th>منتجات مكتملة</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($warehouses)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد مخازن مسجلة
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($warehouse['name']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($warehouse['address'] ?? 'غير محدد') ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?= $warehouse['fabric_count'] ?></span>
                                                <small class="text-muted d-block"><?= number_format($warehouse['total_fabric_quantity'], 1) ?> وحدة</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= $warehouse['accessory_count'] ?></span>
                                                <small class="text-muted d-block"><?= number_format($warehouse['total_accessory_quantity'], 0) ?> قطعة</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?= $warehouse['production_count'] ?></span>
                                                <small class="text-muted d-block">أمر</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $warehouse['finished_count'] ?></span>
                                                <small class="text-muted d-block">منتج</small>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($warehouse['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewWarehouse(<?= $warehouse['id'] ?>)" title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="warehouse_contents.php?id=<?= $warehouse['id'] ?>" class="btn btn-sm btn-outline-info" title="محتويات المخزن">
                                                        <i class="fas fa-boxes"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-warning" onclick="editWarehouse(<?= $warehouse['id'] ?>)" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteWarehouse(<?= $warehouse['id'] ?>)" title="حذف">
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

<!-- Modal إضافة مخزن -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مخزن جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم المخزن *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموقع</label>
                        <input type="text" name="location" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_warehouse" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تعديل مخزن -->
<div class="modal fade" id="editWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل المخزن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="warehouse_id" id="edit_warehouse_id">
                    <div class="mb-3">
                        <label class="form-label">اسم المخزن *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموقع</label>
                        <input type="text" name="location" id="edit_location" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="edit_warehouse" class="btn btn-warning">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal عرض تفاصيل المخزن -->
<div class="modal fade" id="viewWarehouseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل المخزن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="warehouse-details"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="goToWarehouseContents()">
                    <i class="fas fa-boxes me-1"></i>عرض محتويات المخزن
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentWarehouseId = null;

function viewWarehouse(id) {
    currentWarehouseId = id;
    
    // جلب تفاصيل المخزن
    fetch(`get_warehouse_details.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const warehouse = data.warehouse;
                const fabrics = data.fabrics;
                
                let fabricsList = '';
                if (fabrics.length > 0) {
                    fabricsList = '<h6 class="mt-3">الأقمشة الموجودة:</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>نوع القماش</th><th>الكمية</th><th>الوحدة</th></tr></thead><tbody>';
                    fabrics.forEach(fabric => {
                        fabricsList += `<tr><td>${fabric.name}</td><td>${fabric.current_quantity}</td><td>${fabric.unit}</td></tr>`;
                    });
                    fabricsList += '</tbody></table></div>';
                } else {
                    fabricsList = '<div class="alert alert-info mt-3">لا توجد أقمشة في هذا المخزن</div>';
                }
                
                document.getElementById('warehouse-details').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>معلومات أساسية</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>اسم المخزن:</strong></td><td>${warehouse.name}</td></tr>
                                <tr><td><strong>الموقع:</strong></td><td>${warehouse.address || 'غير محدد'}</td></tr>
                                <tr><td><strong>الوصف:</strong></td><td>${warehouse.description || 'غير محدد'}</td></tr>
                                <tr><td><strong>تاريخ الإنشاء:</strong></td><td>${warehouse.created_at}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>إحصائيات</h6>
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h4 class="text-primary">${warehouse.fabric_count}</h4>
                                    <p class="mb-1">نوع قماش</p>
                                    <h4 class="text-success">${parseFloat(warehouse.total_quantity).toLocaleString()}</h4>
                                    <p class="mb-0">إجمالي الكمية</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    ${fabricsList}
                `;
                
                new bootstrap.Modal(document.getElementById('viewWarehouseModal')).show();
            } else {
                alert('خطأ في جلب البيانات');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطأ في الاتصال');
        });
}

function editWarehouse(id) {
    // جلب بيانات المخزن للتعديل
    fetch(`get_warehouse_details.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const warehouse = data.warehouse;
                document.getElementById('edit_warehouse_id').value = warehouse.id;
                document.getElementById('edit_name').value = warehouse.name;
                document.getElementById('edit_location').value = warehouse.address || '';
                document.getElementById('edit_description').value = warehouse.description || '';
                
                new bootstrap.Modal(document.getElementById('editWarehouseModal')).show();
            } else {
                alert('خطأ في جلب البيانات');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطأ في الاتصال');
        });
}

function goToWarehouseContents() {
    if (currentWarehouseId) {
        window.location.href = `warehouse_contents.php?id=${currentWarehouseId}`;
    }
}

function deleteWarehouse(id) {
    if (confirm('هل أنت متأكد من حذف هذا المخزن؟\nسيتم حذف المخزن نهائ

<?php include '../includes/footer.php'; ?>

</body>
</html>