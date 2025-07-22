<?php
/**
 * سكربت إصلاح جميع صفحات PHP - النسخة الثالثة المحسنة
 * يتعامل مع الملفات التي تستخدم include للـ header
 */

set_time_limit(300);
ini_set('memory_limit', '256M');

// إذا كان طلب AJAX لمعالجة ملف واحد
if (isset($_POST['process_file'])) {
    $filepath = $_POST['filepath'];
    $result = fixPHPFileV3($filepath);
    echo json_encode($result);
    exit;
}

// إذا كان طلب AJAX لجلب قائمة الملفات
if (isset($_GET['get_files'])) {
    $directories = ['.', 'production', 'inventory', 'financial', 'hr', 'reports', 'admin', 'warehouse', 'sales'];
    $excluded_files = ['config.php', 'database.php', 'functions.php', 'permissions.php', 'fix_all_pages.php', 'fix_all_pages_v2.php', 'fix_all_pages_v3.php', 'debug_files.php', 'install.php', 'setup.php'];
    
    $all_files = [];
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $files = scanDirectory($dir);
            foreach ($files as $file) {
                if (!in_array(basename($file), $excluded_files)) {
                    $all_files[] = $file;
                }
            }
        }
    }
    echo json_encode($all_files);
    exit;
}

function fixPHPFileV3($filepath) {
    $filename = basename($filepath);
    
    // قراءة محتوى الملف
    $content = file_get_contents($filepath);
    if ($content === false) {
        return [
            'status' => 'error',
            'message' => 'فشل في قراءة الملف',
            'file' => $filepath
        ];
    }
    
    // فحص شامل للـ HTML أو Bootstrap أو UI elements
    $hasUIElements = (
        preg_match('/<html/i', $content) ||
        preg_match('/<!DOCTYPE/i', $content) ||
        preg_match('/<head>/i', $content) ||
        preg_match('/<body>/i', $content) ||
        preg_match('/<title>/i', $content) ||
        preg_match('/<meta/i', $content) ||
        preg_match('/<link.*stylesheet/i', $content) ||
        preg_match('/<script/i', $content) ||
        preg_match('/bootstrap/i', $content) ||
        preg_match('/container-fluid/i', $content) ||
        preg_match('/include.*navbar/i', $content) ||
        preg_match('/include.*sidebar/i', $content) ||
        preg_match('/include.*header/i', $content) ||
        preg_match('/include.*footer/i', $content) ||
        preg_match('/<div class="card"/i', $content) ||
        preg_match('/<table class="table"/i', $content) ||
        preg_match('/btn btn-/i', $content) ||
        preg_match('/class=".*col-/i', $content) ||
        preg_match('/class=".*row/i', $content) ||
        preg_match('/class=".*form-/i', $content) ||
        preg_match('/class=".*alert/i', $content) ||
        preg_match('/class=".*badge/i', $content) ||
        preg_match('/class=".*modal/i', $content) ||
        preg_match('/fa-[a-z-]+/i', $content) ||
        preg_match('/fas fa-/i', $content) ||
        preg_match('/far fa-/i', $content) ||
        preg_match('/fab fa-/i', $content)
    );
    
    if (!$hasUIElements) {
        return [
            'status' => 'skipped',
            'message' => 'لا يحتوي على عناصر UI',
            'file' => $filepath
        ];
    }
    
    $original_content = $content;
    $modified = false;
    $changes = [];
    
    // 1. إصلاح HTML tag إذا كان موجود
    if (preg_match('/<html[^>]*>/i', $content, $matches)) {
        $current_html = $matches[0];
        
        // التحقق إذا كان يحتوي بالفعل على lang="ar" dir="rtl"
        if (!preg_match('/lang="ar"/i', $current_html) || !preg_match('/dir="rtl"/i', $current_html)) {
            $new_html = '<html lang="ar" dir="rtl">';
            $content = str_replace($current_html, $new_html, $content);
            $modified = true;
            $changes[] = 'تحديث HTML tag';
        }
    }
    
    // 2. إصلاح Bootstrap CSS - جميع الإصدارات
    $bootstrap_patterns = [
        '/bootstrap@5\.([0-9]+)\.([0-9]+)\/dist\/css\/bootstrap\.min\.css/i',
        '/bootstrap@([0-9]+)\.([0-9]+)\.([0-9]+)\/dist\/css\/bootstrap\.min\.css/i',
        '/bootstrap\.min\.css/i',
        '/bootstrap\.css/i'
    ];
    
    foreach ($bootstrap_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            if (strpos($content, 'bootstrap.rtl.min.css') === false && strpos($content, 'bootstrap.rtl.css') === false) {
                $content = preg_replace($pattern, 'bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css', $content);
                $modified = true;
                $changes[] = 'تحديث Bootstrap RTL';
                break;
            }
        }
    }
    
    // 3. إصلاح مسارات CSS النسبية
    $relative_path = dirname($filepath) === '.' ? '' : '../';
    
    if ($relative_path) {
        // إصلاح مسار style.css
        if (preg_match('/href="assets\/css\/style\.css"/', $content)) {
            $content = str_replace('href="assets/css/style.css"', 'href="../assets/css/style.css"', $content);
            $modified = true;
            $changes[] = 'إصلاح مسار CSS';
        }
        
        // إصلاح مسار style.css مع BASE_URL
        if (preg_match('/href="\.\.\/assets\/css\/style\.css"/', $content) && !preg_match('/BASE_URL/', $content)) {
            $content = str_replace('href="../assets/css/style.css"', 'href="<?= BASE_URL ?>/assets/css/style.css"', $content);
            $modified = true;
            $changes[] = 'إضافة BASE_URL للـ CSS';
        }
    }
    
    // 4. إصلاح include paths
    if (dirname($filepath) !== '.') {
        // إصلاح sidebar
        if (preg_match('/include\s+[\'"]includes\/sidebar\.php[\'"]/', $content)) {
            $content = preg_replace(
                '/include\s+[\'"]includes\/sidebar\.php[\'"]/',
                "include '../includes/sidebar.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار sidebar';
        }
        
        // إصلاح navbar
        if (preg_match('/include\s+[\'"]includes\/navbar\.php[\'"]/', $content)) {
            $content = preg_replace(
                '/include\s+[\'"]includes\/navbar\.php[\'"]/',
                "include '../includes/navbar.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار navbar';
        }
        
        // إصلاح header
        if (preg_match('/include\s+[\'"]includes\/header\.php[\'"]/', $content)) {
            $content = preg_replace(
                '/include\s+[\'"]includes\/header\.php[\'"]/',
                "include '../includes/header.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار header';
        }
        
        // إصلاح footer
        if (preg_match('/include\s+[\'"]includes\/footer\.php[\'"]/', $content)) {
            $content = preg_replace(
                '/include\s+[\'"]includes\/footer\.php[\'"]/',
                "include '../includes/footer.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار footer';
        }
        
        // إصلاح config
        if (preg_match('/require_once\s+[\'"]config\/config\.php[\'"]/', $content)) {
            $content = preg_replace(
                '/require_once\s+[\'"]config\/config\.php[\'"]/',
                "require_once '../config/config.php'",
                $content
            );
            $modified = true;
            $changes[] = 'إصلاح مسار config';
        }
    }
    
    // 5. إضافة HTML wrapper للملفات التي تحتوي على UI elements لكن بدون HTML structure
    if (!preg_match('/<html/i', $content) && !preg_match('/include.*header/i', $content)) {
        // البحث عن بداية المحتوى بعد PHP
        if (preg_match('/(<\?php[^?]*\?>.*?)(<div|<main|<section)/is', $content, $matches)) {
            $php_part = $matches[1];
            $html_part = substr($content, strlen($php_part));
            
            $new_content = $php_part . "\n";
            $new_content .= "<!DOCTYPE html>\n";
            $new_content .= '<html lang="ar" dir="rtl">' . "\n";
            $new_content .= "<head>\n";
            $new_content .= '    <meta charset="UTF-8">' . "\n";
            $new_content .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
            $new_content .= '    <title><?= $page_title ?? "صفحة" ?> - <?= SYSTEM_NAME ?></title>' . "\n";
            $new_content .= '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">' . "\n";
            $new_content .= '    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">' . "\n";
            $new_content .= '    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">' . "\n";
            $new_content .= "</head>\n";
            $new_content .= "<body>\n";
            $new_content .= $html_part;
            
            // إضافة إغلاق body و html في النهاية إذا لم يكن موجود
            if (!preg_match('/<\/body>/i', $new_content)) {
                $new_content .= "\n</body>";
            }
            if (!preg_match('/<\/html>/i', $new_content)) {
                $new_content .= "\n</html>";
            }
            
            $content = $new_content;
            $modified = true;
            $changes[] = 'إضافة HTML structure كامل';
        }
    }
    
    // حفظ الملف إذا تم تعديله
    if ($modified) {
        $backup_file = $filepath . '.backup.' . date('Y-m-d-H-i-s');
        
        if (copy($filepath, $backup_file)) {
            if (file_put_contents($filepath, $content) !== false) {
                return [
                    'status' => 'fixed',
                    'message' => 'تم الإصلاح: ' . implode(', ', $changes),
                    'file' => $filepath,
                    'backup' => $backup_file
                ];
            } else {
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
            'message' => 'لا يحتاج تعديل أو محدث بالفعل',
            'file' => $filepath
        ];
    }
}

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
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إصلاح جميع صفحات المشروع - النسخة الثالثة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
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
        .log-entry { margin: 2px 0; padding: 2px 5px; border-radius: 3px; }
        .log-fixed { background: #d4edda; color: #155724; }
        .log-skipped { background: #fff3cd; color: #856404; }
        .log-error { background: #f8d7da; color: #721c24; }
        .stats-card { 
            background: white; 
            border: 1px solid #dee2e6; 
            border-radius: 8px; 
            padding: 15px; 
            margin: 10px 0; 
            text-align: center;
        }
        .stats-number { font-size: 2em; font-weight: bold; margin: 10px 0; }
        #processSection { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tools"></i> إصلاح جميع صفحات المشروع - النسخة الثالثة المحسنة</h1>
        
        <div class="warning">
            <h4><i class="fas fa-exclamation-triangle"></i> تحذير!</h4>
            <p>هذا السكربت المحسن سيقوم بإصلاح جميع ملفات PHP التي تحتوي على عناصر UI (حتى لو لم تحتوي على HTML tags).</p>
            <ul>
                <li>سيبحث عن: container-fluid, sidebar, navbar, Bootstrap classes, Font Awesome icons</li>
                <li>سيضيف HTML structure كامل للملفات التي تحتاجه</li>
                <li>سيصلح مسارات CSS و JavaScript</li>
                <li>سيحول Bootstrap إلى RTL</li>
            </ul>
        </div>
        
        <div id="startSection">
            <div class="d-grid gap-2">
                <button type="button" id="startBtn" class="btn btn-primary btn-lg">
                    <i class="fas fa-play me-2"></i>بدء الإصلاح الشامل
                </button>
            </div>
        </div>
        
        <div id="processSection">
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
            
            <div class="progress-container">
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="progressBar" style="width: 0%">
                        <span id="progressPercent">0%</span>
                    </div>
                </div>
            </div>
            
            <div class="file-log" id="fileLog">
                <div class="text-muted text-center">سيظهر هنا سجل معالجة الملفات...</div>
            </div>
        </div>
    </div>
    
    <script>
        let totalFiles = 0;
        let processedFiles = 0;
        let fixedCount = 0;
        let skippedCount = 0;
        let errorCount = 0;
        
        document.getElementById('startBtn').addEventListener('click', function() {
            if (confirm('هل أنت متأكد من تشغيل السكربت الشامل؟ سيتم إصلاح جميع الملفات التي تحتوي على عناصر UI.')) {
                startProcessing();
            }
        });
        
        async function startProcessing() {
            document.getElementById('startSection').style.display = 'none';
            document.getElementById('processSection').style.display = 'block';
            
            try {
                const response = await fetch('fix_all_pages_v3.php?get_files=1');
                const allFiles = await response.json();
                totalFiles = allFiles.length;
                
                document.getElementById('totalFiles').textContent = totalFiles;
                addLogEntry(`تم العثور على ${totalFiles} ملف PHP`, 'processing');
                
                for (let i = 0; i < allFiles.length; i++) {
                    await processFile(allFiles[i]);
                }
                
                finishProcessing();
                
            } catch (error) {
                addLogEntry('خطأ: ' + error.message, 'error');
            }
        }
        
        async function processFile(filepath) {
            try {
                const formData = new FormData();
                formData.append('process_file', '1');
                formData.append('filepath', filepath);
                
                const response = await fetch('fix_all_pages_v3.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
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
                
                updateProgress();
                await new Promise(resolve => setTimeout(resolve, 50));
                
            } catch (error) {
                errorCount++;
                addLogEntry(`❌ ${filepath} - خطأ: ${error.message}`, 'error');
                updateProgress();
            }
        }
        
        function updateProgress() {
            const percentage = Math.round((processedFiles / totalFiles) * 100);
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressPercent').textContent = percentage + '%';
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
            document.getElementById('progressBar').classList.remove('progress-bar-animated');
            document.getElementById('progressBar').classList.add('bg-success');
            addLogEntry(`🎉 انتهت المعالجة! مصلح: ${fixedCount} | متجاهل: ${skippedCount} | أخطاء: ${errorCount}`, 'fixed');
        }
    </script>
</body>
</html>