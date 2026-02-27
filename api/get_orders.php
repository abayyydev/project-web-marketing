<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$role = $_SESSION['user']['role'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;
$userWarehouse = $_SESSION['user']['warehouse_name'] ?? '';

try {
    $sql = "SELECT o.*, u.full_name as marketing_name 
            FROM orders o 
            LEFT JOIN users u ON o.marketing_id = u.id 
            WHERE 1=1";
    
    $params = [];

    // LOGIKA PENGUNCIAN DATA PER ROLE
    if ($role === 'marketing') {
        // Marketing hanya boleh melihat pesanannya sendiri
        $sql .= " AND o.marketing_id = ?";
        $params[] = $userId;
    } elseif ($role === 'admin_gudang') {
        // Admin Gudang HANYA boleh melihat data cabang dia saja
        if (!empty($userWarehouse)) {
            $sql .= " AND o.warehouse_source = ?";
            $params[] = $userWarehouse;
        } else {
            // Jika dia admin gudang tapi tidak punya penugasan cabang, tampilkan kosong
            $sql .= " AND 1=0";
        }
    }
    // super_admin dan keuangan bebas melihat semua cabang

    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $orders]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>