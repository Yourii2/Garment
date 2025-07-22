<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
checkLogin();

// معالجة إضافة طلبية جديدة
if (isset($_POST['add_order'])) {
    try {
        $pdo->beginTransaction();
        
        $customer_name = $_POST['customer_name'];
        $customer_phone = $_POST['customer_phone'];
        $customer_address = $_POST['customer_address'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // التحقق من وجود العميل أولاً
        $customer_id = null;
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->execute([$customer_phone]);
        $existing_customer = $stmt->fetch();
        
        if ($existing_customer) {
            $customer_id = $existing_customer['id'];
            echo "العميل موجود مسبقً - ID: $customer_id<br>";
        } else {
            // إنشاء عميل جديد
            $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$customer_name, $customer_phone, $customer_address]);
            $customer_id = $pdo->lastInsertId();
            echo "تم إنشاء عميل جديد - ID: $customer_id<br>";
        }
        
        // توليد رقم الطلبية
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM orders");
        $result = $stmt->fetch();
        $newId = ($result['max_id'] ?? 0) + 1;
        $order_number = 'ORD' . date('Y') . date('m') . str_pad($newId, 4, '0', STR_PAD_LEFT);
        
        echo "Order number: $order_number<br>";
        
        // إدراج الطلبية الرئيسية
        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (order_number, customer_id, customer_name, customer_phone, customer_address, 
             status, notes, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())
        ");
        
        $result = $stmt->execute([
            $order_number, $customer_id, $customer_name, $customer_phone, 
            $customer_address, $notes
        ]);
        
        echo "Insert result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
        
        $order_id = $pdo->lastInsertId();
        echo "Order ID: $order_id<br>";
        
        // إدراج منتجات الطلبية
        if (!empty($_POST['products'])) {
            foreach ($_POST['products'] as $index => $product) {
                if (!empty($product['product_id']) && !empty($product['quantity'])) {
                    // التحقق من وجود المنتج
                    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
                    $stmt->execute([$product['product_id']]);
                    $product_info = $stmt->fetch();
                    
                    if ($product_info) {
                        $stmt = $pdo->prepare("
                            INSERT INTO order_items 
                            (order_id, product_id, quantity, notes) 
                            VALUES (?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $order_id, 
                            $product['product_id'], 
                            $product['quantity'],
                            $product['notes'] ?? ''
                        ]);
                    }
                }
            }
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = "تم إنشاء الطلبية رقم {$order_number} بنجاح";
        
        // إعادة توجيه مباشرة لصفحة الطلبيات
        header('Location: orders.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ في إنشاء الطلبية: ' . $e->getMessage();
        header('Location: orders.php');
        exit;
    }
}

// التحقق من وجود الجداول وإنشاؤها
try {
    // إنشاء جدول customers أولاً
    $stmt = $pdo->query("SHOW TABLES LIKE 'customers'");
    if ($stmt->rowCount() == 0) {
        echo "Creating customers table...<br>";
        $pdo->exec("
            CREATE TABLE customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(20) UNIQUE,
                email VARCHAR(100),
                address TEXT,
                customer_type ENUM('individual', 'company') DEFAULT 'individual',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "Customers table created successfully<br>";
    }
    
    // إنشاء جدول products
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() == 0) {
        echo "Creating products table...<br>";
        $pdo->exec("
            CREATE TABLE products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                price DECIMAL(10,2) DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // إدراج منتجات تجريبية
        $pdo->exec("
            INSERT INTO products (name, code, description, price) VALUES
            ('قميص رجالي', 'SHIRT-M-001', 'قميص رجالي قطني', 150.00),
            ('بنطلون جينز', 'JEANS-001', 'بنطلون جينز كلاسيكي', 200.00),
            ('فستان نسائي', 'DRESS-W-001', 'فستان نسائي أنيق', 300.00),
            ('تي شيرت', 'TSHIRT-001', 'تي شيرت قطني', 80.00),
            ('جاكيت شتوي', 'JACKET-001', 'جاكيت شتوي دافئ', 400.00)
        ");
        echo "Products table created and sample data inserted<br>";
    }
    
    // إنشاء جدول orders
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        echo "Creating orders table...<br>";
        $pdo->exec("
            CREATE TABLE orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(50) UNIQUE NOT NULL,
                customer_id INT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(20) NOT NULL,
                customer_address TEXT,
                status ENUM('pending', 'ready', 'in_production', 'completed', 'cancelled') DEFAULT 'pending',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
            )
        ");
        echo "Orders table created successfully<br>";
    }
    
    // إنشاء جدول order_items
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() == 0) {
        echo "Creating order_items table...<br>";
        $pdo->exec("
            CREATE TABLE order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        ");
        echo "Order_items table created successfully<br>";
    }
    
} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage() . "<br>";
}

// جلب الطلبيات مع المنتجات
try {
    $stmt = $pdo->query("
        SELECT o.*, 
               COUNT(oi.id) as products_count,
               SUM(oi.quantity) as total_quantity
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Error fetching orders: " . $e->getMessage() . "<br>";
    $orders = [];
}

// جلب المنتجات
try {
    $stmt = $pdo->query("SELECT id, name, code FROM products WHERE is_active = 1 ORDER BY name ASC");
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Error fetching products: " . $e->getMessage() . "<br>";
    $products = [];
}

$page_title = 'إدارة الطلبيات';
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
            <!-- عرض معلومات التشخيص -->
            <div class="alert alert-info">
                <strong>معلومات التشخيص:</strong><br>
                عدد الطلبيات: <?= count($orders) ?><br>
                عدد المنتجات: <?= count($products) ?><br>
                <?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
                    echo "عدد السجلات في جدول orders: " . $stmt->fetchColumn() . "<br>";
                    
                    $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
                    echo "عدد العملاء: " . $stmt->fetchColumn() . "<br>";
                } catch (Exception $e) {
                    echo "خطأ في قراءة الجداول: " . $e->getMessage() . "<br>";
                }
                ?>
            </div>

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
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-list-alt me-2"></i><?= $page_title ?>
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                    <i class="fas fa-plus me-2"></i>طلبية جديدة
                </button>
            </div>

            <!-- جدول الطلبيات -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                            <h5>لا توجد طلبيات حتى الآن</h5>
                            <p class="text-muted">ابدأ بإضافة طلبية جديدة</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>رقم الطلبية</th>
                                        <th>اسم العميل</th>
                                        <th>الهاتف</th>
                                        <th>عدد المنتجات</th>
                                        <th>إجمالي الكمية</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الطلب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                                            <td><?= $order['products_count'] ?> منتج</td>
                                            <td><?= number_format($order['total_quantity'] ?? 0) ?></td>
                                            <td>
                                                <span class="badge bg-warning">منتظرة</span>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="viewOrderDetails(<?= $order['id'] ?>)" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal إضافة طلبية جديدة -->
<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة طلبية جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <!-- بيانات العميل -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>بيانات العميل</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">اسم العميل *</label>
                                        <input type="text" name="customer_name" id="customer_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">رقم الهاتف *</label>
                                        <input type="text" name="customer_phone" id="customer_phone" class="form-control" required onblur="checkCustomer()">
                                        <div id="customer_status" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">العنوان</label>
                                <textarea name="customer_address" id="customer_address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- منتجات الطلبية -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-box me-2"></i>منتجات الطلبية</h6>
                            <button type="button" class="btn btn-success btn-sm" onclick="addProductItem()">
                                <i class="fas fa-plus me-1"></i>إضافة منتج
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="products-container">
                                <!-- سيتم إضافة المنتجات هنا -->
                            </div>
                            
                            <div class="alert alert-info mt-3" id="no-products-message">
                                <i class="fas fa-info-circle me-2"></i>
                                لم يتم إضافة أي منتجات بعد. اضغط "إضافة منتج" لبدء إنشاء الطلبية.
                            </div>
                        </div>
                    </div>

                    <!-- ملاحظات عامة -->
                    <div class="mb-3">
                        <label class="form-label">ملاحظات عامة</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="ملاحظات خاصة بالطلبية..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_order" class="btn btn-primary">إنشاء الطلبية</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let productCounter = 0;

// التحقق من وجود العميل
function checkCustomer() {
    const phone = document.getElementById('customer_phone').value;
    const statusDiv = document.getElementById('customer_status');
    
    if (phone.length > 0) {
        fetch('../hr/check_customer.php?phone=' + encodeURIComponent(phone))
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    statusDiv.innerHTML = '<div class="alert alert-success py-2"><i class="fas fa-check-circle me-2"></i>عميل موجود: ' + data.customer.name + '</div>';
                    document.getElementById('customer_name').value = data.customer.name;
                    document.getElementById('customer_address').value = data.customer.address || '';
                } else {
                    statusDiv.innerHTML = '<div class="alert alert-info py-2"><i class="fas fa-info-circle me-2"></i>عميل جديد - سيتم إنشاء حساب جديد</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusDiv.innerHTML = '<div class="alert alert-warning py-2"><i class="fas fa-exclamation-triangle me-2"></i>تعذر التحقق من العميل</div>';
            });
    } else {
        statusDiv.innerHTML = '';
    }
}

function addProductItem() {
    productCounter++;
    const productHtml = `
        <div class="product-item border rounded p-3 mb-3" id="product-${productCounter}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">منتج #${productCounter}</h6>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeProductItem(${productCounter})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">المنتج *</label>
                        <select name="products[${productCounter}][product_id]" class="form-select" required>
                            <option value="">اختر المنتج</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>">
                                    <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">الكمية *</label>
                        <input type="number" name="products[${productCounter}][quantity]" class="form-control" min="1" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">ملاحظات خاصة بالمنتج</label>
                <textarea name="products[${productCounter}][notes]" class="form-control" rows="2"></textarea>
            </div>
        </div>
    `;
    
    document.getElementById('products-container').insertAdjacentHTML('beforeend', productHtml);
    document.getElementById('no-products-message').style.display = 'none';
}

function removeProductItem(itemId) {
    document.getElementById(`product-${itemId}`).remove();
    
    const container = document.getElementById('products-container');
    if (container.children.length === 0) {
        document.getElementById('no-products-message').style.display = 'block';
    }
}

document.getElementById('addOrderModal').addEventListener('shown.bs.modal', function () {
    if (document.getElementById('products-container').children.length === 0) {
        addProductItem();
    }
});

function viewOrderDetails(orderId) {
    window.location.href = `order_details.php?id=${orderId}`;
}
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>