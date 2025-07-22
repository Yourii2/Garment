<?php
require_once '../config/config.php';
checkLogin();

// التحقق من صلاحيات الإدارة
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// معالجة حفظ الإعدادات
if (isset($_POST['save_settings'])) {
    try {
        $settings = [
            'system_name' => $_POST['system_name'],
            'company_name' => $_POST['company_name'],
            'company_address' => $_POST['company_address'],
            'company_phone' => $_POST['company_phone'],
            'company_email' => $_POST['company_email'],
            'currency' => $_POST['currency'],
            'timezone' => $_POST['timezone'],
            'date_format' => $_POST['date_format'],
            'language' => $_POST['language'],
            'items_per_page' => $_POST['items_per_page'],
            'low_stock_threshold' => $_POST['low_stock_threshold'],
            'backup_frequency' => $_POST['backup_frequency'],
            'email_notifications' => isset($_POST['email_notifications']) ? '1' : '0',
            'sms_notifications' => isset($_POST['sms_notifications']) ? '1' : '0',
            'auto_backup' => isset($_POST['auto_backup']) ? '1' : '0',
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $_SESSION['success_message'] = 'تم حفظ الإعدادات بنجاح';
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ في حفظ الإعدادات: ' . $e->getMessage();
    }
    
    header('Location: settings.php');
    exit;
}

// معالجة إنشاء نسخة احتياطية
if (isset($_POST['create_backup'])) {
    try {
        $backup_dir = '../backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // تنفيذ أمر النسخ الاحتياطي
        $command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " > " . $backup_file;
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            $_SESSION['success_message'] = 'تم إنشاء النسخة الاحتياطية بنجاح';
        } else {
            $_SESSION['error_message'] = 'فشل في إنشاء النسخة الاحتياطية';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطأ في إنشاء النسخة الاحتياطية: ' . $e->getMessage();
    }
    
    header('Location: settings.php');
    exit;
}

// جلب الإعدادات الحالية
$current_settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $stmt->fetch()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// القيم الافتراضية
$defaults = [
    'system_name' => 'نظام إدارة مصنع الملابس',
    'company_name' => '',
    'company_address' => '',
    'company_phone' => '',
    'company_email' => '',
    'currency' => 'EGP',
    'timezone' => 'Africa/Cairo',
    'date_format' => 'Y-m-d',
    'language' => 'ar',
    'items_per_page' => '20',
    'low_stock_threshold' => '10',
    'backup_frequency' => 'daily',
    'email_notifications' => '1',
    'sms_notifications' => '0',
    'auto_backup' => '1',
    'maintenance_mode' => '0'
];

// دمج الإعدادات الحالية مع الافتراضية
$settings = array_merge($defaults, $current_settings);

// جلب قائمة النسخ الاحتياطية
$backup_files = [];
$backup_dir = '../backups/';
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => filemtime($backup_dir . $file)
            ];
        }
    }
    // ترتيب حسب التاريخ (الأحدث أولاً)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

$page_title = 'إعدادات النظام';
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
                    <i class="fas fa-cogs text-primary me-2"></i>
                    إعدادات النظام
                </h1>
            </div>

            <form method="POST">
                <!-- الإعدادات العامة -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>الإعدادات العامة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">اسم النظام</label>
                                    <input type="text" name="system_name" class="form-control" value="<?= htmlspecialchars($settings['system_name']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">اسم الشركة</label>
                                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name']) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">عنوان الشركة</label>
                                    <textarea name="company_address" class="form-control" rows="2"><?= htmlspecialchars($settings['company_address']) ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">هاتف الشركة</label>
                                    <input type="text" name="company_phone" class="form-control" value="<?= htmlspecialchars($settings['company_phone']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">بريد الشركة الإلكتروني</label>
                                    <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($settings['company_email']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- إعدادات النظام -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-sliders-h me-2"></i>إعدادات النظام</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">العملة</label>
                                    <select name="currency" class="form-control">
                                        <option value="EGP" <?= $settings['currency'] == 'EGP' ? 'selected' : '' ?>>جنيه مصري (EGP)</option>
                                        <option value="USD" <?= $settings['currency'] == 'USD' ? 'selected' : '' ?>>دولار أمريكي (USD)</option>
                                        <option value="EUR" <?= $settings['currency'] == 'EUR' ? 'selected' : '' ?>>يورو (EUR)</option>
                                        <option value="SAR" <?= $settings['currency'] == 'SAR' ? 'selected' : '' ?>>ريال سعودي (SAR)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">المنطقة الزمنية</label>
                                    <select name="timezone" class="form-control">
                                        <option value="Africa/Cairo" <?= $settings['timezone'] == 'Africa/Cairo' ? 'selected' : '' ?>>القاهرة</option>
                                        <option value="Asia/Riyadh" <?= $settings['timezone'] == 'Asia/Riyadh' ? 'selected' : '' ?>>الرياض</option>
                                        <option value="Asia/Dubai" <?= $settings['timezone'] == 'Asia/Dubai' ? 'selected' : '' ?>>دبي</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تنسيق التاريخ</label>
                                    <select name="date_format" class="form-control">
                                        <option value="Y-m-d" <?= $settings['date_format'] == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                        <option value="d/m/Y" <?= $settings['date_format'] == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        <option value="m/d/Y" <?= $settings['date_format'] == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">اللغة</label>
                                    <select name="language" class="form-control">
                                        <option value="ar" <?= $settings['language'] == 'ar' ? 'selected' : '' ?>>العربية</option>
                                        <option value="en" <?= $settings['language'] == 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">عدد العناصر في الصفحة</label>
                                    <select name="items_per_page" class="form-control">
                                        <option value="10" <?= $settings['items_per_page'] == '10' ? 'selected' : '' ?>>10</option>
                                        <option value="20" <?= $settings['items_per_page'] == '20' ? 'selected' : '' ?>>20</option>
                                        <option value="50" <?= $settings['items_per_page'] == '50' ? 'selected' : '' ?>>50</option>
                                        <option value="100" <?= $settings['items_per_page'] == '100' ? 'selected' : '' ?>>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">حد المخزون المنخفض</label>
                                    <input type="number" name="low_stock_threshold" class="form-control" value="<?= $settings['low_stock_threshold'] ?>" min="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- إعدادات النسخ الاحتياطي -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-database me-2"></i>إعدادات النسخ الاحتياطي</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تكرار النسخ الاحتياطي</label>
                                    <select name="backup_frequency" class="form-control">
                                        <option value="daily" <?= $settings['backup_frequency'] == 'daily' ? 'selected' : '' ?>>يومي</option>
                                        <option value="weekly" <?= $settings['backup_frequency'] == 'weekly' ? 'selected' : '' ?>>أسبوعي</option>
                                        <option value="monthly" <?= $settings['backup_frequency'] == 'monthly' ? 'selected' : '' ?>>شهري</option>
                                        <option value="manual" <?= $settings['backup_frequency'] == 'manual' ? 'selected' : '' ?>>يدوي فقط</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="auto_backup" id="auto_backup" <?= $settings['auto_backup'] == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="auto_backup">
                                            تفعيل النسخ الاحتياطي التلقائي
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- إعدادات الإشعارات -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-bell me-2"></i>إعدادات الإشعارات</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" <?= $settings['email_notifications'] == '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_notifications">
                                        إشعارات البريد الإلكتروني
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sms_notifications" id="sms_notifications" <?= $settings['sms_notifications'] == '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="sms_notifications">
                                        إشعارات الرسائل النصية
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- إعدادات الصيانة -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-tools me-2"></i>إعدادات الصيانة</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" <?= $settings['maintenance_mode'] == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenance_mode">
                                <strong>وضع الصيانة</strong> - سيمنع جميع المستخدمين من الوصول للنظام عدا المديرين
                            </label>
                        </div>
                        <?php if ($settings['maintenance_mode'] == '1'): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            النظام حالياً في وضع الصيانة!
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" name="save_settings" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ الإعدادات
                    </button>
                </div>
            </form>

            <!-- النسخ الاحتياطية -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-archive me-2"></i>النسخ الاحتياطية</h5>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="create_backup" class="btn btn-success btn-sm">
                            <i class="fas fa-plus me-2"></i>إنشاء نسخة احتياطية
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($backup_files)): ?>
                        <p class="text-muted">لا توجد نسخ احتياطية</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>اسم الملف</th>
                                        <th>الحجم</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backup_files as $backup): ?>
                                    <tr>
                                        <td><?= $backup['name'] ?></td>
                                        <td><?= number_format($backup['size'] / 1024, 2) ?> KB</td>
                                        <td><?= date('Y-m-d H:i:s', $backup['date']) ?></td>
                                        <td>
                                            <a href="../backups/<?= $backup['name'] ?>" class="btn btn-sm btn-outline-primary" download>
                                                <i class="fas fa-download"></i> تحميل
                                            </a>
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

<?php include '../includes/footer.php'; ?>

</body>
</html>

</body>
</html>