<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

try {
    // Cari user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifikasi (Untuk demo ini password plain text sesuai seed data Anda)
    // Nanti ganti pakai password_verify($password, $user['password']) jika sudah di-hash
    if ($user && $password === $user['password']) {

        // Simpan sesi
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['full_name'],
            'role' => $user['role']
        ];

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>