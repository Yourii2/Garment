<?php
require_once '../config/config.php';
checkLogin();

$page_title = 'تقرير الموظفين';

// جلب بيانات الموظفين
$employees_data = [];

try {
    $stmt = $pdo->query("
        SELECT 
            e.id,
            e.name,
            e.email,
            e.phone,
            e.position,
            e.department,
            e.salary,
            e.hire_date,
            e.status,
            DATEDIFF(CURDATE(), e.hire_date) as days_employed
        FROM employees e
        ORDER BY e.department, e.name
    ");
    
    $employees_data = $stmt->fetchAll();
    
    // إحصائيات
    $total_employees = count($employees_data);
    $active_employees = count(array_filter($employees_data, function($emp) { return $emp['status'] === 'active'; }));
    $total_salaries = array_sum(array_column($employees_data, 'salary'));
    
} catch (Exception $e) {
    $error_message = 'خطأ في جلب بيانات الموظفين: ' . $e->getMessage();
}

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
                <h1 class="h2">تقرير الموظفين</h1>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>طباعة
                </button>
            </div>

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h4>إجمالي الموظفين</h4>
                            <h2><?= $total_employees ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h4>الموظفين النشطين</h4>
                            <h2><?= $active_employees ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h4>إجمالي الرواتب</h4>
                            <h2><?= number_format($total_salaries, 2) ?> ج.م</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول الموظفين -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الهاتف</th>
                                    <th>المنصب</th>
                                    <th>القسم</th>
                                    <th>الراتب</th>
                                    <th>تاريخ التوظيف</th>
                                    <th>مدة الخدمة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees_data as $employee): ?>
                                <tr>
                                    <td><?= $employee['name'] ?></td>
                                    <td><?= $employee['email'] ?></td>
                                    <td><?= $employee['phone'] ?></td>
                                    <td><?= $employee['position'] ?></td>
                                    <td><?= $employee['department'] ?></td>
                                    <td><?= number_format($employee['salary'], 2) ?> ج.م</td>
                                    <td><?= $employee['hire_date'] ?></td>
                                    <td><?= floor($employee['days_employed'] / 365) ?> سنة <?= $employee['days_employed'] % 365 ?> يوم</td>
                                    <td>
                                        <span class="badge bg-<?= $employee['status'] === 'active' ? 'success' : 'danger' ?>">
                                            <?= $employee['status'] ?>
                                        </span>
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

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>