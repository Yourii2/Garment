<?php
require_once '../config/config.php';
checkLogin();

$invoice_id = $_GET['id'] ?? 0;

// جلب بيانات الفاتورة
$stmt = $pdo->prepare("
    SELECT i.*, s.name as supplier_name, b.name as branch_name, u.full_name as user_name
    FROM inventory_invoices i 
    LEFT JOIN suppliers s ON i.supplier_id = s.id 
    LEFT JOIN branches b ON i.branch_id = b.id 
    LEFT JOIN users u ON i.user_id = u.id
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die('الفاتورة غير موجودة');
}

// جلب تفاصيل الفاتورة
$stmt = $pdo->prepare("
    SELECT ivi.*, 
           ft.name as fabric_name, ft.code as fabric_code, ft.unit as fabric_unit,
           a.name as accessory_name, a.code as accessory_code, a.unit as accessory_unit
    FROM inventory_invoice_items ivi
    LEFT JOIN fabric_types ft ON ivi.fabric_id = ft.id
    LEFT JOIN accessories a ON ivi.accessory_id = a.id
    WHERE ivi.invoice_id = ?
");
$stmt->execute([$invoice_id]);
$items = $stmt->fetchAll();

// إعداد headers للتصدير
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="invoice_' . $invoice['invoice_number'] . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// بدء محتوى Excel
echo "\xEF\xBB\xBF"; // UTF-8 BOM
?>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h2><?= SYSTEM_NAME ?></h2>
        <h3>فاتورة <?php
            $type_names = [
                'purchase' => 'شراء',
                'return' => 'مرتجع',
                'damage' => 'هالك'
            ];
            echo $type_names[$invoice['invoice_type']] ?? $invoice['invoice_type'];
        ?></h3>
    </div>
    
    <div class="info">
        <table style="border: none; margin-bottom: 20px;">
            <tr style="border: none;">
                <td style="border: none;"><strong>رقم الفاتورة:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></td>
                <td style="border: none;"><strong>التاريخ:</strong> <?= date('Y-m-d', strtotime($invoice['invoice_date'])) ?></td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>المورد:</strong> <?= htmlspecialchars($invoice['supplier_name'] ?? 'غير محدد') ?></td>
                <td style="border: none;"><strong>المخزن:</strong> <?= htmlspecialchars($invoice['branch_name'] ?? 'غير محدد') ?></td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>المستخدم:</strong> <?= htmlspecialchars($invoice['user_name']) ?></td>
                <td style="border: none;"><strong>تاريخ الإنشاء:</strong> <?= date('Y-m-d H:i', strtotime($invoice['created_at'])) ?></td>
            </tr>
        </table>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الصنف</th>
                <th>الكود</th>
                <th>الكمية</th>
                <th>الوحدة</th>
                <th>سعر الوحدة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td>
                        <?php if ($item['fabric_id']): ?>
                            <?= htmlspecialchars($item['fabric_name']) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($item['accessory_name']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['fabric_code'] ?? $item['accessory_code']) ?></td>
                    <td><?= number_format($item['quantity'], 2) ?></td>
                    <td><?= htmlspecialchars($item['fabric_unit'] ?? $item['accessory_unit']) ?></td>
                    <td><?= number_format($item['unit_cost'], 2) ?></td>
                    <td><?= number_format($item['total_cost'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #e0e0e0; font-weight: bold;">
                <td colspan="6">الإجمالي الكلي</td>
                <td><?= number_format($invoice['total_amount'], 2) ?> <?= CURRENCY_SYMBOL ?></td>
            </tr>
        </tfoot>
    </table>
    
    <?php if ($invoice['notes']): ?>
        <div style="margin-top: 20px;">
            <strong>ملاحظات:</strong><br>
            <?= nl2br(htmlspecialchars($invoice['notes'])) ?>
        </div>
    <?php endif; ?>
</body>
</html>