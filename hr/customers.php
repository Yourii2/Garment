<?php
require_once '../config/config.php';
checkLogin();

// معالجة إضافة عميل جديد
if (isset($_POST['add_customer'])) {
    try {
        $name = $_POST['name'];
        $company_name = $_POST['company_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $customer_type = $_POST['customer_type'] ?? 'individual';
        $credit_limit = $_POST['credit_limit'] ?? 0;
        
        $stmt = $pdo->prepare("INSERT INTO customers (name, company_name, phone, email, address, customer_type, credit_limit, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$name, $company_name, $phone, $email, $address, $customer_type, $credit_limit]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم إضافة العميل بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: customers.php');
    exit;
}

// معالجة تحديث عميل
if (isset($_POST['update_customer'])) {
    try {
        $id = $_POST['customer_id'];
        $name = $_POST['name'];
        $company_name = $_POST['company_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $customer_type = $_POST['customer_type'] ?? 'individual';
        $credit_limit = $_POST['credit_limit'] ?? 0;
        
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, company_name = ?, phone = ?, email = ?, address = ?, customer_type = ?, credit_limit = ? WHERE id = ?");
        $result = $stmt->execute([$name, $company_name, $phone, $email, $address, $customer_type, $credit_limit, $id]);
        
        if ($result) {
            $_SESSION['success_message'] = 'تم تحديث العميل بنجاح';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: customers.php');
    exit;
}

// معالجة حذف عميل
if (isset($_POST['delete_customer'])) {
    try {
        $id = $_POST['customer_id'];
        
        // التحقق من وجود طلبات مرتبطة (إذا كان الجدول موجود)
        $orders_count = 0;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
            $stmt->execute([$id]);
            $orders_count = $stmt->fetchColumn();
        } catch (Exception $e) {
            // جدول الطلبات غير موجود، يمكن المتابعة
            $orders_count = 0;
        }
        
        if ($orders_count > 0) {
            $_SESSION['error_message'] = 'لا يمكن حذف هذا العميل لوجود طلبات مرتبطة به';
        } else {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $_SESSION['success_message'] = 'تم حذف العميل بنجاح';
            } else {
                $_SESSION['error_message'] = 'فشل في حذف العميل';
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
    }
    
    header('Location: customers.php');
    exit;
}

// جلب العملاء
$stmt = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
$customers = $stmt->fetchAll();

$page_title = 'إدارة العملاء';
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
            <!-- عرض الرسائل -->
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
                    <i class="fas fa-user-friends text-primary me-2"></i>
                    إدارة العملاء
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="fas fa-plus me-2"></i>إضافة عميل جديد
                </button>
            </div>

            <!-- جدول العملاء -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>اسم الشركة</th>
                                    <th>الهاتف</th>
                                    <th>النوع</th>
                                    <th>الحد الائتماني</th>
                                    <th>الرصيد الحالي</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($customer['name']) ?></td>
                                        <td><?= htmlspecialchars($customer['company_name']) ?></td>
                                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                                        <td>
                                            <?php
                                            $types = [
                                                'individual' => 'فرد',
                                                'company' => 'شركة',
                                                'retailer' => 'تاجر تجزئة',
                                                'wholesaler' => 'تاجر جملة'
                                            ];
                                            echo $types[$customer['customer_type']] ?? $customer['customer_type'];
                                            ?>
                                        </td>
                                        <td><?= number_format($customer['credit_limit'], 2) ?> ج.م</td>
                                        <td><?= number_format($customer['current_balance'], 2) ?> ج.م</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewCustomer(<?= $customer['id'] ?>)" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCustomerModal<?= $customer['id'] ?>" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="confirmDeleteCustomer(<?= $customer['id'] ?>, '<?= addslashes($customer['name']) ?>')" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

<!-- Modal إضافة عميل -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة عميل جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم العميل *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم الشركة</label>
                                <input type="text" name="company_name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نوع العميل</label>
                                <select name="customer_type" class="form-select">
                                    <option value="individual">فرد</option>
                                    <option value="company">شركة</option>
                                    <option value="retailer">تاجر تجزئة</option>
                                    <option value="wholesaler">تاجر جملة</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الحد الائتماني</label>
                                <input type="number" name="credit_limit" class="form-control" step="0.01" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <textarea name="address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_customer" class="btn btn-primary">إضافة العميل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تعديل عميل -->
<?php foreach ($customers as $customer): ?>
<div class="modal fade" id="editCustomerModal<?= $customer['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل العميل: <?= htmlspecialchars($customer['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم العميل *</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($customer['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم الشركة</label>
                                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($customer['company_name']) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نوع العميل</label>
                                <select name="customer_type" class="form-select">
                                    <option value="individual" <?= $customer['customer_type'] == 'individual' ? 'selected' : '' ?>>فرد</option>
                                    <option value="company" <?= $customer['customer_type'] == 'company' ? 'selected' : '' ?>>شركة</option>
                                    <option value="retailer" <?= $customer['customer_type'] == 'retailer' ? 'selected' : '' ?>>تاجر تجزئة</option>
                                    <option value="wholesaler" <?= $customer['customer_type'] == 'wholesaler' ? 'selected' : '' ?>>تاجر جملة</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الحد الائتماني</label>
                                <input type="number" name="credit_limit" class="form-control" step="0.01" value="<?= $customer['credit_limit'] ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($customer['address']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="update_customer" class="btn btn-primary">تحديث العميل</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- نموذج حذف العميل -->
<form id="deleteCustomerForm" method="POST" style="display: none;">
    <input type="hidden" name="delete_customer" value="1">
    <input type="hidden" id="deleteCustomerId" name="customer_id">
</form>

<!-- Modal عرض تفاصيل العميل -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل العميل: <span id="customerName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">معلومات العميل</h6>
                            </div>
                            <div class="card-body">
                                <div id="customerDetails"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">الملخص المالي</h6>
                            </div>
                            <div class="card-body">
                                <div id="customerFinancial"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <ul class="nav nav-tabs" id="customerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
                                الطلبات
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab">
                                المعاملات المالية
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="customerTabsContent">
                        <div class="tab-pane fade show active" id="orders" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>رقم الطلب</th>
                                            <th>التاريخ</th>
                                            <th>المبلغ</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerOrders">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="transactions" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>التاريخ</th>
                                            <th>النوع</th>
                                            <th>المبلغ</th>
                                            <th>الوصف</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerTransactions">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteCustomer(id, name) {
    console.log('Delete customer called with ID:', id, 'Name:', name);
    
    if (confirm('هل أنت متأكد من حذف العميل: ' + name + '؟\nسيتم حذف جميع البيانات المرتبطة به نهائ<|im_start|>.')) {
        console.log('User confirmed deletion');
        
        // إنشاء نموذج ديناميكي
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'customers.php';
        form.innerHTML = '<input type="hidden" name="delete_customer" value="1">' +
                        '<input type="hidden" name="customer_id" value="' + id + '">';
        document.body.appendChild(form);
        
        console.log('Form created and submitting...');
        form.submit();
    }
}

function viewCustomer(customerId) {
    console.log('Loading customer details for ID:', customerId);
    
    fetch('get_customer_details.php?id=' + customerId)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                const customer = data.customer;
                
                document.getElementById('customerName').textContent = customer.name;
                
                document.getElementById('customerDetails').innerHTML = `
                    <p><strong>الاسم:</strong> ${customer.name}</p>
                    <p><strong>اسم الشركة:</strong> ${customer.company_name || 'غير محدد'}</p>
                    <p><strong>الهاتف:</strong> ${customer.phone || 'غير محدد'}</p>
                    <p><strong>البريد الإلكتروني:</strong> ${customer.email || 'غير محدد'}</p>
                    <p><strong>العنوان:</strong> ${customer.address || 'غير محدد'}</p>
                `;
                
                document.getElementById('customerFinancial').innerHTML = `
                    <p><strong>الحد الائتماني:</strong> ${parseFloat(customer.credit_limit || 0).toLocaleString()} ج.م</p>
                    <p><strong>الرصيد الحالي:</strong> ${parseFloat(customer.current_balance || 0).toLocaleString()} ج.م</p>
                    <p><strong>إجمالي المشتريات:</strong> ${parseFloat(data.total_purchases || 0).toLocaleString()} ج.م</p>
                    <p><strong>عدد الطلبات:</strong> ${data.orders_count || 0}</p>
                `;
                
                let ordersHtml = '';
                if (data.orders && data.orders.length > 0) {
                    data.orders.forEach(order => {
                        ordersHtml += `
                            <tr>
                                <td>#${order.id}</td>
                                <td>${order.order_date}</td>
                                <td>${parseFloat(order.total_amount).toLocaleString()} ج.م</td>
                                <td><span class="badge bg-info">${order.status}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    ordersHtml = '<tr><td colspan="4" class="text-center">لا توجد طلبات</td></tr>';
                }
                document.getElementById('customerOrders').innerHTML = ordersHtml;
                
                let transactionsHtml = '';
                if (data.transactions && data.transactions.length > 0) {
                    data.transactions.forEach(transaction => {
                        transactionsHtml += `
                            <tr>
                                <td>${transaction.transaction_date}</td>
                                <td>${transaction.type}</td>
                                <td>${parseFloat(transaction.amount).toLocaleString()} ج.م</td>
                                <td>${transaction.description || ''}</td>
                            </tr>
                        `;
                    });
                } else {
                    transactionsHtml = '<tr><td colspan="4" class="text-center">لا توجد معاملات</td></tr>';
                }
                document.getElementById('customerTransactions').innerHTML = transactionsHtml;
                
                new bootstrap.Modal(document.getElementById('viewCustomerModal')).show();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في جلب البيانات: ' + error.message);
        });
}
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>