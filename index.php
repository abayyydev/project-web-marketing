<?php
session_start();

// 1. CEK LOGIN
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
$role = $_SESSION['user']['role'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// 2. HELPER AKSES
$isSuper = ($role === 'super_admin' || $role === 'admin' || $uLogin === 'admin');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sigma ERP - Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-gray-800">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-4 md:p-6 w-full">
        <?php
        switch ($page) {
            case 'dashboard': require_once 'views/dashboard.php'; break;
            
            // MAIN MENU (SEMUA BISA AKSES, READ-ONLY DIATUR DI DALAM FILE VIEW)
            case 'faktur': require_once 'views/faktur.php'; break;
            case 'input_order': require_once 'views/input_order.php'; break;
            case 'edit_order': require_once 'views/edit_order.php'; break;
            case 'penerimaan': require_once 'views/penerimaan.php'; break;
            case 'pengepakan': require_once 'views/pengepakan.php'; break;
            case 'pengiriman': require_once 'views/pengiriman.php'; break;
            
            case 'faktur_ki': require_once 'views/faktur_ki.php'; break;
            case 'penerimaan_ki': require_once 'views/penerimaan_ki.php'; break;
            case 'pengerjaan_ki': require_once 'views/pengerjaan_ki.php'; break;
            
            case 'products': require_once 'views/products.php'; break;
            case 'stock_in': require_once 'views/stock_in.php'; break;
            case 'stock_out': require_once 'views/stock_out.php'; break;
            
            // DATA PUSAT (DIKUNCI KETAT HANYA UNTUK SUPER ADMIN)
            case 'warehouses': 
                if($isSuper) require_once 'views/warehouses.php'; else echo "<div class='p-10 text-center text-red-500 font-bold'>Akses Ditolak! Khusus Super Admin.</div>"; break;
            case 'users': 
                if($isSuper) require_once 'views/users.php'; else echo "<div class='p-10 text-center text-red-500 font-bold'>Akses Ditolak! Khusus Super Admin.</div>"; break;
            case 'reports': 
                if($isSuper) require_once 'views/reports.php'; else echo "<div class='p-10 text-center text-red-500 font-bold'>Akses Ditolak! Khusus Super Admin.</div>"; break;
            case 'settings': 
                if($isSuper) require_once 'views/settings.php'; else echo "<div class='p-10 text-center text-red-500 font-bold'>Akses Ditolak! Khusus Super Admin.</div>"; break;
            
            default: require_once 'views/dashboard.php'; break;
        }
        ?>
    </main>
</body>
</html>