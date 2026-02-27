<?php
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    // Ambil semua produk
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil pemetaan stok multi-gudang
    $stmtStock = $pdo->query("SELECT ps.product_id, w.name as warehouse_name, ps.stock FROM product_stocks ps JOIN warehouses w ON ps.warehouse_id = w.id");
    $stocks = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

    // Gabungkan stok ke dalam array produk
    $stockMap = [];
    foreach($stocks as $st) {
        $stockMap[$st['product_id']][$st['warehouse_name']] = floatval($st['stock']);
    }

    foreach($products as &$p) {
        $p['stocks'] = $stockMap[$p['id']] ?? [];
    }

    echo json_encode(['status' => 'success', 'data' => $products]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>