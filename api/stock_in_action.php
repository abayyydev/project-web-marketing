<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$role = $_SESSION['user']['role'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'get') {
        // Semua Role (Super Admin & Admin Gudang) bisa melihat riwayat semua cabang
        $sql = "SELECT m.*, p.name as product_name, p.unit, w.name as warehouse_name 
                FROM stock_mutations m
                JOIN products p ON m.product_id = p.id
                JOIN warehouses w ON m.warehouse_id = w.id
                WHERE m.mutation_type = 'in'
                ORDER BY m.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    } elseif ($action === 'add') {
        $pdo->beginTransaction();
        
        // 1. Catat ke Mutasi
        $stmt = $pdo->prepare("INSERT INTO stock_mutations (product_id, warehouse_id, mutation_type, qty, reference_no) VALUES (?, ?, 'in', ?, ?)");
        $stmt->execute([$data['product_id'], $data['warehouse_id'], $data['qty'], $data['reference_no']]);

        // 2. Update Stok Utama
        $stmtStock = $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, stock) VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock)");
        $stmtStock->execute([$data['product_id'], $data['warehouse_id'], $data['qty']]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stok berhasil ditambahkan.']);
        
    } elseif ($action === 'edit') {
        // FITUR BARU: Edit Riwayat Stok Masuk
        $pdo->beginTransaction();
        
        $mutationId = $data['mutation_id'];
        $newQty = (float)$data['qty'];
        $newRef = $data['reference_no'];

        // 1. Ambil data mutasi lama untuk mengecek selisih Qty
        $stmtGet = $pdo->prepare("SELECT product_id, warehouse_id, qty FROM stock_mutations WHERE id = ? AND mutation_type = 'in'");
        $stmtGet->execute([$mutationId]);
        $oldData = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$oldData) throw new Exception("Data riwayat tidak ditemukan.");

        $oldQty = (float)$oldData['qty'];
        $diff = $newQty - $oldQty; // Hitung selisih (bisa minus jika qty baru lebih kecil)

        // 2. Update tabel Mutasi
        $stmtUpd = $pdo->prepare("UPDATE stock_mutations SET qty = ?, reference_no = ? WHERE id = ?");
        $stmtUpd->execute([$newQty, $newRef, $mutationId]);

        // 3. Update tabel Stok Utama berdasarkan selisih
        $stmtStock = $pdo->prepare("UPDATE product_stocks SET stock = stock + ? WHERE product_id = ? AND warehouse_id = ?");
        $stmtStock->execute([$diff, $oldData['product_id'], $oldData['warehouse_id']]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Riwayat dan stok berhasil diperbarui.']);
    }
} catch (Exception $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>