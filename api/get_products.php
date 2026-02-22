<?php
// Header agar browser tahu ini respon JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    // PERBAIKAN: Menambahkan kolom 'code' di setelah 'id'
    $stmt = $pdo->prepare("SELECT id, code, name, unit, base_price, fee_amount, fee_code, type FROM products ORDER BY name ASC");
    $stmt->execute();

    $products = $stmt->fetchAll();

    // Kirim data JSON ke frontend
    echo json_encode([
        'status' => 'success',
        'data' => $products
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>