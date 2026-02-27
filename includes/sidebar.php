<?php
$role = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';
$page = $_GET['page'] ?? 'dashboard';

// FUNGSI AKTIF MENU
function isActive($p, $page) {
    return $p === $page ? 'bg-purple-100 text-purple-800 border-purple-300 shadow-sm font-bold' : 'text-gray-500 border-transparent hover:bg-purple-50 hover:text-purple-700';
}
function isMenuOpen($pages, $currentPage) {
    return in_array($currentPage, $pages) ? 'block' : 'hidden';
}
function rotateIcon($pages, $currentPage) {
    return in_array($currentPage, $pages) ? 'rotate-180' : '';
}

$userName = $_SESSION['user']['name'] ?? 'User';
$initial = strtoupper(substr($userName, 0, 1));

// LOGIKA HAK AKSES
$isSuper = ($role === 'super_admin' || $role === 'admin' || $uLogin === 'admin'); 
$isGudang = ($role === 'admin_gudang');

// Tampilan Teks Jabatan
$roleDisplay = strtoupper(str_replace('_', ' ', $role));
if ($isGudang && $userWh) $roleDisplay .= " ($userWh)";
if ($uLogin === 'admin') $roleDisplay = "SUPER ADMIN";
?>

<style>
    .max-w-7xl, .max-w-6xl, .max-w-5xl, .max-w-4xl { margin-top: 5rem !important; }
    #debug-info { position: absolute; bottom: 5px; left: 10px; font-size: 8px; color: #cbd5e1; }
</style>

<header class="fixed top-0 left-0 md:left-64 right-0 h-16 bg-white/90 backdrop-blur-md border-b border-gray-200 z-30 flex items-center justify-between md:justify-end px-4 sm:px-6 shadow-sm">
    <div class="md:hidden flex items-center gap-3">
        <button onclick="toggleSidebar()" class="text-gray-600 hover:text-purple-600 focus:outline-none text-xl p-2 -ml-2 rounded-lg transition">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="font-bold text-gray-800 tracking-tight uppercase text-sm md:text-base">SIGMA MEDIA</h2>
    </div>

    <div class="relative">
        <button onclick="toggleProfile()" class="flex items-center gap-3 focus:outline-none hover:bg-purple-50 p-1.5 rounded-lg transition border border-transparent hover:border-purple-200">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold text-gray-700 leading-none"><?= $userName ?></p>
                <p class="text-[10px] text-purple-600 font-bold uppercase tracking-wider mt-1"><?= $roleDisplay ?></p>
            </div>
            <div class="w-9 h-9 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-purple-100">
                <?= $initial ?>
            </div>
            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
        </button>
        <div id="dropdown-profile" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-50 py-2 overflow-hidden">
            <a href="logout.php" class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition font-medium">
                <i class="fas fa-sign-out-alt mr-2"></i> Keluar Sistem
            </a>
        </div>
    </div>
</header>

<div id="mobile-overlay" class="fixed inset-0 bg-gray-900/50 z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

<aside id="main-sidebar" class="bg-white w-64 border-r border-gray-200 flex-shrink-0 z-50 h-screen fixed md:sticky top-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl md:shadow-sm flex flex-col">
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <!-- Logo Custom Sigma ERP -->
            <div class="w-8 h-8 rounded-lg shadow-md overflow-hidden">
                <img src="img/logosigma.png" alt="Sigma ERP Logo" class="w-full h-full object-cover">
            </div>

            <h2 class="font-black text-purple-900 tracking-tight leading-tight uppercase text-lg">SIGMA ERP</h2>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-red-500 p-1">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <nav class="p-4 space-y-1 overflow-y-auto flex-1">
        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 mt-2">Main Menu</p>
        <a href="index.php?page=dashboard" class="<?= isActive('dashboard', $page) ?> block px-4 py-2.5 rounded-lg border flex items-center gap-3 text-sm transition">
            <i class="fas fa-home w-5 text-center text-blue-500"></i> Dashboard
        </a>

        <!-- PENJUALAN (KP) - DIBUKA UNTUK SEMUA ROLE -->
        <div>
            <button onclick="toggleMenu('menu-penjualan', 'icon-penjualan')" class="w-full text-left <?= in_array($page, ['faktur','penerimaan','pengepakan','pengiriman']) ? 'text-purple-800 font-bold' : 'text-gray-600 hover:bg-purple-50 hover:text-purple-700' ?> px-4 py-2.5 rounded-lg flex items-center justify-between text-sm transition">
                <div class="flex items-center gap-3"><i class="fas fa-shopping-cart w-5 text-center text-blue-600"></i> Penjualan (KP)</div>
                <i id="icon-penjualan" class="fas fa-chevron-down text-xs transition-transform duration-300 <?= rotateIcon(['faktur','penerimaan','pengepakan','pengiriman'], $page) ?>"></i>
            </button>
            <div id="menu-penjualan" class="pl-11 pr-2 py-1 space-y-1 <?= isMenuOpen(['faktur','penerimaan','pengepakan','pengiriman'], $page) ?>">
                <a href="index.php?page=faktur" class="<?= isActive('faktur', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-file-invoice w-4 text-center text-blue-400"></i> Faktur Barang
                </a>
                <a href="index.php?page=penerimaan" class="<?= isActive('penerimaan', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-hand-holding-usd w-4 text-center text-emerald-500"></i> Penerimaan Uang
                </a>
                <a href="index.php?page=pengepakan" class="<?= isActive('pengepakan', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-box-open w-4 text-center text-amber-500"></i> Pengepakan
                </a>
                <a href="index.php?page=pengiriman" class="<?= isActive('pengiriman', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-truck-moving w-4 text-center text-teal-500"></i> Pengiriman
                </a>
            </div>
        </div>

        <!-- INSTALASI (KI) - DIBUKA UNTUK SEMUA ROLE -->
        <div>
            <button onclick="toggleMenu('menu-instalasi', 'icon-instalasi')" class="w-full text-left <?= in_array($page, ['faktur_ki','penerimaan_ki','pengerjaan_ki']) ? 'text-purple-800 font-bold' : 'text-gray-600 hover:bg-purple-50 hover:text-purple-700' ?> px-4 py-2.5 rounded-lg flex items-center justify-between text-sm transition mt-1">
                <div class="flex items-center gap-3"><i class="fas fa-tools w-5 text-center text-orange-500"></i> Instalasi (KI)</div>
                <i id="icon-instalasi" class="fas fa-chevron-down text-xs transition-transform duration-300 <?= rotateIcon(['faktur_ki','penerimaan_ki','pengerjaan_ki'], $page) ?>"></i>
            </button>
            <div id="menu-instalasi" class="pl-11 pr-2 py-1 space-y-1 <?= isMenuOpen(['faktur_ki','penerimaan_ki','pengerjaan_ki'], $page) ?>">
                <a href="index.php?page=faktur_ki" class="<?= isActive('faktur_ki', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-file-signature w-4 text-center text-orange-400"></i> Faktur Jasa (KI)
                </a>
                <a href="index.php?page=penerimaan_ki" class="<?= isActive('penerimaan_ki', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-money-check-alt w-4 text-center text-emerald-500"></i> Penerimaan Jasa
                </a>
                <a href="index.php?page=pengerjaan_ki" class="<?= isActive('pengerjaan_ki', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-hard-hat w-4 text-center text-yellow-500"></i> Pengerjaan Lapangan
                </a>
            </div>
        </div>

        <!-- PERSEDIAAN - DIBUKA UNTUK SEMUA ROLE -->
        <div>
            <button onclick="toggleMenu('menu-persediaan', 'icon-persediaan')" class="w-full text-left <?= in_array($page, ['products','stock_in','stock_out']) ? 'text-purple-800 font-bold' : 'text-gray-600 hover:bg-purple-50 hover:text-purple-700' ?> px-4 py-2.5 rounded-lg flex items-center justify-between text-sm transition mt-1">
                <div class="flex items-center gap-3"><i class="fas fa-boxes w-5 text-center text-teal-600"></i> Persediaan</div>
                <i id="icon-persediaan" class="fas fa-chevron-down text-xs transition-transform duration-300 <?= rotateIcon(['products','stock_in','stock_out'], $page) ?>"></i>
            </button>
            <div id="menu-persediaan" class="pl-11 pr-2 py-1 space-y-1 <?= isMenuOpen(['products','stock_in','stock_out'], $page) ?>">
                <a href="index.php?page=products" class="<?= isActive('products', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-cubes w-4 text-center text-indigo-500"></i> Master Produk
                </a>
                <a href="index.php?page=stock_in" class="<?= isActive('stock_in', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-arrow-circle-down w-4 text-center text-blue-500"></i> Produk Masuk
                </a>
                <a href="index.php?page=stock_out" class="<?= isActive('stock_out', $page) ?> flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition border">
                    <i class="fas fa-arrow-circle-up w-4 text-center text-red-500"></i> Riwayat Keluar
                </a>
            </div>
        </div>

        <!-- DATA PUSAT (TERKUNCI KHUSUS SUPER ADMIN) -->
        <?php if($isSuper): ?>
        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4 border-t border-gray-100 pt-4">Data Pusat</p>
        
        <a href="index.php?page=warehouses" class="<?= isActive('warehouses', $page) ?> block px-4 py-2.5 rounded-lg border flex items-center gap-3 text-sm transition">
            <i class="fas fa-building w-5 text-center text-cyan-500"></i> Gudang Cabang
        </a>
        <a href="index.php?page=users" class="<?= isActive('users', $page) ?> block px-4 py-2.5 rounded-lg border flex items-center gap-3 text-sm transition">
            <i class="fas fa-users-cog w-5 text-center text-pink-500"></i> Kelola User
        </a>
        <a href="index.php?page=reports" class="<?= isActive('reports', $page) ?> block px-4 py-2.5 rounded-lg border flex items-center gap-3 text-sm transition">
            <i class="fas fa-chart-pie w-5 text-center text-green-500"></i> Laporan Keuangan
        </a>
        <a href="index.php?page=settings" class="<?= isActive('settings', $page) ?> block px-4 py-2.5 rounded-lg border flex items-center gap-3 text-sm transition">
            <i class="fas fa-cogs w-5 text-center text-gray-400"></i> Setting Penomoran
        </a>
        <?php endif; ?>

    </nav>
    <div id="debug-info">User: <?= $uLogin ?> | Role: <?= $role ?></div>
</aside>

<script>
function toggleMenu(m, i) {
    document.getElementById(m).classList.toggle('hidden');
    document.getElementById(i).classList.toggle('rotate-180');
}
function toggleProfile() { document.getElementById('dropdown-profile').classList.toggle('hidden'); }
function toggleSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('mobile-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>