<?php
require_once '../config/config.php';

try {
    // إضافة عمود accessory_id إذا لم يكن موجود<|im_end|>
</augment_code_snippet>

الآن دعني أصلح ملف `movements.php` لإضافة معالجة أفضل للأخطاء:

<augment_code_snippet path="inventory/movements.php" mode="EDIT">
```php
// معالجة إضافة حركة إدخال
if ($_POST['add_in_movement'] ?? false) {
    try {
        $fabric_id = $_POST['fabric_id'] ?: null;
        $accessory_id = $_POST['accessory_id'] ?: null;
        $quantity = floatval($_POST['quantity']);
        $unit_cost = floatval($_POST['unit_cost'] ?: 0);
        $total_cost = $quantity * $unit_cost;
        $reference_type = $_POST['reference_type'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // التحقق من صحة البيانات
        if (empty($fabric_id) && empty($accessory_id)) {
            throw new Exception('يجب اختيار قماش أو إكسسوار');
        }
        
        if ($quantity <= 0) {
            throw new Exception('الكمية يجب أن تكون أكبر من صفر');
        }
        
        $stmt = $pdo->prepare("INSERT INTO inventory_movements (fabric_id, accessory_id, movement_type, quantity, unit_cost, total_cost, reference_type, notes, user_id, created_at) VALUES (?, ?, 'in', ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$fabric_id, $accessory_id, $quantity, $unit_cost, $total_cost, $reference_type, $notes, $_SESSION['user_id']]);
        
        if (!$result) {
            throw new Exception('فشل في إدراج البيانات');
        }
        
        // تحديث المخزون
        if ($fabric_id) {
            $stmt = $pdo->prepare("UPDATE fabric_types SET current_quantity = current_quantity + ? WHERE id = ?");
            $stmt->execute([$quantity, $fabric_id]);
        } elseif ($accessory_id) {
            $stmt = $pdo->prepare("UPDATE accessories SET current_quantity = current_quantity + ? WHERE id = ?");
            $stmt->execute([$quantity, $accessory_id]);
        }
        
        $_SESSION['success_message'] = 'تم إدخال المخزون بنجاح';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'حدث خطأ: ' . $e->getMessage();
    }
    
    header('Location: movements.php');
    exit;
}

// معالجة إضافة حركة إخراج
if ($_POST['add_out_movement'] ?? false) {
    try {
        $fabric_id = $_POST['fabric_id'] ?: null;
        $accessory_id = $_POST['accessory_id'] ?: null;
        $quantity = floatval($_POST['quantity']);
        $reference_type = $_POST['reference_type'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // التحقق من صحة البيانات
        if (empty($fabric_id) && empty($accessory_id)) {
            throw new Exception('يجب اختيار قماش أو إكسسوار');
        }
        
        if ($quantity <= 0) {
            throw new Exception('الكمية يجب أن تكون أكبر من صفر');
        }
        
        $stmt = $pdo->prepare("INSERT INTO inventory_movements (fabric_id, accessory_id, movement_type, quantity, reference_type, notes, user_id, created_at) VALUES (?, ?, 'out', ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$fabric_id, $accessory_id, $quantity, $reference_type, $notes, $_SESSION['user_id']]);
        
        if (!$result) {
            throw new Exception('فشل في إدراج البيانات');
        }
        
        // تحديث المخزون
        if ($fabric_id) {
            $stmt = $pdo->prepare("UPDATE fabric_types SET current_quantity = current_quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $fabric_id]);
        } elseif ($accessory_id) {
            $stmt = $pdo->prepare("UPDATE accessories SET current_quantity = current_quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $accessory_id]);
        }
        
        $_SESSION['success_message'] = 'تم إخراج المخزون بنجاح';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'حدث خطأ: ' . $e->getMessage();
    }
    
    header('Location: movements.php');
    exit;
}