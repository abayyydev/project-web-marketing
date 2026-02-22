<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Filter Area (Disembunyikan saat Print) -->
    <div class="print:hidden mb-8">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                <?= $_SESSION['user']['role'] === 'admin' ? 'Laporan Penjualan Pusat' : 'Laporan Penjualan Saya' ?>
            </h1>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-1/4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
                <input type="date" id="start_date" class="w-full px-3 py-2 border rounded-lg focus:ring-green-500">
            </div>
            <div class="w-full md:w-1/4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" id="end_date" class="w-full px-3 py-2 border rounded-lg focus:ring-green-500">
            </div>

            <!-- FILTER MARKETING (KHUSUS ADMIN) -->
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Marketing</label>
                    <select id="marketing_filter" class="w-full px-3 py-2 border rounded-lg focus:ring-green-500 bg-white">
                        <option value="">-- Semua Marketing --</option>
                        <!-- JS akan mengisi ini -->
                    </select>
                </div>
            <?php endif; ?>

            <div class="w-full md:w-auto flex gap-2">
                <button onclick="loadReport()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-bold transition shadow">
                    <i class="fas fa-filter mr-2"></i> Tampilkan
                </button>
                <button onclick="window.print()" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-bold transition shadow">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Area Laporan (Kertas) -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 min-h-[500px]" id="print-area">
        
        <!-- Header Laporan -->
        <div class="text-center mb-8 pb-4 border-b-2 border-gray-800">
            <h2 class="text-3xl font-bold text-gray-800 uppercase tracking-wider">PT Sigma Media</h2>
            <p class="text-sm text-gray-500 mt-1">Laporan Aktivitas Penjualan & Pemasangan</p>
            <p class="text-xs text-gray-400 mt-1" id="periode-txt">Periode: -</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-6 mb-8 text-center">
            <div class="p-4 bg-gray-50 rounded border border-gray-100 print:border-gray-300">
                <div class="text-xs text-gray-500 uppercase font-bold">Total Transaksi</div>
                <div class="text-2xl font-bold text-gray-800" id="rep-qty">0</div>
            </div>
            <div class="p-4 bg-green-50 rounded border border-green-100 print:border-gray-300">
                <div class="text-xs text-green-700 uppercase font-bold">Omzet (Lunas)</div>
                <div class="text-2xl font-bold text-green-700" id="rep-omzet">Rp 0</div>
            </div>
            <div class="p-4 bg-yellow-50 rounded border border-yellow-100 print:border-gray-300">
                <div class="text-xs text-yellow-700 uppercase font-bold">Potensi (DP/Belum)</div>
                <div class="text-2xl font-bold text-yellow-700" id="rep-piutang">Rp 0</div>
            </div>
        </div>

        <!-- Tabel Data -->
        <table class="w-full text-sm text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="py-2">Tanggal</th>
                    <th class="py-2">No. KP</th>
                    <th class="py-2">Pelanggan</th>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <th class="py-2">Marketing</th>
                    <?php endif; ?>
                    <th class="py-2 text-center">Status</th>
                    <th class="py-2 text-right">Nilai Total</th>
                </tr>
            </thead>
            <tbody id="report-rows" class="divide-y divide-gray-200">
                <!-- JS will load here -->
            </tbody>
        </table>

        <!-- Footer Tanda Tangan (Muncul saat Print) -->
        <div class="hidden print:flex justify-end mt-16">
            <div class="text-center">
                <p class="mb-16 text-sm">Bogor, <span id="print-date"></span></p>
                <p class="font-bold underline text-sm"><?= $_SESSION['user']['name'] ?></p>
                <p class="text-xs text-gray-500 uppercase"><?= $_SESSION['user']['role'] ?></p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; border: none; }
        .bg-gray-50, .bg-green-50, .bg-yellow-50 { background-color: white !important; border: 1px solid #ddd !important; }
    }
</style>

<script>
const isAdmin = <?= $_SESSION['user']['role'] === 'admin' ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', () => {
    const d = new Date();
    document.getElementById('end_date').valueAsDate = d;
    document.getElementById('start_date').value = new Date(d.getFullYear(), d.getMonth(), 1).toISOString().split('T')[0];
    document.getElementById('print-date').innerText = d.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
    
    // Jika Admin, load daftar marketing dulu
    if(isAdmin) {
        loadMarketingOptions();
    }
    
    loadReport();
});

async function loadMarketingOptions() {
    try {
        // Kita gunakan API get_users.php yg sudah ada (filter manual di JS)
        const res = await fetch('api/get_users.php');
        const json = await res.json();
        if(json.status === 'success') {
            const select = document.getElementById('marketing_filter');
            json.data.forEach(u => {
                // Hanya tampilkan user role marketing (opsional, tampilkan semua juga boleh)
                if(u.role === 'marketing') {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.text = u.full_name + ' (' + u.username + ')';
                    select.appendChild(opt);
                }
            });
        }
    } catch(e) { console.error("Gagal load marketing options"); }
}

async function loadReport() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const tbody = document.getElementById('report-rows');
    
    // Ambil nilai filter marketing (jika ada)
    let marketingId = '';
    const marketingSelect = document.getElementById('marketing_filter');
    if(marketingSelect) {
        marketingId = marketingSelect.value;
    }

    // Update Text Header
    let periodeTxt = `Periode: ${new Date(start).toLocaleDateString('id-ID')} s/d ${new Date(end).toLocaleDateString('id-ID')}`;
    if(marketingId && marketingSelect.options[marketingSelect.selectedIndex]) {
        periodeTxt += ` | Marketing: ${marketingSelect.options[marketingSelect.selectedIndex].text}`;
    }
    document.getElementById('periode-txt').innerText = periodeTxt;

    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Memuat data...</td></tr>';

    try {
        // Kirim parameter filter ke API
        const res = await fetch(`api/get_report.php?start=${start}&end=${end}&marketing_id=${marketingId}`);
        const json = await res.json();

        if(json.status === 'success') {
            document.getElementById('rep-qty').innerText = json.summary.total_qty || 0;
            document.getElementById('rep-omzet').innerText = "Rp " + parseInt(json.summary.omzet_lunas || 0).toLocaleString('id-ID');
            document.getElementById('rep-piutang').innerText = "Rp " + parseInt(json.summary.potensi_piutang || 0).toLocaleString('id-ID');

            tbody.innerHTML = '';
            if(json.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-400">Tidak ada penjualan pada periode ini.</td></tr>';
                return;
            }

            json.data.forEach(row => {
                const date = new Date(row.created_at).toLocaleDateString('id-ID');
                let statusBadge = row.pay_status;
                let marketingCol = isAdmin ? `<td class="py-2 text-gray-500 text-xs uppercase">${row.marketing}</td>` : '';

                tbody.innerHTML += `
                    <tr>
                        <td class="py-2 text-gray-600">${date}</td>
                        <td class="py-2 font-mono font-bold text-xs">${row.kp_number}</td>
                        <td class="py-2 font-medium">${row.customer_name}</td>
                        ${marketingCol}
                        <td class="py-2 text-center text-xs font-bold text-gray-600">${statusBadge}</td>
                        <td class="py-2 text-right font-bold">Rp ${parseInt(row.grand_total).toLocaleString('id-ID')}</td>
                    </tr>
                `;
            });
        }
    } catch(e) {
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-red-500">Gagal memuat laporan.</td></tr>';
    }
}
</script>