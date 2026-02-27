<?php 
$userRole = $_SESSION['user']['role'] ?? ''; 
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// Definisi Role
$isGudang = ($userRole === 'admin_gudang');
$isMarketing = ($userRole === 'marketing');
$isAdminPusat = ($userRole === 'super_admin' || $userRole === 'admin' || $uLogin === 'admin' || $userRole === 'keuangan');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Faktur Instalasi (KI)</h1>
            <p class="text-sm text-gray-500">Instalasi (KI) > <span class="text-purple-600 font-bold uppercase">Faktur Jasa</span></p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <button onclick="loadKI()" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm w-full md:w-auto justify-center">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="relative w-full md:w-64">
            <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari No KI / Pelanggan..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 outline-none shadow-sm transition">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
        </div>
        
        <?php if(!$isGudang): ?>
        <div class="w-full md:w-auto">
            <select id="filter-cabang" onchange="filterTable()" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-50 focus:ring-purple-500 outline-none font-bold shadow-sm">
                <option value="">-- Semua Cabang --</option>
                <!-- Opsi cabang dimuat via JS -->
            </select>
        </div>
        <?php else: ?>
        <div class="bg-purple-100 text-purple-800 px-4 py-2 rounded-lg text-xs font-bold border border-purple-200 flex items-center justify-center gap-2 shadow-sm">
            <i class="fas fa-warehouse"></i> CABANG: <?= strtoupper($userWh) ?> (Read-Only)
        </div>
        <?php endif; ?>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tgl Order</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. Tiket (KI)</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan & Info Bayar</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Detail Pengerjaan</th>
                        <th class="px-6 py-4 text-right whitespace-nowrap">Total Tagihan Jasa</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Lapangan</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody id="ki-rows" class="bg-white divide-y divide-gray-100 text-sm">
                    <tr><td colspan="7" class="text-center py-10 text-gray-400 italic font-bold">Memuat data instalasi...</td></tr>
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

<!-- MODAL INPUT PEMBAYARAN KI (KHUSUS MARKETING) -->
<div id="modal-payment" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-800"><i class="fas fa-hand-holding-usd mr-2"></i>Bayar Jasa Instalasi</h3>
            <button onclick="closeModal('modal-payment')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="pay-install-id">
            <input type="hidden" id="pay-existing-dp">
            <input type="hidden" id="pay-grand-total">
            
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                <label class="block text-xs font-bold text-gray-500 uppercase" id="label-tagihan-modal">Sisa Tagihan Jasa</label>
                <div class="text-3xl font-black text-gray-800 mt-1" id="pay-total-display">Rp 0</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Pembayaran</label>
                <select id="pay-status" onchange="checkPayModal()" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-purple-500 bg-white font-bold text-gray-700 outline-none shadow-sm">
                    <option value="DP">Uang Muka / Tambah Cicilan</option>
                    <option value="Lunas">Pelunasan Penuh (Lunas)</option>
                </select>
            </div>

            <div id="pay-dp-box">
                <label class="block text-xs font-bold text-purple-600 uppercase mb-1">Nominal Transfer Saat Ini (Rp)</label>
                <input type="number" id="pay-dp-amount" class="w-full border border-purple-300 rounded-lg p-3 font-bold text-purple-800 outline-none focus:ring-purple-500 shadow-sm" placeholder="Contoh: 150000">
            </div>

            <div class="border-2 border-dashed border-purple-300 rounded-xl p-5 text-center hover:bg-purple-50 transition cursor-pointer group">
                <label class="block text-xs font-bold text-purple-700 uppercase mb-2 group-hover:scale-105 transition-transform">Upload Bukti Transfer Jasa</label>
                <input type="file" id="pay-proof-file" accept="image/png, image/jpeg, image/jpg, image/webp" class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-800 w-full cursor-pointer hover:file:bg-purple-200">
            </div>
            
            <button onclick="submitPaymentKI()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-4 rounded-xl shadow-lg mt-2 transition flex items-center justify-center gap-2 transform active:scale-95">
                <i class="fas fa-paper-plane"></i> KIRIM KE KEUANGAN
            </button>
        </div>
    </div>
</div>

<!-- MODAL WA -->
<div id="modal-wa" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-4 border-b bg-green-50 flex justify-between items-center">
            <h3 class="font-bold text-green-900"><i class="fab fa-whatsapp mr-2 text-green-600 text-xl"></i>Format WhatsApp</h3>
            <button onclick="closeModal('modal-wa')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <div class="flex border-b bg-white overflow-x-auto">
            <button onclick="switchTab('ki')" id="tab-ki" class="flex-1 py-3 px-2 text-xs md:text-sm font-bold border-b-2 border-purple-600 text-purple-700 transition outline-none whitespace-nowrap">Format Instalasi (KI)</button>
            <button onclick="switchTab('survey')" id="tab-survey" class="flex-1 py-3 px-2 text-xs md:text-sm font-bold text-gray-500 hover:text-purple-600 border-b-2 border-transparent transition outline-none whitespace-nowrap">Format Survey Lokasi</button>
        </div>

        <div class="p-5 flex-1 overflow-y-auto bg-gray-50">
            <!-- Isi Tab Instalasi -->
            <div id="box-ki" class="tab-content">
                <p class="text-xs text-gray-500 mb-2">Salin teks ini dan kirim ke mandor/tim lapangan.</p>
                <textarea id="wa-content-ki" rows="12" class="w-full p-3 border rounded-lg border-gray-300 font-mono text-xs focus:outline-none focus:border-purple-500" readonly></textarea>
                <button onclick="copyWA('wa-content-ki')" class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl shadow-md transition active:scale-95 flex items-center justify-center gap-2"><i class="fas fa-copy"></i> Salin Format Instalasi</button>
            </div>

            <!-- Isi Tab Survey -->
            <div id="box-survey" class="tab-content hidden">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-blue-700 uppercase mb-1">Tanggal Survey</label>
                        <input type="date" id="survey-date" onchange="generateSurveyWA()" class="w-full border-blue-300 rounded p-1.5 text-xs outline-none focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-blue-700 uppercase mb-1">Jam (Cth: 13.00)</label>
                        <input type="time" id="survey-time" oninput="generateSurveyWA()" class="w-full border-blue-300 rounded p-1.5 text-xs outline-none focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-blue-700 uppercase mb-1">Biaya Survey</label>
                        <input type="text" id="survey-cost" oninput="generateSurveyWA()" placeholder="Cth: Free Acc Bapak / Rp 50.000" class="w-full border-blue-300 rounded p-1.5 text-xs outline-none focus:ring-blue-500" value="Free Acc Bapak">
                    </div>
                </div>
                <textarea id="wa-content-survey" rows="12" class="w-full p-3 border rounded-lg border-gray-300 font-mono text-xs focus:outline-none focus:border-blue-500" readonly></textarea>
                <button onclick="copyWA('wa-content-survey')" class="mt-3 w-full bg-blue-600 text-white font-bold py-3 rounded-xl shadow-md hover:bg-blue-700 transition active:scale-95 flex items-center justify-center gap-2"><i class="fas fa-copy"></i> Salin Format Survey</button>
            </div>
        </div>
    </div>
</div>

<script>
let allData = [];
let currentFilteredData = [];
const currentUserRole = "<?= $userRole ?>";
const isGudang = <?= $isGudang ? 'true' : 'false' ?>;
const myWarehouse = "<?= $userWh ?>"; 
let currentOrderForWA = null; 

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadKI();
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

async function loadKI() {
    try {
        const res = await fetch('api/get_ki.php');
        const json = await res.json();
        if(json.status === 'success') { 
            allData = json.data; 
            filterTable();
        }
    } catch(e) { console.error(e); }
}

function renderTable(data) {
    const tbody = document.getElementById('ki-rows');
    tbody.innerHTML = '';

    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-gray-400 font-bold uppercase tracking-widest">Tidak ada tiket instalasi.</td></tr>';
        renderPagination(0, 0);
        return;
    }

    // LOGIKA PAGINASI: Potong data berdasarkan halaman
    const totalPages = Math.ceil(data.length / rowsPerPage);
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);

    let rowsHTML = '';
    paginatedData.forEach(i => {
        const date = new Date(i.order_date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
        
        const total = Math.round(parseFloat(i.total_price) || 0);
        const dp = Math.round(parseFloat(i.dp_amount) || 0);
        let sisa = total - dp;
        if(i.pay_status === 'Lunas') sisa = 0;

        let payInfo = '';
        if(i.pay_status === 'Lunas') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-emerald-600 bg-emerald-50 inline-block px-2.5 py-1 rounded border border-emerald-200 uppercase">Lunas</div>`;
        } else if (i.pay_status === 'DP') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-red-500 bg-red-50 inline-block px-2.5 py-1 rounded border border-red-200 uppercase">Sisa: Rp ${sisa.toLocaleString('id-ID')}</div>`;
        } else {
            payInfo = `<div class="mt-1 text-[10px] font-black text-gray-500 bg-gray-100 inline-block px-2.5 py-1 rounded border border-gray-200 uppercase">Belum Bayar</div>`;
        }

        if (i.is_finance_verified == 0 && i.status !== 'Menunggu Pembayaran') {
            payInfo += `<div class="mt-1 text-[9px] font-bold text-yellow-600 italic tracking-tighter"><i class="fas fa-spinner fa-spin mr-1"></i>Verifikasi Keuangan...</div>`;
        }

        const statusMap = {
            'Menunggu Pembayaran': `<span class="bg-red-50 text-red-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-red-100 uppercase shadow-sm">Menunggu DP</span>`,
            'Menunggu Verifikasi': `<span class="bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-yellow-200 uppercase shadow-sm animate-pulse">Cek Mutasi</span>`,
            'Dijadwalkan': `<span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-gray-200 uppercase shadow-sm">Antre Jadwal</span>`,
            'Sedang Dikerjakan': `<span class="bg-orange-100 text-orange-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-orange-200 uppercase shadow-sm">Dikerjakan</span>`,
            'Selesai': `<span class="bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-emerald-200 uppercase shadow-sm">Selesai</span>`
        };
        let statusBadge = statusMap[i.status] || `<span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase shadow-sm">${i.status}</span>`;

        let actionHTML = `<div class="relative inline-block text-left">
            <button onclick="toggleAction(${i.id})" class="drop-btn text-gray-400 hover:text-purple-600 p-2 rounded-full hover:bg-purple-50 transition"><i class="fas fa-ellipsis-v pointer-events-none"></i></button>
            <div id="action-${i.id}" class="dropdown-content hidden origin-top-right absolute right-0 mt-1 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 border border-gray-100 py-1">`;
        
        let isPelunasan = (dp > 0 && sisa > 0);
        let tagihanYangHarusDibayar = isPelunasan ? sisa : total;

        actionHTML += `<a href="#" onclick="showWA(${i.id})" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-bold transition uppercase tracking-tighter"><i class="fab fa-whatsapp w-5"></i> Format WA & Survey</a>`;

        if(currentUserRole === 'marketing') {
            if(i.pay_status !== 'Lunas' && i.status !== 'Batal') {
                actionHTML += `<a href="#" onclick="openPaymentModal(${i.id}, ${tagihanYangHarusDibayar}, ${dp}, ${total})" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700 font-bold transition uppercase tracking-tighter"><i class="fas fa-hand-holding-usd w-5"></i> Tambah Cicilan</a>`;
            }
        }

        actionHTML += `</div></div>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-bold text-xs">${date}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-black text-purple-700 font-mono text-sm tracking-wide">${i.ki_number}</div>
                    <div class="text-[9px] text-gray-400 mt-0.5 uppercase font-bold"><i class="fas fa-warehouse mr-1"></i>${i.warehouse_source || 'Pusat'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-gray-900 uppercase font-black text-xs">${i.customer_name}</div>
                    <div class="text-[9px] text-gray-400 uppercase font-bold mt-1">Marketing: ${i.marketing_name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700 font-medium">
                    Mandor: <span class="font-bold text-gray-900">${i.mandor_name || '-'}</span><br>
                    Luas Area: <span class="font-bold text-purple-600">${parseFloat(i.area_size)} m²</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-gray-800 font-black text-sm">Rp ${total.toLocaleString('id-ID')}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${actionHTML}</td>
            </tr>
        `;
    });
    tbody.innerHTML = rowsHTML;
    
    // Tampilkan tombol paginasi
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
    renderTable(currentFilteredData); // Gunakan data hasil filter, bukan allData
}

function filterTable() {
    const term = document.getElementById('search').value.toLowerCase();
    const filterEl = document.getElementById('filter-cabang');
    
    let cabangPilihan = '';
    if (isGudang) {
        cabangPilihan = myWarehouse; 
    } else if (filterEl) {
        cabangPilihan = filterEl.value; 
    }

    const filtered = allData.filter(i => {
        const matchText = i.customer_name.toLowerCase().includes(term) || i.ki_number.toLowerCase().includes(term);
        const dataCabang = i.warehouse_source ? i.warehouse_source : 'Pusat';
        const matchCabang = (cabangPilihan === '' || dataCabang === cabangPilihan);
        return matchText && matchCabang;
    });

    currentFilteredData = filtered; // Simpan data yang sedang terfilter
    currentPage = 1; // Reset selalu ke halaman 1 saat difilter
    renderTable(currentFilteredData);
}

// --- FUNGSI GENERATE WA & SURVEY ---
async function showWA(id) {
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    
    const installData = allData.find(i => i.id == id);
    if (!installData) return Swal.fire('Error', 'Data pesanan tidak ditemukan', 'error');

    Swal.fire({ title: 'Memuat Format...', allowOutsideClick: false });
    Swal.showLoading();

    try {
        const res = await fetch(`api/get_order_detail.php?id=${installData.order_id}`);
        const json = await res.json();

        if (json.status === 'success') {
            const o = json.data.order;
            const ins = json.data.install || installData; 
            
            currentOrderForWA = o; 

            // 1. GENERATE FORMAT KI
            const brandName = o.brand || 'Sigma';
            const kiFormat = `*[${brandName}] TIKET INSTALASI*
_Marketing: ${o.marketing_name}_

*NO: ${o.ki_number}*
Mandor: ${ins.mandor_name || '-'}
Tgl Pasang: ${ins.work_date ? new Date(ins.work_date).toLocaleDateString('id-ID') : '-'}

*Data Lokasi:*
Pelanggan: ${o.customer_name}
No WA: ${o.customer_phone || '-'}
Alamat Pasang: ${o.customer_address || '-'}
Maps: ${o.maps_link || '-'}

*Tugas Lapangan:*
Pemasangan rumput seluas ${parseFloat(ins.area_size)} m²
Total Biaya Jasa: Rp ${parseInt(ins.total_price).toLocaleString('id-ID')}

_(Mohon tim lapangan konfirmasi foto jika sudah selesai)_`;
            
            document.getElementById('wa-content-ki').value = kiFormat;

            // 2. RESET FORM SURVEY DAN GENERATE ULANG
            document.getElementById('survey-date').value = '';
            document.getElementById('survey-time').value = '';
            document.getElementById('survey-cost').value = 'Free Acc Bapak';
            generateSurveyWA(); 

            Swal.close();
            document.getElementById('modal-wa').classList.remove('hidden');
            switchTab('ki'); 
        } else {
            Swal.fire('Error', json.message, 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Gagal memuat format WA dari server', 'error');
    }
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
    const tabs = ['tab-ki', 'tab-survey'];
    tabs.forEach(t => {
        const btn = document.getElementById(t);
        if(btn) {
            btn.classList.remove('border-purple-600', 'text-purple-700', 'border-blue-600', 'text-blue-700');
            btn.classList.add('text-gray-500', 'border-transparent');
        }
    });

    document.getElementById(`box-${type}`).classList.remove('hidden');
    
    if (type === 'ki') {
        document.getElementById('tab-ki').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById('tab-ki').classList.add('border-purple-600', 'text-purple-700');
    } else if (type === 'survey') {
        document.getElementById('tab-survey').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById('tab-survey').classList.add('border-blue-600', 'text-blue-700');
    }
}

function copyWA(id) {
    const txt = document.getElementById(id);
    txt.select();
    document.execCommand('copy');
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Format disalin!', showConfirmButton: false, timer: 1500 });
}

function openPaymentModal(id, tagihanSekarang, dpLama, totalSemua) {
    if(currentUserRole !== 'marketing') return Swal.fire('Akses Ditolak', 'Hanya Marketing.', 'error');
    document.getElementById('pay-install-id').value = id;
    document.getElementById('pay-existing-dp').value = dpLama;
    document.getElementById('pay-grand-total').value = totalSemua;
    document.getElementById('pay-total-display').innerText = "Rp " + tagihanSekarang.toLocaleString('id-ID');
    document.getElementById('label-tagihan-modal').innerText = dpLama > 0 ? "SISA TAGIHAN SAAT INI" : "TOTAL TAGIHAN JASA KESELURUHAN";
    const payStatusSelect = document.getElementById('pay-status');
    payStatusSelect.value = "DP";
    Array.from(payStatusSelect.options).forEach(opt => opt.disabled = false);
    document.getElementById('pay-dp-amount').value = '';
    document.getElementById('pay-proof-file').value = '';
    checkPayModal();
    document.getElementById('modal-payment').classList.remove('hidden');
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
}

function checkPayModal() {
    document.getElementById('pay-dp-box').classList.toggle('hidden', document.getElementById('pay-status').value !== 'DP');
}

async function submitPaymentKI() {
    const installId = document.getElementById('pay-install-id').value;
    let payStatus = document.getElementById('pay-status').value;
    const existingDp = Math.round(parseFloat(document.getElementById('pay-existing-dp').value) || 0);
    const grandTotal = Math.round(parseFloat(document.getElementById('pay-grand-total').value) || 0);
    const maxAllowed = grandTotal - existingDp;
    let dpAmountToSend = 0;

    if (payStatus === 'DP') {
        const nominal = Math.round(parseFloat(document.getElementById('pay-dp-amount').value) || 0);
        if (nominal <= 0) return Swal.fire('Oops', 'Nominal transfer tidak boleh 0!', 'warning');
        if (nominal > maxAllowed) return Swal.fire('Peringatan', `Maksimal transfer melebihi tagihan: Rp ${maxAllowed.toLocaleString('id-ID')}`, 'warning');
        dpAmountToSend = existingDp + nominal;
        if (dpAmountToSend >= grandTotal) { payStatus = 'Lunas'; dpAmountToSend = grandTotal; }
    } else { dpAmountToSend = grandTotal; }

    const fileInput = document.getElementById('pay-proof-file');
    if (fileInput.files.length === 0) return Swal.fire('Oops', 'Wajib melampirkan foto struk transfer!', 'warning');

    Swal.fire({title: 'Mengirim Data...', allowOutsideClick: false}); Swal.showLoading();
    const formData = new FormData();
    formData.append('install_id', installId);
    formData.append('pay_status', payStatus);
    formData.append('dp_amount', dpAmountToSend);
    formData.append('payment_proof', fileInput.files[0]);

    try {
        const res = await fetch('api/update_payment_ki.php', { method: 'POST', body: formData });
        const json = await res.json();
        if(json.status === 'success') {
            closeModal('modal-payment');
            Swal.fire('Terkirim!', 'Bukti berhasil dikirim ke bagian Keuangan.', 'success');
            loadKI();
        } else Swal.fire('Error', json.message, 'error');
    } catch(e) { Swal.fire('Error', 'Koneksi gagal saat mengupload.', 'error'); }
}
</script>