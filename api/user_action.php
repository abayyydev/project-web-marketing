<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$action = $data['action'] ?? '';

try {
    // --- TAMBAH USER ---
    if ($action === 'add') {
        // Cek username kembar
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$data['username']]);
        if ($check->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username sudah dipakai!']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            $data['password'], // Disimpan plain text sesuai sistem login yg sudah dibuat
            $data['fullname'],
            $data['role']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'User baru ditambahkan']);

        // --- EDIT USER ---
    } elseif ($action === 'edit') {
        // Jika password diisi, update password. Jika kosong, biarkan password lama.
        if (!empty($data['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, role=?, password=? WHERE id=?");
            $stmt->execute([$data['fullname'], $data['role'], $data['password'], $data['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, role=? WHERE id=?");
            $stmt->execute([$data['fullname'], $data['role'], $data['id']]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Data user diperbarui']);

        // --- HAPUS USER ---
    } elseif ($action === 'delete') {
        if ($data['id'] == $_SESSION['user']['id']) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak bisa menghapus akun sendiri!']);
            exit;
        }

        // Cek apakah user ini punya riwayat transaksi?
        $checkOrder = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE marketing_id = ?");
        $checkOrder->execute([$data['id']]);
        if ($checkOrder->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'User ini memiliki riwayat pesanan. Tidak bisa dihapus.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['status' => 'success', 'message' => 'User dihapus']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>