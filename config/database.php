<?php
$host = "localhost";
$user = "root";      // Default XAMPP
$pass = "";          // Default kosong
$db = "db_greengrass";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    // Mode error: Lemparkan exception jika ada masalah (Penting untuk debugging)
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // Mode fetch default: Array Asosiatif (['nama' => 'Budi'])
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Matikan emulasi prepare statement (Lebih aman dari SQL Injection)
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Koneksi Berhasil!"; // Uncomment untuk tes
} catch (\PDOException $e) {
    // Jika gagal, hentikan script dan tampilkan error
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}
?>