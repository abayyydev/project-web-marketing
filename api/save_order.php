<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Baca data JSON mentah yang dikirim oleh Javascript
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data JSON tidak valid']);
    exit;
}

try {
    // 1. MULAI TRANSAKSI
    $pdo->beginTransaction();

    // --- STEP A: Cari ID Marketing ---
    // Di frontend kita kirim username, di sini kita cari ID-nya di tabel users
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmtUser->execute([$data['marketing_username'] ?? 'marketing']); // Default ke 'marketing' jika kosong
    $user = $stmtUser->fetch();
    $marketingId = $user ? $user['id'] : 1; // Fallback ke ID 1 jika user tidak ketemu

    // --- STEP B: Insert ke Tabel ORDERS (Header) ---
    $sqlOrder = "INSERT INTO orders (
        kp_number, ki_number, customer_name, customer_phone, customer_address, maps_link, 
        warehouse_source, delivery_date, grand_total, pay_status, 
        total_fee_r, total_fee_dc, marketing_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtOrder = $pdo->prepare($sqlOrder);
    $stmtOrder->execute([
        $data['kp_id'],
        ($data['ki_id'] !== '-' ? $data['ki_id'] : NULL), // Jika '-' kirim NULL
        $data['customer'],
        $data['phone'],
        $data['address'],
        $data['maps'],
        $data['wh'],
        $data['date_send'],
        $data['totals']['grand'], // Total Tagihan Akhir
        $data['pay_status'],
        $data['fees']['r'],  // Fee R
        $data['fees']['dc'], // Fee Dc
        $marketingId
    ]);

    // Ambil ID Order yang baru saja dibuat
    $orderId = $pdo->lastInsertId();

    // --- STEP C: Insert ke Tabel ORDER_ITEMS (Looping Barang) ---
    $sqlItem = "INSERT INTO order_items (order_id, product_id, qty, deal_price, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmtItem = $pdo->prepare($sqlItem);

    // Looping item belanjaan
    foreach ($data['items'] as $item) {
        // Pastikan product_id ada (dikirim dari frontend via atribut data-id di option)
        // Jika frontend mengirim product_id, gunakan itu.
        // Jika tidak, cari berdasarkan nama (opsional/fallback).

        $prodId = $item['product_id'] ?? null;

        if (!$prodId) {
            // Fallback cari ID kalau JS lupa kirim ID
            $stmtFindProd = $pdo->prepare("SELECT id FROM products WHERE name = ? LIMIT 1");
            $stmtFindProd->execute([$item['name']]);
            $prod = $stmtFindProd->fetch();
            $prodId = $prod ? $prod['id'] : null;
        }

        if ($prodId) {
            $stmtItem->execute([
                $orderId,
                $prodId,
                $item['qty'],
                $item['price'], // Ini harga Deal/Manual dari marketing
                $item['sub']
            ]);
        }
    }

    // --- STEP D: Insert ke Tabel INSTALLATIONS (Jika Ada) ---
    if ($data['ki_id'] !== '-' && !empty($data['install_info'])) {
        $info = $data['install_info'];
        $sqlInstall = "INSERT INTO installations (
            order_id, mandor_name, work_date, area_size, service_price, total_price
        ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmtInstall = $pdo->prepare($sqlInstall);
        $stmtInstall->execute([
            $orderId,
            $info['mandor'],
            $info['date'],
            $info['qty'],   // Luas Area
            $info['price'], // Harga Jasa per Meter
            $info['total']
        ]);
    }

    // --- STEP E: UPDATE COUNTER DI SETTINGS (Logic Baru) ---
    // 1. Naikkan counter KP (Pesanan Barang) selalu
    $stmtUpdKP = $pdo->prepare("UPDATE app_settings SET setting_value = setting_value + 1 WHERE setting_key = 'kp_counter'");
    $stmtUpdKP->execute();

    // 2. Naikkan counter KI (Instalasi) HANYA jika ada instalasi
    if ($data['ki_id'] !== '-') {
        $stmtUpdKI = $pdo->prepare("UPDATE app_settings SET setting_value = setting_value + 1 WHERE setting_key = 'ki_counter'");
        $stmtUpdKI->execute();
    }

    // 2. KOMIT TRANSAKSI (Simpan Permanen jika semua langkah sukses)
    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Transaksi Berhasil Disimpan', 'order_id' => $orderId]);

} catch (Exception $e) {
    // 3. ROLLBACK (Batalkan semua jika ada error sekecil apapun di salah satu langkah)
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan database: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString() // Untuk debug
    ]);
}
?>