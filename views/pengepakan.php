<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userWh = $_SESSION['user']['warehouse_name'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// HAK AKSES KETAT: Hanya Admin Gudang atau Super Admin yang bisa memproses barang
$canProcess = ($userRole === 'admin_gudang' || $userRole === 'super_admin' || $uLogin === 'admin');
// Penanda Admin Gudang (Cabang)
$isGudang = ($userRole === 'admin_gudang');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Antrean Pengepakan</h1>
            <p class="text-sm text-gray-500">Beranda > Penjualan > <span class="text-purple-600 font-bold uppercase">Proses Packing</span></p>
        </div>
        
        <div class="bg-purple-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center gap-3">
            <i class="fas fa-box-open text-lg"></i> 
            <div class="flex flex-col text-left">
                <span class="opacity-70 text-[9px] uppercase">Gudang Aktif</span>
                <span><?= strtoupper($userWh ?: 'PUSAT (SEMUA)') ?></span>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="flex flex-wrap gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full md:w-64">
                <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Pelanggan / KP..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 outline-none shadow-sm transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>

            <!-- Filter Cabang Dinamis (Hanya muncul untuk Super Admin) -->
            <?php if(!$isGudang): ?>
            <select id="filter-gudang" onchange="filterTable()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-600 bg-gray-50 outline-none focus:ring-purple-500 shadow-sm transition">
                <option value="">-- Semua Gudang --</option>
                <!-- Data ini akan diisi secara otomatis oleh JavaScript dari database -->
            </select>
            <?php endif; ?>
        </div>

        <div class="flex gap-2">
            <button onclick="loadPengepakan()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-xs w-full md:w-auto justify-center">
                <i class="fas fa-sync-alt"></i> Refresh Antrean
            </button>
        </div>
    </div>

    <!-- TABLE DENGAN PAGINASI -->
    <div class="bg-white rounded-b-xl shadow-md border-t-4 border-purple-600 border-x border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto min-h-[450px]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Estimasi Kirim</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">No. KP</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Cabang</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Packing</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi Gudang</th>
                    </tr>
                </thead>
                <tbody id="packing-rows" class="bg-white divide-y divide-gray-100 text-sm font-medium">
                    <tr><td colspan="6" class="text-center py-10 text-gray-400 italic">Memuat antrean barang...</td></tr>
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

<!-- MODAL UPLOAD FOTO PACKING -->
<div id="modal-packing" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900"><i class="fas fa-camera mr-2 text-purple-600"></i>Bukti Selesai Pengepakan</h3>
            <button onclick="closeModal('modal-packing')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="pack-order-id">
            
            <div class="bg-purple-50/50 p-3 rounded-lg border border-purple-200 text-sm text-purple-900">
                Faktur: <span id="pack-kp" class="font-black font-mono"></span><br>
                Customer: <span id="pack-cust" class="font-black"></span>
            </div>

            <div class="border-2 border-dashed border-purple-300 rounded-xl p-6 text-center hover:bg-purple-50 transition cursor-pointer group">
                <label class="block text-xs font-black text-purple-700 uppercase mb-3 group-hover:scale-105 transition-transform">Ambil Foto Barang (Siap Kirim)</label>
                <input type="file" id="pack-proof-file" accept="image/png, image/jpeg, image/jpg, image/webp" class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-800 hover:file:bg-purple-200 w-full cursor-pointer transition">
            </div>
            
            <button onclick="submitPacking()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-xl shadow-lg mt-2 transition flex items-center justify-center gap-2 transform active:scale-95">
                <i class="fas fa-check-circle"></i> SIMPAN & SIAP KIRIM
            </button>
        </div>
    </div>
</div>

<!-- MODAL PACKING LIST -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gray-100 px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-black text-gray-800 uppercase tracking-tighter">Packing List (Instruksi Potong)</h3>
            <button onclick="closeModal('modal-detail')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto">
            <div id="d-info" class="mb-6 p-4 bg-purple-50 rounded-xl border border-purple-200 shadow-sm"></div>
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white uppercase text-[10px] font-bold tracking-wider">
                        <th class="px-4 py-3 text-left rounded-tl-lg">Nama Produk & Rincian Potong</th>
                        <th class="px-4 py-3 text-center rounded-tr-lg w-32">Total Fisik (Qty)</th>
                    </tr>
                </thead>
                <tbody id="d-items" class="divide-y divide-gray-100 border-x border-b rounded-b-lg"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
let allData = [];
let currentFilteredData = [];
const canProcess = <?= $canProcess ? 'true' : 'false' ?>;
const isGudang = <?= $isGudang ? 'true' : 'false' ?>;

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadPengepakan();
    if(!isGudang) loadCabangFilter(); // Panggil pengisian dropdown otomatis jika bukan admin cabang
});

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

async function loadCabangFilter() {
    try {
        const res = await fetch('api/warehouse_action.php?action=get');
        const json = await res.json();
        if(json.status === 'success') {
            const sel = document.getElementById('filter-gudang');
            if(sel) {
                json.data.forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.name;
                    opt.innerText = w.name;
                    sel.appendChild(opt);
                });
            }
        }
    } catch(e) { console.error("Gagal memuat filter gudang:", e); }
}

async function loadPengepakan() {
    try {
        const res = await fetch('api/get_orders.php');
        const json = await res.json();
        if(json.status === 'success') {
            const targetStatuses = ['Diproses', 'Sedang Dipack', 'Paket Siap', 'Dikirim'];
            allData = json.data.filter(o => targetStatuses.includes(o.order_status));
            filterTable(); // Memanggil filterTable untuk memicu renderTable dengan paginasi
        }
    } catch(e) { console.error(e); }
}

function renderTable(data) {
    const tbody = document.getElementById('packing-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-gray-400 font-bold uppercase tracking-widest">Tidak ada antrean barang di gudang.</td></tr>';
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
        
        let statusBadge = '';
        if(o.order_status === 'Diproses') {
            statusBadge = `<span class="bg-purple-50 text-purple-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-purple-200 uppercase shadow-sm">Antrean</span>`;
        } else if(o.order_status === 'Sedang Dipack') {
            statusBadge = `<span class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-orange-200 uppercase animate-pulse shadow-sm">Diproses</span>`;
        } else if(o.order_status === 'Paket Siap') {
            statusBadge = `<span class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-blue-200 uppercase shadow-sm">Siap Kirim</span>`;
        } else if(o.order_status === 'Dikirim') {
            statusBadge = `<span class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-[10px] font-black border border-emerald-200 uppercase shadow-sm">Dikirim</span>`;
        } else {
            statusBadge = `<span class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-full text-[10px] font-black border border-gray-300 uppercase shadow-sm">${o.order_status}</span>`;
        }

        let actionHTML = `<div class="flex justify-center gap-2 items-center">`;
        actionHTML += `<button onclick="showDetailBarang(${o.id})" class="text-purple-600 bg-purple-50 hover:bg-purple-100 p-2.5 rounded-lg transition shadow-sm" title="Lihat Daftar Barang"><i class="fas fa-list"></i></button>`;

        if(canProcess) {
            if (o.order_status === 'Diproses') {
                actionHTML += `<button onclick="updateStatusPacking(${o.id}, 'Sedang Dipack')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-[10px] font-black shadow-md transition transform active:scale-95 uppercase tracking-tighter">Mulai Pack</button>`;
            } else if (o.order_status === 'Sedang Dipack') {
                actionHTML += `<button onclick="openModalPacking(${o.id}, '${o.kp_number}', '${o.customer_name}')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-[10px] font-black shadow-md transition transform active:scale-95 uppercase tracking-tighter">Selesai & Foto</button>`;
            } else if (o.order_status === 'Paket Siap') {
                actionHTML += `<span class="text-[9px] text-blue-600 font-bold uppercase px-2"><i class="fas fa-clock mr-1"></i> Siap Pickup</span>`;
            } else if (o.order_status === 'Dikirim') {
                actionHTML += `<span class="text-[9px] text-emerald-600 font-bold uppercase px-2"><i class="fas fa-check-circle mr-1"></i> Selesai</span>`;
            }
        } else {
            actionHTML += `<span class="text-[9px] text-gray-400 font-bold uppercase px-2 italic">Read Only</span>`;
        }
        actionHTML += `</div>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-black text-xs">${tglKirim}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-purple-700 font-black font-mono text-sm tracking-wide">${o.kp_number}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-gray-900 font-black text-xs uppercase">${o.customer_name}</div>
                    <div class="text-[9px] text-gray-400 font-bold uppercase mt-1">Marketing: ${o.marketing_name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-purple-600 font-black text-[10px] uppercase"><i class="fas fa-warehouse mr-1"></i>${o.warehouse_source || 'Pusat'}</span>
                </td>
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
    const filterGudang = document.getElementById('filter-gudang');
    const gudang = filterGudang ? filterGudang.value : '';
    
    const filtered = allData.filter(o => {
        const matchText = o.customer_name.toLowerCase().includes(term) || o.kp_number.toLowerCase().includes(term);
        const matchGudang = (gudang === '' || o.warehouse_source === gudang);
        return matchText && matchGudang;
    });
    
    currentFilteredData = filtered; 
    currentPage = 1; 
    renderTable(currentFilteredData);
}

function updateStatusPacking(id, newStatus) {
    if(!canProcess) return Swal.fire('Ditolak', 'Hanya Admin Gudang yang bisa memproses ini.', 'error');
    
    Swal.fire({title: 'Mengupdate Status...', allowOutsideClick: false}); Swal.showLoading();
    fetch('api/order_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update_status', order_id: id, status: newStatus})
    }).then(r => r.json()).then(d => {
        if(d.status === 'success') {
            Swal.fire({icon: 'success', title: 'Dimulai!', text: 'Segera selesaikan pengepakan barang.', timer: 1500, showConfirmButton: false});
            loadPengepakan();
        } else { Swal.fire('Error', d.message, 'error'); }
    });
}

function openModalPacking(id, kp, cust) {
    if(!canProcess) return Swal.fire('Ditolak', 'Akses hanya untuk Admin Gudang.', 'error');
    
    document.getElementById('pack-order-id').value = id;
    document.getElementById('pack-kp').innerText = kp;
    document.getElementById('pack-cust').innerText = cust;
    document.getElementById('pack-proof-file').value = '';
    document.getElementById('modal-packing').classList.remove('hidden');
}

async function submitPacking() {
    const orderId = document.getElementById('pack-order-id').value;
    const fileInput = document.getElementById('pack-proof-file');
    if (fileInput.files.length === 0) return Swal.fire('Peringatan', 'Harap upload foto bukti packing!', 'warning');

    Swal.fire({title: 'Menyimpan Data...', allowOutsideClick: false}); Swal.showLoading();
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('packing_proof', fileInput.files[0]);

    try {
        const res = await fetch('api/update_packing.php', { method: 'POST', body: formData });
        const json = await res.json();
        if(json.status === 'success') {
            closeModal('modal-packing'); 
            Swal.fire('Sukses!', 'Barang telah dipack dan siap untuk pickup.', 'success'); 
            loadPengepakan(); 
        } else { Swal.fire('Gagal', json.message, 'error'); }
    } catch(e) { Swal.fire('Error', 'Koneksi ke server gagal', 'error'); }
}

async function showDetailBarang(id) {
    Swal.fire({title: 'Mengambil Data...', allowOutsideClick: false}); Swal.showLoading();
    try {
        const res = await fetch(`api/get_order_detail.php?id=${id}`);
        const json = await res.json();
        if(json.status === 'success') {
            const o = json.data.order;
            const infoDiv = document.getElementById('d-info');
            infoDiv.innerHTML = `
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div><p class="font-bold text-gray-500 uppercase">No. Faktur</p><p class="text-sm font-black text-purple-700 font-mono tracking-wide">${o.kp_number}</p></div>
                    <div><p class="font-bold text-gray-500 uppercase">Gudang Asal</p><p class="text-sm font-black text-gray-800 uppercase">${o.warehouse_source}</p></div>
                    <div class="col-span-2 mt-2"><p class="font-bold text-gray-500 uppercase">Alamat Kirim</p><p class="text-sm font-bold text-gray-800">${o.customer_address}</p></div>
                </div>
            `;
            const tbody = document.getElementById('d-items');
            tbody.innerHTML = '';
            json.data.items.forEach(i => {
                // Tampilkan catatan potong/ukuran jika tersimpan di database
                let displayName = i.item_note ? i.item_note : i.product_name;
                
                // Regex cerdas: Mengubah teks "[Ukuran: ...]" menjadi Label Badge Oranye yang sangat mencolok untuk Gudang
                displayName = displayName.replace(/\[Ukuran:\s*(.+?)\]/g, '<br><span class="inline-flex mt-2 mb-1 text-[11px] text-orange-700 font-black bg-orange-100 px-3 py-1.5 rounded-lg border border-orange-300 shadow-sm"><i class="fas fa-cut mr-1.5"></i> POTONG UKURAN: $1</span>');

                tbody.innerHTML += `<tr><td class="px-4 py-3 font-bold text-gray-800 uppercase text-xs leading-relaxed border-b border-gray-100">${displayName}</td><td class="px-4 py-3 text-center font-black text-purple-700 text-xl border-b border-l border-gray-100 align-middle">${parseFloat(i.qty)} <span class="text-sm text-gray-500">${i.unit}</span></td></tr>`;
            });
            Swal.close();
            document.getElementById('modal-detail').classList.remove('hidden');
        } else { Swal.fire('Error', json.message, 'error'); }
    } catch(e) { Swal.fire('Error', 'Gagal memuat detail', 'error'); }
}
</script>