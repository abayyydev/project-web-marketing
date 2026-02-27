<?php
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

    // Cari ID Gudang yang dipilih
    $stmtWh = $pdo->prepare("SELECT id FROM warehouses WHERE name = ?");
    $stmtWh->execute([$data['wh']]);
    $whId = $stmtWh->fetchColumn();

    // Insert ke Tabel ORDERS
    $sqlOrder = "INSERT INTO orders (
        kp_number, ki_number, brand, tipe_order, traffic_source, customer_name, customer_phone, customer_address, maps_link, 
        warehouse_source, delivery_date, grand_total, marketplace_fee, order_status, total_fee_r, total_fee_dc, marketing_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran', ?, ?, ?)";

    $stmtOrder = $pdo->prepare($sqlOrder);
    $stmtOrder->execute([
        $data['kp_id'], ($data['ki_id'] !== '-' ? $data['ki_id'] : NULL), $data['brand'], $data['tipe_order'] ?? 'Reguler',
        $data['traffic'], $data['customer'], $data['phone'], $data['address'], $data['maps'], 
        $data['wh'], $data['date_send'], $data['totals']['grand'], $data['totals']['marketplace_fee'],
        $data['fees']['r'], $data['fees']['dc'], $marketingId
    ]);

    $orderId = $pdo->lastInsertId();

    // AUTO-MIGRASI KOLOM ITEM_NOTE (Bapak tidak perlu sentuh database manual)
    // Kolom ini akan menyimpan catatan ukuran potong spesifik per item
    try { $pdo->query("SELECT item_note FROM order_items LIMIT 1"); }
    catch (Exception $e) { $pdo->exec("ALTER TABLE order_items ADD COLUMN item_note VARCHAR(255) NULL AFTER product_id"); }

    // Insert Items DAN Kurangi Stok
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, item_note, qty, deal_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtType = $pdo->prepare("SELECT type FROM products WHERE id = ?");
    $stmtStock = $pdo->prepare("UPDATE product_stocks SET stock = stock - ? WHERE product_id = ? AND warehouse_id = ? AND stock >= ?");

    foreach ($data['items'] as $item) {
        $prodId = $item['product_id'] ?? null;
        if ($prodId) {
            // Kita simpan $item['name'] ke kolom item_note karena dari frontend JS sudah berisi tambahan info [Ukuran: 2mx5m]
            $stmtItem->execute([$orderId, $prodId, $item['name'], $item['qty'], $item['price'], $item['sub']]);
            
            // Cek apakah barang fisik
            $stmtType->execute([$prodId]);
            $pType = $stmtType->fetchColumn();

            if ($whId && $pType === 'goods') {
                // Kurangi stok di gudang bersangkutan
                $stmtStock->execute([$item['qty'], $prodId, $whId, $item['qty']]);
                if ($stmtStock->rowCount() == 0) {
                    // JIKA STOK KURANG, BATALKAN SEMUA TRANSAKSI (ROLLBACK)
                    throw new Exception("SISTEM MENOLAK: Stok tidak mencukupi untuk produk " . $item['name'] . " di Gudang " . $data['wh']);
                }
            }
        }
    }

    // Insert Instalasi
    if ($data['ki_id'] !== '-' && !empty($data['install_info'])) {
        $info = $data['install_info'];
        $stmtInstall = $pdo->prepare("INSERT INTO installations (order_id, mandor_name, work_date, area_size, service_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtInstall->execute([$orderId, $info['mandor'], $info['date'], $info['qty'], $info['price'], $info['total']]);
    }

    // Update Counter
    $pdo->prepare("UPDATE app_settings SET setting_value = setting_value + 1 WHERE setting_key = 'kp_counter'")->execute();
    if ($data['ki_id'] !== '-') $pdo->prepare("UPDATE app_settings SET setting_value = setting_value + 1 WHERE setting_key = 'ki_counter'")->execute();

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Transaksi Berhasil & Stok Gudang Dikurangi']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>