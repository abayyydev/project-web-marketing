<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Ambil info user yang login
$role = $_SESSION['user']['role'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;

// Filter Tanggal
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');
// Filter Marketing (Optional, dari dropdown admin)
$marketingFilter = $_GET['marketing_id'] ?? '';

try {
    // --- 1. Query Data Transaksi ---
    $sql = "SELECT o.id, o.kp_number, o.created_at, o.customer_name, o.grand_total, o.pay_status, u.username as marketing
            FROM orders o
            JOIN users u ON o.marketing_id = u.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?";

    $params = [$start, $end];

    // LOGIKA FILTER
    if ($role === 'marketing') {
        // Marketing HANYA bisa lihat punya sendiri
        $sql .= " AND o.marketing_id = ?";
        $params[] = $userId;
    } elseif ($role === 'admin' && !empty($marketingFilter)) {
        // Admin BISA filter berdasarkan marketing tertentu
        $sql .= " AND o.marketing_id = ?";
        $params[] = $marketingFilter;
    }

    $sql .= " ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    // --- 2. Query Ringkasan (Summary) ---
    $sqlSum = "SELECT 
                COUNT(*) as total_qty,
                SUM(CASE WHEN pay_status = 'Lunas' THEN grand_total ELSE 0 END) as omzet_lunas,
                SUM(CASE WHEN pay_status = 'DP' THEN grand_total ELSE 0 END) as potensi_piutang
               FROM orders 
               WHERE DATE(created_at) BETWEEN ? AND ?";

    $paramsSum = [$start, $end];

    if ($role === 'marketing') {
        $sqlSum .= " AND marketing_id = ?";
        $paramsSum[] = $userId;
    } elseif ($role === 'admin' && !empty($marketingFilter)) {
        $sqlSum .= " AND marketing_id = ?";
        $paramsSum[] = $marketingFilter;
    }

    $stmtSum = $pdo->prepare($sqlSum);
    $stmtSum->execute($paramsSum);
    $summary = $stmtSum->fetch();

    echo json_encode([
        'status' => 'success',
        'data' => $transactions,
        'summary' => $summary,
        'user_role' => $role
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>