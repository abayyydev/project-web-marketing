<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// AUTO-MIGRASI: Tambahkan kolom yang dibutuhkan jika belum ada di database
try { $pdo->query("SELECT is_finance_verified FROM installations LIMIT 1"); }
catch (Exception $e) { $pdo->exec("ALTER TABLE installations ADD COLUMN is_finance_verified TINYINT(1) DEFAULT 1 AFTER payment_proof"); }

try { $pdo->query("SELECT estimasi_selesai FROM installations LIMIT 1"); }
catch (Exception $e) { $pdo->exec("ALTER TABLE installations ADD COLUMN estimasi_selesai DATE NULL AFTER work_date"); }

// Menampung Data (Bisa dari FormData/POST biasa, atau dari raw JSON)
$action = $_POST['action'] ?? '';
$installId = $_POST['install_id'] ?? 0;
$status = $_POST['status'] ?? '';
$mandor = $_POST['mandor_name'] ?? '';
$estimasi = $_POST['estimasi_selesai'] ?? '';

// Deteksi input JSON (Karena Javascript mengirimnya via JSON.stringify)
$json = file_get_contents('php://input');
if (!empty($json)) {
    $data = json_decode($json, true);
    if (isset($data['action'])) $action = $data['action'];
    if (isset($data['install_id'])) $installId = $data['install_id'];
    if (isset($data['status'])) $status = $data['status'];
    if (isset($data['mandor_name'])) $mandor = $data['mandor_name'];
    if (isset($data['estimasi_selesai'])) $estimasi = $data['estimasi_selesai'];
}

try {
    // 1. UPDATE STATUS BIASA (Tolak Pembayaran atau Ubah ke Sedang Dikerjakan)
    if ($action === 'update_status') {
        if (!empty($mandor) && !empty($estimasi)) {
            $stmt = $pdo->prepare("UPDATE installations SET status = ?, mandor_name = ?, estimasi_selesai = ? WHERE id = ?");
            $stmt->execute([$status, $mandor, $estimasi, $installId]);
        } else {
            $stmt = $pdo->prepare("UPDATE installations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $installId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Status pengerjaan berhasil diupdate.']);
    }
    
    // 2. VALIDASI KEUANGAN (Tombol Centang Hijau)
    elseif ($action === 'validasi_keuangan') {
        $stmtCheck = $pdo->prepare("SELECT status FROM installations WHERE id = ?");
        $stmtCheck->execute([$installId]);
        $currentStatus = $stmtCheck->fetchColumn();

        // Jika ini pembayaran pertama, jadwalkan. Jika cicilan lanjutan, biarkan statusnya.
        $newStatus = ($currentStatus === 'Menunggu Verifikasi') ? 'Dijadwalkan' : $currentStatus;

        // Tandai bahwa Keuangan sudah memvalidasinya
        $stmt = $pdo->prepare("UPDATE installations SET is_finance_verified = 1, status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $installId]);

        echo json_encode(['status' => 'success', 'message' => 'Dana Jasa Tervalidasi! Masuk ke jadwal pemasangan.']);
    }
    
    // 3. UPLOAD FOTO HASIL KERJA LAPANGAN
    elseif ($action === 'upload_work') {
        $workProofPath = null;

        if (isset($_FILES['work_proof']) && $_FILES['work_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/installations/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = strtolower(pathinfo($_FILES['work_proof']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $newFileName = 'WORK_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['work_proof']['tmp_name'], $uploadDir . $newFileName)) {
                    $workProofPath = 'uploads/installations/' . $newFileName;
                }
            }
        }

        if ($workProofPath) {
            $stmt = $pdo->prepare("UPDATE installations SET work_proof = ?, status = 'Selesai' WHERE id = ?");
            $stmt->execute([$workProofPath, $installId]);
            echo json_encode(['status' => 'success', 'message' => 'Foto hasil pemasangan berhasil disimpan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload foto. Format tidak sesuai.']);
        }
    } 
    
    // JIKA AKSI TIDAK DITEMUKAN
    else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid atau tidak dikenal oleh server.']);
    }
    
} catch (Exception $e) {
    // Tangkap error Database
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>