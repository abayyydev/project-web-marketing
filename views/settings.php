<?php
$role = $_SESSION['user']['role'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';
$isSuperAdmin = ($role === 'super_admin' || $uLogin === 'admin');

// BLOKIR AKSES JIKA BUKAN SUPER ADMIN
if (!$isSuperAdmin) {
    echo "<div class='flex flex-col items-center justify-center py-20 text-center'>
            <div class='w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-4xl mb-6 shadow-inner border border-red-100'><i class='fas fa-lock'></i></div>
            <h2 class='text-3xl font-black text-gray-800 uppercase tracking-widest'>Akses Ditolak!</h2>
            <p class='text-gray-500 mt-3 font-bold'>Halaman pengaturan sistem ini eksklusif hanya untuk Super Admin.</p>
          </div>";
    return;
}

// Muat nilai counter saat ini langsung dari database
require_once 'config/database.php';
$kp_counter = 0;
$ki_counter = 0;
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key IN ('kp_counter', 'ki_counter')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'kp_counter') $kp_counter = (int)$row['setting_value'];
        if ($row['setting_key'] === 'ki_counter') $ki_counter = (int)$row['setting_value'];
    }
} catch(PDOException $e) {
    // Abaikan jika tabel belum terbuat (akan otomatis dibuat saat klik simpan)
}
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Pengaturan Sistem</h1>
            <p class="text-sm text-gray-500">Konfigurasi <span class="text-purple-600 font-bold uppercase">Nomor Urut Faktur</span></p>
        </div>
    </div>

    <!-- KONTEN SETTINGS -->
    <div class="bg-white rounded-2xl shadow-xl border-t-4 border-purple-600 p-8 overflow-hidden relative">
        <i class="fas fa-cogs absolute -bottom-10 -right-10 text-[12rem] text-purple-50 opacity-40 pointer-events-none"></i>
        
        <h2 class="text-lg font-black text-purple-900 mb-2 uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-list-ol text-purple-500"></i> Counter Transaksi
        </h2>
        <div class="p-4 bg-purple-50 rounded-xl border border-purple-100 text-sm text-purple-800 mb-8 font-medium">
            <i class="fas fa-info-circle text-purple-600 mr-2"></i>
            Atur angka terakhir (Counter). Sistem akan mulai menghitung dari <b class="font-black">Angka Berikutnya</b>.
            <br><span class="ml-6 text-[11px] opacity-80 uppercase font-bold tracking-tighter">Contoh: Jika diisi <b>5</b>, maka faktur/pesanan berikutnya otomatis bernomor <b>06</b>.</span>
        </div>

        <form onsubmit="saveSettings(event)" class="space-y-6 max-w-xl relative z-10">
            
            <!-- Counter Barang (KP) -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all hover:border-purple-300 hover:shadow-sm group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform"><i class="fas fa-box"></i></div>
                    <div>
                        <label class="font-black text-gray-800 uppercase tracking-wide text-sm">Faktur Barang (KP)</label>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5 tracking-widest">Penjualan Fisik</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white p-2 rounded-lg border border-gray-200">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-2">Terakhir:</span>
                    <input type="number" id="set-kp" value="<?= $kp_counter ?>" class="w-24 bg-gray-50 border border-gray-200 rounded-md p-2 text-center font-mono font-black text-blue-700 text-xl focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition" placeholder="0">
                </div>
            </div>

            <!-- Counter Jasa (KI) -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all hover:border-purple-300 hover:shadow-sm group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform"><i class="fas fa-tools"></i></div>
                    <div>
                        <label class="font-black text-gray-800 uppercase tracking-wide text-sm">Faktur Instalasi (KI)</label>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5 tracking-widest">Jasa Pasang</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white p-2 rounded-lg border border-gray-200">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-2">Terakhir:</span>
                    <input type="number" id="set-ki" value="<?= $ki_counter ?>" class="w-24 bg-gray-50 border border-gray-200 rounded-md p-2 text-center font-mono font-black text-orange-700 text-xl focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition" placeholder="0">
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full md:w-auto bg-purple-600 text-white px-8 py-3.5 rounded-xl font-black hover:bg-purple-700 transition transform active:scale-95 shadow-lg shadow-purple-200 flex items-center justify-center gap-2">
                    <i class="fas fa-save text-lg"></i> SIMPAN PENGATURAN
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    async function saveSettings(e) {
        e.preventDefault();
        const kp = document.getElementById('set-kp').value;
        const ki = document.getElementById('set-ki').value;

        if(kp === '' || ki === '') return Swal.fire('Peringatan', 'Nomor Counter tidak boleh kosong!', 'warning');

        Swal.fire({title: 'Menyimpan Pengaturan...', allowOutsideClick: false}); 
        Swal.showLoading();

        try {
            const res = await fetch('api/setting_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ kp_counter: kp, ki_counter: ki })
            });
            const json = await res.json();
            
            if (json.status === 'success') {
                Swal.fire('Berhasil!', json.message, 'success');
            } else {
                Swal.fire('Gagal', json.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal terhubung ke server', 'error');
        }
    }
</script>