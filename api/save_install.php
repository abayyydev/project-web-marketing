<?php
// File: api/save_install.php
header('Content-Type: application/json');
require_once '../config/database.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['order_id'])) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']); exit;
}

try {
    $pdo->beginTransaction();

    // Generate KI Number (Reset Harian)
    $todayDb = date('Y-m-d');
    $todayFormat = date('dmY');

    // Cek jumlah instalasi yang dibuat hari ini
    $stmtKi = $pdo->prepare("SELECT COUNT(*) FROM installations WHERE DATE(created_at) = ?");
    $stmtKi->execute([$todayDb]);
    $countKi = (int)$stmtKi->fetchColumn();
    $realKiNumber = "KI-" . $todayFormat . "-" . str_pad($countKi + 1, 2, '0', STR_PAD_LEFT);

    // 1. Update orders table (Set ki_number)
    $stmtUpd = $pdo->prepare("UPDATE orders SET ki_number = ? WHERE id = ? AND (ki_number IS NULL OR ki_number = '-')");
    $stmtUpd->execute([$realKiNumber, $data['order_id']]);

    if($stmtUpd->rowCount() === 0) {
        throw new Exception("Faktur sudah memiliki instalasi atau tidak ditemukan.");
    }

    // 2. Insert into installations
    $stmtIns = $pdo->prepare("INSERT INTO installations (order_id, mandor_name, work_date, area_size, service_price, total_price, pay_status) VALUES (?, ?, ?, ?, ?, ?, 'Belum Bayar')");
    $stmtIns->execute([
        $data['order_id'], 
        $data['mandor'], 
        $data['date'], 
        $data['qty'], 
        $data['price'], 
        $data['total']
    ]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'ki_number' => $realKiNumber, 'message' => 'Instalasi Berhasil Dibuat']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>