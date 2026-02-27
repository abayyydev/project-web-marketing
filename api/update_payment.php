<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// AUTO-MIGRASI KOLOM VERIFIKASI KEUANGAN (Bapak tidak perlu sentuh phpMyAdmin)
try { $pdo->query("SELECT is_finance_verified FROM orders LIMIT 1"); } 
catch (PDOException $e) { $pdo->exec("ALTER TABLE orders ADD COLUMN is_finance_verified TINYINT(1) DEFAULT 1 AFTER payment_proof"); }

$orderId = $_POST['order_id'] ?? '';
$payStatus = $_POST['pay_status'] ?? '';
$dpAmount = $_POST['dp_amount'] ?? 0;

if (empty($orderId) || empty($payStatus)) {
    echo json_encode(['status' => 'error', 'message' => 'Data order tidak lengkap.']); exit;
}

try {
    $stmtCheck = $pdo->prepare("SELECT order_status FROM orders WHERE id = ?");
    $stmtCheck->execute([$orderId]);
    $currentStatus = $stmtCheck->fetchColumn();

    // JANGAN mundurkan status operasional jika sudah diproses/dikirim
    $newOrderStatus = $currentStatus;
    if (in_array($currentStatus, ['Menunggu Pembayaran', 'Batal'])) {
        $newOrderStatus = 'Menunggu Verifikasi';
    }

    $paymentProofPath = null;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/payments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newFileName = 'PAY_' . time() . '_' . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $newFileName)) {
                $paymentProofPath = 'uploads/payments/' . $newFileName;
            }
        }
    }

    // UPDATE DATA: Selalu set is_finance_verified = 0 agar masuk antrean Keuangan
    if ($paymentProofPath) {
        $sql = "UPDATE orders SET pay_status=?, dp_amount=?, payment_proof=?, order_status=?, is_finance_verified=0 WHERE id=?";
        $pdo->prepare($sql)->execute([$payStatus, $dpAmount, $paymentProofPath, $newOrderStatus, $orderId]);
    } else {
        $sql = "UPDATE orders SET pay_status=?, dp_amount=?, order_status=?, is_finance_verified=0 WHERE id=?";
        $pdo->prepare($sql)->execute([$payStatus, $dpAmount, $newOrderStatus, $orderId]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil dikirim ke Keuangan!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>