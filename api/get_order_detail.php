<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
    exit;
}

try {
    // 1. Ambil Parent Order (Gunakan o.* agar kolom baru terbaca)
    $stmtO = $pdo->prepare("
        SELECT o.*, u.full_name as marketing_name 
        FROM orders o 
        LEFT JOIN users u ON o.marketing_id = u.id 
        WHERE o.id = ?
    ");
    $stmtO->execute([$id]);
    $order = $stmtO->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan']);
        exit;
    }

    // 2. Ambil Items
    $stmtI = $pdo->prepare("
        SELECT i.*, p.name as product_name, p.unit 
        FROM order_items i 
        JOIN products p ON i.product_id = p.id 
        WHERE i.order_id = ?
    ");
    $stmtI->execute([$id]);
    $items = $stmtI->fetchAll(PDO::FETCH_ASSOC);

    // 3. Ambil Info Instalasi (Jika ada)
    $stmtIns = $pdo->prepare("SELECT * FROM installations WHERE order_id = ?");
    $stmtIns->execute([$id]);
    $install = $stmtIns->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'order' => $order,
            'items' => $items,
            'install' => $install ?: null
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>