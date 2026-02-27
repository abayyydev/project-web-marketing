<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// Definisi Hak Akses
$isAdminPusat = ($userRole === 'super_admin' || $userRole === 'admin' || $uLogin === 'admin' || $userRole === 'keuangan');
$isGudang = ($userRole === 'admin_gudang');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Penerimaan Jasa (KI)</h1>
            <p class="text-sm text-gray-500">Instalasi (KI) > <span class="text-purple-600 font-bold uppercase">Validasi Keuangan</span></p>
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
                <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Pelanggan / No Tiket..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 outline-none shadow-sm transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>
            
            <!-- Filter Status Validasi -->
            <select id="filter-val" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:ring-purple-500 outline-none font-bold shadow-sm transition">
                <option value="pending" selected>Menunggu Validasi</option>
                <option value="done">Riwayat Tervalidasi</option>
                <option value="all">Semua Data</option>
            </select>

            <!-- Filter Cabang Dinamis (Khusus Pusat) -->
            <?php if(!$isGudang): ?>
            <select id="filter-cabang" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 font-bold bg-gray-50 focus:ring-purple-500 outline-none shadow-sm transition">
                <option value="">-- Semua Cabang --</option>
                <!-- Data cabang dimuat otomatis via JS -->
            </select>
            <?php endif; ?>
        </div>

        <?php if($isAdminPusat): ?>
        <div class="text-[10px] text-gray-400 font-bold uppercase italic text-right hidden lg:block">
            <i class="fas fa-info-circle mr-1 text-purple-500"></i> Klik Centang jika mutasi masuk.
        </div>
        <?php endif; ?>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto min-h-[450px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tgl Order</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. Tiket (KI) & Cabang</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan & Sales</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Info Pembayaran</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Lapangan</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi / Validasi</th>
                    </tr>
                </thead>
                <tbody id="receipt-ki-rows" class="bg-white divide-y divide-gray-100 text-sm font-medium">
                    <tr><td colspan="6" class="text-center py-10 text-gray-400 italic">Memuat antrean validasi jasa...</td></tr>
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

<!-- MODAL BUKTI TRANSFER (SEMUA BISA LIHAT) -->
<div id="modal-proof" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden relative">
        <div class="p-4 bg-purple-50 border-b border-purple-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900"><i class="fas fa-image mr-2 text-purple-600"></i>Bukti Transfer Jasa</h3>
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
const myWarehouse = "<?= $userWh ?>"; 

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadReceiptsKI();
    if(isAdminPusat) loadCabangFilter(); // Admin pusat butuh filter cabang
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

async function loadReceiptsKI() {
    try {
        const res = await fetch('api/get_ki.php');
        const json = await res.json();
        if(json.status === 'success') {
            // Hilangkan status Batal & Menunggu Pembayaran
            allData = json.data.filter(i => i.status !== 'Menunggu Pembayaran' && i.status !== 'Batal');
            filterTable();
        }
    } catch(e) { 
        console.error(e);
        document.getElementById('receipt-ki-rows').innerHTML = '<tr><td colspan="6" class="text-center py-10 text-red-500">Gagal memuat data.</td></tr>';
    }
}

function renderTable(data) {
    const tbody = document.getElementById('receipt-ki-rows');
    tbody.innerHTML = '';

    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-400 font-bold uppercase tracking-widest">Tidak ada data ditemukan.</td></tr>';
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
        const date = new Date(i.order_date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
        
        const total = Math.round(parseFloat(i.total_price) || 0);
        const dp = Math.round(parseFloat(i.dp_amount) || 0);
        let sisa = total - dp;
        if(i.pay_status === 'Lunas') sisa = 0;

        // Pengecekan Status Validasi Keuangan
        let isPendingValidation = (i.status === 'Menunggu Verifikasi' || i.is_finance_verified == 0);
        let cicilanTag = (isPendingValidation && i.status !== 'Menunggu Verifikasi') ? '<div class="text-[9px] text-purple-600 font-black italic mt-1">Cicilan Tambahan</div>' : '';

        // Tampilan Info Pembayaran
        let payInfo = '';
        if(i.pay_status === 'Lunas') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-emerald-600 bg-emerald-50 inline-block px-2.5 py-1 rounded-md border border-emerald-200 uppercase">Lunas (Rp ${total.toLocaleString()})</div>`;
        } else if (i.pay_status === 'DP') {
            payInfo = `<div class="mt-1 text-[10px] font-black text-blue-600 bg-blue-50 inline-block px-2.5 py-1 rounded-md border border-blue-200 uppercase">Trf: Rp ${dp.toLocaleString()} | Sisa: Rp ${sisa.toLocaleString()}</div>`;
        } else {
            payInfo = `<div class="mt-1 text-[10px] font-black text-gray-500 bg-gray-100 inline-block px-2.5 py-1 rounded-md border border-gray-200 uppercase">Belum Bayar</div>`;
        }

        // Tampilan Status Validasi/Lapangan
        let statusLap = '';
        if(isPendingValidation) {
            statusLap = `<span class="bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-yellow-300 animate-pulse uppercase shadow-sm">Cek Mutasi Bank</span>`;
        } else {
            if(i.status === 'Dijadwalkan') statusLap = `<span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-gray-300 uppercase shadow-sm">Menunggu Jadwal</span>`;
            else if(i.status === 'Sedang Dikerjakan') statusLap = `<span class="bg-orange-100 text-orange-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-orange-300 uppercase shadow-sm">Dikerjakan</span>`;
            else if(i.status === 'Selesai') statusLap = `<span class="bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-emerald-300 uppercase shadow-sm">Selesai</span>`;
            else statusLap = `<span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase shadow-sm">${i.status}</span>`;
        }

        // --- AKSI VALIDASI KEUANGAN ---
        let btnHTML = `<div class="flex gap-2 justify-center items-center flex-wrap">`;
        
        // 1. Tombol Lihat Bukti (Semua bisa lihat jika ada)
        if (i.payment_proof) {
            btnHTML += `<button onclick="showProof('${i.payment_proof}')" class="text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg text-[10px] font-bold border border-blue-200 transition shadow-sm">LIHAT BUKTI</button>`;
        } else {
            btnHTML += `<span class="text-gray-400 text-[10px] italic font-bold uppercase">Tanpa Foto</span>`;
        }

        // 2. Tombol Validasi (Khusus Admin/Keuangan - Warna hijau/merah dipertahankan untuk semantik success/danger)
        if(isPendingValidation && isAdminPusat) {
            btnHTML += `
                <button onclick="prosesValidasi(${i.id}, 'valid')" class="bg-green-600 hover:bg-green-700 text-white w-8 h-8 rounded-lg shadow-md flex justify-center items-center transition transform active:scale-90" title="Validasi Uang Masuk"><i class="fas fa-check"></i></button>
            `;
            // Tolak hanya jika masih verifikasi awal
            if (i.status === 'Menunggu Verifikasi') {
                btnHTML += `<button onclick="prosesValidasi(${i.id}, 'tidak_valid')" class="bg-red-600 hover:bg-red-700 text-white w-8 h-8 rounded-lg shadow-md flex justify-center items-center transition transform active:scale-90" title="Tolak Pesanan"><i class="fas fa-times"></i></button>`;
            }
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
                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-bold text-xs">${date}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-black text-purple-700 font-mono text-sm tracking-wide">${i.ki_number}</div>
                    <div class="text-[9px] text-gray-400 mt-0.5 uppercase font-bold"><i class="fas fa-warehouse mr-1"></i>${i.warehouse_source || 'Pusat'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-gray-900 uppercase font-black text-xs">${i.customer_name}</div>
                    <div class="text-[9px] text-gray-400 uppercase font-bold mt-1">Marketing: ${i.marketing_name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${payInfo}
                    ${cicilanTag}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${statusLap}</td>
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
    const filterEl = document.getElementById('filter-cabang');
    
    // LOGIKA FILTER KETAT CABANG
    let cabangPilihan = '';
    if (isGudang) {
        cabangPilihan = myWarehouse; // Jika admin gudang, paksa filter sesuai nama gudangnya
    } else if (filterEl) {
        cabangPilihan = filterEl.value; // Jika pusat, ambil dari dropdown
    }

    const filtered = allData.filter(i => {
        const matchText = i.customer_name.toLowerCase().includes(term) || i.ki_number.toLowerCase().includes(term);
        
        // Atasi jika data di database kolom gudangnya null/kosong, maka dianggap milik "Pusat"
        const dataCabang = i.warehouse_source ? i.warehouse_source : 'Pusat';
        const matchCabang = (cabangPilihan === '' || dataCabang === cabangPilihan);
        
        let matchVal = true;
        let isPending = (i.status === 'Menunggu Verifikasi' || i.is_finance_verified == 0);

        if (valStatus === 'pending') matchVal = isPending;
        else if (valStatus === 'done') matchVal = !isPending;

        return matchText && matchCabang && matchVal;
    });

    currentFilteredData = filtered; // Simpan data yang sedang terfilter
    currentPage = 1; // Reset selalu ke halaman 1 saat difilter
    renderTable(currentFilteredData);
}

// LIHAT FOTO TRANSFER
function showProof(url) {
    document.getElementById('proof-img').src = url;
    document.getElementById('modal-proof').classList.remove('hidden');
}

// PROSES VALIDASI (KHUSUS ADMIN PUSAT & KEUANGAN)
function prosesValidasi(id, actionType) {
    if(!isAdminPusat) return Swal.fire('Akses Ditolak', 'Hanya Keuangan yang bisa memvalidasi.', 'error');

    if (actionType === 'valid') {
        Swal.fire({
            title: 'Validasi Uang Masuk?', text: 'Konfirmasi bahwa dana jasa instalasi telah masuk rekening.',
            icon: 'question', showCancelButton: true, confirmButtonColor: '#9333ea', confirmButtonText: 'Ya, Valid'
        }).then((res) => {
            if(res.isConfirmed) updateStatusAPI(id, 'validasi_keuangan', 'Pembayaran berhasil divalidasi.');
        });
    } else {
        Swal.fire({
            title: 'Tolak Bukti?', text: 'Bukti transfer ditolak dan dikembalikan ke marketing.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Tolak'
        }).then((res) => {
            if(res.isConfirmed) updateStatusAPI(id, 'update_status', 'Pesanan dikembalikan.');
        });
    }
}

function updateStatusAPI(id, actionCode, successMsg) {
    Swal.fire({title: 'Memproses...', allowOutsideClick: false}); Swal.showLoading();

    let payload = { action: actionCode, install_id: id };
    if (actionCode === 'update_status') { payload.status = 'Menunggu Pembayaran'; }

    fetch('api/install_action.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    }).then(r => r.json()).then(d => {
        if(d.status === 'success') { Swal.fire('Berhasil!', successMsg, 'success'); loadReceiptsKI(); } 
        else Swal.fire('Error', d.message, 'error');
    });
}
</script>