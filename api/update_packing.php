<?php
session_start();
require_once '../config/database.php';

// Pastikan request adalah POST (FormData)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}

$orderId = $_POST['order_id'] ?? '';

if (empty($orderId)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Order tidak ditemukan']);
    exit;
}

try {
    $packingProofPath = null;
    
    // Proses Upload File Fisik (Foto Packing)
    if (isset($_FILES['packing_proof']) && $_FILES['packing_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/shipping/'; // Gabung di folder shipping
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileTmpPath = $_FILES['packing_proof']['tmp_name'];
        $fileName = $_FILES['packing_proof']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if(in_array($fileExtension, $allowedExts)) {
            $newFileName = 'PACK_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $destPath)) {
                $packingProofPath = 'uploads/shipping/' . $newFileName;
            }
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Format file harus JPG, PNG, atau WEBP']);
             exit;
        }
    }

    // Jika berhasil upload foto, ubah status menjadi "Paket Siap"
    if ($packingProofPath) {
        $newStatus = 'Paket Siap';
        $sql = "UPDATE orders SET packing_proof = ?, order_status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$packingProofPath, $newStatus, $orderId]);
        
        echo json_encode(['status' => 'success', 'message' => 'Foto packing berhasil diunggah! Status: Paket Siap.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah foto. Pastikan file valid.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>