<?php
require_once 'config/database.php';

$role = $_SESSION['user']['role'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;
$userWh = $_SESSION['user']['warehouse_name'] ?? '';

// Definisi Hak Akses
$isGudang = ($role === 'admin_gudang');
$isMarketing = ($role === 'marketing');
$isSuper = ($role === 'super_admin' || $role === 'admin' || $role === 'keuangan' || $_SESSION['user']['username'] === 'admin');

// --- INISIALISASI VARIABEL ---
$totalKP = 0; $totalKI = 0; $omzet = 0;
$pending = 0; $proses = 0; $dikirim = 0;
$recentOrders = [];

// Variabel Grafik
$salesLabels = []; $salesData = [];
$markLabels = []; $markData = [];

// --- LOGIKA QUERY BERDASARKAN ROLE ---
try {
    if ($isGudang) {
        // ==========================================
        // QUERY KHUSUS ADMIN GUDANG
        // ==========================================
        $stmtGudang = $pdo->prepare("SELECT order_status, COUNT(*) as cnt FROM orders WHERE warehouse_source = ? GROUP BY order_status");
        $stmtGudang->execute([$userWh]);
        $whStats = $stmtGudang->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $pending = ($whStats['Diproses'] ?? 0) + ($whStats['Sedang Dipack'] ?? 0);
        $proses = $whStats['Paket Siap'] ?? 0; // Siap Kirim
        $dikirim = $whStats['Dikirim'] ?? 0;
        
        $stmtRecent = $pdo->prepare("SELECT kp_number, customer_name, order_status, created_at FROM orders WHERE warehouse_source = ? AND order_status IN ('Diproses', 'Sedang Dipack', 'Paket Siap', 'Dikirim') ORDER BY created_at DESC LIMIT 5");
        $stmtRecent->execute([$userWh]);
        $recentOrders = $stmtRecent->fetchAll();

    } else {
        // ==========================================
        // QUERY UNTUK SUPER ADMIN / KEUANGAN / MARKETING
        // ==========================================
        $where = ($isMarketing) ? "WHERE marketing_id = ?" : "WHERE 1=1";
        $whereAnd = ($isMarketing) ? "AND marketing_id = ?" : "";
        $params = ($isMarketing) ? [$userId] : [];

        // 1. Total Order & Instalasi
        $stmt1 = $pdo->prepare("SELECT COUNT(*) FROM orders $where");
        $stmt1->execute($params);
        $totalKP = $stmt1->fetchColumn();

        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE ki_number IS NOT NULL AND ki_number != '-' $whereAnd");
        $stmt2->execute($params);
        $totalKI = $stmt2->fetchColumn();

        // 2. Omzet Lunas
        $stmt3 = $pdo->prepare("SELECT SUM(grand_total) FROM orders WHERE pay_status = 'Lunas' $whereAnd");
        $stmt3->execute($params);
        $omzet = $stmt3->fetchColumn() ?: 0;

        // 3. Ringkasan Status
        $stmt4 = $pdo->prepare("SELECT 
            SUM(CASE WHEN order_status = 'Menunggu Pembayaran' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN order_status IN ('Menunggu Verifikasi', 'Diproses', 'Sedang Dipack', 'Paket Siap') THEN 1 ELSE 0 END) as proses,
            SUM(CASE WHEN order_status = 'Dikirim' THEN 1 ELSE 0 END) as dikirim
            FROM orders $where");
        $stmt4->execute($params);
        $statusCounts = $stmt4->fetch();

        $pending = $statusCounts['pending'] ?? 0;
        $proses = $statusCounts['proses'] ?? 0;
        $dikirim = $statusCounts['dikirim'] ?? 0;

        // 4. Data Grafik Penjualan Bulanan (6 Bulan Terakhir)
        $monthQuery = $pdo->prepare("
            SELECT DATE_FORMAT(created_at, '%b %Y') as month_name, SUM(grand_total) as total 
            FROM orders 
            $where AND pay_status = 'Lunas'
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), month_name 
            ORDER BY DATE_FORMAT(created_at, '%Y-%m') DESC 
            LIMIT 6
        ");
        $monthQuery->execute($params);
        $monthlyRes = array_reverse($monthQuery->fetchAll()); // Balik urutan agar dari bulan terlama ke terbaru
        foreach($monthlyRes as $m) {
            $salesLabels[] = $m['month_name'];
            $salesData[] = $m['total'];
        }

        // 5. Data Grafik Top Marketing (Khusus Super Admin)
        if ($isSuper) {
            $topQuery = $pdo->query("
                SELECT u.name, SUM(o.grand_total) as total 
                FROM orders o 
                JOIN users u ON o.marketing_id = u.id 
                WHERE o.pay_status = 'Lunas' 
                GROUP BY u.name 
                ORDER BY total DESC 
                LIMIT 5
            ");
            $topRes = $topQuery->fetchAll();
            foreach($topRes as $t) {
                $markLabels[] = $t['name'];
                $markData[] = $t['total'];
            }
        }

        // 6. Lima Pesanan Terakhir
        $stmt5 = $pdo->prepare("SELECT kp_number, customer_name, grand_total, order_status, created_at FROM orders $where ORDER BY created_at DESC LIMIT 5");
        $stmt5->execute($params);
        $recentOrders = $stmt5->fetchAll();
    }
} catch (PDOException $e) {}
?>

<!-- Panggil Library Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    <!-- HEADER -->
    <div class="mb-8 flex flex-col md:flex-row justify-between md:items-center gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Dashboard <?= $isGudang ? 'Gudang' : '' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Selamat datang kembali, <span class="font-bold text-purple-700 uppercase"><?= $_SESSION['user']['name'] ?></span>!</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Tanggal Hari Ini</div>
                <div class="font-bold text-purple-900 bg-purple-100 px-3 py-1 rounded-lg border border-purple-200"><?= date('d F Y') ?></div>
            </div>
            <?php if($isMarketing || $isSuper): ?>
            <button onclick="window.location.href='index.php?page=input_order'" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Buat Pesanan
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if($isGudang): ?>
    <!-- ========================================== -->
    <!-- TAMPILAN KHUSUS ADMIN GUDANG               -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div onclick="window.location.href='index.php?page=pengepakan'" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:border-amber-300 transition group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -z-0 opacity-50 group-hover:scale-110 transition"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <div class="text-gray-400 text-xs uppercase font-bold mb-1 tracking-wider">Antrean Packing</div>
                    <div class="text-4xl font-black text-gray-800"><?= number_format($pending) ?></div>
                </div>
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-lg"><i class="fas fa-box-open"></i></div>
            </div>
        </div>

        <div onclick="window.location.href='index.php?page=pengiriman'" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:border-blue-300 transition group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -z-0 opacity-50 group-hover:scale-110 transition"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <div class="text-gray-400 text-xs uppercase font-bold mb-1 tracking-wider">Paket Siap Kirim</div>
                    <div class="text-4xl font-black text-gray-800"><?= number_format($proses) ?></div>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-lg"><i class="fas fa-truck-loading"></i></div>
            </div>
        </div>

        <div onclick="window.location.href='index.php?page=pengiriman'" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:border-emerald-300 transition group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -z-0 opacity-50 group-hover:scale-110 transition"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <div class="text-gray-400 text-xs uppercase font-bold mb-1 tracking-wider">Total Dikirim</div>
                    <div class="text-4xl font-black text-gray-800"><?= number_format($dikirim) ?></div>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider"><i class="fas fa-tasks mr-2 text-purple-600"></i> Pekerjaan Gudang Terakhir</h2>
            <a href="index.php?page=pengepakan" class="text-xs font-bold text-purple-600 hover:underline">Lihat Antrean</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-white text-gray-400 text-[10px] uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Faktur</th>
                        <th class="px-5 py-3 text-left">Pelanggan</th>
                        <th class="px-5 py-3 text-right">Status Gudang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 italic">Gudang Kosong.</td></tr>
                    <?php else: ?>
                        <?php foreach($recentOrders as $ro): 
                            $bgBadge = 'bg-gray-100 text-gray-600';
                            if(in_array($ro['order_status'], ['Diproses', 'Sedang Dipack'])) $bgBadge = 'bg-amber-100 text-amber-700';
                            if($ro['order_status'] == 'Paket Siap') $bgBadge = 'bg-blue-100 text-blue-700';
                            if($ro['order_status'] == 'Dikirim') $bgBadge = 'bg-emerald-100 text-emerald-700';
                        ?>
                        <tr class="hover:bg-purple-50/30 transition">
                            <td class="px-5 py-3">
                                <div class="font-bold text-purple-700 font-mono text-xs"><?= $ro['kp_number'] ?></div>
                                <div class="text-[10px] text-gray-400"><?= date('d M Y', strtotime($ro['created_at'])) ?></div>
                            </td>
                            <td class="px-5 py-3 font-black text-gray-800 text-xs uppercase"><?= $ro['customer_name'] ?></td>
                            <td class="px-5 py-3 text-right">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black border uppercase <?= $bgBadge ?> border-opacity-50"><?= $ro['order_status'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php else: ?>
    <!-- ========================================== -->
    <!-- TAMPILAN SUPER ADMIN & MARKETING           -->
    <!-- ========================================== -->
    
    <!-- STATISTIK METRIK -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div onclick="window.location.href='index.php?page=faktur'" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:border-purple-400 transition group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -z-0 opacity-50 group-hover:scale-110 transition"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <div class="text-gray-400 text-[10px] uppercase font-bold mb-1 tracking-widest">Total Pesanan (KP)</div>
                    <div class="text-4xl font-black text-gray-800"><?= number_format($totalKP) ?></div>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-lg"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>

        <div onclick="window.location.href='index.php?page=faktur_ki'" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:border-purple-400 transition group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -z-0 opacity-50 group-hover:scale-110 transition"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <div class="text-gray-400 text-[10px] uppercase font-bold mb-1 tracking-widest">Tiket Instalasi (KI)</div>
                    <div class="text-4xl font-black text-gray-800"><?= number_format($totalKI) ?></div>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-lg"><i class="fas fa-tools"></i></div>
            </div>
        </div>

        <div onclick="window.location.href='index.php?page=reports'" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:border-purple-400 transition group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-32 h-32 bg-purple-100 rounded-bl-full -z-0 opacity-40 group-hover:scale-110 transition"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <div class="text-purple-600 text-[10px] uppercase font-black mb-1 tracking-widest">Omzet Valid (Lunas)</div>
                    <div class="text-3xl font-black text-purple-900 truncate max-w-[200px]">Rp <?= number_format($omzet, 0, ',', '.') ?></div>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-200 text-purple-700 flex items-center justify-center text-lg flex-shrink-0"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
    </div>

    <!-- AREA GRAFIK -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Grafik Penjualan -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2"><i class="fas fa-chart-line mr-2 text-purple-600"></i> Tren Penjualan Lunas (6 Bulan)</h2>
            <div class="relative h-64 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Grafik Top Marketing (Hanya untuk Super Admin/Keuangan) -->
        <?php if($isSuper): ?>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2"><i class="fas fa-trophy mr-2 text-yellow-500"></i> Top Marketing (Omzet Lunas)</h2>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="marketingChart"></canvas>
            </div>
        </div>
        <?php else: ?>
        <!-- Panel Alternatif untuk Marketing -->
        <div class="bg-gradient-to-br from-purple-600 to-purple-900 p-6 rounded-xl shadow-md text-white flex flex-col justify-center items-center text-center relative overflow-hidden">
            <i class="fas fa-bullseye absolute -right-4 -bottom-4 text-9xl opacity-10"></i>
            <h2 class="text-xl font-black uppercase tracking-widest mb-2 z-10">Target Bulan Ini</h2>
            <p class="text-purple-200 text-sm z-10 mb-6">Ayo kejar target penjualanmu dan maksimalkan closing rate hari ini!</p>
            <a href="index.php?page=input_order" class="bg-white text-purple-800 font-bold py-3 px-6 rounded-full shadow-lg hover:bg-purple-50 transition z-10 active:scale-95">CLOSING SEKARANG</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- KOLOM BAWAH: STATUS & TABEL -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Ringkasan Status -->
        <div class="lg:col-span-1 space-y-4">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-2"><i class="fas fa-chart-pie mr-2 text-purple-600"></i> Pantauan Status Transaksi</h2>
            
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-red-300 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center"><i class="fas fa-hourglass-half"></i></div>
                    <div>
                        <div class="text-xs text-gray-500 font-bold uppercase">Menunggu Pembayaran</div>
                        <div class="text-[10px] text-gray-400">Belum ada DP masuk</div>
                    </div>
                </div>
                <div class="text-2xl font-black text-gray-800"><?= $pending ?></div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-yellow-300 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center"><i class="fas fa-box-open"></i></div>
                    <div>
                        <div class="text-xs text-gray-500 font-bold uppercase">Sedang Diproses</div>
                        <div class="text-[10px] text-gray-400">Gudang / Packing</div>
                    </div>
                </div>
                <div class="text-2xl font-black text-gray-800"><?= $proses ?></div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-green-300 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center"><i class="fas fa-truck"></i></div>
                    <div>
                        <div class="text-xs text-gray-500 font-bold uppercase">Sudah Dikirim</div>
                        <div class="text-[10px] text-gray-400">Selesai Logistik</div>
                    </div>
                </div>
                <div class="text-2xl font-black text-gray-800"><?= $dikirim ?></div>
            </div>
        </div>

        <!-- Kolom Kanan: Pesanan Terakhir -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full">
                <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider"><i class="fas fa-history mr-2 text-purple-600"></i> 5 Transaksi Terakhir</h2>
                    <a href="index.php?page=faktur" class="text-xs font-black text-purple-600 hover:text-purple-800 uppercase hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-white text-gray-400 text-[10px] uppercase font-bold tracking-wider">
                            <tr>
                                <th class="px-5 py-3 text-left">Faktur</th>
                                <th class="px-5 py-3 text-left">Pelanggan</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-right">Nilai Deal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-400 italic font-bold">Belum ada riwayat pesanan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recentOrders as $ro): 
                                    $bgBadge = 'bg-gray-100 text-gray-600 border border-gray-200';
                                    if(in_array($ro['order_status'], ['Diproses', 'Sedang Dipack', 'Paket Siap', 'Menunggu Verifikasi'])) $bgBadge = 'bg-yellow-50 text-yellow-700 border border-yellow-200';
                                    if($ro['order_status'] == 'Dikirim') $bgBadge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                    if($ro['order_status'] == 'Menunggu Pembayaran') $bgBadge = 'bg-red-50 text-red-600 border border-red-200';
                                ?>
                                <tr class="hover:bg-purple-50/30 transition">
                                    <td class="px-5 py-3">
                                        <div class="font-bold text-purple-700 font-mono text-xs"><?= $ro['kp_number'] ?></div>
                                        <div class="text-[9px] text-gray-400 mt-0.5 font-bold"><?= date('d M Y', strtotime($ro['created_at'])) ?></div>
                                    </td>
                                    <td class="px-5 py-3 font-black text-gray-800 text-xs uppercase"><?= $ro['customer_name'] ?></td>
                                    <td class="px-5 py-3">
                                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-tighter <?= $bgBadge ?>"><?= $ro['order_status'] ?></span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-black text-gray-800 text-sm">Rp <?= number_format($ro['grand_total'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ========================================== -->
<!-- SCRIPT UNTUK MERENDER GRAFIK CHART.JS      -->
<!-- ========================================== -->
<?php if(!$isGudang): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. GRAFIK PENJUALAN BULANAN (LINE/BAR CHART)
    const ctxSales = document.getElementById('salesChart');
    if (ctxSales) {
        new Chart(ctxSales.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($salesLabels) ?>,
                datasets: [{
                    label: 'Omzet Lunas (Rp)',
                    data: <?= json_encode($salesData) ?>,
                    borderColor: '#9333ea', // Purple-600
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#9333ea',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4 // Membuat garis melengkung elegan
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(value) { return 'Rp ' + (value/1000000) + ' Jt'; }, font: {size: 10} } },
                    x: { ticks: { font: {size: 10} } }
                }
            }
        });
    }

    // 2. GRAFIK TOP MARKETING (PIE/DOUGHNUT CHART) - HANYA SUPER ADMIN
    <?php if($isSuper): ?>
    const ctxMark = document.getElementById('marketingChart');
    if (ctxMark) {
        new Chart(ctxMark.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($markLabels) ?>,
                datasets: [{
                    data: <?= json_encode($markData) ?>,
                    backgroundColor: [
                        '#9333ea', // Purple-600
                        '#a855f7', // Purple-500
                        '#c084fc', // Purple-400
                        '#d8b4fe', // Purple-300
                        '#e9d5ff'  // Purple-200
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: {size: 10, weight: 'bold'} } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                return ' Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                cutout: '65%' // Membuat lubang di tengah
            }
        });
    }
    <?php endif; ?>
});
</script>
<?php endif; ?>