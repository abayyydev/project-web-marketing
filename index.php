<?php
session_start();

// 1. Cek Login
if (!isset($_SESSION['user'])) {
    require_once 'views/login.php';
    exit;
}

// 2. Load Header (Membuka div container flex)
require_once 'includes/header.php';

// 3. Load Sidebar (Akan jadi kolom kiri)
require_once 'includes/sidebar.php';

// 4. Area Konten Utama (Akan jadi kolom kanan, memenuhi sisa ruang)
?>

<main class="flex-1 bg-gray-50 h-full md:h-screen overflow-y-auto">
    <!-- Header Mobile (Hanya muncul di HP) -->
    <div class="md:hidden bg-white p-4 flex justify-between items-center shadow-sm sticky top-0 z-30">
        <div class="flex items-center gap-2 font-bold text-green-700">
            <i class="fas fa-leaf"></i> PT Sigma Media
        </div>
        <button onclick="alert('Gunakan Laptop untuk fitur lengkap')" class="text-gray-500"><i
                class="fas fa-bars"></i></button>
    </div>

    <!-- Konten Halaman Dinamis -->
    <div class="p-4 md:p-8 pb-20">
        <?php
        $page = $_GET['page'] ?? 'dashboard';

        switch ($page) {
            case 'dashboard':
                require_once 'views/dashboard.php';
                break;
            case 'input_order':
                require_once 'views/input_order.php';
                break;
            case 'my_orders':
            case 'verify_orders':
                require_once 'views/list_orders.php';
                break;
            case 'products':
                if ($_SESSION['user']['role'] !== 'admin') {
                    echo "Akses Ditolak";
                    break;
                }
                require_once 'views/products.php';
                break;
            case 'reports':
                require_once 'views/reports.php';
                break;
            case 'users':
                if ($_SESSION['user']['role'] !== 'admin') {
                    echo "Akses Ditolak";
                    break;
                }
                require_once 'views/users.php';
                break;
            case 'settings':
                if ($_SESSION['user']['role'] !== 'admin') {
                    echo "Akses Ditolak";
                    break;
                }
                require_once 'views/settings.php';
                break;
            case 'edit_order':
                require_once 'views/edit_order.php';
                break;
            default:
                echo "<div class='text-center py-20 text-gray-400'>Halaman tidak ditemukan!</div>";
                break;
        }
        ?>
    </div>
</main>

</div> <!-- Penutup Div Container Utama (Dibuka di header.php) -->
</body>

</html>