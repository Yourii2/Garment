<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة عامل جديد
if (isset($_POST['add_worker'])) {
    try {
        $name = $_POST['name'];
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $salary_type = $_POST['salary_type'];
        $salary_amount = $_POST['salary_amount'] ?? 0;
        
        $stmt = $pdo->prepare("INSERT INTO workers (name, phone, address, salary_type, salary_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $address, $salary_type, $salary_amount]);
        
        $_SESSION['success_message'] = 'تم إضافة العامل بنجاح';
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: workers.php');
    exit;
}

// معالجة تحديث عامل
if (isset($_POST['update_worker'])) {
    try {
        $worker_id = $_POST['worker_id'];
        $name = $_POST['name'];
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $salary_type = $_POST['salary_type'];
        $salary_amount = $_POST['salary_amount'] ?? 0;
        
        $stmt = $pdo->prepare("UPDATE workers SET name = ?, phone = ?, address = ?, salary_type = ?, salary_amount = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $salary_type, $salary_amount, $worker_id]);
        
        $_SESSION['success_message'] = 'تم تحديث العامل بنجاح';
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: workers.php');
    exit;
}

// معالجة حذف عامل
if (isset($_POST['delete_worker'])) {
    try {
        $worker_id = $_POST['worker_id'];
        
        // التحقق من وجود معاملات مالية مرتبطة
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM worker_transactions WHERE worker_id = ?");
        $stmt->execute([$worker_id]);
        $transactions_count = $stmt->fetchColumn();
        
        if ($transactions_count > 0) {
            $_SESSION['error_message'] = 'لا يمكن حذف هذا العامل لوجود معاملات مالية مرتبطة به';
        } else {
            $stmt = $pdo->prepare("DELETE FROM workers WHERE id = ?");
            $result = $stmt->execute([$worker_id]);
            
            if ($result) {
                $_SESSION['success_message'] = 'تم حذف العامل بنجاح';
            } else {
                $_SESSION['error_message'] = 'فشل في حذف العامل';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: workers.php');
    exit;
}

// جلب العمال
$stmt = $pdo->query("SELECT * FROM workers ORDER BY id DESC");
$workers = $stmt->fetchAll();

$page_title = 'إدارة العمال';
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
                    <i class="fas fa-hard-hat text-primary me-2"></i>
                    إدارة العمال
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWorkerModal">
                    <i class="fas fa-plus me-2"></i>إضافة عامل جديد
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

            <!-- جدول العمال -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>قائمة العمال
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>الاسم</th>
                                    <th>رقم الهاتف</th>
                                    <th>العنوان</th>
                                    <th>نوع الراتب</th>
                                    <th>المبلغ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($workers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا يوجد عمال مسجلين
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($workers as $worker): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($worker['name']) ?></td>
                                        <td><?= htmlspecialchars($worker['phone']) ?></td>
                                        <td><?= htmlspecialchars($worker['address']) ?></td>
                                        <td>
                                            <?php
                                            $salary_types = [
                                                'daily' => 'يومي',
                                                'weekly' => 'أسبوعي',
                                                'monthly' => 'شهري',
                                                'per_piece' => 'بالإنتاج'
                                            ];
                                            echo $salary_types[$worker['salary_type']] ?? $worker['salary_type'];
                                            ?>
                                        </td>
                                        <td><?= number_format($worker['salary_amount'], 2) ?> ج.م</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewWorker(<?= $worker['id'] ?>)" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editWorkerModal<?= $worker['id'] ?>" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="confirmDeleteWorker(<?= $worker['id'] ?>, '<?= addslashes($worker['name']) ?>')" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal إضافة عامل -->
            <div class="modal fade" id="addWorkerModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">إضافة عامل جديد</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">الاسم الكامل</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">رقم الهاتف</label>
                                            <input type="text" class="form-control" name="phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">العنوان</label>
                                    <textarea class="form-control" name="address" rows="2"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">نوع الراتب</label>
                                            <select class="form-select" name="salary_type" required>
                                                <option value="daily">يومي</option>
                                                <option value="weekly">أسبوعي</option>
                                                <option value="monthly">شهري</option>
                                                <option value="per_piece">بالإنتاج</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">المبلغ</label>
                                            <input type="number" step="0.01" class="form-control" name="salary_amount" value="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary" name="add_worker">إضافة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal تعديل عامل -->
            <?php foreach ($workers as $worker): ?>
            <div class="modal fade" id="editWorkerModal<?= $worker['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">تعديل العامل</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="worker_id" value="<?= $worker['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">الاسم</label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($worker['name']) ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">الهاتف</label>
                                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($worker['phone']) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">العنوان</label>
                                    <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($worker['address']) ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">نوع الراتب</label>
                                            <select class="form-select" name="salary_type" required>
                                                <option value="daily" <?= $worker['salary_type'] == 'daily' ? 'selected' : '' ?>>يومي</option>
                                                <option value="weekly" <?= $worker['salary_type'] == 'weekly' ? 'selected' : '' ?>>أسبوعي</option>
                                                <option value="monthly" <?= $worker['salary_type'] == 'monthly' ? 'selected' : '' ?>>شهري</option>
                                                <option value="per_piece" <?= $worker['salary_type'] == 'per_piece' ? 'selected' : '' ?>>بالقطعة</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">مبلغ الراتب</label>
                                            <input type="number" step="0.01" class="form-control" name="salary_amount" value="<?= $worker['salary_amount'] ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" name="update_worker" class="btn btn-warning">تحديث</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Modal عرض معاملات العامل -->
            <div class="modal fade" id="viewWorkerModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">معاملات العامل: <span id="workerName"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">معلومات العامل</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="workerDetails"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">الملخص المالي</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="workerFinancial"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">المعاملات المالية</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>التاريخ</th>
                                                            <th>النوع</th>
                                                            <th>المبلغ</th>
                                                            <th>الوصف</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="workerTransactions"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function viewWorker(workerId) {
    fetch('get_worker_details.php?id=' + workerId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const worker = data.worker;
                
                document.getElementById('workerName').textContent = worker.name;
                
                document.getElementById('workerDetails').innerHTML = `
                    <p><strong>الاسم:</strong> ${worker.name}</p>
                    <p><strong>الهاتف:</strong> ${worker.phone || 'غير محدد'}</p>
                    <p><strong>العنوان:</strong> ${worker.address || 'غير محدد'}</p>
                    <p><strong>نوع الراتب:</strong> ${getSalaryTypeText(worker.salary_type)}</p>
                    <p><strong>مبلغ الراتب:</strong> ${parseFloat(worker.salary_amount).toLocaleString()} ج.م</p>
                `;
                
                document.getElementById('workerFinancial').innerHTML = `
                    <p><strong>إجمالي الرواتب:</strong> ${parseFloat(data.total_salaries || 0).toLocaleString()} ج.م</p>
                    <p><strong>إجمالي الحوافز:</strong> ${parseFloat(data.total_bonuses || 0).toLocaleString()} ج.م</p>
                    <p><strong>إجمالي الخصومات:</strong> ${parseFloat(data.total_deductions || 0).toLocaleString()} ج.م</p>
                    <p><strong>إجمالي السلف:</strong> ${parseFloat(data.total_advances || 0).toLocaleString()} ج.م</p>
                    <p><strong>صافي المستحقات:</strong> ${parseFloat(data.net_amount || 0).toLocaleString()} ج.م</p>
                `;
                
                let transactionsHtml = '';
                if (data.transactions && data.transactions.length > 0) {
                    data.transactions.forEach(transaction => {
                        transactionsHtml += `
                            <tr>
                                <td>${transaction.transaction_date}</td>
                                <td>${getTransactionTypeText(transaction.transaction_type)}</td>
                                <td>${parseFloat(transaction.amount).toLocaleString()} ج.م</td>
                                <td>${transaction.description || ''}</td>
                            </tr>
                        `;
                    });
                } else {
                    transactionsHtml = '<tr><td colspan="4" class="text-center">لا توجد معاملات</td></tr>';
                }
                document.getElementById('workerTransactions').innerHTML = transactionsHtml;
                
                new bootstrap.Modal(document.getElementById('viewWorkerModal')).show();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في جلب البيانات');
        });
}

function getTransactionTypeText(type) {
    const types = {
        'salary': 'راتب',
        'bonus': 'حافز',
        'deduction': 'خصم',
        'advance': 'سلفة',
        'overtime': 'إضافي',
        'piece_work': 'عمل بالقطعة'
    };
    return types[type] || type;
}

function getSalaryTypeText(type) {
    const types = {
        'daily': 'يومي',
        'weekly': 'أسبوعي',
        'monthly': 'شهري',
        'per_piece': 'بالقطعة'
    };
    return types[type] || type;
}

function confirmDeleteWorker(workerId, workerName) {
    if (confirm('هل أنت متأكد من حذف العامل: ' + workerName + '؟\nسيتم حذف جميع البيانات المرتبطة به.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_worker" value="1">
            <input type="hidden" name="worker_id" value="${workerId}">
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