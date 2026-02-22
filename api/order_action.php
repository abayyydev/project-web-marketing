<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$userRole = $_SESSION['user']['role'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;
$isAdmin = $userRole === 'admin';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$action = $data['action'] ?? '';
$orderId = $data['order_id'] ?? 0;

if (!$orderId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

try {
    // 1. Ambil Info Pesanan Dulu
    $stmtCheck = $pdo->prepare("SELECT order_status, marketing_id FROM orders WHERE id = ?");
    $stmtCheck->execute([$orderId]);
    $order = $stmtCheck->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan']);
        exit;
    }

    // --- LOGIKA HAPUS ---
    if ($action === 'delete') {
        // Aturan:
        // 1. Admin BEBAS hapus kapanpun.
        // 2. Marketing HANYA BOLEH hapus punya sendiri DAN status masih Pending.

        $isOwner = ($order['marketing_id'] == $userId);
        $isPending = ($order['order_status'] === 'Pending');

        if ($isAdmin || ($isOwner && $isPending)) {
            // Hapus Child dulu
            $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
            $pdo->prepare("DELETE FROM installations WHERE order_id = ?")->execute([$orderId]);
            // Hapus Header
            $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);

            echo json_encode(['status' => 'success', 'message' => 'Pesanan berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal! Pesanan sudah diverifikasi admin atau bukan milik Anda.']);
        }

        // --- LOGIKA VERIFIKASI (Admin Only) ---
    } elseif (($action === 'verify' || $action === 'reject') && $isAdmin) {
        $status = ($action === 'verify') ? 'Verified' : 'Rejected';
        $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$status, $orderId]);
        echo json_encode(['status' => 'success', 'message' => "Status diubah menjadi $status"]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>