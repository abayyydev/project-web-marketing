<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$json = file_get_contents('php://input');
$data = json_decode($json, true);
if(!$action && isset($data['action'])) $action = $data['action'];

if ($action === 'get') {
    try {
        $stmt = $pdo->query("SELECT * FROM warehouses ORDER BY id ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
} elseif ($action === 'add') {
    $name = $data['name'] ?? '';
    $address = $data['address'] ?? ''; 
    
    if($name) {
        try {
            $pdo->prepare("INSERT INTO warehouses (name, address) VALUES (?, ?)")->execute([$name, $address]);
            $newWhId = $pdo->lastInsertId();
            
            // Otomatis buatkan slot stok 0 untuk semua produk di gudang baru ini
            $pdo->prepare("INSERT IGNORE INTO product_stocks (product_id, warehouse_id, stock) SELECT id, ?, 0 FROM products")->execute([$newWhId]);
            
            echo json_encode(['status' => 'success', 'message' => 'Cabang Gudang berhasil ditambahkan!']);
        } catch(Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal: Nama gudang mungkin sudah ada.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal: Nama cabang wajib diisi!']);
    }

} elseif ($action === 'edit') {
    $id = $data['id'] ?? 0;
    $name = $data['name'] ?? '';
    $address = $data['address'] ?? ''; 
    
    if($id && $name) {
        try {
            $pdo->prepare("UPDATE warehouses SET name = ?, address = ? WHERE id = ?")->execute([$name, $address, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Data Gudang berhasil diperbarui!']);
        } catch(Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal: Nama gudang mungkin sudah dipakai cabang lain.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
    }

} elseif ($action === 'delete') {
    $id = $data['id'] ?? 0;
    try {
        $pdo->prepare("DELETE FROM warehouses WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Gudang berhasil dihapus!']);
    } catch(Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus gudang.']);
    }
}
?>