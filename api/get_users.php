<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Hanya Admin
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak']);
    exit;
}

try {
    // Ambil id, username, nama, role (Password jangan dikirim demi keamanan)
    $stmt = $pdo->query("SELECT id, username, full_name, role, created_at FROM users ORDER BY username ASC");
    $users = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $users]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>