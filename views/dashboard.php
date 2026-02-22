<?php
// Koneksi Database sudah tersedia via index.php -> require header
require_once 'config/database.php';

// Hitung Statistik Sederhana
try {
    // 1. Total Order (KP)
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalKP = $stmt->fetchColumn();

    // 2. Total Instalasi (KI)
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE ki_number IS NOT NULL");
    $totalKI = $stmt->fetchColumn();

    // 3. Omzet Lunas
    $stmt = $pdo->query("SELECT SUM(grand_total) FROM orders WHERE pay_status = 'Lunas'");
    $omzet = $stmt->fetchColumn() ?: 0;

} catch (PDOException $e) {
    $totalKP = 0;
    $totalKI = 0;
    $omzet = 0;
}
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500">Halo, <span
                class="font-bold text-green-700"><?= $_SESSION['user']['name'] ?></span>!</p>
    </div>
    <div class="text-right hidden md:block">
        <div class="text-xs text-gray-400 uppercase font-bold">Tanggal Hari Ini</div>
        <div class="font-bold text-gray-700"><?= date('d F Y') ?></div>
    </div>
</div>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
        <div class="text-gray-400 text-xs uppercase font-bold mb-1">Total Pesanan (KP)</div>
        <div class="text-3xl font-bold text-gray-800"><?= number_format($totalKP) ?></div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-orange-500">
        <div class="text-gray-400 text-xs uppercase font-bold mb-1">Tiket Instalasi (KI)</div>
        <div class="text-3xl font-bold text-gray-800"><?= number_format($totalKI) ?></div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
        <div class="text-gray-400 text-xs uppercase font-bold mb-1">Omzet Lunas</div>
        <div class="text-3xl font-bold text-green-600">Rp <?= number_format($omzet, 0, ',', '.') ?></div>
    </div>
</div>

<!-- Shortcut Button -->
<?php if ($_SESSION['user']['role'] === 'marketing'): ?>
    <div onclick="window.location.href='index.php?page=input_order'"
        class="bg-gradient-to-r from-green-600 to-teal-600 rounded-xl p-6 text-white shadow-lg cursor-pointer hover:shadow-xl transition flex justify-between items-center transform hover:-translate-y-1">
        <div>
            <h3 class="text-xl font-bold mb-1">Buat Pesanan Baru</h3>
            <p class="text-green-100 text-sm">Klik di sini untuk input transaksi Barang & Instalasi.</p>
        </div>
        <div class="bg-white/20 w-12 h-12 rounded-full flex items-center justify-center text-xl backdrop-blur-sm">
            <i class="fas fa-plus"></i>
        </div>
    </div>
<?php endif; ?>