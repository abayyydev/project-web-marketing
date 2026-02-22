<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Keamanan: Hanya Admin yang boleh akses
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$action = $data['action'] ?? '';

try {
    // --- TAMBAH PRODUK ---
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO products (code, name, unit, base_price, fee_amount, fee_code, type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['code'],
            $data['name'],
            $data['unit'],
            $data['price'],
            $data['fee'],
            $data['fee_code'],
            $data['type']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan']);

        // --- EDIT PRODUK ---
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE products SET code=?, name=?, unit=?, base_price=?, fee_amount=?, fee_code=?, type=? WHERE id=?");
        $stmt->execute([
            $data['code'],
            $data['name'],
            $data['unit'],
            $data['price'],
            $data['fee'],
            $data['fee_code'],
            $data['type'],
            $data['id']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Data produk diperbarui']);

        // --- HAPUS PRODUK ---
    } elseif ($action === 'delete') {
        // Cek dulu apakah produk sudah pernah dipesan? Kalau sudah, jangan dihapus sembarangan
        $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
        $check->execute([$data['id']]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Produk ini ada di riwayat transaksi. Tidak bisa dihapus!']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['status' => 'success', 'message' => 'Produk dihapus']);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>