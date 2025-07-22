<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المورد مطلوب']);
    exit;
}

$supplier_id = $_GET['id'];

try {
    // جلب تفاصيل المورد
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();
    
    if (!$supplier) {
        echo json_encode(['success' => false, 'message' => 'المورد غير موجود']);
        exit;
    }
    
    // جلب الفواتير
    $stmt = $pdo->prepare("SELECT id, invoice_date, total_amount, status FROM inventory_invoices WHERE supplier_id = ? ORDER BY invoice_date DESC LIMIT 10");
    $stmt->execute([$supplier_id]);
    $invoices = $stmt->fetchAll();
    
    // حساب إجمالي المشتريات
    $stmt = $pdo->prepare("SELECT COUNT(*) as invoices_count, COALESCE(SUM(total_amount), 0) as total_purchases FROM inventory_invoices WHERE supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $summary = $stmt->fetch();
    
    // جلب المدفوعات
    $stmt = $pdo->prepare("SELECT payment_date, amount, payment_method, description FROM supplier_payments WHERE supplier_id = ? ORDER BY payment_date DESC LIMIT 10");
    $stmt->execute([$supplier_id]);
    $payments = $stmt->fetchAll();
    
    // حساب إجمالي المدفوعات
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total_payments FROM supplier_payments WHERE supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $payments_summary = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'supplier' => $supplier,
        'invoices' => $invoices,
        'payments' => $payments,
        'invoices_count' => $summary['invoices_count'],
        'total_purchases' => $summary['total_purchases'],
        'total_payments' => $payments_summary['total_payments']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>