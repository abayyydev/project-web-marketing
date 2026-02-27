<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$role = $_SESSION['user']['role'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;

try {
    $sql = "SELECT i.*, o.customer_name, o.ki_number, o.created_at as order_date, u.full_name as marketing_name 
            FROM installations i 
            JOIN orders o ON i.order_id = o.id 
            LEFT JOIN users u ON o.marketing_id = u.id";
    
    $params = [];
    if ($role === 'marketing') {
        $sql .= " WHERE o.marketing_id = ?";
        $params[] = $userId;
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ki_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $ki_data]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>