<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// Hak akses khusus untuk validasi keuangan (Super Admin, Keuangan, atau username 'admin')
$isAdminPusat = ($userRole === 'super_admin' || $userRole === 'keuangan' || $uLogin === 'admin');
// Penanda Admin Gudang (Cabang)
$isGudang = ($userRole === 'admin_gudang');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Penerimaan Uang (KP)</h1>
            <p class="text-sm text-gray-500">Beranda > Keuangan > <span class="text-purple-600 font-bold uppercase">Validasi Mutasi</span></p>
        </div>
        
        <?php if($isGudang): ?>
            <div class="bg-purple-100 text-purple-800 px-5 py-2.5 rounded-xl text-xs font-bold border border-purple-200 shadow-sm flex items-center gap-3">
                <i class="fas fa-warehouse text-lg"></i> 
                <div class="flex flex-col text-left">
                    <span class="opacity-70 text-[9px] uppercase">Monitoring Cabang</span>
                    <span><?= strtoupper($userWh) ?> (Read-Only)</span>
                </div>
            </div>
        <?php elseif($isAdminPusat): ?>
            <div class="bg-purple-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center gap-3">
                <i class="fas fa-money-check-alt text-lg"></i>
                <div class="flex flex-col text-left">
                    <span class="opacity-70 text-[9px] uppercase">Akses Keuangan</span>
                    <span>Verifikasi Mutasi</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="flex flex-wrap gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full md:w-64">
                <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Pelanggan / KP..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 outline-none shadow-sm transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>

            <!-- Filter Status Validasi (Agar riwayat tidak hilang) -->
            <select id="filter-val" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-600 bg-gray-50 outline-none focus:ring-purple-500 shadow-sm transition">
                <option value="pending" selected>Menunggu Validasi</option>
                <option value="done">Riwayat Tervalidasi</option>
                <option value="all">Semua Data</option>
            </select>

            <!-- Filter Cabang (Hanya muncul untuk Admin Pusat/Keuangan) -->
            <?php if($isAdminPusat): ?>
            <select id="filter-cabang" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-700 bg-gray-50 outline-none focus:ring-purple-500 shadow-sm transition">
                <option value="">-- Semua Cabang --</option>
                <!-- Opsi cabang dimuat via JS -->
            </select>
            <?php endif; ?>
        </div>

        <?php if($isAdminPusat): ?>
        <div class="text-[10px] text-gray-400 font-bold uppercase italic text-right hidden lg:block">
            <i class="fas fa-info-circle mr-1 text-purple-500"></i> Klik centang hijau jika dana sudah masuk mutasi.
        </div>
        <?php endif; ?>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto min-h-[450px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. Faktur</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Info Bayar</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Jumlah Transfer</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Barang</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi / Validasi</th>
                    </tr>
                </thead>
                <tbody id="receipt-rows" class="bg-white divide-y divide-gray-100 text-sm font-medium">
                    <tr><td colspan="7" class="text-center py-10 text-gray-400 italic">Memuat data penerimaan...</td></tr>
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

<!-- MODAL BUKTI TRANSFER -->
<div id="modal-proof" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden relative">
        <div class="p-4 bg-purple-50 border-b border-purple-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900"><i class="fas fa-image mr-2 text-purple-600"></i> Bukti Transfer</h3>
            <button onclick="closeModal('modal-proof')" class="text-gray-500 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 flex justify-center bg-gray-50">
            <img id="proof-img" src="" class="max-w-full h-auto max-h-[60vh] object-contain rounded-lg border border-gray-300 shadow-sm">
        </div>
    </div>
</div>

<script>
let allData = [];
let currentFilteredData = [];
const isAdminPusat = <?= $isAdminPusat ? 'true' : 'false' ?>;
const isGudang = <?= $isGudang ? 'true' : 'false' ?>;

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadReceipts();
    if(isAdminPusat) loadCabangFilter();
});

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

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

async function loadReceipts() {
    try {
        const res = await fetch('api/get_orders.php');
        const json = await res.json();
        if(json.status === 'success') {
            // Tampilkan semua data yang sudah mulai bayar (bukan yang menunggu DP pertama atau Batal)
            allData = json.data.filter(o => o.order_status !== 'Menunggu Pembayaran' && o.order_status !== 'Batal');
            filterTable();
        }
    } catch(e) { 
        console.error(e);
        document.getElementById('receipt-rows').innerHTML = '<tr><td colspan="7" class="text-center py-10 text-red-500">Gagal memuat data.</td></tr>';
    }
}

function renderTable(data) {
    const tbody = document.getElementById('receipt-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-gray-400 font-bold uppercase tracking-widest">Tidak ada data pembayaran ditemukan.</td></tr>';
        renderPagination(0, 0);
        return;
    }

    // LOGIKA PAGINASI
    const totalPages = Math.ceil(data.length / rowsPerPage);
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);

    let rowsHTML = '';
    paginatedData.forEach(o => {
        const date = new Date(o.created_at).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'});
        let dpVal = parseFloat(o.dp_amount) || 0;
        let grandVal = parseFloat(o.grand_total) || 0;
        let nominalTrf = o.pay_status === 'DP' ? dpVal : grandVal;
        
        // Pengecekan apakah sedang butuh verifikasi (pembayaran awal atau cicilan baru)
        let isPendingValidation = (o.order_status === 'Menunggu Verifikasi' || o.is_finance_verified == 0);
        
        let statusBadge = isPendingValidation
            ? `<span class="bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-yellow-300 shadow-sm animate-pulse uppercase">Cek Mutasi</span>`
            : `<span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-green-300 shadow-sm uppercase">Dana Valid</span>`;

        // Tanda jika ini cicilan baru pada barang yang sudah diproses gudang
        let cicilanTag = (isPendingValidation && o.order_status !== 'Menunggu Verifikasi') ? '<div class="text-[9px] text-purple-600 font-black italic mt-1">Cicilan Lanjutan</div>' : '';

        let btnHTML = `<div class="flex gap-2 justify-center items-center flex-wrap">`;
        if (o.payment_proof) {
            btnHTML += `<button onclick="showProof('${o.payment_proof}')" class="text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white px-3 py-1.5 rounded-lg text-[10px] font-bold border border-purple-200 transition shadow-sm">LIHAT BUKTI</button>`;
        } else {
            btnHTML += `<span class="text-gray-400 text-[10px] font-bold uppercase italic">Tanpa Foto</span>`;
        }

        // Tombol aksi hanya untuk Admin Pusat/Keuangan dan saat status Pending
        if(isPendingValidation && isAdminPusat) {
            btnHTML += `
                <button onclick="prosesValidasi(${o.id}, 'valid')" class="bg-green-600 hover:bg-green-700 text-white w-8 h-8 rounded-lg shadow-md flex items-center justify-center transition transform active:scale-90" title="Valid">
                    <i class="fas fa-check"></i>
                </button>
                <button onclick="prosesValidasi(${o.id}, 'tidak_valid')" class="bg-red-600 hover:bg-red-700 text-white w-8 h-8 rounded-lg shadow-md flex items-center justify-center transition transform active:scale-90" title="Tolak">
                    <i class="fas fa-times"></i>
                </button>
            `;
        } else if (!isPendingValidation && isAdminPusat) {
            btnHTML += `<span class="text-green-600 font-black text-[10px] ml-2 uppercase"><i class="fas fa-check-double mr-1"></i>Tervalidasi</span>`;
        }

        // 3. Label Read-Only (Khusus Gudang / Bukan Pusat)
        if(!isAdminPusat) {
            btnHTML += `<span class="bg-gray-100 text-gray-500 border border-gray-200 px-3 py-1 rounded-lg text-[9px] font-bold uppercase italic shadow-sm">Read-Only</span>`;
        }

        btnHTML += `</div>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500">${date}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-black font-mono text-xs text-purple-700">${o.kp_number}</div>
                    <div class="text-[9px] text-gray-400 mt-0.5 font-bold uppercase"><i class="fas fa-warehouse mr-1"></i>${o.warehouse_source || 'Pusat'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-black uppercase text-xs text-gray-900">${o.customer_name}</div>
                    <div class="text-[9px] text-gray-400 font-bold uppercase mt-1">MARKETING: ${o.marketing_name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-black text-xs uppercase ${o.pay_status==='Lunas' ? 'text-emerald-600':'text-red-500'}">${o.pay_status}</div>
                    ${cicilanTag}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-black">Rp ${nominalTrf.toLocaleString('id-ID')}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-[10px] font-bold text-gray-500 uppercase">${o.order_status}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${btnHTML}</td>
            </tr>
        `;
    });
    tbody.innerHTML = rowsHTML;

    // Render paginasi di bawah tabel
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
    const valStatus = document.getElementById('filter-val').value;
    const cabangFilter = document.getElementById('filter-cabang') ? document.getElementById('filter-cabang').value : '';

    const filtered = allData.filter(o => {
        const matchText = o.customer_name.toLowerCase().includes(term) || o.kp_number.toLowerCase().includes(term);
        const matchCabang = (cabangFilter === '') || (o.warehouse_source === cabangFilter);
        
        let matchVal = true;
        let isPending = (o.order_status === 'Menunggu Verifikasi' || o.is_finance_verified == 0);
        
        if (valStatus === 'pending') matchVal = isPending;
        else if (valStatus === 'done') matchVal = !isPending;
        
        return matchText && matchCabang && matchVal;
    });

    currentFilteredData = filtered; 
    currentPage = 1; 
    renderTable(currentFilteredData);
}

function showProof(url) {
    document.getElementById('proof-img').src = url;
    document.getElementById('modal-proof').classList.remove('hidden');
}

function prosesValidasi(id, actionType) {
    if (actionType === 'valid') {
        Swal.fire({
            title: 'Pembayaran Valid?', text: 'Uang masuk sudah dicek di mutasi rekening.',
            icon: 'question', showCancelButton: true, confirmButtonColor: '#9333ea', confirmButtonText: 'Ya, Valid'
        }).then((res) => {
            if(res.isConfirmed) updateStatusAPI(id, 'validasi_keuangan', 'Penerimaan berhasil!');
        });
    } else {
        Swal.fire({
            title: 'Tolak Pembayaran?', text: 'Pesanan akan dikembalikan ke Marketing.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Ya, Tolak'
        }).then((res) => {
            if(res.isConfirmed) updateStatusAPI(id, 'update_status', 'Pesanan ditolak.');
        });
    }
}

function updateStatusAPI(id, actionCode, successMsg) {
    Swal.fire({title: 'Memproses...', allowOutsideClick: false}); Swal.showLoading();
    let payload = { action: actionCode, order_id: id };
    if (actionCode === 'update_status') { payload.status = 'Menunggu Pembayaran'; }
    fetch('api/order_action.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    }).then(r => r.json()).then(d => {
        if(d.status === 'success') {
            Swal.fire('Berhasil!', successMsg, 'success'); loadReceipts();
        } else Swal.fire('Error', d.message, 'error');
    });
}
</script>