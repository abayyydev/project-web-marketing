<?php
$role = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// HAK AKSES: Hanya Super Admin dan Admin Gudang yang bisa memproses stok masuk
$isSuperAdmin = ($role === 'super_admin' || $uLogin === 'admin');
$isGudang = ($role === 'admin_gudang');
$canProcess = ($isGudang || $isSuperAdmin);
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Stok & Produk Masuk</h1>
            <p class="text-sm text-gray-500">Persediaan > <span class="text-purple-600 font-bold uppercase">Barang Masuk (Inbound)</span></p>
        </div>
        
        <?php if($canProcess): ?>
        <button onclick="openModalIn()" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md transition flex items-center gap-2 transform active:scale-95">
            <i class="fas fa-sign-in-alt"></i> Tambah Stok Baru
        </button>
        <?php else: ?>
        <div class="bg-gray-100 text-gray-500 px-5 py-2.5 rounded-xl text-xs font-bold shadow-sm flex items-center gap-3 border border-gray-200">
            <i class="fas fa-eye text-lg"></i> 
            <div class="flex flex-col text-left">
                <span class="opacity-70 text-[9px] uppercase">Akses Terbatas</span>
                <span>Mode Read-Only</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-md border-t-4 border-purple-600 overflow-hidden">
        
        <!-- NAVIGASI TAB -->
        <div class="flex border-b border-gray-200 bg-gray-50 overflow-x-auto">
            <button onclick="switchTab('stok')" id="tab-stok" class="flex-1 py-4 px-4 text-sm font-bold border-b-2 border-purple-600 text-purple-700 transition outline-none whitespace-nowrap">
                <i class="fas fa-boxes mr-2"></i> Total Stok Saat Ini
            </button>
            <button onclick="switchTab('riwayat')" id="tab-riwayat" class="flex-1 py-4 px-4 text-sm font-bold text-gray-500 hover:text-purple-600 border-b-2 border-transparent transition outline-none whitespace-nowrap">
                <i class="fas fa-history mr-2"></i> Riwayat Barang Masuk
            </button>
        </div>

        <!-- TAB 1: TOTAL STOK SAAT INI -->
        <div id="box-stok" class="p-0">
            <div class="p-4 bg-purple-50/50 border-b border-purple-100 text-xs text-purple-800 flex items-center">
                <i class="fas fa-info-circle text-purple-600 mr-2 text-lg"></i> 
                Pantau ketersediaan barang fisik secara real-time. <?= $isGudang ? 'Menampilkan data khusus cabang <b class="ml-1">'.strtoupper($userWh).'</b>.' : '' ?>
            </div>
            <div class="overflow-x-auto min-h-[400px]">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-white text-gray-600 text-[11px] uppercase font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left w-1/3">Nama Produk</th>
                            <th class="px-6 py-4 text-center w-1/4">Total Stok <?= $isGudang ? 'Gudang' : '(Nasional)' ?></th>
                            <th class="px-6 py-4 text-left">Rincian <?= $isGudang ? 'Cabang' : 'Per Cabang' ?></th>
                        </tr>
                    </thead>
                    <tbody id="stock-rows" class="divide-y divide-gray-100 text-sm">
                        <tr><td colspan="3" class="text-center py-10 text-gray-400 italic">Memuat data stok...</td></tr>
                    </tbody>
                </table>
            </div>
            <!-- Paginasi Stok -->
            <div id="stock-pagination-container" class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-xs text-gray-500 font-bold" id="stock-page-info">Memuat paginasi...</div>
                <div class="flex gap-1" id="stock-page-buttons"></div>
            </div>
        </div>

        <!-- TAB 2: RIWAYAT MASUK -->
        <div id="box-riwayat" class="hidden p-0">
            <div class="overflow-x-auto min-h-[400px]">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-gray-600 text-[11px] uppercase font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">Waktu Masuk</th>
                            <th class="px-6 py-4 text-left">No. Referensi / PO</th>
                            <th class="px-6 py-4 text-left">Produk</th>
                            <th class="px-6 py-4 text-left">Gudang Tujuan</th>
                            <th class="px-6 py-4 text-right">Qty Masuk</th>
                        </tr>
                    </thead>
                    <tbody id="in-rows" class="divide-y divide-gray-100 text-sm">
                        <tr><td colspan="5" class="text-center py-10 text-gray-400 italic">Memuat riwayat...</td></tr>
                    </tbody>
                </table>
            </div>
            <!-- Paginasi Riwayat -->
            <div id="history-pagination-container" class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-xs text-gray-500 font-bold" id="history-page-info">Memuat paginasi...</div>
                <div class="flex gap-1" id="history-page-buttons"></div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL INPUT STOK (TAMBAH BARANG) -->
<div id="modal-in" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900"><i class="fas fa-truck-loading mr-2 text-purple-600"></i> Form Barang Masuk</h3>
            <button onclick="closeModalIn()" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nomor Referensi / Surat Jalan</label>
                <input type="text" id="in-ref" placeholder="Contoh: SJ-SUPPLIER-001" class="w-full border border-gray-300 rounded-lg p-3 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 font-bold text-gray-700 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Produk (Fisik)</label>
                <select id="in-product" class="w-full border border-gray-300 rounded-lg p-3 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white font-bold text-gray-700 transition"></select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Gudang Tujuan</label>
                <select id="in-warehouse" class="w-full border border-gray-300 rounded-lg p-3 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-purple-50 font-bold text-purple-800 transition"></select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jumlah Masuk (Qty)</label>
                <input type="number" step="any" min="0.01" id="in-qty" class="w-full border border-purple-300 rounded-lg p-4 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500 text-3xl font-black text-purple-800 text-center shadow-inner transition" placeholder="0">
                <p class="text-[10px] text-gray-400 mt-2 text-center font-medium">Gunakan tanda titik (.) untuk nilai desimal (Cth: 1.5)</p>
            </div>
            <button onclick="submitStockIn()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-4 rounded-xl shadow-lg mt-4 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> SIMPAN & TAMBAH STOK
            </button>
        </div>
    </div>
</div>

<script>
const canProcess = <?= $canProcess ? 'true' : 'false' ?>;
const isGudang = <?= $isGudang ? 'true' : 'false' ?>;
const myWarehouse = "<?= $userWh ?>";

// VARIABEL DATA & PAGINASI
let allStockData = [];
let currentStockPage = 1;
const stockRowsPerPage = 10;

let allHistoryData = [];
let currentHistoryPage = 1;
const historyRowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => { 
    loadCurrentStock(); 
    loadHistory(); 
    if(canProcess) loadOptions(); 
});

// LOGIKA PINDAH TAB
function switchTab(tab) {
    if (tab === 'stok') {
        document.getElementById('box-stok').classList.remove('hidden');
        document.getElementById('box-riwayat').classList.add('hidden');
        document.getElementById('tab-stok').classList.add('border-purple-600', 'text-purple-700');
        document.getElementById('tab-stok').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById('tab-riwayat').classList.remove('border-purple-600', 'text-purple-700');
        document.getElementById('tab-riwayat').classList.add('text-gray-500', 'border-transparent');
    } else {
        document.getElementById('box-riwayat').classList.remove('hidden');
        document.getElementById('box-stok').classList.add('hidden');
        document.getElementById('tab-riwayat').classList.add('border-purple-600', 'text-purple-700');
        document.getElementById('tab-riwayat').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById('tab-stok').classList.remove('border-purple-600', 'text-purple-700');
        document.getElementById('tab-stok').classList.add('text-gray-500', 'border-transparent');
    }
}

// ----------------------------------------
// TAB 1: TOTAL STOK SAAT INI (DENGAN PAGINASI)
// ----------------------------------------
async function loadCurrentStock() {
    try {
        const res = await fetch('api/get_products.php');
        const json = await res.json();
        allStockData = json.data.filter(p => p.type === 'goods');
        renderStockTable();
    } catch(e) { console.error(e); }
}

function renderStockTable() {
    const tbody = document.getElementById('stock-rows');
    tbody.innerHTML = '';
    
    if(allStockData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-10 text-gray-400 font-bold uppercase tracking-widest">Belum ada data produk fisik.</td></tr>';
        renderStockPagination(0, 0);
        return;
    }

    const totalPages = Math.ceil(allStockData.length / stockRowsPerPage);
    const startIndex = (currentStockPage - 1) * stockRowsPerPage;
    const paginatedData = allStockData.slice(startIndex, startIndex + stockRowsPerPage);

    let rowsHTML = '';
    paginatedData.forEach(p => {
        let totalTampil = 0;
        let rincianCabang = `<div class="flex flex-wrap gap-2">`;
        
        if (isGudang) {
            let qty = parseFloat(p.stocks[myWarehouse]) || 0;
            totalTampil = qty;
            
            let badgeColor = qty <= 0 ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-700 border-green-200';
            rincianCabang += `<span class="px-3 py-1.5 rounded-lg text-xs font-black border ${badgeColor} shadow-sm uppercase">${myWarehouse}: ${qty}</span>`;
        } else {
            for (let wh in p.stocks) { 
                let qty = parseFloat(p.stocks[wh]) || 0;
                totalTampil += qty;
                
                let badgeColor = qty <= 0 ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-700 border-green-200';
                rincianCabang += `<span class="px-2.5 py-1.5 rounded-md text-[11px] font-bold border ${badgeColor} shadow-sm">${wh}: ${qty}</span>`;
            }
        }
        rincianCabang += `</div>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4">
                    <div class="font-black text-gray-900 uppercase text-sm">${p.name}</div>
                    <div class="text-[10px] text-gray-400 font-mono mt-0.5">${p.code}</div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="text-xl font-black text-purple-700">${totalTampil}</span> <span class="text-xs text-gray-500 font-bold ml-1">${p.unit}</span>
                </td>
                <td class="px-6 py-4">${rincianCabang}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = rowsHTML;
    renderStockPagination(allStockData.length, totalPages);
}

function renderStockPagination(totalItems, totalPages) {
    const info = document.getElementById('stock-page-info');
    const buttons = document.getElementById('stock-page-buttons');

    if(totalItems <= stockRowsPerPage) {
        info.innerHTML = totalItems === 0 ? '' : `Menampilkan total ${totalItems} data.`;
        buttons.innerHTML = '';
        return;
    }

    let startItem = ((currentStockPage - 1) * stockRowsPerPage) + 1;
    let endItem = Math.min(currentStockPage * stockRowsPerPage, totalItems);
    info.innerHTML = `Menampilkan ${startItem} - ${endItem} dari <span class="text-purple-600 font-black">${totalItems}</span> data.`;

    let html = '';
    
    if(currentStockPage > 1) {
        html += `<button onclick="changeStockPage(${currentStockPage - 1})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm"><i class="fas fa-chevron-left"></i></button>`;
    } else {
        html += `<button disabled class="px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 text-xs font-bold cursor-not-allowed"><i class="fas fa-chevron-left"></i></button>`;
    }

    for(let p = 1; p <= totalPages; p++) {
        if(p === currentStockPage) {
            html += `<button class="px-3 py-1.5 rounded-lg bg-purple-600 border border-purple-600 text-white text-xs font-bold shadow-md">${p}</button>`;
        } else if (p === 1 || p === totalPages || (p >= currentStockPage - 1 && p <= currentStockPage + 1)) {
            html += `<button onclick="changeStockPage(${p})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm">${p}</button>`;
        } else if (p === currentStockPage - 2 || p === currentStockPage + 2) {
            html += `<span class="px-2 py-1 text-gray-400 text-xs">...</span>`;
        }
    }

    if(currentStockPage < totalPages) {
        html += `<button onclick="changeStockPage(${currentStockPage + 1})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm"><i class="fas fa-chevron-right"></i></button>`;
    } else {
        html += `<button disabled class="px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 text-xs font-bold cursor-not-allowed"><i class="fas fa-chevron-right"></i></button>`;
    }

    buttons.innerHTML = html;
}

function changeStockPage(page) {
    currentStockPage = page;
    renderStockTable();
}

// ----------------------------------------
// TAB 2: RIWAYAT MASUK (DENGAN PAGINASI)
// ----------------------------------------
async function loadHistory() {
    try {
        const res = await fetch('api/stock_in_action.php?action=get');
        const json = await res.json();
        allHistoryData = json.data;
        renderHistoryTable();
    } catch(e) { console.error(e); }
}

function renderHistoryTable() {
    const tbody = document.getElementById('in-rows');
    tbody.innerHTML = '';
    
    if(allHistoryData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400 font-bold uppercase tracking-widest">Belum ada riwayat barang masuk.</td></tr>';
        renderHistoryPagination(0, 0);
        return;
    }

    const totalPages = Math.ceil(allHistoryData.length / historyRowsPerPage);
    const startIndex = (currentHistoryPage - 1) * historyRowsPerPage;
    const paginatedData = allHistoryData.slice(startIndex, startIndex + historyRowsPerPage);
    
    let rowsHTML = '';
    paginatedData.forEach(d => {
        const date = new Date(d.created_at).toLocaleString('id-ID');
        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4 text-gray-500 font-bold text-xs">${date}</td>
                <td class="px-6 py-4 font-mono text-purple-700 text-xs font-black tracking-wide">${d.reference_no}</td>
                <td class="px-6 py-4 font-black text-gray-900 uppercase text-xs">${d.product_name}</td>
                <td class="px-6 py-4 text-gray-700 font-bold text-xs uppercase"><i class="fas fa-warehouse text-purple-400 mr-1"></i>${d.warehouse_name}</td>
                <td class="px-6 py-4 text-right font-black text-emerald-600 text-sm bg-emerald-50/30">+ ${parseFloat(d.qty)} <span class="text-[10px] text-emerald-500 ml-1">${d.unit}</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = rowsHTML;
    renderHistoryPagination(allHistoryData.length, totalPages);
}

function renderHistoryPagination(totalItems, totalPages) {
    const info = document.getElementById('history-page-info');
    const buttons = document.getElementById('history-page-buttons');

    if(totalItems <= historyRowsPerPage) {
        info.innerHTML = totalItems === 0 ? '' : `Menampilkan total ${totalItems} data.`;
        buttons.innerHTML = '';
        return;
    }

    let startItem = ((currentHistoryPage - 1) * historyRowsPerPage) + 1;
    let endItem = Math.min(currentHistoryPage * historyRowsPerPage, totalItems);
    info.innerHTML = `Menampilkan ${startItem} - ${endItem} dari <span class="text-purple-600 font-black">${totalItems}</span> data.`;

    let html = '';
    
    if(currentHistoryPage > 1) {
        html += `<button onclick="changeHistoryPage(${currentHistoryPage - 1})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm"><i class="fas fa-chevron-left"></i></button>`;
    } else {
        html += `<button disabled class="px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 text-xs font-bold cursor-not-allowed"><i class="fas fa-chevron-left"></i></button>`;
    }

    for(let p = 1; p <= totalPages; p++) {
        if(p === currentHistoryPage) {
            html += `<button class="px-3 py-1.5 rounded-lg bg-purple-600 border border-purple-600 text-white text-xs font-bold shadow-md">${p}</button>`;
        } else if (p === 1 || p === totalPages || (p >= currentHistoryPage - 1 && p <= currentHistoryPage + 1)) {
            html += `<button onclick="changeHistoryPage(${p})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm">${p}</button>`;
        } else if (p === currentHistoryPage - 2 || p === currentHistoryPage + 2) {
            html += `<span class="px-2 py-1 text-gray-400 text-xs">...</span>`;
        }
    }

    if(currentHistoryPage < totalPages) {
        html += `<button onclick="changeHistoryPage(${currentHistoryPage + 1})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm"><i class="fas fa-chevron-right"></i></button>`;
    } else {
        html += `<button disabled class="px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 text-xs font-bold cursor-not-allowed"><i class="fas fa-chevron-right"></i></button>`;
    }

    buttons.innerHTML = html;
}

function changeHistoryPage(page) {
    currentHistoryPage = page;
    renderHistoryTable();
}

// ----------------------------------------
// MODAL & SUBMIT ACTION
// ----------------------------------------
async function loadOptions() {
    try {
        const [resP, resW] = await Promise.all([fetch('api/get_products.php'), fetch('api/warehouse_action.php?action=get')]);
        const dataP = await resP.json(); const dataW = await resW.json();
        
        const selP = document.getElementById('in-product');
        selP.innerHTML = '<option value="">-- Pilih Barang --</option>';
        dataP.data.filter(p => p.type === 'goods').forEach(p => selP.innerHTML += `<option value="${p.id}">${p.name} (${p.unit})</option>`);
        
        const selW = document.getElementById('in-warehouse');
        selW.innerHTML = '<option value="">-- Pilih Cabang --</option>';
        if(dataW.data && dataW.data.length > 0) {
            dataW.data.forEach(w => {
                if(isGudang && w.name !== myWarehouse) return;
                selW.innerHTML += `<option value="${w.id}">${w.name}</option>`;
            });
            
            if(selW.options.length === 2) selW.selectedIndex = 1;
        } else {
            selW.innerHTML = '<option value="">(Gudang Kosong)</option>';
        }
    } catch(e) { console.error("Gagal load opsi", e); }
}

function openModalIn() {
    if(!canProcess) return Swal.fire('Ditolak', 'Akses proses ditolak.', 'error');
    document.getElementById('in-ref').value = ''; 
    document.getElementById('in-qty').value = '';
    document.getElementById('modal-in').classList.remove('hidden');
}

function closeModalIn() { document.getElementById('modal-in').classList.add('hidden'); }

function submitStockIn() {
    const payload = {
        action: 'add', 
        product_id: document.getElementById('in-product').value,
        warehouse_id: document.getElementById('in-warehouse').value,
        qty: parseFloat(document.getElementById('in-qty').value), 
        reference_no: document.getElementById('in-ref').value || 'Tanpa Referensi'
    };
    
    if(!payload.product_id) return Swal.fire('Peringatan', 'Barang belum dipilih!', 'warning');
    if(!payload.warehouse_id) return Swal.fire('Peringatan', 'Gudang belum dipilih!', 'warning');
    if(!payload.qty || payload.qty <= 0) return Swal.fire('Peringatan', 'Jumlah/Qty masuk tidak valid!', 'warning');
    
    Swal.fire({title: 'Memproses...', allowOutsideClick: false}); Swal.showLoading();

    fetch('api/stock_in_action.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) })
    .then(r => r.json()).then(d => {
        if(d.status === 'success') { 
            closeModalIn(); 
            Swal.fire('Sukses', d.message, 'success'); 
            loadCurrentStock(); 
            loadHistory(); 
        } else {
            Swal.fire('Error', d.message, 'error');
        }
    }).catch(() => Swal.fire('Error', 'Koneksi gagal', 'error'));
}
</script>