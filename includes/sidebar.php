<?php
$role = $_SESSION['user']['role'] ?? '';
$page = $_GET['page'] ?? 'dashboard';
?>

<!-- SIDEBAR -->
<!-- Di HP (Hidden), Di Laptop (Block, Lebar 64, Sticky) -->
<aside
    class="bg-white w-full md:w-64 border-r border-gray-200 flex-shrink-0 z-20 md:h-screen md:sticky md:top-0 shadow-sm">

    <!-- Logo Area -->
    <div class="p-6 border-b border-gray-100 flex items-center gap-3 bg-green-50/50">
        <div
            class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl shadow-sm">
            <i class="fas fa-leaf"></i>
        </div>
        <div>
            <h2 class="font-bold text-gray-800 tracking-tight leading-tight">PT Sigma Media</h2>
            <p
                class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded inline-block font-bold uppercase tracking-wider mt-1">
                <?= $role ?>
            </p>
        </div>
    </div>

    <!-- Menu Items -->
    <nav class="p-4 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 80px);">

        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-2">Menu Utama</p>

        <a href="index.php?page=dashboard"
            class="<?= $page == 'dashboard' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
            <i class="fas fa-home w-5 text-center"></i> Dashboard
        </a>

        <?php if ($role === 'marketing'): ?>
            <a href="index.php?page=input_order"
                class="<?= $page == 'input_order' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-plus-circle w-5 text-center"></i> Input Pesanan
            </a>
            <a href="index.php?page=my_orders"
                class="<?= $page == 'my_orders' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-list w-5 text-center"></i> Riwayat Saya
            </a>
            <a href="index.php?page=reports"
                class="<?= $page == 'reports' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-chart-pie w-5 text-center"></i> Laporan Saya
            </a>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <a href="index.php?page=verify_orders"
                class="<?= $page == 'verify_orders' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-check-double w-5 text-center"></i> Verifikasi Order
            </a>
            <a href="index.php?page=products"
                class="<?= $page == 'products' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-boxes w-5 text-center"></i> Master Produk
            </a>
            <a href="index.php?page=reports"
                class="<?= $page == 'reports' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-chart-line w-5 text-center"></i> Laporan
            </a>
            <a href="index.php?page=users"
                class="<?= $page == 'users' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-users-cog w-5 text-center"></i> Kelola User
            </a>
            <a href="index.php?page=settings"
                class="<?= $page == 'settings' ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'text-gray-600 border-transparent hover:bg-gray-50' ?> block px-4 py-3 rounded-lg border font-medium flex items-center gap-3 mb-1">
                <i class="fas fa-cogs w-5 text-center"></i> Pengaturan
            </a>
        <?php endif; ?>

        <div class="pt-4 mt-4 border-t border-gray-100">
            <a href="logout.php"
                class="block px-4 py-3 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 font-medium flex items-center gap-3 transition">
                <i class="fas fa-sign-out-alt w-5 text-center"></i> Keluar
            </a>
        </div>
    </nav>
</aside>