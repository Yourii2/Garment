<?php
require_once '../config/config.php';
checkLogin();

// التحقق من صلاحيات الإدارة
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// معالجة إضافة مستخدم جديد
if (isset($_POST['add_user'])) {
    try {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = $_POST['full_name'];
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'];
        $permissions = isset($_POST['permissions']) ? json_encode($_POST['permissions']) : null;
        
        // التحقق من عدم تكرار اسم المستخدم
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->execute([$username]);
        
        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error_message'] = 'اسم المستخدم موجود بالفعل';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role, permissions, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$username, $password, $full_name, $email, $phone, $role, $permissions]);
            
            if ($result) {
                $_SESSION['success_message'] = 'تم إضافة المستخدم بنجاح';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: users.php');
    exit;
}

// معالجة تعديل مستخدم
if (isset($_POST['edit_user'])) {
    try {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $permissions = isset($_POST['permissions']) ? json_encode($_POST['permissions']) : null;
        
        // التحقق من عدم تكرار اسم المستخدم (باستثناء المستخدم الحالي)
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check_stmt->execute([$username, $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error_message'] = 'اسم المستخدم موجود بالفعل';
        } else {
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ?, phone = ?, role = ?, is_active = ?, permissions = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$username, $password, $full_name, $email, $phone, $role, $is_active, $permissions, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ?, is_active = ?, permissions = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$username, $full_name, $email, $phone, $role, $is_active, $permissions, $user_id]);
            }
            
            if ($result) {
                $_SESSION['success_message'] = 'تم تحديث المستخدم بنجاح';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: users.php');
    exit;
}

// معالجة حذف مستخدم
if (isset($_POST['delete_user'])) {
    try {
        $user_id = $_POST['user_id'];
        
        // منع حذف المستخدم الحالي
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'لا يمكن حذف حسابك الشخصي';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$user_id]);
            
            if ($result) {
                $_SESSION['success_message'] = 'تم حذف المستخدم بنجاح';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: users.php');
    exit;
}

// جلب المستخدمين
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// تعريف الأدوار والصلاحيات
$roles = [
    'admin' => 'مدير النظام',
    'supervisor' => 'مشرف',
    'accountant' => 'محاسب',
    'worker' => 'عامل',
    'sales_rep' => 'مندوب مبيعات',
    'limited_user' => 'مستخدم محدود'
];

$available_permissions = [
    'view_reports' => 'عرض التقارير',
    'manage_inventory' => 'إدارة المخزون',
    'manage_orders' => 'إدارة الطلبيات',
    'manage_production' => 'إدارة الإنتاج',
    'manage_finance' => 'إدارة المالية',
    'manage_hr' => 'إدارة الموارد البشرية',
    'manage_quality' => 'إدارة الجودة'
];

$page_title = 'إدارة المستخدمين';
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
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-users-cog text-primary me-2"></i>
                    إدارة المستخدمين
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i>إضافة مستخدم جديد
                </button>
            </div>

            <!-- جدول المستخدمين -->
            <div class="card">
                <div class="card-header">
                    <h5>قائمة المستخدمين</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>اسم المستخدم</th>
                                    <th>الاسم الكامل</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الهاتف</th>
                                    <th>الدور</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="badge bg-info ms-1">أنت</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $roles[$user['role']] ?? $user['role'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">نشط</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">معطل</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" data-bs-target="#deleteUserModal<?= $user['id'] ?>">
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

<!-- Modal إضافة مستخدم -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مستخدم جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الاسم الكامل *</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الدور *</label>
                                <select name="role" class="form-control" required>
                                    <?php foreach ($roles as $role_key => $role_name): ?>
                                    <option value="<?= $role_key ?>"><?= $role_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الصلاحيات</label>
                        <div class="row">
                            <?php foreach ($available_permissions as $perm_key => $perm_name): ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $perm_key ?>" id="add_<?= $perm_key ?>">
                                    <label class="form-check-label" for="add_<?= $perm_key ?>">
                                        <?= $perm_name ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_user" class="btn btn-primary">إضافة المستخدم</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals تعديل المستخدمين -->
<?php foreach ($users as $user): 
    $user_permissions = $user['permissions'] ? json_decode($user['permissions'], true) : [];
?>
<div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل المستخدم: <?= htmlspecialchars($user['full_name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم *</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور الجديدة</label>
                                <input type="password" name="password" class="form-control" placeholder="اتركها فارغة للاحتفاظ بالحالية">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الاسم الكامل *</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الدور *</label>
                                <select name="role" class="form-control" required>
                                    <?php foreach ($roles as $role_key => $role_name): ?>
                                    <option value="<?= $role_key ?>" <?= $user['role'] == $role_key ? 'selected' : '' ?>><?= $role_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active_<?= $user['id'] ?>" <?= $user['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active_<?= $user['id'] ?>">
                                حساب نشط
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الصلاحيات</label>
                        <div class="row">
                            <?php foreach ($available_permissions as $perm_key => $perm_name): ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $perm_key ?>" 
                                           id="edit_<?= $perm_key ?>_<?= $user['id'] ?>" 
                                           <?= in_array($perm_key, $user_permissions) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="edit_<?= $perm_key ?>_<?= $user['id'] ?>">
                                        <?= $perm_name ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="edit_user" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal حذف المستخدم -->
<?php if ($user['id'] != $_SESSION['user_id']): ?>
<div class="modal fade" id="deleteUserModal<?= $user['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف المستخدم: <strong><?= htmlspecialchars($user['full_name']) ?></strong>؟</p>
                <p class="text-danger">هذا الإجراء لا يمكن التراجع عنه!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button type="submit" name="delete_user" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>