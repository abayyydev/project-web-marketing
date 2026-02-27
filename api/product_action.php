<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$action = $data['action'] ?? '';

try {
    if ($action === 'add' || $action === 'edit') {
        $code = $data['code'] ?? ''; $name = $data['name'] ?? '';
        $unit = $data['unit'] ?? ''; $price = $data['base_price'] ?? 0;
        $feeAmt = $data['fee_amount'] ?? 0; $feeCode = $data['fee_code'] ?? '';
        $type = $data['type'] ?? 'goods';
        
        if ($action === 'add') {
            $sql = "INSERT INTO products (code, name, unit, base_price, fee_amount, fee_code, type) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$code, $name, $unit, $price, $feeAmt, $feeCode, $type]);
            $pid = $pdo->lastInsertId();
            
            // JIKA BARANG FISIK, BUATKAN SLOT STOK 0 DI SEMUA GUDANG OTOMATIS
            if($type === 'goods') {
                $pdo->prepare("INSERT IGNORE INTO product_stocks (product_id, warehouse_id, stock) SELECT ?, id, 0 FROM warehouses")->execute([$pid]);
            }
        } else {
            $pid = $data['id'];
            $sql = "UPDATE products SET code=?, name=?, unit=?, base_price=?, fee_amount=?, fee_code=?, type=? WHERE id=?";
            $pdo->prepare($sql)->execute([$code, $name, $unit, $price, $feeAmt, $feeCode, $type, $pid]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Produk berhasil disimpan']);
        
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$data['id']]);
        echo json_encode(['status' => 'success', 'message' => 'Produk dihapus']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>