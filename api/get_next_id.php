<?php
// File: api/get_next_id.php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$type = $_GET['type'] ?? 'kp';
$prefix = strtoupper($type);

// Ambil tanggal hari ini
$todayDb = date('Y-m-d');
$todayFormat = date('dmY');

try {
    // Hitung ada berapa transaksi HARI INI
    if ($prefix === 'KP') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE ki_number IS NOT NULL AND ki_number != '-' AND DATE(created_at) = ?");
    }
    
    $stmt->execute([$todayDb]);
    $count = (int)$stmt->fetchColumn();
    
    // Tambahkan 1 untuk nomor berikutnya dan format jadi 2 digit (01, 02, dst)
    $nextNum = $count + 1;
    $sequence = str_pad($nextNum, 2, '0', STR_PAD_LEFT);
    
    // Rangkai string ID baru
    $newId = "{$prefix}-{$todayFormat}-{$sequence}";
    
    echo json_encode(['status' => 'success', 'id' => $newId, 'seq' => $nextNum]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>