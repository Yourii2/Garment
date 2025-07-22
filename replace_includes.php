<?php
echo "<h1>استبدال ملفات includes</h1>";

// نسخ الملفات النظيفة
if (copy('includes/navbar_clean.php', 'includes/navbar.php')) {
    echo "✅ تم استبدال navbar.php<br>";
} else {
    echo "❌ فشل في استبدال navbar.php<br>";
}

if (copy('includes/sidebar_clean.php', 'includes/sidebar.php')) {
    echo "✅ تم استبدال sidebar.php<br>";
} else {
    echo "❌ فشل في استبدال sidebar.php<br>";
}

if (copy('includes/footer_clean.php', 'includes/footer.php')) {
    echo "✅ تم استبدال footer.php<br>";
} else {
    echo "❌ فشل في استبدال footer.php<br>";
}

echo "<br><strong>جرب فتح dashboard.php الآن</strong>";
?>