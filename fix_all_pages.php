<?php
/**
 * سكربت إصلاح جميع صفحات PHP في المشروع
 * يقوم بتحديث جميع الملفات لتحتوي على HTML صحيح مع اتجاه عربي
 */

set_time_limit(300); // 5 دقائق
ini_set('memory_limit', '256M');

$fixed_files = [];
$errors = [];
$skipped_files = [];

// المجلدات المراد فحصها
$directories = [
    '.',
    'production',
    'inventory', 
    'financial',
    'hr',
    'reports',
    'admin'
];

// الملفات المستثناة من التعديل
$excluded_files = [
    'config.php',
    'database.php',
    'functions.php',
    'permissions.php',
    'fix_all_pages.php',
    'install.php',
    'setup.php'
];

// إذا كان طلب AJAX لحساب الملفات
if (isset($_GET['count_files'])) {
    $total_files = 0;
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $files = scanDirectory($dir);
            $total_files += count($files);
        }
    }
    echo json_encode(['total' => $total_files]);
    exit;
}

// إذا كان طلب AJAX لمعالجة ملف واحد
if (isset($_POST['process_file'])) {
    $filepath = $_POST['filepath'];
    $result = fixPHPFile($filepath);
    echo json_encode($result);
    exit;
}

function fixPHPFile($filepath) {
    global $excluded_files;
    
    $filename = basename($filepath);
    
    // تجاهل الملفات المستثناة
    if (in_array($filename, $excluded_files)) {
        return [
            'status' => 'skipped',
            'message' => 'مستثنى من التعديل',
            'file' => $filepath
        ];
    }
    
    // قراءة محتوى الملف
    $content = file_get_contents($filepath);
    if ($content === false) {
        return [
            'status' => 'error',
            'message' => 'فشل في قراءة الملف',
            'file' => $filepath
        ];
    }
    
    // تحسين فحص HTML - البحث عن أي من هذه العلامات
    $hasHTML = (
        preg_match('/<html/i', $content) ||
        preg_match('/<!DOCTYPE/i', $content) ||
        preg_match('/<head>/i', $content) ||
        preg_match('/<body>/i', $content) ||
        preg_match('/<title>/i', $content) ||
        preg_match('/<meta/i', $content) ||
        preg_match('/<link.*stylesheet/i', $content) ||
        preg_match('/<script/i', $content) ||
        preg_match('/bootstrap.*css/i', $content) ||
        preg_match('/container-fluid/i', $content) ||
        preg_match('/include.*navbar/i', $content) ||
        preg_match('/include.*sidebar/i', $content)
    );
    
    if (!$hasHTML) {
        return [
            'status' => 'skipped',
            'message' => 'لا يحتوي على HTML',
            'file' => $filepath
        ];
    }
    
    // التحقق إذا كان الملف يحتوي بالفعل على lang="ar" dir="rtl"
    if (preg_match('/<html[^>]*lang="ar"[^>]*dir="rtl"/i', $content)) {
        return [
            'status' => 'skipped',
            'message' => 'محدث بالفعل',
            'file' => $filepath
        ];
    }
    
    $original_content = $content;
    $modified = false;
    $changes = [];
    
    // إصلاح DOCTYPE و HTML tag
    if (preg_match('/<!DOCTYPE html>\s*<html[^>]*>/i', $content)) {
        $content = preg_replace(
            '/<!DOCTYPE html>\s*<html[^>]*>/i',
            '<!DOCTYPE html>' . "\n" . '<html lang="ar" dir="rtl">',
            $content
        );
        $modified = true;
        $changes[] = 'إضافة lang="ar" dir="rtl"';
    } elseif (preg_match('/<html[^>]*>/i', $content)) {
        $content = preg_replace(
            '/<html[^>]*>/i',
            '<html lang="ar" dir="rtl">',
            $content
        );
        $modified = true;
        $changes[] = 'إضافة lang="ar" dir="rtl"';
    } elseif (preg_match('/<head>/i', $content) && !preg_match('/<html/i', $content)) {
        // إضافة HTML tag إذا كان يحتوي على head بدون html
        $content = preg_replace(
            '/(<head>)/i',
            '<!DOCTYPE html>' . "\n" . '<html lang="ar" dir="rtl">' . "\n" . '$1',
            $content
        );
        // إضافة إغلاق html في النهاية
        if (!preg_match('/<\/html>/i', $content)) {
            $content .= "\n</html>";
        }
        $modified = true;
        $changes[] = 'إضافة HTML كامل مع lang="ar" dir="rtl"';
    }
    
    // إصلاح Bootstrap CSS links لاستخدام RTL
    if (preg_match('/bootstrap@5\.[0-9]+\.[0-9]+\/dist\/css\/bootstrap\.min\.css/i', $content)) {
        $content = preg_replace(
            '/bootstrap@5\.([0-9]+)\.([0-9]+)\/dist\/css\/bootstrap\.min\.css/i',
            'bootstrap@5.$1.$2/dist/css/bootstrap.rtl.min.css',
            $content
        );
        $modified = true;
        $changes[] = 'تحديث Bootstrap RTL';
    }
    
    // إصلاح Bootstrap 5.3.0
    if (preg_match('/bootstrap@5\.3\.0\/dist\/css\/bootstrap\.min\.css/i', $content)) {
        $content = str_replace(
            'bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css',
            $content
        );
        $modified = true;
        $changes[] = 'تحديث Bootstrap 5.3.0 RTL';
    }
    
    // إصلاح مسارات CSS
    $relative_path = dirname($filepath) === '.' ? '' : '../';
    if (strpos($content, 'assets/css/style.css') !== false && $relative_path) {
        $content = str_replace('assets/css/style.css', '../assets/css/style.css', $content);
        $modified = true;
        $changes[] = 'إصلاح مسار CSS';
    }
    
    // إصلاح include paths للـ sidebar
    if (preg_match('/include\s+[\'"]includes\/sidebar\.php[\'"]/', $content)) {
        if (dirname($filepath) !== '.') {
            $content = preg_replace(
                '/include\s+[\'"]includes\/sidebar\.php[\'"]/',
                "include '../includes/sidebar.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار sidebar';
        }
    }
    
    // إصلاح include paths للـ navbar
    if (preg_match('/include\s+[\'"]includes\/navbar\.php[\'"]/', $content)) {
        if (dirname($filepath) !== '.') {
            $content = preg_replace(
                '/include\s+[\'"]includes\/navbar\.php[\'"]/',
                "include '../includes/navbar.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار navbar';
        }
    }
    
    // إصلاح مسارات config
    if (preg_match('/require_once\s+[\'"]config\/config\.php[\'"]/', $content)) {
        if (dirname($filepath) !== '.') {
            $content = preg_replace(
                '/require_once\s+[\'"]config\/config\.php[\'"]/',
                "require_once '../config/config.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار config';
        }
    }
    
    // إضافة BASE_URL للمسارات إذا لم تكن موجودة
    if (preg_match('/href="assets\/css\/style\.css"/', $content)) {
        $content = str_replace(
            'href="assets/css/style.css"',
            'href="<?= BASE_URL ?>/assets/css/style.css"',
            $content
        );
        $modified = true;
        $changes[] = 'إضافة BASE_URL للـ CSS';
    }
    
    // التأكد من وجود القائمة الجانبية في المكان الصحيح
    if (preg_match('/<div class="container-fluid">\s*<div class="row">/i', $content)) {
        if (!preg_match('/include.*sidebar\.php/i', $content)) {
            $sidebar_include = dirname($filepath) === '.' ? 
                "            <?php include 'includes/sidebar.php'; ?>" : 
                "            <?php include '../includes/sidebar.php'; ?>";
            
            $content = preg_replace(
                '/(<div class="container-fluid">\s*<div class="row">)/i',
                '$1' . "\n" . $sidebar_include,
                $content
            );
            $modified = true;
            $changes[] = 'إضافة القائمة الجانبية';
        }
    }
    
    // حفظ الملف إذا تم تعديله
    if ($modified) {
        $backup_file = $filepath . '.backup.' . date('Y-m-d-H-i-s');
        
        // إنشاء نسخة احتياطية
        if (copy($filepath, $backup_file)) {
            // حفظ الملف المحدث
            if (file_put_contents($filepath, $content) !== false) {
                return [
                    'status' => 'fixed',
                    'message' => 'تم الإصلاح: ' . implode(', ', $changes),
                    'file' => $filepath,
                    'backup' => $backup_file
                ];
            } else {
                // استعادة النسخة الاحتياطية
                copy($backup_file, $filepath);
                return [
                    'status' => 'error',
                    'message' => 'فشل في حفظ الملف',
                    'file' => $filepath
                ];
            }
        } else {
            return [
                'status' => 'error',
                'message' => 'فشل في إنشاء نسخة احتياطية',
                'file' => $filepath
            ];
        }
    } else {
        return [
            'status' => 'skipped',
            'message' => 'لا يحتاج تعديل',
            'file' => $filepath
        ];
    }
}

// البحث عن جميع ملفات PHP
function scanDirectory($dir) {
    $files = [];
    
    if (!is_dir($dir)) {
        return $files;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// جمع جميع الملفات للمعالجة
if (isset($_GET['get_files'])) {
    $all_files = [];
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $files = scanDirectory($dir);
            $all_files = array_merge($all_files, $files);
        }
    }
    echo json_encode($all_files);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إصلاح جميع صفحات المشروع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .results { margin-top: 20px; }
        .results ul { max-height: 300px; overflow-y: auto; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; }
        .progress-container { margin: 20px 0; }
        .file-log { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 5px; 
            padding: 15px; 
            max-height: 400px; 
            overflow-y: auto; 
            font-family: monospace; 
            font-size: 12px;
            margin-top: 15px;
        }
        .log-entry { 
            margin: 2px 0; 
            padding: 2px 5px; 
            border-radius: 3px;
        }
        .log-fixed { background: #d4edda; color: #155724; }
        .log-skipped { background: #fff3cd; color: #856404; }
        .log-error { background: #f8d7da; color: #721c24; }
        .log-processing { background: #cce7ff; color: #004085; }
        .stats-card { 
            background: white; 
            border: 1px solid #dee2e6; 
            border-radius: 8px; 
            padding: 15px; 
            margin: 10px 0; 
            text-align: center;
        }
        .stats-number { font-size: 2em; font-weight: bold; margin: 10px 0; }
        .btn-process { font-size: 18px; padding: 12px 30px; }
        #processSection { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tools"></i> إصلاح جميع صفحات المشروع</h1>
        
        <div class="warning">
            <h4><i class="fas fa-exclamation-triangle"></i> تحذير مهم!</h4>
            <p>هذا السكربت سيقوم بتعديل جميع ملفات PHP في المشروع. سيتم إنشاء نسخ احتياطية تلقائ.</p>
            <ul>
                <li>سيتم إضافة <code>&lt;html lang="ar" dir="rtl"&gt;</code> لجميع الصفحات</li>
                <li>سيتم تحديث روابط Bootstrap لاستخدام النسخة العربية (RTL)</li>
                <li>سيتم إصلاح مسارات الملفات المضمنة</li>
                <li>سيتم التأكد من وجود القائمة الجانبية في المكان الصحيح</li>
            </ul>
        </div>
        
        <div class="info">
            <h4><i class="fas fa-info-circle"></i> المجلدات التي سيتم فحصها:</h4>
            <ul>
                <?php foreach ($directories as $dir): ?>
                    <li><code><?= $dir ?></code> <?= is_dir($dir) ? '✅' : '❌ غير موجود' ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="info">
            <h4><i class="fas fa-file-code"></i> الملفات المستثناة:</h4>
            <ul>
                <?php foreach ($excluded_files as $file): ?>
                    <li><code><?= $file ?></code></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- قسم البدء -->
        <div id="startSection">
            <div class="d-grid gap-2">
                <button type="button" id="startBtn" class="btn btn-primary btn-process">
                    <i class="fas fa-search me-2"></i>فحص الملفات وبدء الإصلاح
                </button>
            </div>
        </div>
        
        <!-- قسم المعالجة -->
        <div id="processSection">
            <!-- الإحصائيات -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted">إجمالي الملفات</div>
                        <div class="stats-number text-primary" id="totalFiles">0</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted">تم الإصلاح</div>
                        <div class="stats-number text-success" id="fixedFiles">0</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted">متجاهل</div>
                        <div class="stats-number text-warning" id="skippedFiles">0</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted">أخطاء</div>
                        <div class="stats-number text-danger" id="errorFiles">0</div>
                    </div>
                </div>
            </div>
            
            <!-- شريط التقدم -->
            <div class="progress-container">
                <div class="d-flex justify-content-between mb-2">
                    <span>التقدم:</span>
                    <span id="progressText">0%</span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="progressBar" style="width: 0%">
                        <span id="progressPercent">0%</span>
                    </div>
                </div>
            </div>
            
            <!-- الملف الحالي -->
            <div class="alert alert-info" id="currentFile" style="display: none;">
                <i class="fas fa-cog fa-spin me-2"></i>
                <span id="currentFileName">جاري المعالجة...</span>
            </div>
            
            <!-- سجل الملفات -->
            <div class="file-log" id="fileLog">
                <div class="text-muted text-center">سيظهر هنا سجل معالجة الملفات...</div>
            </div>
            
            <!-- أزرار التحكم -->
            <div class="d-grid gap-2 mt-3" id="controlButtons" style="display: none;">
                <a href="fix_all_pages.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> تشغيل مرة أخرى
                </a>
                <a href="dashboard.php" class="btn btn-success">
                    <i class="fas fa-home"></i> العودة للوحة التحكم
                </a>
            </div>
        </div>
        
        <div class="mt-4">
            <h4><i class="fas fa-code"></i> معاينة التعديلات:</h4>
            <pre>
قبل التعديل:
&lt;!DOCTYPE html&gt;
&lt;html&gt;

بعد التعديل:
&lt;!DOCTYPE html&gt;
&lt;html lang="ar" dir="rtl"&gt;

---

قبل التعديل:
bootstrap@5.1.3/dist/css/bootstrap.min.css

بعد التعديل:
bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css
            </pre>
        </div>
    </div>
    
    <script>
        let totalFiles = 0;
        let processedFiles = 0;
        let fixedCount = 0;
        let skippedCount = 0;
        let errorCount = 0;
        let allFiles = [];
        
        document.getElementById('startBtn').addEventListener('click', function() {
            if (confirm('هل أنت متأكد من تشغيل السكربت؟ سيتم تعديل جميع ملفات PHP في المشروع.')) {
                startProcessing();
            }
        });
        
        async function startProcessing() {
            // إخفاء قسم البدء وإظهار قسم المعالجة
            document.getElementById('startSection').style.display = 'none';
            document.getElementById('processSection').style.display = 'block';
            
            // جلب قائمة الملفات
            try {
                const response = await fetch('fix_all_pages.php?get_files=1');
                allFiles = await response.json();
                totalFiles = allFiles.length;
                
                document.getElementById('totalFiles').textContent = totalFiles;
                
                if (totalFiles === 0) {
                    addLogEntry('لا توجد ملفات PHP للمعالجة', 'error');
                    return;
                }
                
                addLogEntry(`تم العثور على ${totalFiles} ملف PHP`, 'processing');
                
                // بدء معالجة الملفات
                for (let i = 0; i < allFiles.length; i++) {
                    await processFile(allFiles[i], i + 1);
                }
                
                // انتهاء المعالجة
                finishProcessing();
                
            } catch (error) {
                addLogEntry('خطأ في جلب قائمة الملفات: ' + error.message, 'error');
            }
        }
        
        async function processFile(filepath, index) {
            // تحديث الملف الحالي
            document.getElementById('currentFile').style.display = 'block';
            document.getElementById('currentFileName').textContent = `معالجة: ${filepath}`;
            
            try {
                const formData = new FormData();
                formData.append('process_file', '1');
                formData.append('filepath', filepath);
                
                const response = await fetch('fix_all_pages.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // تحديث الإحصائيات
                processedFiles++;
                
                if (result.status === 'fixed') {
                    fixedCount++;
                    addLogEntry(`✅ ${filepath} - ${result.message}`, 'fixed');
                } else if (result.status === 'skipped') {
                    skippedCount++;
                    addLogEntry(`⏭️ ${filepath} - ${result.message}`, 'skipped');
                } else if (result.status === 'error') {
                    errorCount++;
                    addLogEntry(`❌ ${filepath} - ${result.message}`, 'error');
                }
                
                // تحديث شريط التقدم
                updateProgress();
                
                // تأخير قصير لإظهار التقدم
                await new Promise(resolve => setTimeout(resolve, 100));
                
            } catch (error) {
                errorCount++;
                addLogEntry(`❌ ${filepath} - خطأ في المعالجة: ${error.message}`, 'error');
                updateProgress();
            }
        }
        
        function updateProgress() {
            const percentage = Math.round((processedFiles / totalFiles) * 100);
            
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressPercent').textContent = percentage + '%';
            document.getElementById('progressText').textContent = `${processedFiles}/${totalFiles} (${percentage}%)`;
            
            document.getElementById('fixedFiles').textContent = fixedCount;
            document.getElementById('skippedFiles').textContent = skippedCount;
            document.getElementById('errorFiles').textContent = errorCount;
        }
        
        function addLogEntry(message, type) {
            const logDiv = document.getElementById('fileLog');
            const entry = document.createElement('div');
            entry.className = `log-entry log-${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            
            logDiv.appendChild(entry);
            logDiv.scrollTop = logDiv.scrollHeight;
        }
        
        function finishProcessing() {
            document.getElementById('currentFile').style.display = 'none';
            document.getElementById('controlButtons').style.display = 'block';
            
            // تحديث شريط التقدم للانتهاء
            document.getElementById('progressBar').classList.remove('progress-bar-animated');
            document.getElementById('progressBar').classList.add('bg-success');
            
            addLogEntry(`🎉 انتهت المعالجة! إجمالي: ${totalFiles} | مصلح: ${fixedCount} | متجاهل: ${skippedCount} | أخطاء: ${errorCount}`, 'fixed');
        }
    </script>
</body>
</html>

