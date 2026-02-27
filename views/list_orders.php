<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b-2 border-purple-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-purple-900 uppercase tracking-wider">
                <?= $_SESSION['user']['role'] === 'admin' ? 'Verifikasi & Pengiriman' : 'Riwayat Pesanan Saya' ?>
            </h1>
            <p class="text-sm text-purple-600">Pantau status transaksi dan pengiriman barang.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <!-- Filter Gudang (Hanya untuk Admin) -->
            <?php if($_SESSION['user']['role'] === 'admin'): ?>
            <select id="filter-cabang" onchange="filterTable()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 bg-white font-bold text-purple-800 shadow-sm">
                <option value="">Semua Cabang (Nasional)</option>
                <option value="Bogor">Cabang Bogor</option>
                <option value="Bandung">Cabang Bandung</option>
                <option value="Yogyakarta">Cabang Yogyakarta</option>
                <option value="Semarang">Cabang Semarang</option>
                <option value="Surabaya">Cabang Surabaya</option>
                <option value="Bali">Cabang Bali</option>
                <option value="Medan">Cabang Medan</option>
            </select>
            <?php endif; ?>

            <div class="relative w-full sm:w-64">
                <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Pelanggan / KP..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 shadow-sm">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>
    </div>

    <!-- TABEL UTAMA -->
    <div class="bg-white rounded-xl shadow-md border-t-4 border-purple-600 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-4 text-left whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-4 text-left whitespace-nowrap">Info Pesanan</th>
                        <th class="px-4 py-4 text-left whitespace-nowrap">Pelanggan</th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">Total Tagihan</th>
                        <th class="px-4 py-4 text-center whitespace-nowrap">Pembayaran</th>
                        <th class="px-4 py-4 text-center whitespace-nowrap">Status Admin</th>
                        <th class="px-4 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody id="order-rows" class="bg-white divide-y divide-gray-200">
                    <tr><td colspan="7" class="text-center py-8 text-gray-400 italic">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL INPUT PEMBAYARAN (Marketing/Admin) -->
<div id="modal-payment" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-emerald-800"><i class="fas fa-money-bill-wave mr-2"></i>Input Pembayaran</h3>
            <button onclick="closeModal('modal-payment')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="pay-order-id">
            <div><label class="block text-xs font-bold text-gray-500 uppercase">Total Tagihan Bersih</label><div class="text-2xl font-bold text-gray-800" id="pay-grand-total">Rp 0</div></div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Pembayaran</label>
                <select id="pay-status" onchange="checkPayModal()" class="w-full border-gray-300 rounded p-2 focus:ring-emerald-500 bg-gray-50 font-bold text-gray-700">
                    <option value="DP">DP (Down Payment)</option>
                    <option value="Lunas">Lunas</option>
                </select>
            </div>
            <div id="pay-dp-box">
                <label class="block text-xs font-bold text-emerald-600 uppercase mb-1">Nominal DP Transfer (Rp)</label>
                <input type="number" id="pay-dp-amount" class="w-full border-emerald-300 rounded p-2 font-bold text-emerald-800" placeholder="0">
            </div>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:bg-gray-50">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Upload Bukti Transfer</label>
                <input type="file" id="pay-proof-file" accept="image/*" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 w-full">
            </div>
            <button onclick="submitPayment()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded shadow mt-2 transition">Simpan & Update Bukti</button>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD RESI & PACKING (Khusus Admin) -->
<div id="modal-shipping" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-blue-800"><i class="fas fa-box-open mr-2"></i>Update Pengiriman</h3>
            <button onclick="closeModal('modal-shipping')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="ship-order-id">
            
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">1. Foto Bukti Packing Barang</label>
                <input type="file" id="ship-packing-file" accept="image/*" class="text-sm text-gray-500 w-full">
            </div>

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">2. Foto Resi Ekspedisi</label>
                <input type="file" id="ship-resi-file" accept="image/*" class="text-sm text-gray-500 w-full mb-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nomor Resi (Opsional)</label>
                <input type="text" id="ship-resi-number" class="w-full border border-gray-300 rounded p-2" placeholder="Contoh: JNT123456789">
            </div>
            
            <button onclick="submitShipping()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded shadow mt-2 transition">Upload Data Pengiriman</button>
        </div>
    </div>
</div>

<!-- MODAL DETAIL (Lihat Rincian, Pembayaran, Resi) -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900" id="d-kp">KP-XXXXX</h3>
            <button onclick="closeModal('modal-detail')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 max-h-[75vh] overflow-y-auto space-y-4">
            
            <!-- Box Pembayaran -->
            <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-lg flex flex-col md:flex-row justify-between md:items-center gap-4">
                <div>
                    <p class="text-xs font-bold text-emerald-600 uppercase">Status Pembayaran</p>
                    <p class="font-bold text-gray-800 text-lg" id="d-pay-status">-</p>
                    <p class="text-sm text-emerald-700 font-bold" id="d-pay-dp"></p>
                </div>
                <div id="d-pay-img-box" class="hidden">
                    <a id="d-pay-link" href="#" target="_blank" class="bg-emerald-600 text-white px-4 py-2 rounded text-sm font-bold shadow hover:bg-emerald-700 flex items-center"><i class="fas fa-receipt mr-2"></i>Lihat Bukti Transfer</a>
                </div>
            </div>

            <!-- Box Pengiriman / Resi -->
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-bold text-blue-600 uppercase mb-2">Foto Packing</p>
                    <div id="d-pack-box" class="text-sm text-gray-500 italic">Belum ada foto packing</div>
                </div>
                <div>
                    <p class="text-xs font-bold text-blue-600 uppercase mb-2">Resi Ekspedisi <span id="d-resi-txt" class="text-gray-800 font-mono"></span></p>
                    <div id="d-resi-box" class="text-sm text-gray-500 italic">Belum ada foto resi</div>
                </div>
            </div>

            <!-- Tabel Barang -->
            <div class="border rounded-lg overflow-hidden mt-4">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-xs uppercase font-bold text-gray-600">
                        <tr><th class="px-4 py-2 text-left">Produk</th><th class="px-4 py-2 text-center">Qty</th><th class="px-4 py-2 text-right">Harga</th><th class="px-4 py-2 text-right">Subtotal</th></tr>
                    </thead>
                    <tbody id="d-items" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let allOrders = [];
const isAdmin = <?= $_SESSION['user']['role'] === 'admin' ? 'true' : 'false' ?>;
const myUsername = "<?= $_SESSION['user']['username'] ?>";

document.addEventListener('DOMContentLoaded', loadOrders);

async function loadOrders() {
    const tbody = document.getElementById('order-rows');
    try {
        const res = await fetch('api/get_orders.php');
        const json = await res.json();
        if(json.status === 'success') {
            allOrders = json.data;
            filterTable(); // Call filter instead of render directly to apply branch filter if needed
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-red-500">Gagal mengambil data.</td></tr>';
        }
    } catch(e) { console.error(e); }
}

function filterTable() {
    const term = document.getElementById('search').value.toLowerCase();
    
    let filtered = allOrders;

    // Filter by Search Text
    if(term) {
        filtered = filtered.filter(o => 
            o.customer_name.toLowerCase().includes(term) || 
            o.kp_number.toLowerCase().includes(term) ||
            (o.warehouse_source && o.warehouse_source.toLowerCase().includes(term))
        );
    }

    // Filter by Cabang (Admin Only)
    if(isAdmin) {
        const cabang = document.getElementById('filter-cabang').value;
        if(cabang) {
            filtered = filtered.filter(o => o.warehouse_source === cabang);
        }
    }

    renderTable(filtered);
}

function renderTable(data) {
    const tbody = document.getElementById('order-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-400">Tidak ada data ditemukan.</td></tr>';
        return;
    }

    data.forEach(o => {
        const date = new Date(o.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'});
        let kiBadge = o.ki_number ? `<div class="mt-1 text-[10px] font-mono text-orange-600 bg-orange-50 px-1 rounded border border-orange-200">KI: ${o.ki_number}</div>` : '';
        let whBadge = `<div class="mt-1 text-[10px] font-bold text-purple-600 uppercase"><i class="fas fa-map-marker-alt mr-1"></i>${o.warehouse_source || '-'}</div>`;

        let statusAdmin = o.order_status || 'Pending';
        let adminBadge = statusAdmin === 'Pending' ? 'bg-yellow-100 text-yellow-800' : (statusAdmin === 'Verified' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
        
        let payStatus = o.pay_status || 'Belum Bayar';
        let payBadge = payStatus === 'Belum Bayar' ? 'bg-gray-100 text-gray-600' : (payStatus === 'Lunas' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-blue-100 text-blue-800 border border-blue-200');

        let marketingName = `<div class="text-[10px] text-gray-400 mt-1 uppercase">Oleh: ${o.marketing_name}</div>`;
        
        // --- BUTTONS ---
        let btns = `<div class="flex items-center justify-center space-x-1 flex-wrap gap-y-1">`;
        
        // 1. Tombol Detail (Semua)
        btns += `<button onclick="showDetail(${o.id})" class="text-blue-600 bg-blue-50 hover:bg-blue-100 p-2 rounded transition" title="Lihat Detail Foto"><i class="fas fa-eye"></i></button>`;
        
        // 2. Tombol WA (Marketing/Admin)
        btns += `<button onclick="showWA(${o.id})" class="text-green-500 bg-green-50 hover:bg-green-100 p-2 rounded transition" title="Format WA"><i class="fab fa-whatsapp"></i></button>`;
        
        // 3. Tombol Input Pembayaran (Hilang jika Lunas)
        if (payStatus !== 'Lunas') {
            btns += `<button onclick="openPaymentModal(${o.id}, ${o.grand_total})" class="text-emerald-600 bg-emerald-50 hover:bg-emerald-100 p-2 rounded transition" title="Input Transfer"><i class="fas fa-money-bill-wave"></i></button>`;
        }

        // 4. Tombol Upload Packing/Resi (KHUSUS ADMIN)
        if (isAdmin) {
            btns += `<button onclick="openShippingModal(${o.id})" class="text-purple-600 bg-purple-50 hover:bg-purple-100 p-2 rounded transition" title="Upload Resi/Packing"><i class="fas fa-box-open"></i></button>`;
            
            // Verifikasi Admin
            if(statusAdmin === 'Pending') {
                btns += `<button onclick="updateStatus(${o.id}, 'verify')" class="text-white bg-green-500 hover:bg-green-600 p-2 rounded shadow-sm transition" title="Verifikasi Pesanan"><i class="fas fa-check"></i></button>`;
            }
        }

        // 5. Tombol Edit (Kuning) - Marketing hanya bisa edit jika Pending
        let canEdit = isAdmin || (statusAdmin === 'Pending');
        if (canEdit) {
            btns += `<a href="index.php?page=edit_order&id=${o.id}" class="text-yellow-600 bg-yellow-50 hover:bg-yellow-100 p-2 rounded transition" title="Edit Pesanan"><i class="fas fa-edit"></i></a>`;
        }

        // 6. Tombol Hapus (Merah) - Marketing hanya jika Pending
        if(canEdit) {
             btns += `<button onclick="updateStatus(${o.id}, 'delete')" class="text-red-500 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="Hapus"><i class="fas fa-trash"></i></button>`;
        }

        btns += `</div>`;

        tbody.innerHTML += `
            <tr class="hover:bg-purple-50/50 transition">
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">${date}</td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="font-bold text-purple-700 font-mono text-sm">${o.kp_number}</div>
                    ${kiBadge}
                    ${whBadge}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-900">${o.customer_name}</div>
                    ${marketingName}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-800">Rp ${parseInt(o.grand_total).toLocaleString('id-ID')}</td>
                <td class="px-4 py-4 whitespace-nowrap text-center"><span class="px-3 py-1 text-[11px] rounded-full font-bold ${payBadge}">${payStatus}</span></td>
                <td class="px-4 py-4 whitespace-nowrap text-center"><span class="px-3 py-1 text-[11px] rounded-full font-bold ${adminBadge}">${statusAdmin}</span></td>
                <td class="px-4 py-4 whitespace-nowrap text-center">${btns}</td>
            </tr>
        `;
    });
}

// --- FUNGSI HELPER MODAL & UPLOAD ---
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function getBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
}

// --- MODAL PEMBAYARAN (Marketing/Admin) ---
function openPaymentModal(orderId, grandTotal) {
    document.getElementById('pay-order-id').value = orderId;
    document.getElementById('pay-grand-total').innerText = "Rp " + parseInt(grandTotal).toLocaleString('id-ID');
    document.getElementById('pay-status').value = 'DP';
    document.getElementById('pay-dp-amount').value = '';
    document.getElementById('pay-proof-file').value = '';
    checkPayModal();
    document.getElementById('modal-payment').classList.remove('hidden');
}

function checkPayModal() {
    const val = document.getElementById('pay-status').value;
    if(val === 'DP') document.getElementById('pay-dp-box').classList.remove('hidden');
    else document.getElementById('pay-dp-box').classList.add('hidden');
}

async function submitPayment() {
    const orderId = document.getElementById('pay-order-id').value;
    const payStatus = document.getElementById('pay-status').value;
    const dpAmount = payStatus === 'DP' ? (document.getElementById('pay-dp-amount').value || 0) : 0;
    const fileInput = document.getElementById('pay-proof-file');
    
    if (fileInput.files.length === 0) return Swal.fire('Oops', 'Harap upload foto bukti transfer!', 'warning');
    if (payStatus === 'DP' && dpAmount <= 0) return Swal.fire('Oops', 'Nominal DP tidak boleh kosong!', 'warning');

    Swal.fire({title: 'Mengupload...', allowOutsideClick: false}); Swal.showLoading();
    const base64Image = await getBase64(fileInput.files[0]);

    try {
        const res = await fetch('api/update_payment.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, pay_status: payStatus, dp_amount: dpAmount, payment_proof_base64: base64Image })
        });
        const json = await res.json();
        if(json.status === 'success') {
            closeModal('modal-payment'); Swal.fire('Berhasil', json.message, 'success'); loadOrders(); 
        } else Swal.fire('Gagal', json.message, 'error');
    } catch(e) { Swal.fire('Error', 'Koneksi gagal', 'error'); }
}

// --- MODAL PENGIRIMAN (Khusus Admin) ---
function openShippingModal(orderId) {
    document.getElementById('ship-order-id').value = orderId;
    document.getElementById('ship-packing-file').value = '';
    document.getElementById('ship-resi-file').value = '';
    document.getElementById('ship-resi-number').value = '';
    document.getElementById('modal-shipping').classList.remove('hidden');
}

async function submitShipping() {
    const orderId = document.getElementById('ship-order-id').value;
    const packFile = document.getElementById('ship-packing-file');
    const resiFile = document.getElementById('ship-resi-file');
    const resiNum = document.getElementById('ship-resi-number').value;

    if (packFile.files.length === 0 && resiFile.files.length === 0 && !resiNum) {
        return Swal.fire('Oops', 'Harap isi minimal 1 data (Foto Packing atau Resi)!', 'warning');
    }

    Swal.fire({title: 'Menyimpan Data Pengiriman...', allowOutsideClick: false}); Swal.showLoading();
    
    let packBase64 = ''; let resiBase64 = '';
    if(packFile.files.length > 0) packBase64 = await getBase64(packFile.files[0]);
    if(resiFile.files.length > 0) resiBase64 = await getBase64(resiFile.files[0]);

    try {
        const res = await fetch('api/update_shipping.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, packing_base64: packBase64, resi_base64: resiBase64, resi_number: resiNum })
        });
        const json = await res.json();
        if(json.status === 'success') {
            closeModal('modal-shipping'); Swal.fire('Berhasil', json.message, 'success'); loadOrders(); 
        } else Swal.fire('Gagal', json.message, 'error');
    } catch(e) { Swal.fire('Error', 'Koneksi gagal', 'error'); }
}

// --- FUNGSI LIHAT DETAIL (Ada Foto) ---
async function showDetail(id) {
    Swal.showLoading();
    try {
        const res = await fetch(`api/get_order_detail.php?id=${id}`);
        const json = await res.json();
        
        if(json.status === 'success') {
            const o = json.data.order;
            document.getElementById('d-kp').innerText = `${o.kp_number} (${o.brand || 'Sigma'})`;
            
            // Payment Info
            document.getElementById('d-pay-status').innerText = o.pay_status || 'Belum Bayar';
            if (o.pay_status === 'DP') document.getElementById('d-pay-dp').innerText = "DP: Rp " + parseInt(o.dp_amount || 0).toLocaleString();
            else document.getElementById('d-pay-dp').innerText = "";

            if (o.payment_proof) {
                document.getElementById('d-pay-img-box').classList.remove('hidden');
                document.getElementById('d-pay-link').href = o.payment_proof; 
            } else { document.getElementById('d-pay-img-box').classList.add('hidden'); }

            // Shipping Info (Packing & Resi)
            const packBox = document.getElementById('d-pack-box');
            if(o.packing_proof) {
                packBox.innerHTML = `<a href="${o.packing_proof}" target="_blank"><img src="${o.packing_proof}" class="w-full h-32 object-cover rounded shadow hover:opacity-80 transition"></a>`;
            } else packBox.innerHTML = `<span class="text-sm text-gray-500 italic">Belum ada foto packing</span>`;

            const resiBox = document.getElementById('d-resi-box');
            document.getElementById('d-resi-txt').innerText = o.resi_number ? `(${o.resi_number})` : '';
            if(o.resi_proof) {
                resiBox.innerHTML = `<a href="${o.resi_proof}" target="_blank"><img src="${o.resi_proof}" class="w-full h-32 object-cover rounded shadow hover:opacity-80 transition"></a>`;
            } else resiBox.innerHTML = `<span class="text-sm text-gray-500 italic">Belum ada foto resi</span>`;

            // Items
            const tbody = document.getElementById('d-items');
            tbody.innerHTML = '';
            json.data.items.forEach(i => {
                tbody.innerHTML += `<tr><td class="px-4 py-2">${i.product_name}</td><td class="px-4 py-2 text-center">${parseFloat(i.qty)} ${i.unit}</td><td class="px-4 py-2 text-right">Rp ${parseInt(i.deal_price).toLocaleString()}</td><td class="px-4 py-2 text-right font-bold text-purple-800">Rp ${parseInt(i.subtotal).toLocaleString()}</td></tr>`;
            });

            Swal.close();
            document.getElementById('modal-detail').classList.remove('hidden');
        }
    } catch(e) { console.error(e); Swal.fire('Error', 'Gagal memuat detail', 'error'); }
}

function updateStatus(id, action) {
    let title = action === 'delete' ? 'Hapus Permanen?' : (action === 'verify' ? 'Verifikasi Pesanan Ini?' : 'Tolak Pesanan?');
    Swal.fire({
        title: title, icon: 'warning', showCancelButton: true, confirmButtonColor: '#10b981', confirmButtonText: 'Ya', cancelButtonText: 'Batal'
    }).then((res) => {
        if (res.isConfirmed) {
            fetch('api/order_action.php', { method: 'POST', body: JSON.stringify({ order_id: id, action: action }) })
            .then(r => r.json()).then(d => { if(d.status === 'success') { Swal.fire('Sukses', d.message, 'success'); loadOrders(); } });
        }
    });
}
// showWA dan copyWA tidak saya tulis ulang agar hemat ruang, logikanya sama seperti yang sebelumnya.
</script>