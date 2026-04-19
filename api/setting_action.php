<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$role = $_SESSION['user']['role'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';
$isSuperAdmin = ($role === 'super_admin' || $uLogin === 'admin');

// Proteksi Ganda: Hanya Super Admin / Admin Utama
if (!$isSuperAdmin) {
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak! Khusus Super Admin.']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

try {
    // 1. Update KP Counter (Faktur Barang)
    if(isset($data['kp_counter'])) {
        $stmt = $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'kp_counter'");
        $stmt->execute([$data['kp_counter']]);
        
        // Auto Insert jika row belum ada di database
        if($stmt->rowCount() === 0) {
            $check = $pdo->query("SELECT 1 FROM app_settings WHERE setting_key = 'kp_counter'")->fetch();
            if(!$check) {
                $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('kp_counter', ?)")->execute([$data['kp_counter']]);
            }
        }
    }
    
    // 2. Update KI Counter (Faktur Instalasi)
    if(isset($data['ki_counter'])) {
        $stmt = $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'ki_counter'");
        $stmt->execute([$data['ki_counter']]);
        
        // Auto Insert jika row belum ada di database
        if($stmt->rowCount() === 0) {
            $check = $pdo->query("SELECT 1 FROM app_settings WHERE setting_key = 'ki_counter'")->fetch();
            if(!$check) {
                $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('ki_counter', ?)")->execute([$data['ki_counter']]);
            }
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pengaturan nomor urut berhasil disimpan.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>