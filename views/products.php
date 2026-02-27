<?php
$role = $_SESSION['user']['role'] ?? '';
$uLogin = $_SESSION['user']['username'] ?? '';

// Hak akses untuk MENGUBAH data (Hanya Super Admin)
// Role lain (Gudang, Marketing, Keuangan) otomatis hanya bisa melihat (Read-Only)
$isSuperAdmin = ($role === 'super_admin' || $uLogin === 'admin');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Master Produk</h1>
            <p class="text-sm text-gray-500">Kelola data dasar barang fisik dan jasa.</p>
        </div>
        
        <?php if($isSuperAdmin): ?>
        <button onclick="openModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md transition flex items-center gap-2 transform active:scale-95">
            <i class="fas fa-plus"></i> Tambah Produk
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

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-t-xl border border-gray-200 flex flex-wrap justify-between items-center gap-4">
        <div class="flex flex-wrap gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full md:w-64">
                <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Kode / Nama Produk..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 outline-none shadow-sm transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="loadData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center justify-center gap-2 text-xs w-full md:w-auto">
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
                        <th class="px-6 py-4 text-left whitespace-nowrap">Kode</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Nama Produk</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tipe</th>
                        <th class="px-6 py-4 text-right whitespace-nowrap">Harga Dasar (Saran)</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody id="product-rows" class="bg-white divide-y divide-gray-100 text-sm font-medium text-gray-700">
                    <tr><td colspan="5" class="text-center py-10 text-gray-400 italic font-bold">Memuat data produk...</td></tr>
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

<!-- MODAL TAMBAH/EDIT (HANYA MUNCUL UNTUK SUPER ADMIN) -->
<?php if($isSuperAdmin): ?>
<div id="modal-product" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900" id="modal-title"><i class="fas fa-box mr-2"></i>Tambah Produk</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6">
            <input type="hidden" id="p-id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Barang</label>
                    <input type="text" id="p-code" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 outline-none focus:border-purple-500 font-mono font-bold text-purple-700" placeholder="Cth: P01">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Item</label>
                    <select id="p-type" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 outline-none focus:border-purple-500 bg-white font-bold text-gray-700">
                        <option value="goods">Barang Fisik (Ada Stok)</option>
                        <option value="service">Jasa / Instalasi</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Produk</label>
                    <input type="text" id="p-name" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 outline-none focus:border-purple-500 font-bold" placeholder="Cth: Swiss Platinum 4cm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Satuan Ukur</label>
                    <input type="text" id="p-unit" placeholder="Cth: m2 / Sak / Pcs" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 outline-none focus:border-purple-500 font-bold text-gray-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Saran Harga Jual (Rp)</label>
                    <input type="number" id="p-price" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 outline-none focus:border-purple-500 font-black text-gray-800" placeholder="0">
                </div>
            </div>

            <button onclick="saveProduct()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-3 rounded-xl shadow-lg mt-4 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> SIMPAN DATA PRODUK
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
let allProducts = [];
let currentFilteredData = [];
const isSuperAdmin = <?= $isSuperAdmin ? 'true' : 'false' ?>;

// VARIABEL PAGINASI
let currentPage = 1;
const rowsPerPage = 10;

document.addEventListener('DOMContentLoaded', loadData);

async function loadData() {
    try {
        const resP = await fetch('api/get_products.php');
        const json = await resP.json();
        if(json.status === 'success') {
            allProducts = json.data;
            filterTable();
        }
    } catch(e) { console.error("Gagal load produk", e); }
}

function renderTable(data) {
    const tbody = document.getElementById('product-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-12 text-gray-400 font-bold uppercase tracking-widest">Tidak ada data produk.</td></tr>';
        renderPagination(0, 0);
        return;
    }

    // LOGIKA PAGINASI
    const totalPages = Math.ceil(data.length / rowsPerPage);
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);

    let rowsHTML = '';
    paginatedData.forEach(p => {
        let actionHTML = '';
        if(isSuperAdmin) {
            actionHTML = `
                <div class="flex justify-center gap-2">
                    <button onclick="openModal(${p.id})" class="text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg transition font-bold text-xs shadow-sm"><i class="fas fa-edit mr-1"></i> Edit</button>
                    <button onclick="deleteProduct(${p.id})" class="text-red-500 bg-red-50 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-lg transition font-bold text-xs shadow-sm"><i class="fas fa-trash mr-1"></i> Hapus</button>
                </div>
            `;
        } else {
            actionHTML = `<span class="text-[10px] text-gray-400 font-bold uppercase italic bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">Read Only</span>`;
        }

        let typeBadge = p.type === 'service' 
            ? `<span class="text-orange-700 bg-orange-50 border border-orange-200 px-2.5 py-1 rounded-md text-[10px]"><i class="fas fa-tools mr-1"></i>Jasa</span>` 
            : `<span class="text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-md text-[10px]"><i class="fas fa-box mr-1"></i>Fisik</span>`;

        rowsHTML += `
            <tr class="hover:bg-purple-50/20 transition border-b border-gray-100">
                <td class="px-6 py-4 text-purple-700 font-mono text-sm font-black">${p.code}</td>
                <td class="px-6 py-4">
                    <div class="text-gray-900 font-black uppercase tracking-wide text-sm">${p.name}</div>
                    <div class="text-[10px] text-gray-500 font-bold mt-1 uppercase tracking-tighter"><i class="fas fa-balance-scale mr-1"></i>Satuan: ${p.unit}</div>
                </td>
                <td class="px-6 py-4 font-bold uppercase">${typeBadge}</td>
                <td class="px-6 py-4 text-right text-gray-800 font-black text-sm">Rp ${parseInt(p.base_price).toLocaleString('id-ID')}</td>
                <td class="px-6 py-4 text-center">${actionHTML}</td>
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
    
    const filtered = allProducts.filter(p => {
        const matchText = p.name.toLowerCase().includes(term) || p.code.toLowerCase().includes(term);
        return matchText;
    });
    
    currentFilteredData = filtered; 
    currentPage = 1; 
    renderTable(currentFilteredData);
}

function openModal(id = null) {
    if(!isSuperAdmin) return Swal.fire('Akses Ditolak', 'Hanya Super Admin yang bisa mengedit produk.', 'error');

    document.getElementById('modal-title').innerHTML = id ? '<i class="fas fa-edit mr-2 text-purple-600"></i>Edit Produk' : '<i class="fas fa-box-open mr-2 text-purple-600"></i>Tambah Produk Baru';
    let p = id ? allProducts.find(x => x.id === id) : null;
    
    document.getElementById('p-id').value = id || '';
    document.getElementById('p-code').value = p ? p.code : '';
    document.getElementById('p-name').value = p ? p.name : '';
    document.getElementById('p-type').value = p ? p.type : 'goods';
    document.getElementById('p-unit').value = p ? p.unit : '';
    document.getElementById('p-price').value = p ? p.base_price : '';

    document.getElementById('modal-product').classList.remove('hidden');
}

function closeModal() { document.getElementById('modal-product').classList.add('hidden'); }

async function saveProduct() {
    if(!isSuperAdmin) return;

    let payload = {
        action: document.getElementById('p-id').value ? 'edit' : 'add',
        id: document.getElementById('p-id').value,
        code: document.getElementById('p-code').value,
        name: document.getElementById('p-name').value,
        type: document.getElementById('p-type').value,
        unit: document.getElementById('p-unit').value,
        base_price: document.getElementById('p-price').value,
        fee_amount: 0, // Dikosongkan karena diurus marketing di nota
        fee_code: '-'
    };

    if(!payload.code || !payload.name) return Swal.fire('Peringatan', 'Kode dan Nama Produk wajib diisi', 'warning');

    Swal.fire({title: 'Menyimpan...', allowOutsideClick: false}); Swal.showLoading();

    try {
        const res = await fetch('api/product_action.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
        const d = await res.json();
        if(d.status === 'success') { 
            closeModal(); 
            loadData(); 
            Swal.fire('Tersimpan!', d.message, 'success'); 
        }
        else { Swal.fire('Error', d.message, 'error'); }
    } catch(e) { Swal.fire('Error', 'Gagal tersambung ke server.', 'error'); }
}

function deleteProduct(id) {
    if(!isSuperAdmin) return Swal.fire('Akses Ditolak', 'Hanya Super Admin yang bisa menghapus produk.', 'error');

    Swal.fire({
        title: 'Hapus Produk?', 
        text: 'Produk ini akan dihilangkan dari master data dan berpotensi mempengaruhi data historis yang belum selesai.', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonText: 'Ya, Hapus!', 
        confirmButtonColor: '#ef4444'
    }).then(r => { 
        if(r.isConfirmed) { 
            fetch('api/product_action.php', {method:'POST', body: JSON.stringify({action:'delete', id:id})})
            .then(res => res.json()).then(data => {
                if(data.status === 'success') {
                    Swal.fire('Terhapus!', 'Produk berhasil dihapus.', 'success');
                    loadData();
                } else Swal.fire('Gagal', data.message, 'error');
            });
        } 
    });
}
</script>