<?php
$userRole = $_SESSION['user']['role'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';
$isAdminPusat = ($userRole === 'super_admin' || $userRole === 'admin' || $uLogin === 'admin' || $userRole === 'keuangan');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    
    <!-- Filter Area (Disembunyikan saat Print) -->
    <div class="print:hidden mb-8">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">
                <?= $isAdminPusat ? 'Laporan Penjualan Pusat' : 'Laporan Penjualan Saya' ?>
            </h1>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-md border-t-4 border-purple-600 flex flex-wrap gap-4 items-end">
            <div class="w-full md:w-auto flex-1 min-w-[150px]">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
                <input type="date" id="start_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 outline-none font-bold text-gray-700">
            </div>
            <div class="w-full md:w-auto flex-1 min-w-[150px]">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" id="end_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 outline-none font-bold text-gray-700">
            </div>

            <!-- FILTER KHUSUS ADMIN PUSAT/KEUANGAN -->
            <?php if($isAdminPusat): ?>
            <div class="w-full md:w-auto flex-1 min-w-[150px]">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Marketing (Closing)</label>
                <select id="marketing_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 bg-white font-bold text-gray-700 outline-none">
                    <option value="">-- Semua Sales --</option>
                </select>
            </div>
            <div class="w-full md:w-auto flex-1 min-w-[150px]">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Cabang Gudang</label>
                <select id="warehouse_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 bg-white font-bold text-gray-700 outline-none">
                    <option value="">-- Semua Cabang --</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="w-full md:w-auto flex gap-2 pt-2 md:pt-0">
                <button onclick="loadReport()" class="w-full md:w-auto bg-purple-600 hover:bg-purple-700 text-white px-6 py-2.5 rounded-lg font-bold transition shadow-md flex items-center justify-center transform active:scale-95">
                    <i class="fas fa-filter mr-2"></i> Tampilkan
                </button>
                <button onclick="window.print()" class="w-full md:w-auto bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-lg font-bold transition shadow-md flex items-center justify-center transform active:scale-95">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>
    </div>

    <!-- Area Laporan (Kertas A4 / Tampilan Web) -->
    <div class="bg-white p-8 rounded-xl shadow-xl border border-gray-200 min-h-[500px]" id="print-area">
        
        <!-- Header Laporan -->
        <div class="text-center mb-8 pb-4 border-b-2 border-purple-800">
            <h2 class="text-3xl font-black text-purple-900 uppercase tracking-widest">PT Sigma Media Asia</h2>
            <p class="text-sm text-gray-600 mt-1 font-bold">LAPORAN AKTIVITAS PENJUALAN & INSTALASI</p>
            <p class="text-xs text-gray-500 mt-1 font-mono bg-purple-50 inline-block px-3 py-1 rounded-md border border-purple-100" id="periode-txt">Periode: -</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 text-center">
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 print:border-gray-300">
                <div class="text-[10px] text-gray-500 uppercase font-black tracking-widest">Total Faktur</div>
                <div class="text-2xl font-black text-gray-800 mt-1" id="rep-qty">0</div>
            </div>
            <div class="p-4 bg-purple-50 rounded-xl border border-purple-200 print:border-gray-300">
                <div class="text-[10px] text-purple-700 uppercase font-black tracking-widest">Omzet Barang Lunas</div>
                <div class="text-xl font-black text-purple-800 mt-1" id="rep-omzet-kp">Rp 0</div>
            </div>
            <div class="p-4 bg-orange-50 rounded-xl border border-orange-200 print:border-gray-300">
                <div class="text-[10px] text-orange-700 uppercase font-black tracking-widest">Omzet Jasa Lunas</div>
                <div class="text-xl font-black text-orange-800 mt-1" id="rep-omzet-ki">Rp 0</div>
            </div>
            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 print:border-gray-300">
                <div class="text-[10px] text-emerald-700 uppercase font-black tracking-widest">Grand Omzet Keseluruhan</div>
                <div class="text-2xl font-black text-emerald-700 mt-1" id="rep-omzet-total">Rp 0</div>
            </div>
        </div>

        <!-- TABEL 1: BARANG (KP) -->
        <div class="mb-10">
            <h3 class="font-bold text-purple-900 uppercase mb-3 border-b border-purple-200 pb-2 flex items-center"><i class="fas fa-box text-purple-600 mr-2"></i> Rincian Penjualan Barang (KP)</h3>
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-3 px-2 rounded-tl-lg">Tanggal</th>
                        <th class="py-3 px-2">No. KP</th>
                        <th class="py-3 px-2">Pelanggan</th>
                        <th class="py-3 px-2">Sales (Closing)</th>
                        <?php if($isAdminPusat): ?>
                        <th class="py-3 px-2 text-center">Cabang</th>
                        <?php endif; ?>
                        <th class="py-3 px-2 text-center">Status Bayar</th>
                        <th class="py-3 px-2 text-right rounded-tr-lg">Nilai Barang</th>
                    </tr>
                </thead>
                <tbody id="report-kp-rows" class="divide-y divide-gray-100 font-medium text-gray-700">
                    <!-- JS will load here -->
                </tbody>
            </table>
        </div>

        <!-- TABEL 2: INSTALASI (KI) -->
        <div class="mb-4">
            <h3 class="font-bold text-orange-800 uppercase mb-3 border-b border-orange-200 pb-2 flex items-center"><i class="fas fa-tools text-orange-600 mr-2"></i> Rincian Jasa Instalasi (KI)</h3>
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-3 px-2 rounded-tl-lg">Tanggal</th>
                        <th class="py-3 px-2">No. KI</th>
                        <th class="py-3 px-2">Pelanggan</th>
                        <th class="py-3 px-2">Sales (Closing)</th>
                        <?php if($isAdminPusat): ?>
                        <th class="py-3 px-2 text-center">Cabang</th>
                        <?php endif; ?>
                        <th class="py-3 px-2 text-center">Status Bayar</th>
                        <th class="py-3 px-2 text-right rounded-tr-lg">Nilai Jasa</th>
                    </tr>
                </thead>
                <tbody id="report-ki-rows" class="divide-y divide-gray-100 font-medium text-gray-700">
                    <!-- JS will load here -->
                </tbody>
            </table>
        </div>

        <!-- Footer Tanda Tangan (Muncul saat Print) -->
        <div class="hidden print:flex justify-end mt-20">
            <div class="text-center">
                <p class="mb-20 text-sm text-gray-600">Dicetak di: Bogor, <span id="print-date"></span></p>
                <p class="font-black underline text-sm text-gray-800 uppercase tracking-widest"><?= $_SESSION['user']['name'] ?></p>
                <p class="text-xs text-gray-500 uppercase font-bold"><?= str_replace('_', ' ', $_SESSION['user']['role']) ?></p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; border: none; box-shadow: none; }
        .bg-gray-50, .bg-purple-50, .bg-orange-50, .bg-emerald-50, .bg-gray-100 { background-color: white !important; border: 1px solid #e5e7eb !important; }
    }
</style>

<script>
const isAdminPusat = <?= $isAdminPusat ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', () => {
    const d = new Date();
    document.getElementById('end_date').valueAsDate = d;
    document.getElementById('start_date').value = new Date(d.getFullYear(), d.getMonth(), 1).toISOString().split('T')[0];
    document.getElementById('print-date').innerText = d.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
    
    if(isAdminPusat) {
        loadMarketingOptions();
        loadWarehouseOptions();
    }
    
    loadReport();
});

async function loadMarketingOptions() {
    try {
        // PERBAIKAN: Memanggil API yang benar (user_action.php?action=get)
        const res = await fetch('api/user_action.php?action=get');
        const json = await res.json();
        if(json.status === 'success') {
            const select = document.getElementById('marketing_filter');
            select.innerHTML = '<option value="">-- Semua Sales --</option>'; // Reset opsi
            json.data.forEach(u => {
                if(u.role === 'marketing') {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.text = u.full_name + ' (' + u.username + ')';
                    select.appendChild(opt);
                }
            });
        } else {
            console.error("Gagal memuat marketing:", json.message);
        }
    } catch(e) {
        console.error("Koneksi error saat memuat marketing:", e);
    }
}

async function loadWarehouseOptions() {
    try {
        const res = await fetch('api/warehouse_action.php?action=get');
        const json = await res.json();
        if(json.status === 'success') {
            const select = document.getElementById('warehouse_filter');
            select.innerHTML = '<option value="">-- Semua Cabang --</option>'; // Reset opsi
            json.data.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.name;
                opt.text = w.name;
                select.appendChild(opt);
            });
        }
    } catch(e) {
        console.error("Koneksi error saat memuat cabang:", e);
    }
}

async function loadReport() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const tbodyKP = document.getElementById('report-kp-rows');
    const tbodyKI = document.getElementById('report-ki-rows');
    
    let marketingId = '';
    let warehouseName = '';
    
    if(isAdminPusat) {
        const mSelect = document.getElementById('marketing_filter');
        const wSelect = document.getElementById('warehouse_filter');
        if(mSelect) marketingId = mSelect.value;
        if(wSelect) warehouseName = wSelect.value;
    }

    // Teks Header Print
    let periodeTxt = `TGL: ${new Date(start).toLocaleDateString('id-ID')} s/d ${new Date(end).toLocaleDateString('id-ID')}`;
    if(isAdminPusat) {
        const mSelect = document.getElementById('marketing_filter');
        const wSelect = document.getElementById('warehouse_filter');
        if(marketingId && mSelect.options[mSelect.selectedIndex]) periodeTxt += ` | SALES: ${mSelect.options[mSelect.selectedIndex].text}`;
        if(warehouseName && wSelect.options[wSelect.selectedIndex]) periodeTxt += ` | CABANG: ${wSelect.options[wSelect.selectedIndex].text}`;
    }
    document.getElementById('periode-txt').innerText = periodeTxt.toUpperCase();

    tbodyKP.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-purple-500 font-bold animate-pulse">Menyiapkan Data Barang...</td></tr>';
    tbodyKI.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-orange-500 font-bold animate-pulse">Menyiapkan Data Jasa...</td></tr>';

    try {
        const res = await fetch(`api/get_report.php?start=${start}&end=${end}&marketing_id=${marketingId}&warehouse=${warehouseName}`);
        const json = await res.json();

        if(json.status === 'success') {
            // Update Summary
            document.getElementById('rep-qty').innerText = json.summary.qty || 0;
            document.getElementById('rep-omzet-kp').innerText = "Rp " + parseInt(json.summary.omzet_kp || 0).toLocaleString('id-ID');
            document.getElementById('rep-omzet-ki').innerText = "Rp " + parseInt(json.summary.omzet_ki || 0).toLocaleString('id-ID');
            document.getElementById('rep-omzet-total').innerText = "Rp " + parseInt(json.summary.omzet_total || 0).toLocaleString('id-ID');

            // Render Tabel KP (Barang)
            tbodyKP.innerHTML = '';
            if(json.data_kp.length === 0) {
                tbodyKP.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-gray-400 font-bold uppercase tracking-widest">Kosong / Tidak Ada Penjualan Barang</td></tr>';
            } else {
                json.data_kp.forEach(row => {
                    const date = new Date(row.date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
                    let warehouseCol = isAdminPusat ? `<td class="py-2 px-2 text-center text-purple-700 text-xs font-bold uppercase">${row.warehouse || 'Pusat'}</td>` : '';
                    let payBadge = row.pay_status === 'Lunas' ? 'text-emerald-600 bg-emerald-50 border border-emerald-200' : 'text-red-500 bg-red-50 border border-red-200';

                    tbodyKP.innerHTML += `
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-3 px-2 text-xs text-gray-600">${date}</td>
                            <td class="py-3 px-2 font-mono font-bold text-xs text-purple-800">${row.number}</td>
                            <td class="py-3 px-2 font-black text-xs uppercase">${row.customer}</td>
                            <td class="py-3 px-2 text-xs uppercase font-bold text-gray-600"><i class="fas fa-user-tag text-purple-400 mr-1"></i>${row.marketing}</td>
                            ${warehouseCol}
                            <td class="py-3 px-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-black uppercase ${payBadge}">${row.pay_status}</span></td>
                            <td class="py-3 px-2 text-right font-black text-gray-800">Rp ${parseInt(row.total).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
            }

            // Render Tabel KI (Jasa)
            tbodyKI.innerHTML = '';
            if(json.data_ki.length === 0) {
                tbodyKI.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-gray-400 font-bold uppercase tracking-widest">Kosong / Tidak Ada Jasa Instalasi</td></tr>';
            } else {
                json.data_ki.forEach(row => {
                    const date = new Date(row.date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
                    let warehouseCol = isAdminPusat ? `<td class="py-2 px-2 text-center text-orange-700 text-xs font-bold uppercase">${row.warehouse || 'Pusat'}</td>` : '';
                    let payBadge = row.pay_status === 'Lunas' ? 'text-emerald-600 bg-emerald-50 border border-emerald-200' : 'text-red-500 bg-red-50 border border-red-200';

                    tbodyKI.innerHTML += `
                        <tr class="hover:bg-orange-50/20 transition">
                            <td class="py-3 px-2 text-xs text-gray-600">${date}</td>
                            <td class="py-3 px-2 font-mono font-bold text-xs text-orange-800">${row.number}</td>
                            <td class="py-3 px-2 font-black text-xs uppercase">${row.customer}</td>
                            <td class="py-3 px-2 text-xs uppercase font-bold text-gray-600"><i class="fas fa-user-tag text-orange-400 mr-1"></i>${row.marketing}</td>
                            ${warehouseCol}
                            <td class="py-3 px-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-black uppercase ${payBadge}">${row.pay_status}</span></td>
                            <td class="py-3 px-2 text-right font-black text-gray-800">Rp ${parseInt(row.total).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
            }
        }
    } catch(e) {
        console.error(e);
        tbodyKP.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500 font-bold">Gagal memuat laporan server.</td></tr>';
        tbodyKI.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500 font-bold">Gagal memuat laporan server.</td></tr>';
    }
}
</script>