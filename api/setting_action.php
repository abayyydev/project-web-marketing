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

try {
    // Update KP Counter
    if(isset($data['kp_counter'])) {
        $stmt = $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'kp_counter'");
        $stmt->execute([$data['kp_counter']]);
    }
    
    // Update KI Counter
    if(isset($data['ki_counter'])) {
        $stmt = $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'ki_counter'");
        $stmt->execute([$data['ki_counter']]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Pengaturan disimpan']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>