<?php
session_start();
require_once '../config/database.php';

// Pastikan request adalah POST (FormData)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}

$orderId = $_POST['order_id'] ?? '';
$courier = $_POST['courier_name'] ?? '';
$resiNum = $_POST['resi_number'] ?? '';

if (empty($orderId)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Order tidak ditemukan']);
    exit;
}

if (empty($courier)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama Kurir wajib diisi!']);
    exit;
}

try {
    $resiProofPath = null;
    
    // Proses Upload File Fisik (Foto Resi)
    if (isset($_FILES['resi_proof']) && $_FILES['resi_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/shipping/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileTmpPath = $_FILES['resi_proof']['tmp_name'];
        $fileName = $_FILES['resi_proof']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if(in_array($fileExtension, $allowedExts)) {
            $newFileName = 'RESI_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $destPath)) {
                $resiProofPath = 'uploads/shipping/' . $newFileName;
            }
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Format file resi harus JPG, PNG, atau WEBP']);
             exit;
        }
    }

    $newStatus = 'Dikirim';

    // Jika upload foto resi berhasil
    if ($resiProofPath) {
        $sql = "UPDATE orders SET courier_name = ?, resi_number = ?, resi_proof = ?, order_status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$courier, $resiNum, $resiProofPath, $newStatus, $orderId]);
    } else {
        // Jika hanya input teks resi tanpa foto
        $sql = "UPDATE orders SET courier_name = ?, resi_number = ?, order_status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$courier, $resiNum, $newStatus, $orderId]);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Data pengiriman berhasil disimpan! Status: Dikirim.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>