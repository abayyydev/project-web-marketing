<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Cek User & Role
$userRole = $_SESSION['user']['role'] ?? '';
$userId   = $_SESSION['user']['id'] ?? 0;
$isAdmin  = $userRole === 'admin';

// Tangkap Data JSON dari Frontend
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$action = $data['action'] ?? '';
$orderId = $data['order_id'] ?? 0;

// Validasi ID
if (!$orderId) { 
    echo json_encode(['status' => 'error', 'message' => 'ID Pesanan tidak valid']); 
    exit; 
}

try {
    // 1. Ambil Info Pesanan Saat Ini dari Database
    $stmtCheck = $pdo->prepare("SELECT order_status, marketing_id FROM orders WHERE id = ?");
    $stmtCheck->execute([$orderId]);
    $order = $stmtCheck->fetch();

    if (!$order) { 
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan di database']); 
        exit; 
    }

    // --- LOGIKA HAPUS (DELETE) ---
    if ($action === 'delete') {
        $isOwner = ($order['marketing_id'] == $userId);
        
        // Di alur baru, status awal adalah "Menunggu Pembayaran"
        $isPending = ($order['order_status'] === 'Menunggu Pembayaran');

        // Boleh hapus JIKA Admin, ATAU (Marketing yang punya orderan DAN belum dibayar)
        if ($isAdmin || ($isOwner && $isPending)) {
            // Hapus Child Data dulu
            $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
            $pdo->prepare("DELETE FROM installations WHERE order_id = ?")->execute([$orderId]);
            // Hapus Parent Data
            $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
            
            echo json_encode(['status' => 'success', 'message' => 'Faktur pesanan berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak! Pesanan sudah diproses atau bukan milik Anda.']);
        }

    // --- LOGIKA UPDATE STATUS (ALUR ERP BARU) ---
    } elseif ($action === 'update_status') {
        $newStatus = $data['status'] ?? 'Menunggu Pembayaran';
        
        $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
        echo json_encode(['status' => 'success', 'message' => "Status berhasil diubah menjadi: $newStatus"]);

    // --- LOGIKA VALIDASI KEUANGAN (UPDATE TERBARU) ---
    } elseif ($action === 'validasi_keuangan') {
        // Gunakan status saat ini yang sudah diambil di atas
        $currentStatus = $order['order_status'];
        
        // Jika ini adalah verifikasi pertama kali, ubah ke 'Diproses' (Maju ke Gudang)
        $newStatus = ($currentStatus === 'Menunggu Verifikasi') ? 'Diproses' : $currentStatus;
        
        // Hapus flag penanda cicilan (is_finance_verified = 1) agar hilang dari antrean Keuangan
        $pdo->prepare("UPDATE orders SET is_finance_verified = 1, order_status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
        
        echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil divalidasi!']);

    // --- LOGIKA VERIFIKASI (BACKWARD COMPATIBILITY / JAGA-JAGA) ---
    } elseif (($action === 'verify' || $action === 'reject') && $isAdmin) {
        $status = ($action === 'verify') ? 'Diproses' : 'Batal';
        $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$status, $orderId]);
        echo json_encode(['status' => 'success', 'message' => "Pesanan $status"]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak diizinkan atau perintah tidak dikenal.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>