<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Ambil info user yang login
$role = $_SESSION['user']['role'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;

// Tangkap Filter
$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end'] ?? date('Y-m-t');
$marketingFilter = $_GET['marketing_id'] ?? '';
$warehouseFilter = $_GET['warehouse'] ?? '';

// AUTO MIGRASI (Memastikan kolom pay_status ada di tabel installations jika belum ada)
try { $pdo->query("SELECT pay_status FROM installations LIMIT 1"); } 
catch (PDOException $e) { $pdo->exec("ALTER TABLE installations ADD COLUMN pay_status ENUM('Belum Bayar', 'DP', 'Lunas') DEFAULT 'Belum Bayar'"); }

try {
    // --- 1. Query Data Transaksi Gabungan (Order Induk & Instalasi) ---
    $sql = "SELECT o.id, o.kp_number, o.ki_number, o.created_at, o.customer_name, o.grand_total, o.pay_status, o.warehouse_source, u.full_name as marketing,
            i.total_price as nominal_jasa, i.pay_status as pay_status_ki
            FROM orders o
            JOIN users u ON o.marketing_id = u.id
            LEFT JOIN installations i ON o.id = i.order_id
            WHERE DATE(o.created_at) BETWEEN ? AND ?";
    
    $params = [$start, $end];

    // LOGIKA FILTER ROLE
    if ($role === 'marketing') {
        $sql .= " AND o.marketing_id = ?";
        $params[] = $userId;
    } elseif (in_array($role, ['admin', 'super_admin', 'keuangan'])) {
        if (!empty($marketingFilter)) {
            $sql .= " AND o.marketing_id = ?";
            $params[] = $marketingFilter;
        }
    }

    // Filter Gudang / Cabang
    if (!empty($warehouseFilter)) {
        $sql .= " AND o.warehouse_source = ?";
        $params[] = $warehouseFilter;
    }

    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 2. Pemisahan Data KP dan KI ---
    $dataKP = [];
    $dataKI = [];
    
    $sum_qty_kp = 0; $sum_omzet_kp = 0; $sum_piutang_kp = 0;
    $sum_qty_ki = 0; $sum_omzet_ki = 0; $sum_piutang_ki = 0;

    foreach ($transactions as $t) {
        // Harga Jasa
        $val_jasa = floatval($t['nominal_jasa'] ?? 0);
        // Harga Barang = Total Keseluruhan (Grand Total) - Harga Jasa
        $val_barang = floatval($t['grand_total']) - $val_jasa;

        // --- Susun Data Barang (KP) ---
        $dataKP[] = [
            'date' => $t['created_at'],
            'number' => $t['kp_number'],
            'customer' => $t['customer_name'],
            'marketing' => $t['marketing'],
            'warehouse' => $t['warehouse_source'],
            'pay_status' => $t['pay_status'],
            'total' => $val_barang
        ];
        $sum_qty_kp++;
        if ($t['pay_status'] === 'Lunas') $sum_omzet_kp += $val_barang;
        else $sum_piutang_kp += $val_barang;

        // --- Susun Data Jasa (KI) JIKA ADA ---
        if (!empty($t['ki_number']) && $t['ki_number'] !== '-') {
            $pay_ki = $t['pay_status_ki'] ?? 'Belum Bayar';
            
            $dataKI[] = [
                'date' => $t['created_at'],
                'number' => $t['ki_number'],
                'customer' => $t['customer_name'],
                'marketing' => $t['marketing'],
                'warehouse' => $t['warehouse_source'], // Diasumsikan cabang jasanya sama dengan barang
                'pay_status' => $pay_ki,
                'total' => $val_jasa
            ];
            $sum_qty_ki++;
            if ($pay_ki === 'Lunas') $sum_omzet_ki += $val_jasa;
            else $sum_piutang_ki += $val_jasa;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data_kp' => $dataKP,
        'data_ki' => $dataKI,
        'summary' => [
            'qty' => $sum_qty_kp + $sum_qty_ki,
            'omzet_kp' => $sum_omzet_kp,
            'omzet_ki' => $sum_omzet_ki,
            'omzet_total' => $sum_omzet_kp + $sum_omzet_ki,
            'piutang' => $sum_piutang_kp + $sum_piutang_ki
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>