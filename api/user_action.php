<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Hanya Super Admin yang boleh akses manajemen user
if(($_SESSION['user']['role'] ?? '') !== 'super_admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak!']); exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$action = $data['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'get') {
        // Ambil semua user untuk ditampilkan di tabel manajemen user
        $stmt = $pdo->query("SELECT id, username, full_name, role, warehouse_name FROM users ORDER BY role, full_name");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
    } 
    elseif ($action === 'add') {
        // Validasi input
        if(empty($data['username']) || empty($data['password'])) {
            throw new Exception("Username dan Password wajib diisi.");
        }
        
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role, warehouse_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['full_name'], 
            $data['username'], 
            $data['password'], 
            $data['role'],
            $data['warehouse_name'] // Menyimpan info dia admin cabang mana
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Akun baru berhasil dibuat!']);
    } 
    elseif ($action === 'edit') {
        $id = $data['id'];
        if (!empty($data['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, password=?, role=?, warehouse_name=? WHERE id=?");
            $stmt->execute([$data['full_name'], $data['username'], $data['password'], $data['role'], $data['warehouse_name'], $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, role=?, warehouse_name=? WHERE id=?");
            $stmt->execute([$data['full_name'], $data['username'], $data['role'], $data['warehouse_name'], $id]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Data user berhasil diperbarui.']);
    } 
    elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$data['id']]);
        echo json_encode(['status' => 'success', 'message' => 'User telah dihapus dari sistem.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>