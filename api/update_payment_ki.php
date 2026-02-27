<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// AUTO-MIGRASI KOLOM VERIFIKASI KEUANGAN
try { $pdo->query("SELECT is_finance_verified FROM installations LIMIT 1"); } 
catch (PDOException $e) { $pdo->exec("ALTER TABLE installations ADD COLUMN is_finance_verified TINYINT(1) DEFAULT 1 AFTER payment_proof"); }

$installId = $_POST['install_id'] ?? '';
$payStatus = $_POST['pay_status'] ?? '';
$dpAmount = $_POST['dp_amount'] ?? 0;

if (empty($installId) || empty($payStatus)) {
    echo json_encode(['status' => 'error', 'message' => 'Data instalasi tidak lengkap.']); exit;
}

try {
    $stmtCheck = $pdo->prepare("SELECT status FROM installations WHERE id = ?");
    $stmtCheck->execute([$installId]);
    $currentStatus = $stmtCheck->fetchColumn();

    $newStatus = $currentStatus;
    if (in_array($currentStatus, ['Menunggu Pembayaran', 'Batal'])) {
        $newStatus = 'Menunggu Verifikasi';
    }

    $paymentProofPath = null;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/payments_ki/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newFileName = 'PAY_KI_' . time() . '_' . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $newFileName)) {
                $paymentProofPath = 'uploads/payments_ki/' . $newFileName;
            }
        }
    }

    if ($paymentProofPath) {
        $sql = "UPDATE installations SET pay_status=?, dp_amount=?, payment_proof=?, status=?, is_finance_verified=0 WHERE id=?";
        $pdo->prepare($sql)->execute([$payStatus, $dpAmount, $paymentProofPath, $newStatus, $installId]);
    } else {
        $sql = "UPDATE installations SET pay_status=?, dp_amount=?, status=?, is_finance_verified=0 WHERE id=?";
        $pdo->prepare($sql)->execute([$payStatus, $dpAmount, $newStatus, $installId]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Cicilan Instalasi dikirim ke Keuangan!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>