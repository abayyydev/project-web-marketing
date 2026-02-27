<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// HAK AKSES PROSES: Dibuka untuk Gudang, Marketing, dan Super Admin
$canProcess = ($userRole === 'admin_gudang' || $userRole === 'marketing' || $userRole === 'super_admin' || $uLogin === 'admin');
// Penanda Role
$isGudang = ($userRole === 'admin_gudang');
$isMarketing = ($userRole === 'marketing');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Pengiriman Barang</h1>
            <p class="text-sm text-gray-500">Beranda > Penjualan > <span class="text-purple-600 font-bold uppercase">Logistik & Resi</span></p>
        </div>
        
        <div class="bg-purple-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center gap-3">
            <i class="fas fa-truck-moving text-lg"></i> 
            <div class="flex flex-col text-left">
                <span class="opacity-70 text-[9px] uppercase">Status Logistik</span>
                <span><?= $isGudang ? 'GUDANG: '.strtoupper($userWh) : ($isMarketing ? 'PROGRES SALES' : 'PUSAT / NASIONAL') ?></span>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="flex flex-wrap gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full md:w-64">
                <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Faktur / Resi / Nama..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 outline-none shadow-sm transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>

            <!-- Filter Status Pengiriman -->
            <select id="filter-status" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-600 bg-gray-50 outline-none focus:ring-purple-500 shadow-sm transition">
                <option value="">Semua Status</option>
                <option value="Paket Siap">Menunggu Kurir (Siap)</option>
                <option value="Dikirim">Sudah Dikirim</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button onclick="loadPengiriman()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center justify-center gap-2 text-xs w-full md:w-auto">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tgl Kirim</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. Faktur (KP)</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan & Info Bayar</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Kurir / Ekspedisi</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Nomor Resi</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody id="shipping-rows" class="bg-white divide-y divide-gray-100 text-sm font-medium text-gray-700">
                    <tr><td colspan="7" class="text-center py-10 text-gray-400 italic font-bold">Memuat data pengiriman...</td></tr>
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

<!-- MODAL INPUT RESI -->
<div id="modal-shipping" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900"><i class="fas fa-truck mr-2 text-purple-600"></i>Input Data Pengiriman</h3>
            <button onclick="closeModal('modal-shipping')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="ship-order-id">
            
            <div class="bg-purple-50/50 p-3 rounded-lg border border-purple-200 text-sm text-purple-900">
                Faktur: <span id="ship-kp" class="font-black font-mono"></span><br>
                Pelanggan: <span id="ship-cust" class="font-black"></span><br>
                <span class="text-[10px] text-purple-600 font-bold uppercase italic">Alamat: </span><span id="ship-addr" class="text-[10px]"></span>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Nama Ekspedisi / Kurir</label>
                    <input type="text" id="ship-courier" class="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-purple-500 focus:border-purple-500 font-bold transition" placeholder="Contoh: J&T, Indah Cargo, Grab">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Nomor Resi / AWB (Opsional)</label>
                    <input type="text" id="ship-resi" class="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-purple-500 focus:border-purple-500 font-mono font-bold transition" placeholder="Contoh: JD0123456789">
                </div>
            </div>

            <div class="border-2 border-dashed border-purple-300 rounded-xl p-6 text-center hover:bg-purple-50 transition cursor-pointer group">
                <label class="block text-xs font-black text-purple-700 uppercase mb-2 group-hover:scale-105 transition-transform">Upload Foto Resi Fisik (Opsional)</label>
                <input type="file" id="ship-proof-file" accept="image/png, image/jpeg, image/jpg, image/webp" class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 w-full cursor-pointer transition">
            </div>
            
            <button onclick="submitShipping()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-4 rounded-xl shadow-lg mt-2 transition flex items-center justify-center gap-2 transform active:scale-95">
                <i class="fas fa-paper-plane"></i> SIMPAN & KONFIRMASI KIRIM
            </button>
        </div>
    </div>
</div>

<!-- MODAL LIHAT RESI -->
<div id="modal-proof" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative transform transition-all">
        <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
            <h3 class="text-lg font-black text-gray-800"><i class="fas fa-image mr-2 text-purple-600"></i> Bukti Resi Pengiriman</h3>
            <button onclick="closeModal('modal-proof')" class="text-gray-500 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 flex justify-center bg-gray-50">
            <img id="proof-img" src="" class="max-w-full h-auto max-h-[60vh] object-contain rounded-lg shadow-md border border-gray-300">
        </div>
    </div>
</div>

<script>
let allData = [];
let currentFilteredData = [];
// Variabel hak akses dilempar ke JS
const canProcess = <?= $canProcess ? 'true' : 'false' ?>;

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', loadPengiriman);

window.onclick = function(event) {
    if (!event.target.matches('.drop-btn')) {
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    }
}

function toggleAction(id) {
    const el = document.getElementById('action-' + id);
    const isHidden = el.classList.contains('hidden');
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    if(isHidden) el.classList.remove('hidden');
}

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

async function loadPengiriman() {
    try {
        const res = await fetch('api/get_orders.php');
        const json = await res.json();
        if(json.status === 'success') {
            // Tampilkan yang sudah Paket Siap (Siap Pickup) atau sudah Dikirim
            const validStatuses = ['Paket Siap', 'Dikirim'];
            allData = json.data.filter(o => validStatuses.includes(o.order_status));
            filterTable();
        }
    } catch(e) { console.error(e); }
}

function renderTable(data) {
    const tbody = document.getElementById('shipping-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12 text-gray-400 font-bold uppercase tracking-widest">Tidak ada antrean pengiriman.</td></tr>';
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
        const tglKirim = o.delivery_date ? new Date(o.delivery_date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'}) : '-';
        
        // Kalkulasi sisa tagihan (Info Logistik)
        const grand = Math.round(parseFloat(o.grand_total) || 0);
        const dp = Math.round(parseFloat(o.dp_amount) || 0);
        let sisa = grand - dp;
        if(o.pay_status === 'Lunas') sisa = 0;

        let payBadge = '';
        if(o.pay_status === 'Lunas') {
            payBadge = `<div class="mt-1 text-[9px] font-black text-emerald-600 bg-emerald-50 inline-block px-2.5 py-1 rounded border border-emerald-200 uppercase">Lunas</div>`;
        } else {
            payBadge = `<div class="mt-1 text-[9px] font-black text-red-500 bg-red-50 inline-block px-2.5 py-1 rounded border border-red-200 uppercase">Tagihan: Rp ${sisa.toLocaleString('id-ID')}</div>`;
        }

        // Status Badge Pengiriman
        let statusBadge = '';
        if(o.order_status === 'Paket Siap') {
            statusBadge = `<span class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-orange-200 uppercase animate-pulse shadow-sm">Siap Pickup</span>`;
        } else {
            statusBadge = `<span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-emerald-200 uppercase shadow-sm">Dikirim</span>`;
        }

        let actionHTML = `
            <div class="relative inline-block text-left">
                <button onclick="toggleAction(${o.id})" class="drop-btn text-gray-400 hover:text-purple-600 p-1 transition"><i class="fas fa-ellipsis-v pointer-events-none"></i></button>
                <div id="action-${o.id}" class="dropdown-content hidden origin-top-right absolute right-0 mt-2 w-40 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 border border-gray-100 py-1">
        `;
        
        // 1. Fitur Input Resi (Hanya Gudang, Marketing, Super Admin)
        if(canProcess && o.order_status === 'Paket Siap') {
            actionHTML += `<a href="#" onclick="openModalShipping(${o.id}, '${o.kp_number}', '${o.customer_name}', '${o.customer_address}')" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700 font-black transition uppercase tracking-tighter"><i class="fas fa-truck mr-2"></i> Input Resi</a>`;
        }

        // 2. Fitur Lihat Foto Resi
        if (o.resi_proof) {
            actionHTML += `<a href="#" onclick="showResiProof('${o.resi_proof}')" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 font-bold transition uppercase tracking-tighter"><i class="fas fa-image mr-2"></i> Foto Resi</a>`;
        }
        
        if (actionHTML.indexOf('<a') === -1) actionHTML += `<span class="block px-4 py-2 text-[10px] text-gray-400 italic">No Action</span>`;
        
        actionHTML += `</div></div>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-black text-xs">${tglKirim}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-purple-700 font-black font-mono text-xs uppercase">${o.kp_number}</div>
                    <div class="text-[9px] text-gray-400 font-bold uppercase mt-0.5"><i class="fas fa-warehouse mr-1"></i>${o.warehouse_source || 'Pusat'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-gray-900 font-black text-xs uppercase">${o.customer_name}</div>
                    ${payBadge}
                </td>
                <td class="px-6 py-4 whitespace-nowrap font-bold text-xs text-gray-700 uppercase">${o.courier_name || '<span class="text-gray-300">-</span>'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-purple-600 font-mono font-black text-xs uppercase">${o.resi_number || '<span class="text-gray-300">-</span>'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${actionHTML}</td>
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
    const status = document.getElementById('filter-status').value;
    
    const filtered = allData.filter(o => {
        const matchText = o.customer_name.toLowerCase().includes(term) || o.kp_number.toLowerCase().includes(term) || (o.resi_number && o.resi_number.toLowerCase().includes(term));
        const matchStatus = (status === '' || o.order_status === status);
        return matchText && matchStatus;
    });
    
    currentFilteredData = filtered; 
    currentPage = 1; 
    renderTable(currentFilteredData);
}

function openModalShipping(id, kp, cust, addr) {
    if(!canProcess) return Swal.fire('Ditolak', 'Akses proses pengiriman terbatas.', 'error');

    document.getElementById('ship-order-id').value = id;
    document.getElementById('ship-kp').innerText = kp;
    document.getElementById('ship-cust').innerText = cust;
    document.getElementById('ship-addr').innerText = addr || '-';
    document.getElementById('ship-courier').value = '';
    document.getElementById('ship-resi').value = '';
    document.getElementById('ship-proof-file').value = '';
    
    document.getElementById('modal-shipping').classList.remove('hidden');
}

async function submitShipping() {
    const orderId = document.getElementById('ship-order-id').value;
    const courier = document.getElementById('ship-courier').value;
    const resiNum = document.getElementById('ship-resi').value;
    const fileInput = document.getElementById('ship-proof-file');
    
    if (!courier) return Swal.fire('Peringatan', 'Harap isi Nama Kurir / Ekspedisi!', 'warning');

    Swal.fire({title: 'Memproses Data...', allowOutsideClick: false}); Swal.showLoading();

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('courier_name', courier);
    formData.append('resi_number', resiNum);
    if (fileInput.files.length > 0) formData.append('resi_proof', fileInput.files[0]);

    try {
        const res = await fetch('api/update_shipping.php', { method: 'POST', body: formData });
        const json = await res.json();
        
        if(json.status === 'success') {
            closeModal('modal-shipping'); 
            Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Barang telah dikonfirmasi kirim.', timer: 2000, showConfirmButton: false});
            loadPengiriman(); 
        } else { Swal.fire('Gagal', json.message, 'error'); }
    } catch(e) { Swal.fire('Error', 'Koneksi gagal atau foto terlalu besar', 'error'); }
}

function showResiProof(url) {
    document.getElementById('proof-img').src = url;
    document.getElementById('modal-proof').classList.remove('hidden');
}
</script>