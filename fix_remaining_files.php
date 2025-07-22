<?php
/**
 * سكربت إصلاح الملفات المتبقية التي تحتوي على UI elements بدون HTML structure
 */

set_time_limit(300);
ini_set('memory_limit', '256M');

// إذا كان طلب AJAX لمعالجة ملف واحد
if (isset($_POST['process_file'])) {
    $filepath = $_POST['filepath'];
    $result = fixRemainingFile($filepath);
    echo json_encode($result);
    exit;
}

// إذا كان طلب AJAX لجلب قائمة الملفات المتبقية
if (isset($_GET['get_remaining_files'])) {
    $remaining_files = getRemainingFiles();
    echo json_encode($remaining_files);
    exit;
}

function getRemainingFiles() {
    $directories = ['.', 'production', 'inventory', 'financial', 'hr', 'reports', 'admin', 'warehouse', 'sales'];
    $excluded_files = ['config.php', 'database.php', 'functions.php', 'permissions.php', 'fix_all_pages.php', 'fix_all_pages_v2.php', 'fix_all_pages_v3.php', 'debug_files.php', 'install.php', 'setup.php', 'check_final_results.php', 'fix_remaining_files.php'];
    
    $remaining_files = [];
    
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $files = scanDirectory($dir);
            foreach ($files as $file) {
                if (!in_array(basename($file), $excluded_files)) {
                    $content = file_get_contents($file);
                    
                    // فحص عناصر UI
                    $hasUIElements = (
                        preg_match('/<html/i', $content) ||
                        preg_match('/<!DOCTYPE/i', $content) ||
                        preg_match('/<head>/i', $content) ||
                        preg_match('/<body>/i', $content) ||
                        preg_match('/bootstrap/i', $content) ||
                        preg_match('/container-fluid/i', $content) ||
                        preg_match('/include.*navbar/i', $content) ||
                        preg_match('/include.*sidebar/i', $content) ||
                        preg_match('/include.*header/i', $content) ||
                        preg_match('/btn btn-/i', $content) ||
                        preg_match('/class=".*col-/i', $content) ||
                        preg_match('/fa-[a-z-]+/i', $content) ||
                        preg_match('/class=".*card/i', $content) ||
                        preg_match('/class=".*table/i', $content) ||
                        preg_match('/class=".*form-/i', $content) ||
                        preg_match('/class=".*alert/i', $content) ||
                        preg_match('/class=".*modal/i', $content)
                    );
                    
                    if ($hasUIElements) {
                        // فحص إذا كان محدث بالفعل
                        $isFixed = preg_match('/<html[^>]*lang="ar"[^>]*dir="rtl"/i', $content);
                        
                        if (!$isFixed) {
                            // فحص إذا كان يحتوي على HTML structure
                            $hasHTMLStructure = preg_match('/<html/i', $content);
                            
                            if (!$hasHTMLStructure) {
                                $remaining_files[] = $file;
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $remaining_files;
}

function fixRemainingFile($filepath) {
    $content = file_get_contents($filepath);
    if ($content === false) {
        return [
            'status' => 'error',
            'message' => 'فشل في قراءة الملف',
            'file' => $filepath
        ];
    }
    
    $original_content = $content;
    $modified = false;
    $changes = [];
    
    // تحديد المسار النسبي
    $relative_path = dirname($filepath) === '.' ? '' : '../';
    
    // البحث عن بداية المحتوى بعد PHP
    $php_section = '';
    $html_section = '';
    
    // استخراج قسم PHP
    if (preg_match('/^(<\?php.*?\?>)/s', $content, $matches)) {
        $php_section = $matches[1];
        $html_section = substr($content, strlen($php_section));
    } else {
        $html_section = $content;
    }
    
    // إنشاء HTML structure جديد
    $new_content = $php_section . "\n\n";
    
    // تحديد page_title إذا لم يكن موجود
    if (!preg_match('/\$page_title\s*=/', $php_section)) {
        $page_name = ucfirst(str_replace(['_', '.php'], [' ', ''], basename($filepath)));
        $new_content = str_replace('?>', "\$page_title = '$page_name';\n?>", $new_content);
    }
    
    $new_content .= "<!DOCTYPE html>\n";
    $new_content .= '<html lang="ar" dir="rtl">' . "\n";
    $new_content .= "<head>\n";
    $new_content .= '    <meta charset="UTF-8">' . "\n";
    $new_content .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    $new_content .= '    <title><?= $page_title ?? "صفحة" ?> - <?= SYSTEM_NAME ?></title>' . "\n";
    $new_content .= '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">' . "\n";
    $new_content .= '    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">' . "\n";
    $new_content .= '    <link href="<?= BASE_URL ?>/' . $relative_path . 'assets/css/style.css" rel="stylesheet">' . "\n";
    $new_content .= "</head>\n";
    $new_content .= "<body>\n";
    
    // إضافة navbar إذا لم يكن موجود
    if (!preg_match('/include.*navbar/i', $html_section)) {
        $new_content .= '    <?php include \'' . $relative_path . 'includes/navbar.php\'; ?>' . "\n\n";
        $changes[] = 'إضافة navbar';
    }
    
    // إضافة المحتوى الأصلي مع تنظيف
    $html_section = trim($html_section);
    
    // إصلاح include paths في المحتوى
    if ($relative_path) {
        // إصلاح sidebar
        $html_section = preg_replace(
            '/include\s+[\'"]includes\/sidebar\.php[\'"]/',
            "include '{$relative_path}includes/sidebar.php'",
            $html_section
        );
        
        // إصلاح navbar
        $html_section = preg_replace(
            '/include\s+[\'"]includes\/navbar\.php[\'"]/',
            "include '{$relative_path}includes/navbar.php'",
            $html_section
        );
        
        // إصلاح footer
        $html_section = preg_replace(
            '/include\s+[\'"]includes\/footer\.php[\'"]/',
            "include '{$relative_path}includes/footer.php'",
            $html_section
        );
        
        $changes[] = 'إصلاح مسارات include';
    }
    
    $new_content .= "    " . $html_section . "\n\n";
    
    // إضافة footer إذا لم يكن موجود
    if (!preg_match('/include.*footer/i', $html_section)) {
        $new_content .= '<?php include \'' . $relative_path . 'includes/footer.php\'; ?>' . "\n";
        $changes[] = 'إضافة footer';
    }
    
    $new_content .= "</body>\n";
    $new_content .= "</html>";
    
    $modified = true;
    $changes[] = 'إضافة HTML structure كامل';
    
    // حفظ الملف
    if ($modified) {
        $backup_file = $filepath . '.backup.' . date('Y-m-d-H-i-s');
        
        if (copy($filepath, $backup_file)) {
            if (file_put_contents($filepath, $new_content) !== false) {
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
    }
    
    return [
        'status' => 'skipped',
        'message' => 'لا يحتاج تعديل',
        'file' => $filepath
    ];
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
    <title>إصلاح الملفات المتبقية</title>
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
        <h1><i class="fas fa-tools"></i> إصلاح الملفات المتبقية (124 ملف)</h1>
        
        <div class="warning">
            <h4><i class="fas fa-exclamation-triangle"></i> إصلاح متخصص!</h4>
            <p>هذا السكربت سيقوم بإصلاح الـ 124 ملف المتبقية التي تحتوي على عناصر UI لكن بدون HTML structure.</p>
            <ul>
                <li>سيضيف HTML structure كامل مع lang="ar" dir="rtl"</li>
                <li>سيضيف navbar و footer تلقائياً</li>
                <li>سيصلح جميع مسارات include</li>
                <li>سيضيف Bootstrap RTL و Font Awesome</li>
            </ul>
        </div>
        
        <div id="startSection">
            <div class="d-grid gap-2">
                <button type="button" id="startBtn" class="btn btn-success btn-lg">
                    <i class="fas fa-magic me-2"></i>إصلاح الملفات المتبقية (124 ملف)
                </button>
            </div>
        </div>
        
        <div id="processSection">
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="text-muted">ملفات متبقية</div>
                        <div class="stats-number text-primary" id="totalFiles">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="text-muted">تم الإصلاح</div>
                        <div class="stats-number text-success" id="fixedFiles">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="text-muted">أخطاء</div>
                        <div class="stats-number text-danger" id="errorFiles">0</div>
                    </div>
                </div>
            </div>
            
            <div class="progress-container">
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                         id="progressBar" style="width: 0%">
                        <span id="progressPercent">0%</span>
                    </div>
                </div>
            </div>
            
            <div class="file-log" id="fileLog">
                <div class="text-muted text-center">سيظهر هنا سجل إصلاح الملفات المتبقية...</div>
            </div>
        </div>
    </div>
    
    <script>
        let totalFiles = 0;
        let processedFiles = 0;
        let fixedCount = 0;
        let errorCount = 0;
        
        document.getElementById('startBtn').addEventListener('click', function() {
            if (confirm('هل أنت متأكد من إصلاح الملفات المتبقية؟ سيتم إضافة HTML structure كامل لجميع الملفات.')) {
                startProcessing();
            }
        });
        
        async function startProcessing() {
            document.getElementById('startSection').style.display = 'none';
            document.getElementById('processSection').style.display = 'block';
            
            try {
                const response = await fetch('fix_remaining_files.php?get_remaining_files=1');
                const remainingFiles = await response.json();
                totalFiles = remainingFiles.length;
                
                document.getElementById('totalFiles').textContent = totalFiles;
                addLogEntry(`تم العثور على ${totalFiles} ملف يحتاج إصلاح`, 'processing');
                
                for (let i = 0; i < remainingFiles.length; i++) {
                    await processFile(remainingFiles[i]);
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
                
                const response = await fetch('fix_remaining_files.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                processedFiles++;
                
                if (result.status === 'fixed') {
                    fixedCount++;
                    addLogEntry(`✅ ${filepath} - ${result.message}`, 'fixed');
                } else if (result.status === 'error') {
                    errorCount++;
                    addLogEntry(`❌ ${filepath} - ${result.message}`, 'error');
                }
                
                updateProgress();
                await new Promise(resolve => setTimeout(resolve, 100));
                
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
            addLogEntry(`🎉 انتهى الإصلاح! مصلح: ${fixedCount} | أخطاء: ${errorCount}`, 'fixed');
            
            setTimeout(() => {
                alert(`تم الانتهاء!\n\nمصلح: ${fixedCount} ملف\nأخطاء: ${errorCount} ملف\n\nيمكنك الآن تشغيل check_final_results.php للتحقق من النتيجة النهائية.`);
            }, 1000);
        }
    </script>
</body>
</html>