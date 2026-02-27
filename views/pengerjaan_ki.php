<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// HAK AKSES PROSES: Dibuka untuk Marketing, Admin Gudang, dan Super Admin
$canProcess = ($userRole === 'marketing' || $userRole === 'admin_gudang' || $userRole === 'super_admin' || $uLogin === 'admin');

// Penanda Role Khusus
$isGudang = ($userRole === 'admin_gudang');
$isAdminPusat = ($userRole === 'super_admin' || $uLogin === 'admin' || $userRole === 'keuangan');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Pengerjaan Lapangan (KI)</h1>
            <p class="text-sm text-gray-500">Instalasi (KI) > <span class="text-purple-600 font-bold uppercase">Progres Kerja</span></p>
        </div>
        
        <?php if(!$canProcess): ?>
        <div class="bg-gray-100 text-gray-500 px-5 py-2.5 rounded-xl text-xs font-bold shadow-sm flex items-center gap-3 border border-gray-200">
            <i class="fas fa-eye text-lg"></i> 
            <div class="flex flex-col text-left">
                <span class="opacity-70 text-[9px] uppercase">Akses Terbatas</span>
                <span>Mode Read-Only</span>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-purple-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center gap-3">
            <i class="fas fa-hard-hat text-lg"></i> 
            <div class="flex flex-col text-left">
                <span class="opacity-70 text-[9px] uppercase">Akses Eksekusi</span>
                <span><?= $isGudang ? 'GUDANG '.strtoupper($userWh) : 'TIM LAPANGAN' ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="flex flex-wrap gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full md:w-64">
                <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari No KI / Pelanggan..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 outline-none shadow-sm transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>
            
            <!-- Filter Status Pengerjaan -->
            <select id="filter-status" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:ring-purple-500 outline-none font-bold shadow-sm transition">
                <option value="">Semua Status Lapangan</option>
                <option value="Dijadwalkan">Menunggu Jadwal (Antre)</option>
                <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                <option value="Selesai">Sudah Selesai</option>
            </select>

            <!-- Filter Cabang (Hanya untuk Admin Pusat/Keuangan) -->
            <?php if($isAdminPusat): ?>
            <select id="filter-cabang" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-purple-700 font-bold bg-purple-50 focus:ring-purple-500 outline-none shadow-sm transition">
                <option value="">-- Semua Cabang --</option>
                <!-- Data diisi via JS -->
            </select>
            <?php endif; ?>
        </div>

        <div class="flex gap-2">
            <button onclick="loadWorkKI()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center justify-center gap-2 text-xs w-full md:w-auto">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tgl & Estimasi</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. Tiket (KI)</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan & Info Bayar</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">PJ / Mandor</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Luas (m²)</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Pengerjaan</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi / Foto</th>
                    </tr>
                </thead>
                <tbody id="work-rows" class="bg-white divide-y divide-gray-100 text-sm">
                    <tr><td colspan="7" class="text-center py-10 text-gray-400 italic font-bold">Memuat data lapangan...</td></tr>
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

<!-- MODAL MULAI KERJA (INPUT PJ & ESTIMASI) -->
<div id="modal-start-work" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900"><i class="fas fa-play-circle mr-2 text-purple-600"></i>Mulai Pengerjaan</h3>
            <button onclick="closeModal('modal-start-work')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="start-install-id">
            
            <p class="text-xs text-gray-500 mb-2 font-medium">Pastikan nama Mandor/Penanggung Jawab dan estimasi selesai sudah sesuai sebelum memulai.</p>
            
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penanggung Jawab (Mandor/Tim)</label>
                <input type="text" id="start-mandor" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-purple-500 focus:border-purple-500 font-bold text-gray-800 outline-none shadow-sm transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Estimasi Selesai (Tanggal)</label>
                <input type="date" id="start-estimasi" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-purple-500 focus:border-purple-500 font-bold text-purple-700 outline-none shadow-sm transition">
            </div>

            <button onclick="submitStartWork()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-4 rounded-xl shadow-lg mt-4 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-hard-hat"></i> EKSEKUSI LAPANGAN
            </button>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD FOTO KERJA -->
<div id="modal-work" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900"><i class="fas fa-camera mr-2 text-purple-600"></i>Upload Hasil Kerja</h3>
            <button onclick="closeModal('modal-work')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="work-install-id">
            <div class="border-2 border-dashed border-purple-300 rounded-xl p-6 text-center hover:bg-purple-50 transition cursor-pointer group">
                <label class="block text-xs font-black text-purple-700 uppercase mb-3 group-hover:scale-105 transition-transform">Foto Lapangan (Selesai Pasang)</label>
                <input type="file" id="work-proof-file" accept="image/png, image/jpeg, image/jpg, image/webp" class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-800 w-full cursor-pointer transition hover:file:bg-purple-200">
            </div>
            <button onclick="submitWorkProof()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-xl shadow-lg mt-2 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> TANDAI SELESAI
            </button>
        </div>
    </div>
</div>

<!-- MODAL LIHAT FOTO KERJA -->
<div id="modal-view" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative transform transition-all">
        <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
            <h3 class="text-lg font-black text-gray-800">Hasil Pemasangan Lapangan</h3>
            <button onclick="closeModal('modal-view')" class="text-gray-500 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 flex justify-center bg-gray-50">
            <img id="work-img" src="" class="max-w-full h-auto max-h-[60vh] object-contain rounded-lg border border-gray-300 shadow-sm">
        </div>
    </div>
</div>

<script>
let allData = [];
let currentFilteredData = [];
const canProcess = <?= $canProcess ? 'true' : 'false' ?>;
const isGudang = <?= $isGudang ? 'true' : 'false' ?>;
const isAdminPusat = <?= $isAdminPusat ? 'true' : 'false' ?>;
const myWarehouse = "<?= $userWh ?>";

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadWorkKI();
    if(isAdminPusat) loadCabangFilter();
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

// Ambil daftar Gudang Dinamis untuk filter pusat
async function loadCabangFilter() {
    try {
        const res = await fetch('api/warehouse_action.php?action=get');
        const json = await res.json();
        if(json.status === 'success') {
            const sel = document.getElementById('filter-cabang');
            if(sel) {
                json.data.forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.name;
                    opt.innerText = w.name;
                    sel.appendChild(opt);
                });
            }
        }
    } catch(e) { console.error(e); }
}

async function loadWorkKI() {
    try {
        const res = await fetch('api/get_ki.php');
        const json = await res.json();
        if(json.status === 'success') {
            // Kita hanya ambil status yg sudah lepas dari verifikasi pembayaran awal
            const validStatuses = ['Dijadwalkan', 'Sedang Dikerjakan', 'Selesai'];
            allData = json.data.filter(i => validStatuses.includes(i.status));
            filterTable();
        }
    } catch(e) { console.error(e); }
}

function renderTable(data) {
    const tbody = document.getElementById('work-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-gray-400 font-bold uppercase tracking-widest">Tidak ada jadwal pengerjaan lapangan.</td></tr>';
        renderPagination(0, 0);
        return;
    }

    // LOGIKA PAGINASI
    const totalPages = Math.ceil(data.length / rowsPerPage);
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);

    let rowsHTML = '';
    paginatedData.forEach(i => {
        const dateOrder = new Date(i.order_date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
        let estimasiStr = i.estimasi_selesai ? new Date(i.estimasi_selesai).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'}) : '-';
        
        // KALKULASI SISA TAGIHAN UNTUK INFO DI LAPANGAN
        const total = parseFloat(i.total_price) || 0;
        const dp = parseFloat(i.dp_amount) || 0;
        let sisa = total - dp;
        if(i.pay_status === 'Lunas') sisa = 0;

        let payInfo = '';
        if(i.pay_status === 'Lunas') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-emerald-600 bg-emerald-50 inline-block px-2.5 py-1 rounded border border-emerald-200 uppercase">Lunas</div>`;
        } else if (i.pay_status === 'DP') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-red-500 bg-red-50 inline-block px-2.5 py-1 rounded border border-red-200 uppercase">Sisa Tagihan: Rp ${sisa.toLocaleString('id-ID')}</div>`;
        } else {
            payInfo = `<div class="mt-1 text-[10px] font-black text-gray-500 bg-gray-100 inline-block px-2.5 py-1 rounded border border-gray-200 uppercase">Belum Bayar</div>`;
        }

        let statusBadge = '';
        if(i.status === 'Dijadwalkan') statusBadge = `<span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-gray-300 uppercase shadow-sm">Menunggu Jadwal</span>`;
        else if(i.status === 'Sedang Dikerjakan') statusBadge = `<span class="bg-purple-100 text-purple-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-purple-300 uppercase animate-pulse shadow-sm">Dikerjakan</span>`;
        else statusBadge = `<span class="bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-emerald-300 uppercase shadow-sm">Selesai</span>`;

        // ACTION BUTTONS
        let actionHTML = `
            <div class="relative inline-block text-left">
                <button onclick="toggleAction(${i.id})" class="drop-btn text-gray-400 hover:text-purple-600 p-2 rounded-full hover:bg-purple-50 transition"><i class="fas fa-ellipsis-v pointer-events-none"></i></button>
                <div id="action-${i.id}" class="dropdown-content hidden origin-top-right absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 border border-gray-100 py-1">
        `;
        
        // Aksi Proses Pengerjaan (Mulai & Selesai)
        if (canProcess) {
            if (i.status === 'Dijadwalkan') {
                actionHTML += `<a href="#" onclick="openStartModal(${i.id}, '${i.mandor_name}')" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700 font-bold transition uppercase tracking-tighter"><i class="fas fa-play-circle w-5"></i> Mulai Kerjakan</a>`;
            } else if (i.status === 'Sedang Dikerjakan') {
                actionHTML += `<a href="#" onclick="openModalWork(${i.id})" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-bold transition uppercase tracking-tighter"><i class="fas fa-camera w-5"></i> Upload Foto Selesai</a>`;
            }
        }

        // Aksi Lihat Foto (Semua bisa akses)
        if (i.work_proof) {
            actionHTML += `<a href="#" onclick="showWorkPhoto('${i.work_proof}')" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-bold transition uppercase tracking-tighter"><i class="fas fa-image w-5"></i> Lihat Foto Hasil</a>`;
        }
        
        // Jika list kosong/Selesai dan role Read-only
        if (actionHTML.indexOf('<a') === -1) {
            actionHTML += `<span class="block px-4 py-2.5 text-[10px] text-gray-400 italic font-bold uppercase">Read-Only / Selesai</span>`;
        }

        actionHTML += `</div></div>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4 whitespace-nowrap text-xs">
                    <div class="text-gray-500 mb-1 font-bold">Mulai: <span class="text-gray-800">${dateOrder}</span></div>
                    <div class="text-gray-500 font-bold">Est: <span class="text-purple-600">${estimasiStr}</span></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-black text-purple-700 font-mono text-sm tracking-wide">${i.ki_number}</div>
                    <div class="text-[9px] text-gray-400 mt-0.5 uppercase font-bold"><i class="fas fa-warehouse mr-1"></i>${i.warehouse_source || 'Pusat'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-black text-xs uppercase text-gray-900">${i.customer_name}</div>
                    ${payInfo}
                </td>
                <td class="px-6 py-4 font-black text-xs text-gray-700 uppercase">
                    <i class="fas fa-user-hard-hat mr-1 text-purple-500"></i>${i.mandor_name || '-'}
                </td>
                <td class="px-6 py-4 font-black text-xs text-gray-800">${parseFloat(i.area_size)} m²</td>
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
    const statusVal = document.getElementById('filter-status').value;
    const filterCabangEl = document.getElementById('filter-cabang');
    
    // Logika Kunci Filter Cabang
    let cabangVal = '';
    if (isGudang) {
        cabangVal = myWarehouse; // Admin gudang dikunci ke cabangnya sendiri
    } else if (filterCabangEl) {
        cabangVal = filterCabangEl.value; // Admin Pusat ambil dari dropdown
    }

    const filtered = allData.filter(i => {
        const matchText = i.customer_name.toLowerCase().includes(term) || i.ki_number.toLowerCase().includes(term) || (i.mandor_name && i.mandor_name.toLowerCase().includes(term));
        const matchStatus = statusVal === '' || i.status === statusVal;
        
        // Data yang kolom warehouse_source nya kosong dianggap milik Pusat
        const dataCabang = i.warehouse_source ? i.warehouse_source : 'Pusat';
        const matchCabang = (cabangVal === '' || dataCabang === cabangVal);

        return matchText && matchStatus && matchCabang;
    });

    currentFilteredData = filtered; 
    currentPage = 1; 
    renderTable(currentFilteredData);
}

function openStartModal(id, currentMandor) {
    if(!canProcess) return Swal.fire('Ditolak', 'Akses proses ditolak.', 'error');

    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    document.getElementById('start-install-id').value = id;
    
    // Auto fill nama PJ jika sebelumnya sudah diinput
    document.getElementById('start-mandor').value = (currentMandor !== 'null' && currentMandor) ? currentMandor : '';
    document.getElementById('start-estimasi').value = ''; 
    
    document.getElementById('modal-start-work').classList.remove('hidden');
}

function submitStartWork() {
    const id = document.getElementById('start-install-id').value;
    const mandor = document.getElementById('start-mandor').value;
    const estimasi = document.getElementById('start-estimasi').value;

    if (!mandor) return Swal.fire('Peringatan', 'Nama Penanggung Jawab wajib diisi!', 'warning');
    if (!estimasi) return Swal.fire('Peringatan', 'Estimasi Selesai wajib diisi!', 'warning');

    Swal.fire({title: 'Memperbarui...', allowOutsideClick: false}); Swal.showLoading();

    fetch('api/install_action.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'update_status', 
            install_id: id, 
            status: 'Sedang Dikerjakan',
            mandor_name: mandor,
            estimasi_selesai: estimasi
        })
    }).then(r => r.json()).then(d => { 
        if(d.status === 'success') {
            closeModal('modal-start-work');
            Swal.fire('Berhasil', 'Pengerjaan dimulai, jadwal telah diupdate!', 'success');
            loadWorkKI();
        } else {
            Swal.fire('Error', d.message, 'error');
        }
    }).catch(e => { Swal.fire('Error', 'Gagal tersambung ke server.', 'error'); });
}

function openModalWork(id) {
    if(!canProcess) return Swal.fire('Ditolak', 'Akses proses ditolak.', 'error');

    document.getElementById('work-install-id').value = id;
    document.getElementById('work-proof-file').value = '';
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    document.getElementById('modal-work').classList.remove('hidden');
}

async function submitWorkProof() {
    const id = document.getElementById('work-install-id').value;
    const fileInput = document.getElementById('work-proof-file');
    
    if (fileInput.files.length === 0) return Swal.fire('Oops', 'Wajib melampirkan foto hasil pemasangan!', 'warning');

    Swal.fire({title: 'Mengupload Foto...', allowOutsideClick: false}); Swal.showLoading();

    const formData = new FormData();
    formData.append('action', 'upload_work');
    formData.append('install_id', id);
    formData.append('work_proof', fileInput.files[0]);

    try {
        const res = await fetch('api/install_action.php', { method: 'POST', body: formData });
        const json = await res.json();
        if(json.status === 'success') {
            closeModal('modal-work');
            Swal.fire('Selesai!', json.message, 'success');
            loadWorkKI();
        } else Swal.fire('Error', json.message, 'error');
    } catch(e) { Swal.fire('Error', 'Koneksi gagal', 'error'); }
}

function showWorkPhoto(url) {
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    document.getElementById('work-img').src = url;
    document.getElementById('modal-view').classList.remove('hidden');
}
</script>