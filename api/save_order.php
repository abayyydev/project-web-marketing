<?php
// File: api/save_order.php
header('Content-Type: application/json');
require_once '../config/database.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Data JSON tidak valid']); exit;
}

try {
    $pdo->beginTransaction();

    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmtUser->execute([$data['marketing_username'] ?? 'marketing']);
    $user = $stmtUser->fetch();
    $marketingId = $user ? $user['id'] : 1;

    $stmtWh = $pdo->prepare("SELECT id FROM warehouses WHERE name = ?");
    $stmtWh->execute([$data['wh']]);
    $whId = $stmtWh->fetchColumn();

    // Penomoran Otomatis Harian untuk KP
    $todayDb = date('Y-m-d'); 
    $todayFormat = date('dmY'); 

    $stmtKp = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
    $stmtKp->execute([$todayDb]);
    $countKp = (int)$stmtKp->fetchColumn();
    $realKpNumber = "KP-" . $todayFormat . "-" . str_pad($countKp + 1, 2, '0', STR_PAD_LEFT);

    // KI Number otomatis NULL saat buat KP baru
    $realKiNumber = NULL;

    $sqlOrder = "INSERT INTO orders (
        kp_number, ki_number, brand, tipe_order, traffic_source, customer_name, customer_phone, customer_address, maps_link, 
        warehouse_source, delivery_date, grand_total, marketplace_fee, order_status, total_fee_r, total_fee_dc, marketing_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran', ?, ?, ?)";

    $stmtOrder = $pdo->prepare($sqlOrder);
    $stmtOrder->execute([
        $realKpNumber, $realKiNumber, $data['brand'], $data['tipe_order'] ?? 'Reguler',
        $data['traffic'], $data['customer'], $data['phone'], $data['address'], $data['maps'], 
        $data['wh'], $data['date_send'], $data['totals']['grand'], $data['totals']['marketplace_fee'],
        $data['fees']['r'], $data['fees']['dc'], $marketingId
    ]);

    $orderId = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, item_note, qty, deal_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtType = $pdo->prepare("SELECT type FROM products WHERE id = ?");
    $stmtStock = $pdo->prepare("UPDATE product_stocks SET stock = stock - ? WHERE product_id = ? AND warehouse_id = ? AND stock >= ?");

    foreach ($data['items'] as $item) {
        $prodId = $item['product_id'] ?? null;
        if ($prodId) {
            $stmtItem->execute([$orderId, $prodId, $item['name'], $item['qty'], $item['price'], $item['sub']]);
            $stmtType->execute([$prodId]);
            $pType = $stmtType->fetchColumn();

            if ($whId && $pType === 'goods') {
                $stmtStock->execute([$item['qty'], $prodId, $whId, $item['qty']]);
                if ($stmtStock->rowCount() == 0) {
                    throw new Exception("Stok tidak mencukupi untuk " . $item['name'] . " di Gudang " . $data['wh']);
                }
            }
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Faktur Barang Berhasil Dibuat']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>