<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $userRole = $_SESSION['user']['role'] ?? '';
    $userId = $_SESSION['user']['id'] ?? 0;

    $sql = "SELECT o.id, o.kp_number, o.ki_number, o.customer_name, o.grand_total, o.pay_status, o.order_status, o.created_at, u.username as marketing_name 
            FROM orders o 
            JOIN users u ON o.marketing_id = u.id ";

    // Jika marketing, hanya lihat punya sendiri
    if ($userRole === 'marketing') {
        $sql .= "WHERE o.marketing_id = :mid ";
    }

    $sql .= "ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);

    if ($userRole === 'marketing') {
        $stmt->execute(['mid' => $userId]);
    } else {
        $stmt->execute();
    }

    $orders = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $orders]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>