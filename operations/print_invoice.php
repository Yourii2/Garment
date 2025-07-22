<?php
require_once '../config/config.php';
checkLogin();

$order_id = $_GET['id'] ?? 0;

// جلب بيانات الطلبية
$stmt = $pdo->prepare("
    SELECT o.*, 
           SUM(COALESCE(oi.quantity * oi.unit_price, 0)) as products_total,
           (SUM(COALESCE(oi.quantity * oi.unit_price, 0)) + COALESCE(o.shipping_cost, 0)) as order_total
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "الطلبية غير موجودة";
    exit;
}

// جلب منتجات الطلبية
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.product_code
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة - <?= htmlspecialchars($order['order_number']) ?></title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 5mm;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 5mm;
            width: 70mm;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .invoice-title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .info-section {
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        
        .products-table th,
        .products-table td {
            border-bottom: 1px solid #ddd;
            padding: 3px 2px;
            text-align: right;
        }
        
        .products-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .total-section {
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .final-total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        .no-print {
            display: block;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- أزرار التحكم -->
    <div class="no-print" style="text-align: center; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 5px 10px; margin: 0 5px;">طباعة</button>
        <button onclick="window.close()" style="padding: 5px 10px; margin: 0 5px;">إغلاق</button>
    </div>

    <!-- رأس الفاتورة -->
    <div class="header">
        <div class="company-name">شركة التصنيع</div>
        <div style="font-size: 10px;">العنوان - الهاتف</div>
        <div class="invoice-title">فاتورة مبيعات</div>
    </div>

    <!-- معلومات الفاتورة -->
    <div class="info-section">
        <div class="info-row">
            <span><strong>رقم الفاتورة:</strong></span>
            <span><?= htmlspecialchars($order['order_number']) ?></span>
        </div>
        <div class="info-row">
            <span><strong>التاريخ:</strong></span>
            <span><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="info-row">
            <span><strong>العميل:</strong></span>
            <span><?= htmlspecialchars($order['customer_name']) ?></span>
        </div>
        <?php if ($order['customer_phone']): ?>
        <div class="info-row">
            <span><strong>الهاتف:</strong></span>
            <span><?= htmlspecialchars($order['customer_phone']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- جدول المنتجات -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 40%;">المنتج</th>
                <th style="width: 15%;">الكمية</th>
                <th style="width: 20%;">السعر</th>
                <th style="width: 25%;">المجموع</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td style="text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="text-align: center;"><?= number_format($item['unit_price'], 2) ?></td>
                    <td style="text-align: center;"><?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- إجمالي الفاتورة -->
    <div class="total-section">
        <div class="total-row">
            <span>مجموع المنتجات:</span>
            <span><?= number_format($order['products_total'], 2) ?> ج.م</span>
        </div>
        
        <?php if ($order['shipping_cost'] > 0): ?>
        <div class="total-row">
            <span>مصروفات الشحن:</span>
            <span><?= number_format($order['shipping_cost'], 2) ?> ج.م</span>
        </div>
        <?php endif; ?>
        
        <div class="total-row final-total">
            <span>الإجمالي النهائي:</span>
            <span><?= number_format($order['order_total'], 2) ?> ج.م</span>
        </div>
    </div>

    <?php if ($order['notes']): ?>
    <div style="margin-top: 10px; font-size: 10px;">
        <strong>ملاحظات:</strong><br>
        <?= nl2br(htmlspecialchars($order['notes'])) ?>
    </div>
    <?php endif; ?>

    <!-- تذييل الفاتورة -->
    <div class="footer">
        <div>شكراً لتعاملكم معنا</div>
        <div>تم الطباعة في: <?= date('Y-m-d H:i:s') ?></div>
    </div>

    <script>
        // طباعة تلقائية عند فتح النافذة
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>