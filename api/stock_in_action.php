<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$role = $_SESSION['user']['role'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'get') {
        // Logika Riwayat: Admin Gudang hanya lihat riwayat gudang dia
        $sql = "SELECT m.*, p.name as product_name, p.unit, w.name as warehouse_name 
                FROM stock_mutations m
                JOIN products p ON m.product_id = p.id
                JOIN warehouses w ON m.warehouse_id = w.id
                WHERE m.mutation_type = 'in'";
        
        $params = [];
        if ($role === 'admin_gudang') {
            $sql .= " AND w.name = ?";
            $params[] = $_SESSION['user']['warehouse_name'];
        }

        $sql .= " ORDER BY m.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);

    } elseif ($action === 'add') {
        $pdo->beginTransaction();
        
        // 1. Catat ke Mutasi
        $stmt = $pdo->prepare("INSERT INTO stock_mutations (product_id, warehouse_id, mutation_type, qty, reference_no) VALUES (?, ?, 'in', ?, ?)");
        $stmt->execute([$data['product_id'], $data['warehouse_id'], $data['qty'], $data['reference_no']]);

        // 2. Update Stok Utama (Gunakan ON DUPLICATE KEY agar aman)
        $stmtStock = $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, stock) VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock)");
        $stmtStock->execute([$data['product_id'], $data['warehouse_id'], $data['qty']]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stok berhasil ditambahkan.']);
    }
} catch (Exception $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>