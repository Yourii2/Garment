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
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة فاتورة <?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; margin: 0; }
            .page-break { page-break-after: always; }
        }
        body { 
            font-family: Arial, sans-serif; 
            direction: rtl; 
            margin: 20px;
            background: white;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .invoice-info { 
            margin-bottom: 20px; 
        }
        .invoice-info table { 
            width: 100%; 
            border-collapse: collapse;
        }
        .invoice-info td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        .items-table th, .items-table td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: center; 
        }
        .items-table th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .total { 
            text-align: left; 
            margin-top: 20px; 
            font-weight: bold; 
            font-size: 18px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body onload="window.print();">
    <div class="header">
        <h1><?= SYSTEM_NAME ?></h1>
        <h2>فاتورة <?php
            $type_names = [
                'purchase' => 'شراء',
                'return' => 'مرتجع',
                'damage' => 'هالك'
            ];
            echo $type_names[$invoice['invoice_type']] ?? $invoice['invoice_type'];
        ?></h2>
        <p><strong>رقم الفاتورة:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></p>
        <p><strong>التاريخ:</strong> <?= date('Y-m-d', strtotime($invoice['invoice_date'])) ?></p>
    </div>
    
    <div class="invoice-info">
        <table>
            <tr>
                <td><strong>المورد:</strong> <?= htmlspecialchars($invoice['supplier_name'] ?? 'غير محدد') ?></td>
                <td><strong>المخزن:</strong> <?= htmlspecialchars($invoice['branch_name'] ?? 'غير محدد') ?></td>
            </tr>
            <tr>
                <td><strong>المستخدم:</strong> <?= htmlspecialchars($invoice['user_name']) ?></td>
                <td><strong>تاريخ الإنشاء:</strong> <?= date('Y-m-d H:i', strtotime($invoice['created_at'])) ?></td>
            </tr>
        </table>
    </div>
    
    <table class="items-table">
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
                    <td><?= htmlspecialchars($item['fabric_name'] ?? $item['accessory_name']) ?></td>
                    <td><?= htmlspecialchars($item['fabric_code'] ?? $item['accessory_code']) ?></td>
                    <td><?= number_format($item['quantity'], 2) ?></td>
                    <td><?= htmlspecialchars($item['fabric_unit'] ?? $item['accessory_unit']) ?></td>
                    <td><?= number_format($item['unit_cost'], 2) ?></td>
                    <td><?= number_format($item['total_cost'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="total">
        <h3>الإجمالي الكلي: <?= number_format($invoice['total_amount'], 2) ?> <?= CURRENCY_SYMBOL ?></h3>
    </div>
    
    <?php if ($invoice['notes']): ?>
        <div style="margin-top: 20px; border: 1px solid #ddd; padding: 15px;">
            <strong>ملاحظات:</strong><br>
            <?= nl2br(htmlspecialchars($invoice['notes'])) ?>
        </div>
    <?php endif; ?>
</body>
</html>
