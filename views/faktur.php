<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// Definisi Hak Akses
$isAdminPusat = ($userRole === 'super_admin' || $uLogin === 'admin' || $userRole === 'keuangan');
$isGudang = ($userRole === 'admin_gudang');
$isMarketing = ($userRole === 'marketing');

// Siapa yang boleh buat faktur
$canCreate = ($isMarketing || $isAdminPusat);
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Faktur Penjualan</h1>
            <p class="text-sm text-gray-500">Beranda > Penjualan > <span class="text-purple-600 font-bold uppercase">Faktur Barang (KP)</span></p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <button onclick="loadFaktur()" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm w-full md:w-auto justify-center">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
            <?php if($canCreate): ?>
            <a href="index.php?page=input_order" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-lg font-bold shadow transition flex items-center gap-2 text-sm w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i> Buat Faktur
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="relative w-full md:w-64">
            <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari No KP / Pelanggan..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 outline-none shadow-sm transition">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
        </div>
        
        <?php if(!$isGudang): ?>
        <div class="w-full md:w-auto">
            <select id="filter-cabang" onchange="filterTable()" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-50 focus:ring-purple-500 outline-none font-bold shadow-sm">
                <option value="">-- Semua Cabang --</option>
            </select>
        </div>
        <?php else: ?>
            <div class="bg-purple-100 text-purple-800 px-4 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2 justify-center border border-purple-200">
                <i class="fas fa-warehouse text-sm"></i> CABANG: <?= strtoupper($userWh) ?> (Read-Only)
            </div>
        <?php endif; ?>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. Faktur (KP)</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan & Info Bayar</th>
                        <th class="px-6 py-4 text-right whitespace-nowrap">Total Tagihan</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Sales</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Barang</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody id="faktur-rows" class="bg-white divide-y divide-gray-100 text-sm">
                    <tr><td colspan="7" class="text-center py-10 text-gray-400 italic font-bold">Memuat data faktur...</td></tr>
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

<!-- MODAL INPUT PEMBAYARAN -->
<div id="modal-payment" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-800"><i class="fas fa-hand-holding-usd mr-2"></i>Tambah Pembayaran</h3>
            <button onclick="closeModal('modal-payment')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="pay-order-id">
            <input type="hidden" id="pay-existing-dp">
            <input type="hidden" id="pay-grand-total-val">
            
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                <label class="block text-xs font-bold text-gray-500 uppercase" id="label-tagihan-modal">Sisa Tagihan / Total</label>
                <div class="text-3xl font-black text-gray-800 mt-1" id="pay-grand-total">Rp 0</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Pembayaran</label>
                <select id="pay-status" onchange="checkPayModal()" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-purple-500 bg-white font-bold text-gray-700 outline-none shadow-sm">
                    <option value="DP">Uang Muka (DP) / Tambah Cicilan</option>
                    <option value="Lunas">Pelunasan Penuh (Lunas)</option>
                </select>
            </div>

            <div id="pay-dp-box">
                <label class="block text-xs font-bold text-purple-600 uppercase mb-1">Nominal Transfer Saat Ini (Rp)</label>
                <input type="number" id="pay-dp-amount" class="w-full border border-purple-300 rounded-lg p-3 font-bold text-purple-800 outline-none focus:ring-purple-500 shadow-sm" placeholder="Contoh: 1500000">
            </div>

            <div class="border-2 border-dashed border-purple-300 rounded-xl p-5 text-center hover:bg-purple-50 transition cursor-pointer group">
                <label class="block text-xs font-bold text-purple-700 uppercase mb-2 group-hover:scale-105 transition-transform">Upload Bukti Transfer Barang</label>
                <input type="file" id="pay-proof-file" accept="image/png, image/jpeg, image/jpg, image/webp" class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-800 w-full cursor-pointer hover:file:bg-purple-200">
            </div>
            
            <button onclick="submitPayment()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-4 rounded-xl shadow-lg mt-2 transition flex items-center justify-center gap-2 transform active:scale-95">
                <i class="fas fa-paper-plane"></i> KIRIM KE KEUANGAN
            </button>
        </div>
    </div>
</div>

<!-- MODAL WA (TAB SURVEY & FORMAT UKURAN TEMA UNGU) -->
<div id="modal-wa" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-4 border-b bg-purple-50 flex justify-between items-center">
            <h3 class="font-bold text-purple-900"><i class="fab fa-whatsapp mr-2 text-purple-600 text-xl"></i>Format WhatsApp</h3>
            <button onclick="closeModal('modal-wa')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <div class="flex border-b bg-white overflow-x-auto">
            <button onclick="switchTab('kp')" id="tab-kp" class="flex-1 py-3 px-2 text-xs md:text-sm font-bold border-b-2 border-purple-600 text-purple-700 transition outline-none whitespace-nowrap">1. Barang (KP)</button>
            <button onclick="switchTab('ki')" id="tab-ki" class="flex-1 py-3 px-2 text-xs md:text-sm font-bold text-gray-500 hover:text-purple-600 border-b-2 border-transparent transition outline-none whitespace-nowrap hidden">2. Instalasi (KI)</button>
            <button onclick="switchTab('survey')" id="tab-survey" class="flex-1 py-3 px-2 text-xs md:text-sm font-bold text-gray-500 hover:text-purple-600 border-b-2 border-transparent transition outline-none whitespace-nowrap">3. Survey Lokasi</button>
        </div>

        <div class="p-5 flex-1 overflow-y-auto bg-gray-50">
            <!-- Isi Tab Barang -->
            <div id="box-kp" class="tab-content">
                <p class="text-xs text-gray-500 mb-2 font-bold"><i class="fas fa-info-circle text-purple-600 mr-1"></i> Ukuran potong otomatis disisipkan jika sudah diinput di Faktur.</p>
                <textarea id="wa-content-kp" rows="12" class="w-full p-3 border rounded-lg border-gray-300 font-mono text-xs focus:outline-none focus:border-purple-500" readonly></textarea>
                <button onclick="copyWA('wa-content-kp')" class="mt-3 w-full bg-purple-600 text-white font-bold py-3 rounded-xl shadow-md hover:bg-purple-700 transition active:scale-95 flex items-center justify-center gap-2"><i class="fas fa-copy"></i> Salin Format Barang</button>
            </div>
            
            <!-- Isi Tab Instalasi -->
            <div id="box-ki" class="tab-content hidden">
                <p class="text-xs text-gray-500 mb-2">Salin teks ini dan kirim ke mandor/tim lapangan.</p>
                <textarea id="wa-content-ki" rows="12" class="w-full p-3 border rounded-lg border-gray-300 font-mono text-xs focus:outline-none focus:border-purple-500" readonly></textarea>
                <button onclick="copyWA('wa-content-ki')" class="mt-3 w-full bg-purple-600 text-white font-bold py-3 rounded-xl shadow-md hover:bg-purple-700 transition active:scale-95 flex items-center justify-center gap-2"><i class="fas fa-copy"></i> Salin Format Instalasi</button>
            </div>

            <!-- Isi Tab Survey -->
            <div id="box-survey" class="tab-content hidden">
                <p class="text-xs text-gray-500 mb-2 font-bold text-purple-600"><i class="fas fa-info-circle"></i> Ubah Hari, Tanggal, Jam, dan Biaya Survey sesuai kesepakatan.</p>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Tanggal Survey</label>
                        <input type="date" id="survey-date" onchange="generateSurveyWA()" class="w-full border-purple-300 rounded p-1.5 text-xs outline-none focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Jam (Cth: 13.00)</label>
                        <input type="time" id="survey-time" oninput="generateSurveyWA()" class="w-full border-purple-300 rounded p-1.5 text-xs outline-none focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Biaya Survey</label>
                        <input type="text" id="survey-cost" oninput="generateSurveyWA()" placeholder="Cth: Free Acc Bapak / Rp 50.000" class="w-full border-purple-300 rounded p-1.5 text-xs outline-none focus:ring-purple-500" value="Free Acc Bapak">
                    </div>
                </div>
                <textarea id="wa-content-survey" rows="12" class="w-full p-3 border rounded-lg border-gray-300 font-mono text-xs focus:outline-none focus:border-purple-500" readonly></textarea>
                <button onclick="copyWA('wa-content-survey')" class="mt-3 w-full bg-purple-600 text-white font-bold py-3 rounded-xl shadow-md hover:bg-purple-700 transition active:scale-95 flex items-center justify-center gap-2"><i class="fas fa-copy"></i> Salin Format Survey</button>
            </div>
        </div>
    </div>
</div>

<script>
let allData = [];
let currentFilteredData = [];
const currentUserRole = "<?= $userRole ?>";
const isGudang = <?= $isGudang ? 'true' : 'false' ?>;
const isAdminPusat = <?= $isAdminPusat ? 'true' : 'false' ?>;
let currentOrderForWA = null;

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadFaktur();
    if(!isGudang) loadCabangFilter();
});

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

async function loadFaktur() {
    try {
        const res = await fetch('api/get_orders.php');
        const json = await res.json();
        if(json.status === 'success') {
            allData = json.data;
            filterTable();
        }
    } catch(e) { console.error("Gagal load data:", e); }
}

function renderTable(data) {
    const tbody = document.getElementById('faktur-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-gray-400 uppercase tracking-widest font-bold">Tidak ada faktur ditemukan.</td></tr>';
        renderPagination(0, 0);
        return;
    }

    // LOGIKA PAGINASI: Potong data berdasarkan halaman
    const totalPages = Math.ceil(data.length / rowsPerPage);
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);

    let rowsHTML = '';
    paginatedData.forEach(o => {
        const date = new Date(o.created_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
        const grand = Math.round(parseFloat(o.grand_total) || 0);
        const dp = Math.round(parseFloat(o.dp_amount) || 0);
        let sisa = grand - dp;
        if(o.pay_status === 'Lunas') sisa = 0;

        let payInfo = '';
        if(o.pay_status === 'Lunas') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-emerald-600 bg-emerald-50 inline-block px-2.5 py-1 rounded-md border border-emerald-200 uppercase">LUNAS</div>`;
        } else if (o.pay_status === 'DP') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-red-500 bg-red-50 inline-block px-2.5 py-1 rounded-md border border-red-200 uppercase">SISA: Rp ${sisa.toLocaleString('id-ID')}</div>`;
        } else {
            payInfo = `<div class="mt-1 text-[10px] font-black text-gray-500 bg-gray-100 inline-block px-2.5 py-1 rounded-md border border-gray-200 text-center uppercase">BELUM BAYAR</div>`;
        }

        if (o.is_finance_verified == 0 && o.order_status !== 'Menunggu Pembayaran') {
            payInfo += `<div class="mt-1 text-[9px] font-bold text-yellow-600 italic tracking-tighter"><i class="fas fa-spinner fa-spin mr-1"></i>Verifikasi Keuangan...</div>`;
        }

        const statusMap = {
            'Menunggu Pembayaran': `<span class="bg-red-50 text-red-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-red-100 uppercase shadow-sm">Menunggu DP</span>`,
            'Menunggu Verifikasi': `<span class="bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-yellow-200 uppercase shadow-sm animate-pulse">Cek Mutasi Awal</span>`,
            'Diproses': `<span class="bg-purple-50 text-purple-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-purple-200 uppercase shadow-sm">Gudang</span>`,
            'Sedang Dipack': `<span class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-orange-200 uppercase shadow-sm">Packing</span>`,
            'Paket Siap': `<span class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-blue-200 uppercase shadow-sm">Siap Kirim</span>`,
            'Dikirim': `<span class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-emerald-200 uppercase shadow-sm">Dikirim</span>`
        };
        let statusBadge = statusMap[o.order_status] || `<span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase shadow-sm">${o.order_status}</span>`;

        let actionHTML = `
            <div class="relative inline-block text-left">
                <button onclick="toggleAction(${o.id})" class="drop-btn text-gray-400 hover:text-purple-600 p-2 rounded-full hover:bg-purple-50 transition">
                    <i class="fas fa-ellipsis-v pointer-events-none"></i>
                </button>
                <div id="action-${o.id}" class="dropdown-content hidden origin-top-right absolute right-0 mt-1 w-44 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 border border-gray-100 py-1">
        `;
        
        // Modal Action
        if(!isGudang) {
            actionHTML += `<a href="#" onclick="showWA(${o.id})" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700 font-bold transition uppercase tracking-tighter"><i class="fab fa-whatsapp w-5"></i> Format WA & Survey</a>`;
            if(o.pay_status !== 'Lunas' && o.order_status !== 'Batal') {
                actionHTML += `<a href="#" onclick="openPaymentModal(${o.id}, ${sisa > 0 ? sisa : grand}, ${dp}, ${grand})" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700 font-bold transition uppercase tracking-tighter"><i class="fas fa-hand-holding-usd w-5"></i> Tambah Cicilan</a>`;
            }

            if(isAdminPusat || (currentUserRole === 'marketing' && o.order_status === 'Menunggu Pembayaran')) {
                actionHTML += `<a href="index.php?page=edit_order&id=${o.id}" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700 font-bold transition uppercase tracking-tighter"><i class="fas fa-edit w-5"></i> Edit Pesanan</a>`;
                actionHTML += `<div class="border-t border-gray-100 my-1"></div>`;
                actionHTML += `<a href="#" onclick="deleteOrder(${o.id})" class="block px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 font-bold transition uppercase tracking-tighter"><i class="fas fa-trash w-5"></i> Hapus Pesanan</a>`;
            }
        }
        
        // Lihat Detail HP Friendly untuk Semua
        actionHTML += `<a href="#" onclick="viewDetail(${o.id}, '${o.kp_number}')" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-purple-50 font-bold transition uppercase tracking-tighter"><i class="fas fa-search w-5"></i> Lihat Rincian</a>`;
        
        actionHTML += `</div></div>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-bold text-xs">${date}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-purple-700 font-black font-mono text-sm tracking-wide">${o.kp_number}</div>
                    <div class="text-[9px] text-gray-400 mt-0.5 font-bold uppercase"><i class="fas fa-warehouse mr-1"></i>${o.warehouse_source || 'Pusat'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-gray-900 uppercase font-black text-xs">${o.customer_name}</div>
                    ${payInfo}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-gray-800 font-black text-sm">Rp ${grand.toLocaleString('id-ID')}</div>
                    <div class="text-[9px] text-gray-400 font-bold uppercase mt-0.5">${o.tipe_order || 'Reguler'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-600 uppercase text-[11px] font-black">${o.marketing_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${actionHTML}</td>
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
    const filterEl = document.getElementById('filter-cabang');
    const cabang = filterEl ? filterEl.value : '';

    const filtered = allData.filter(o => {
        const matchText = o.customer_name.toLowerCase().includes(term) || o.kp_number.toLowerCase().includes(term);
        const matchCabang = (cabang === '') || (o.warehouse_source === cabang);
        return matchText && matchCabang;
    });
    
    currentFilteredData = filtered; 
    currentPage = 1; 
    renderTable(currentFilteredData);
}

// --- MODAL PEMBAYARAN ---
function openPaymentModal(id, tagihanSisa, dpLama, totalSemua) {
    document.getElementById('pay-order-id').value = id;
    document.getElementById('pay-existing-dp').value = dpLama;
    document.getElementById('pay-grand-total-val').value = totalSemua;
    document.getElementById('pay-grand-total').innerText = "Rp " + tagihanSisa.toLocaleString('id-ID');
    
    const labelTagihan = document.getElementById('label-tagihan-modal');
    labelTagihan.innerText = dpLama > 0 ? "SISA TAGIHAN SAAT INI" : "TOTAL TAGIHAN BERSIH";
    
    document.getElementById('pay-status').value = "DP";
    document.getElementById('pay-dp-amount').value = '';
    document.getElementById('pay-proof-file').value = '';
    
    checkPayModal();
    document.getElementById('modal-payment').classList.remove('hidden');
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
}

function checkPayModal() {
    const val = document.getElementById('pay-status').value;
    document.getElementById('pay-dp-box').classList.toggle('hidden', val !== 'DP');
}

async function submitPayment() {
    const orderId = document.getElementById('pay-order-id').value;
    let payStatus = document.getElementById('pay-status').value;
    const existingDp = Math.round(parseFloat(document.getElementById('pay-existing-dp').value) || 0);
    const grandTotal = Math.round(parseFloat(document.getElementById('pay-grand-total-val').value) || 0);
    
    const maxAllowed = grandTotal - existingDp;
    let finalDpToSend = 0;
    
    if (payStatus === 'DP') {
        const nominalTransfer = Math.round(parseFloat(document.getElementById('pay-dp-amount').value) || 0);
        if (nominalTransfer <= 0) return Swal.fire('Oops', 'Nominal transfer tidak boleh 0!', 'warning');
        if (nominalTransfer > maxAllowed) return Swal.fire('Peringatan!', `Transfer melebihi sisa tagihan (Maks Rp ${maxAllowed.toLocaleString('id-ID')})`, 'warning');

        finalDpToSend = existingDp + nominalTransfer;
        if (finalDpToSend >= grandTotal) { payStatus = 'Lunas'; finalDpToSend = grandTotal; }
    } else { finalDpToSend = grandTotal; }

    const fileInput = document.getElementById('pay-proof-file');
    if (fileInput.files.length === 0) return Swal.fire('Oops', 'Wajib melampirkan foto struk transfer!', 'warning');

    Swal.fire({title: 'Mengupload...', allowOutsideClick: false}); Swal.showLoading();
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('pay_status', payStatus);
    formData.append('dp_amount', finalDpToSend);
    formData.append('payment_proof', fileInput.files[0]);

    try {
        const res = await fetch('api/update_payment.php', { method: 'POST', body: formData });
        const json = await res.json();
        if(json.status === 'success') {
            closeModal('modal-payment'); 
            Swal.fire('Terkirim!', 'Bukti disubmit ke Keuangan.', 'success'); 
            loadFaktur(); 
        } else { Swal.fire('Gagal', json.message, 'error'); }
    } catch(e) { Swal.fire('Error', 'Koneksi jaringan bermasalah', 'error'); }
}

function deleteOrder(id) {
    Swal.fire({title: 'Hapus Faktur?', text: 'Data tidak dapat dikembalikan.', icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Hapus'})
    .then((res) => {
        if(res.isConfirmed) {
            fetch('api/order_action.php', { method: 'POST', body: JSON.stringify({action: 'delete', order_id: id}) })
            .then(r => r.json()).then(d => {
                if(d.status === 'success') loadFaktur();
                else Swal.fire('Error', d.message, 'error');
            });
        }
    });
}

// --- FUNGSI GENERATE WA (DIPERBARUI AUTO DETEKSI UKURAN) ---
async function showWA(id) {
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    Swal.showLoading();
    try {
        const res = await fetch(`api/get_order_detail.php?id=${id}`);
        const json = await res.json();
        
        if(json.status === 'success') {
            const o = json.data.order;
            const items = json.data.items;
            const ins = json.data.install;
            
            currentOrderForWA = o; // Simpan untuk survey

            // 1. FORMAT BARANG KP (Auto Deteksi Ukuran Potong)
            let kpItemsTxt = '';
            items.forEach((i, idx) => {
                let sizeStr = "..... m x ..... m";
                let rawName = i.product_name;

                // Jika di item_note ada format ukuran dari Input/Edit order
                if (i.item_note) {
                    const match = i.item_note.match(/\[Ukuran:\s*(.+?)\]/i);
                    if (match && match[1]) {
                        sizeStr = match[1]; 
                    }
                    rawName = i.item_note.replace(/\s*\[Ukuran:\s*(.+?)\]/i, '');
                }

                kpItemsTxt += `• ${rawName}\n   Qty: ${parseFloat(i.qty)} ${i.unit}\n   Ukuran Potong: ${sizeStr}\n   Subtotal: Rp ${parseInt(i.subtotal).toLocaleString('id-ID')}\n\n`;
            });
            
            let sisa = Math.round(parseFloat(o.grand_total)) - Math.round(parseFloat(o.dp_amount || 0));
            if (o.pay_status === 'Lunas') sisa = 0;

            let payTxt = o.pay_status;
            if(o.pay_status === 'DP') {
                payTxt = `Total Dibayar: Rp ${parseInt(o.dp_amount || 0).toLocaleString('id-ID')}\nSisa Pembayaran: Rp ${sisa.toLocaleString('id-ID')}`;
            }

            let trafficTxt = o.traffic_source && o.traffic_source !== 'Organik' ? `[${o.traffic_source}]` : '';
            let brandName = o.brand || 'Sigma';

            const kpFormat = `*[${brandName}] PESANAN BARANG*
_Marketing: ${o.marketing_name}_ ${trafficTxt}

*NO: ${o.kp_number}*
Gudang: ${o.warehouse_source}
Tgl Kirim: ${new Date(o.delivery_date).toLocaleDateString('id-ID')}

*Data Pelanggan:*
Nama: ${o.customer_name}
No WA: ${o.customer_phone}
Alamat: ${o.customer_address}
Maps: ${o.maps_link}

*Rincian Belanja:*
${kpItemsTxt}*Total Tagihan: Rp ${parseInt(o.grand_total).toLocaleString('id-ID')}*

Status Bayar: ${o.pay_status}
${o.pay_status === 'DP' ? payTxt : ''}`;

            document.getElementById('wa-content-kp').value = kpFormat;

            // 2. FORMAT INSTALASI KI
            if(o.ki_number && o.ki_number !== '-' && ins) {
                document.getElementById('tab-ki').classList.remove('hidden');
                const kiFormat = `*[${brandName}] TIKET INSTALASI*
_Marketing: ${o.marketing_name}_

*NO: ${o.ki_number}*
Mandor: ${ins.mandor_name || '-'}
Tgl Pasang: ${new Date(ins.work_date).toLocaleDateString('id-ID')}

*Data Lokasi:*
Pelanggan: ${o.customer_name}
No WA: ${o.customer_phone}
Alamat Pasang: ${o.customer_address}
Maps: ${o.maps_link}

*Tugas Lapangan:*
Pemasangan rumput seluas ${parseFloat(ins.area_size)} m²
Total Biaya Jasa: Rp ${parseInt(ins.total_price).toLocaleString('id-ID')}

_(Mohon tim lapangan konfirmasi foto jika sudah selesai)_`;
                document.getElementById('wa-content-ki').value = kiFormat;
            } else {
                document.getElementById('tab-ki').classList.add('hidden');
            }

            // 3. RESET FORM SURVEY DAN GENERATE ULANG
            document.getElementById('survey-date').value = '';
            document.getElementById('survey-time').value = '';
            document.getElementById('survey-cost').value = 'Free Acc Bapak';
            generateSurveyWA();

            Swal.close();
            document.getElementById('modal-wa').classList.remove('hidden');
            switchTab('kp'); // Default buka tab KP
        } else { Swal.fire('Error', json.message, 'error'); }
    } catch(e) { console.error(e); Swal.fire('Error', 'Gagal memuat format WA', 'error'); }
}

function generateSurveyWA() {
    if(!currentOrderForWA) return;
    const o = currentOrderForWA;
    
    const sDateVal = document.getElementById('survey-date').value;
    const sTimeVal = document.getElementById('survey-time').value;
    const sCostVal = document.getElementById('survey-cost').value || 'Free Acc Bapak';

    let dateStr = '[Pilih Tanggal]';
    if(sDateVal) {
        const d = new Date(sDateVal);
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        dateStr = `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }

    const timeStr = sTimeVal ? sTimeVal : '[Ketik Jam]';
    const brandName = o.brand || 'Sigma';

    const surveyFormat = `*Format Survey*
*${brandName} by ${o.marketing_name}*

Nama : ${o.customer_name}
No. Hp : ${o.customer_phone || '-'}
Alamat : ${o.customer_address || '-'} (${o.maps_link || 'Maps'})

Hari/ tanggal : ${dateStr}
Jam : ${timeStr} sudah di lokasi

Biaya Survei: ${sCostVal}

Note :
- Perhatikan Jamnya jangan sampai telat.
- Infokan klo mau berangkat
- Bawa All Sampel Rumput Lanscape terutama Swiss Premium 2,5cm kecuali Mini Soccer
- Bawa sampel Drainase cell`;

    document.getElementById('wa-content-survey').value = surveyFormat;
}

function switchTab(type) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    const tabs = ['tab-kp', 'tab-ki', 'tab-survey'];
    tabs.forEach(t => {
        const btn = document.getElementById(t);
        if(btn) {
            btn.classList.remove('border-purple-600', 'text-purple-700');
            btn.classList.add('text-gray-500', 'border-transparent');
        }
    });

    document.getElementById(`box-${type}`).classList.remove('hidden');
    
    if (type === 'kp') {
        document.getElementById('tab-kp').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById('tab-kp').classList.add('border-purple-600', 'text-purple-700');
    } else if (type === 'ki') {
        document.getElementById('tab-ki').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById('tab-ki').classList.add('border-purple-600', 'text-purple-700');
    } else if (type === 'survey') {
        document.getElementById('tab-survey').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById('tab-survey').classList.add('border-purple-600', 'text-purple-700');
    }
}

function copyWA(id) {
    const txt = document.getElementById(id);
    txt.select();
    document.execCommand('copy');
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Format disalin!', showConfirmButton: false, timer: 1500 });
}

// FUNGSI LIHAT DETAIL RESPONSIVE HP (DITAMBAH RINCIAN POTONG)
async function viewDetail(id, kp) {
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    Swal.fire({ title: 'Memuat Detail...', allowOutsideClick: false });
    Swal.showLoading();
    try {
        const res = await fetch(`api/get_order_detail.php?id=${id}`);
        const json = await res.json();
        if(json.status === 'success') {
            const items = json.data.items;
            
            // Design grid list yang nyaman di HP
            let list = `<div class="text-left space-y-3 mt-2">
                            <div class="font-bold text-center text-purple-700 font-mono mb-4 border-b border-purple-200 pb-2">${kp}</div>`;
            
            items.forEach(i => { 
                let sizeBadge = '';
                let rawName = i.product_name;

                // Ekstrak ukuran untuk ditampilkan sebagai label khusus
                if (i.item_note) {
                    const match = i.item_note.match(/\[Ukuran:\s*(.+?)\]/i);
                    if (match && match[1]) {
                        sizeBadge = `<div class="mt-2 bg-purple-100 text-purple-800 border border-purple-200 px-3 py-1.5 rounded-lg text-[11px] font-black inline-flex items-center shadow-sm">
                                        <i class="fas fa-cut mr-1.5"></i> POTONG: ${match[1]}
                                     </div>`;
                    }
                    rawName = i.item_note.replace(/\s*\[Ukuran:\s*(.+?)\]/i, '');
                }

                list += `
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl shadow-sm">
                    <div class="font-black text-gray-800 text-sm">${rawName}</div>
                    <div class="text-xs text-gray-600 mt-1">Total Qty: <span class="font-black text-purple-700">${parseFloat(i.qty)} ${i.unit}</span></div>
                    ${sizeBadge}
                </div>`; 
            });
            list += '</div>';
            Swal.fire({ title: 'Rincian Barang', html: list, confirmButtonText: 'Tutup', confirmButtonColor: '#7e22ce' });
        }
    } catch(e) { Swal.fire('Error', 'Gagal memuat data', 'error'); }
}
</script>