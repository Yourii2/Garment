// تحديث استعلام جلب العمال المتاحين للمرحلة التالية
$stmt = $pdo->prepare("
    SELECT id, name as full_name 
    FROM workers 
    WHERE is_active = 1
    ORDER BY name
");
$stmt->execute();
$available_workers = $stmt->fetchAll();