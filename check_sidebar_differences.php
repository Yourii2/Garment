<?php
echo "<h1>فحص الاختلافات في القائمة الجانبية</h1>";

// فحص dashboard.php
echo "<h3>CSS في dashboard.php:</h3>";
$dashboard = file_get_contents('dashboard.php');
preg_match('/href="([^"]*style\.css)"/', $dashboard, $matches);
echo "مسار CSS: " . ($matches[1] ?? 'غير موجود') . "<br>";

// فحص صفحة أخرى
echo "<h3>CSS في production/manufacturing_stages.php:</h3>";
if (file_exists('production/manufacturing_stages.php')) {
    $production = file_get_contents('production/manufacturing_stages.php');
    preg_match('/href="([^"]*style\.css)"/', $production, $matches2);
    echo "مسار CSS: " . ($matches2[1] ?? 'غير موجود') . "<br>";
}

// فحص sidebar classes
echo "<h3>Classes في sidebar.php:</h3>";
$sidebar = file_get_contents('includes/sidebar.php');
if (strpos($sidebar, 'collapse') !== false) {
    echo "✅ يحتوي على collapse class<br>";
} else {
    echo "❌ لا يحتوي على collapse class<br>";
}

echo "<h3>Bootstrap version check:</h3>";
if (strpos($dashboard, 'bootstrap@5.1.3') !== false) {
    echo "✅ Dashboard يستخدم Bootstrap 5.1.3<br>";
} else {
    echo "❌ Dashboard يستخدم إصدار مختلف<br>";
}
?>