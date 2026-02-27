<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$role = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';

try {
    $sql = "
        SELECT m.*, p.name as product_name, p.unit, w.name as warehouse_name 
        FROM stock_mutations m
        JOIN products p ON m.product_id = p.id
        JOIN warehouses w ON m.warehouse_id = w.id
        WHERE m.mutation_type = 'out'
    ";

    $params = [];

    // Jika Admin Gudang, HANYA tampilkan barang keluar dari gudang dia saja
    if ($role === 'admin_gudang' && !empty($userWh)) {
        $sql .= " AND w.name = ?";
        $params[] = $userWh;
    }

    $sql .= " ORDER BY m.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>