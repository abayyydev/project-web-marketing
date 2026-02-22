<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$type = $_GET['type'] ?? 'kp'; // 'kp' atau 'ki'
$date = $_GET['date'] ?? date('Y-m-d');

try {
    // 1. Ambil Counter Terakhir dari DB
    $key = ($type === 'ki') ? 'ki_counter' : 'kp_counter';
    $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $lastNum = $stmt->fetchColumn() ?: 0;

    // 2. Tambah 1 untuk ID Baru
    $nextNum = $lastNum + 1;

    // 3. Format Tanggal (281125)
    $d = new DateTime($date);
    $dateCode = date('dmy', strtotime($date)); // 281125

    // 4. Susun Kode (KP-281125-01)
    // Pad left dengan 0 (misal 1 jadi 01)
    $padNum = str_pad($nextNum, 2, '0', STR_PAD_LEFT);
    $prefix = strtoupper($type);

    $generatedID = "$prefix-$dateCode-$padNum";

    echo json_encode(['status' => 'success', 'id' => $generatedID, 'seq' => $nextNum]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'id' => 'ERROR']);
}
?>