<?php
$role = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';

$isGudang = ($role === 'admin_gudang');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Produk Keluar</h1>
            <p class="text-sm text-gray-500">Persediaan > <span class="text-purple-600 font-bold uppercase">Riwayat Pengeluaran (Outbound)</span></p>
        </div>
        <div class="flex items-center gap-3">
            <?php if($isGudang): ?>
            <div class="bg-purple-100 text-purple-800 px-4 py-2 rounded-lg border border-purple-200 text-xs font-bold shadow-sm uppercase">
                <i class="fas fa-warehouse mr-1"></i> Cabang: <?= strtoupper($userWh) ?>
            </div>
            <?php endif; ?>
            <div class="text-xs bg-gray-100 text-gray-500 px-4 py-2 rounded-lg border border-gray-200 font-bold shadow-sm uppercase flex items-center gap-2">
                <i class="fas fa-lock"></i> Read-Only
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="relative w-full md:w-64">
            <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Faktur / Produk..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 outline-none shadow-sm transition">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
        </div>
        
        <?php if(!$isGudang): ?>
        <div class="w-full md:w-auto">
            <select id="filter-cabang" onchange="filterTable()" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-50 focus:ring-purple-500 focus:border-purple-500 outline-none font-bold shadow-sm transition">
                <option value="">-- Semua Cabang --</option>
                <!-- Opsi di-load via JS -->
            </select>
        </div>
        <?php endif; ?>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="p-4 bg-purple-50 border-b border-purple-100 text-xs text-purple-800 font-medium">
            <i class="fas fa-info-circle mr-1 text-purple-600"></i> Pengeluaran stok diproses <b>Otomatis</b> saat Faktur Penjualan (KP) dibuat. Halaman ini hanya untuk pemantauan histori mutasi.
        </div>
        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-black tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Waktu Keluar</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. Faktur (KP)</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Nama Produk</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Diambil Dari Gudang</th>
                        <th class="px-6 py-4 text-right whitespace-nowrap">Qty Keluar</th>
                    </tr>
                </thead>
                <tbody id="out-rows" class="bg-white divide-y divide-gray-100 text-sm font-medium">
                    <tr><td colspan="5" class="text-center py-10 text-gray-400 italic">Memuat riwayat...</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Paginasi Container -->
        <div id="pagination-container" class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-xs text-gray-500 font-bold" id="page-info">Memuat paginasi...</div>
            <div class="flex gap-1" id="page-buttons"></div>
        </div>
    </div>
</div>

<script>
let allData = [];
let currentFilteredData = [];
const isGudang = <?= $isGudang ? 'true' : 'false' ?>;

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadData();
    if(!isGudang) loadCabangFilter();
});

async function loadCabangFilter() {
    try {
        const res = await fetch('api/warehouse_action.php?action=get');
        const json = await res.json();
        if(json.status === 'success') {
            const sel = document.getElementById('filter-cabang');
            if(sel) {
                json.data.forEach(w => {
                    sel.innerHTML += `<option value="${w.name}">${w.name}</option>`;
                });
            }
        }
    } catch(e) { console.error(e); }
}

async function loadData() {
    try {
        const res = await fetch('api/stock_out_action.php');
        const json = await res.json();
        if(json.status === 'success') {
            allData = json.data;
            filterTable(); // Memanggil filter untuk trigger paginasi
        }
    } catch(e) { console.error(e); }
}

function renderTable(data) {
    const tbody = document.getElementById('out-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-12 text-gray-400 font-bold uppercase tracking-widest">Belum ada riwayat keluar.</td></tr>';
        renderPagination(0, 0);
        return;
    }

    // LOGIKA PAGINASI
    const totalPages = Math.ceil(data.length / rowsPerPage);
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);

    let rowsHTML = '';
    paginatedData.forEach(d => {
        const date = new Date(d.created_at).toLocaleString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});
        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4 text-gray-500 text-xs font-bold">${date}</td>
                <td class="px-6 py-4 font-mono text-purple-700 font-black text-sm tracking-wide">${d.reference_no}</td>
                <td class="px-6 py-4 font-black text-gray-900 uppercase text-xs">${d.product_name}</td>
                <td class="px-6 py-4 text-gray-600 font-bold text-xs uppercase"><i class="fas fa-box-open text-purple-400 mr-2"></i>${d.warehouse_name}</td>
                <td class="px-6 py-4 text-right font-black text-red-600 text-sm bg-red-50/30">- ${parseFloat(d.qty)} <span class="text-[10px] text-red-500 ml-1 uppercase">${d.unit}</span></td>
            </tr>
        `;
    });

    tbody.innerHTML = rowsHTML;
    renderPagination(data.length, totalPages);
}

function renderPagination(totalItems, totalPages) {
    const info = document.getElementById('page-info');
    const buttons = document.getElementById('page-buttons');

    if(totalItems <= rowsPerPage) {
        info.innerHTML = totalItems === 0 ? '' : `Menampilkan total ${totalItems} data.`;
        buttons.innerHTML = '';
        return;
    }

    let startItem = ((currentPage - 1) * rowsPerPage) + 1;
    let endItem = Math.min(currentPage * rowsPerPage, totalItems);
    info.innerHTML = `Menampilkan ${startItem} - ${endItem} dari <span class="text-purple-600 font-black">${totalItems}</span> data.`;

    let html = '';
    
    // Tombol Prev
    if(currentPage > 1) {
        html += `<button onclick="changePage(${currentPage - 1})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm"><i class="fas fa-chevron-left"></i></button>`;
    } else {
        html += `<button disabled class="px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 text-xs font-bold cursor-not-allowed"><i class="fas fa-chevron-left"></i></button>`;
    }

    // Nomor Halaman
    for(let p = 1; p <= totalPages; p++) {
        if(p === currentPage) {
            html += `<button class="px-3 py-1.5 rounded-lg bg-purple-600 border border-purple-600 text-white text-xs font-bold shadow-md">${p}</button>`;
        } else if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
            html += `<button onclick="changePage(${p})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm">${p}</button>`;
        } else if (p === currentPage - 2 || p === currentPage + 2) {
            html += `<span class="px-2 py-1 text-gray-400 text-xs">...</span>`;
        }
    }

    // Tombol Next
    if(currentPage < totalPages) {
        html += `<button onclick="changePage(${currentPage + 1})" class="px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition text-xs font-bold shadow-sm"><i class="fas fa-chevron-right"></i></button>`;
    } else {
        html += `<button disabled class="px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 text-xs font-bold cursor-not-allowed"><i class="fas fa-chevron-right"></i></button>`;
    }

    buttons.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    renderTable(currentFilteredData); 
}

function filterTable() {
    const term = document.getElementById('search').value.toLowerCase();
    const filterEl = document.getElementById('filter-cabang');
    const cabang = filterEl ? filterEl.value : '';

    const filtered = allData.filter(d => {
        const matchText = d.reference_no.toLowerCase().includes(term) || d.product_name.toLowerCase().includes(term);
        const matchCabang = (cabang === '') || (d.warehouse_name === cabang);
        return matchText && matchCabang;
    });

    currentFilteredData = filtered;
    currentPage = 1; // Reset ke halaman 1 setiap kali filter berubah
    renderTable(currentFilteredData);
}
</script>