<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validasi ID
if (empty($data['order_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID Pesanan hilang']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. UPDATE HEADER ORDER
    // Note: KP Number tidak boleh diubah agar konsisten
    $sqlOrder = "UPDATE orders SET 
        customer_name = ?, customer_phone = ?, customer_address = ?, maps_link = ?, 
        warehouse_source = ?, delivery_date = ?, grand_total = ?, pay_status = ?,
        total_fee_r = ?, total_fee_dc = ?, ki_number = ?
        WHERE id = ?";

    $kiNum = ($data['ki_id'] !== '-') ? $data['ki_id'] : NULL;

    $stmt = $pdo->prepare($sqlOrder);
    $stmt->execute([
        $data['customer'],
        $data['phone'],
        $data['address'],
        $data['maps'],
        $data['wh'],
        $data['date_send'],
        $data['totals']['grand'],
        $data['pay_status'],
        $data['fees']['r'],
        $data['fees']['dc'],
        $kiNum,
        $data['order_id']
    ]);

    // 2. UPDATE ITEMS (Hapus Lama -> Insert Baru)
    $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$data['order_id']]);

    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, deal_price, subtotal) VALUES (?, ?, ?, ?, ?)");

    foreach ($data['items'] as $item) {
        $prodId = $item['product_id'];
        // Jika product_id hilang (kasus edit tapi item tidak disentuh), cari lagi by name
        if (!$prodId) {
            $f = $pdo->prepare("SELECT id FROM products WHERE name = ?");
            $f->execute([$item['name']]);
            $prodId = $f->fetchColumn();
        }

        if ($prodId) {
            $stmtItem->execute([$data['order_id'], $prodId, $item['qty'], $item['price'], $item['sub']]);
        }
    }

    // 3. UPDATE INSTALASI
    // Hapus dulu
    $pdo->prepare("DELETE FROM installations WHERE order_id = ?")->execute([$data['order_id']]);

    // Insert baru jika ada
    if ($data['ki_id'] !== '-' && !empty($data['install_info'])) {
        $info = $data['install_info'];
        $stmtIns = $pdo->prepare("INSERT INTO installations (order_id, mandor_name, work_date, area_size, service_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtIns->execute([
            $data['order_id'],
            $info['mandor'],
            $info['date'],
            $info['qty'],
            $info['price'],
            $info['total']
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Pesanan Berhasil Diperbarui']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>