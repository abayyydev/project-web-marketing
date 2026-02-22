<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

try {
    // 1. Ambil Data Header (Order)
    $stmt = $pdo->prepare("
        SELECT o.*, u.username as marketing_name 
        FROM orders o
        LEFT JOIN users u ON o.marketing_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan']);
        exit;
    }

    // 2. Ambil Rincian Barang (Items) + Nama Produk
    $stmtItems = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.unit
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();

    // 3. Ambil Data Instalasi (Jika ada)
    $stmtInstall = $pdo->prepare("SELECT * FROM installations WHERE order_id = ?");
    $stmtInstall->execute([$id]);
    $install = $stmtInstall->fetch();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'order' => $order,
            'items' => $items,
            'install' => $install
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>