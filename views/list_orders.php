<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">
            <?= $_SESSION['user']['role'] === 'admin' ? 'Verifikasi Pesanan' : 'Riwayat Pesanan Saya' ?>
        </h1>
        <div class="relative w-full md:w-72">
            <input type="text" id="search" onkeyup="filterTable()" placeholder="Cari Nama / KP..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition shadow-sm">
            <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-600 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Kode KP / KI</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Pelanggan</th>
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <th class="px-6 py-4 text-left whitespace-nowrap">Marketing</th>
                        <?php endif; ?>
                        <th class="px-6 py-4 text-right whitespace-nowrap">Total</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">StatusAdmin</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody id="order-rows" class="bg-white divide-y divide-gray-200">
                    <tr><td colspan="7" class="text-center py-8 text-gray-400 italic">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DETAIL PESANAN -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-green-800" id="d-kp">KP-XXXXX</h3>
                <p class="text-xs text-green-600" id="d-date">Tanggal</p>
            </div>
            <button onclick="closeDetail()" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto space-y-6">
            <!-- Info Pelanggan -->
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Pelanggan</p>
                    <p class="font-bold text-gray-800" id="d-cust">-</p>
                    <p class="text-gray-600" id="d-phone">-</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Pengiriman</p>
                    <p class="text-gray-800"><span class="font-bold">Gudang:</span> <span id="d-wh">-</span></p>
                    <p class="text-gray-800"><span class="font-bold">Alamat:</span> <span id="d-addr">-</span></p>
                </div>
            </div>
            <!-- Tabel Barang -->
            <div class="border rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-xs uppercase font-bold text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Produk</th>
                            <th class="px-4 py-2 text-center">Qty</th>
                            <th class="px-4 py-2 text-right">Harga</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="d-items" class="divide-y divide-gray-100"></tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right text-gray-600">Total Barang</td>
                            <td class="px-4 py-2 text-right text-gray-800" id="d-total-goods">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Info Instalasi (Conditional) -->
            <div id="d-install-box" class="hidden bg-orange-50 border border-orange-200 rounded-lg p-4">
                <h4 class="text-sm font-bold text-orange-800 mb-2 border-b border-orange-200 pb-1"><i class="fas fa-hard-hat mr-1"></i> Jasa Instalasi (<span id="d-ki">KI-XXX</span>)</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-xs text-orange-600 font-bold">Mandor</p><p id="d-mandor">-</p></div>
                    <div><p class="text-xs text-orange-600 font-bold">Pengerjaan</p><p id="d-work-date">-</p></div>
                </div>
                <div class="mt-2 flex justify-between items-center text-sm font-bold text-gray-800 pt-2 border-t border-orange-200">
                    <span>Biaya Pasang (<span id="d-area">0</span> m²)</span>
                    <span id="d-install-cost">Rp 0</span>
                </div>
            </div>
            <!-- Grand Total -->
            <div class="flex justify-between items-center bg-gray-800 text-white p-4 rounded-lg shadow">
                <span class="text-sm font-bold uppercase">Grand Total</span>
                <span class="text-xl font-bold" id="d-grand-total">Rp 0</span>
            </div>
            <!-- Fee Info (Admin Only) -->
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <div class="text-xs text-gray-400 text-right italic border-t pt-2">Internal Fee -> R: <span id="d-fee-r">0</span> | Dc: <span id="d-fee-dc">0</span></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL WA -->
<div id="modal-wa" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden transform transition-all">
        <div class="p-4 border-b bg-green-50 flex justify-between items-center">
            <h3 class="font-bold text-green-800"><i class="fab fa-whatsapp mr-2"></i>Format WhatsApp</h3>
            <button onclick="document.getElementById('modal-wa').classList.add('hidden')" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-4">
            <p class="text-xs text-gray-500 mb-2">Salin teks di bawah ini:</p>
            <textarea id="wa-content" rows="12" class="w-full p-3 border rounded-lg bg-gray-50 font-mono text-xs focus:outline-none text-gray-700" readonly></textarea>
        </div>
        <div class="p-4 border-t flex justify-end gap-2 bg-gray-50">
            <button onclick="copyWA()" class="w-full bg-green-600 text-white font-bold py-2.5 rounded-lg hover:bg-green-700 flex items-center justify-center gap-2 shadow-md transition"><i class="fas fa-copy"></i> Salin Teks</button>
        </div>
    </div>
</div>

<script>
let allOrders = [];
const isAdmin = <?= $_SESSION['user']['role'] === 'admin' ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', loadOrders);

async function loadOrders() {
    const tbody = document.getElementById('order-rows');
    try {
        const res = await fetch('api/get_orders.php');
        const json = await res.json();
        if(json.status === 'success') {
            allOrders = json.data;
            renderTable(allOrders);
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-red-500">Gagal mengambil data.</td></tr>';
        }
    } catch(e) { console.error(e); }
}

function renderTable(data) {
    const tbody = document.getElementById('order-rows');
    tbody.innerHTML = '';
    
    if(data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-400">Belum ada data transaksi.</td></tr>';
        return;
    }

    data.forEach(o => {
        const date = new Date(o.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
        let kiBadge = o.ki_number ? `<div class="mt-1 text-xs font-mono text-orange-600 font-bold bg-orange-50 px-1 rounded inline-block border border-orange-200">${o.ki_number}</div>` : '';
        
        let statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200'; // Default Pending
        let statusLabel = o.order_status || 'Pending';
        
        if(statusLabel === 'Verified') {
            statusClass = 'bg-green-100 text-green-800 border border-green-200';
        } else if(statusLabel === 'Rejected') {
            statusClass = 'bg-red-100 text-red-800 border border-red-200';
        }

        let marketingCol = isAdmin ? `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${o.marketing_name}</td>` : '';
        
        // --- BUTTON LOGIC ---
        let btns = `<div class="flex justify-center space-x-1">`;
        
        // 1. Detail (Semua bisa lihat)
        btns += `<button onclick="showDetail(${o.id})" class="text-blue-600 hover:bg-blue-50 p-2 rounded transition" title="Lihat Detail"><i class="fas fa-eye"></i></button>`;
        
        // 2. WA (Semua bisa lihat)
        btns += `<button onclick="showWA(${o.id})" class="text-green-600 hover:bg-green-50 p-2 rounded transition" title="Format WA"><i class="fab fa-whatsapp text-lg"></i></button>`;

        // 3. Verifikasi/Tolak (Hanya Admin & Status Pending)
        if(isAdmin && statusLabel === 'Pending') {
            btns += `<button onclick="updateStatus(${o.id}, 'verify')" class="text-green-600 hover:bg-green-50 p-2 rounded transition" title="Setujui"><i class="fas fa-check"></i></button>`;
            btns += `<button onclick="updateStatus(${o.id}, 'reject')" class="text-orange-500 hover:bg-orange-50 p-2 rounded transition" title="Tolak"><i class="fas fa-ban"></i></button>`;
        }

        // 4. Edit (Admin BEBAS, Marketing hanya jika PENDING)
        // Admin bisa edit kapanpun? Biasanya ya. Marketing cuma kalau belum di acc.
        let canEdit = isAdmin || (statusLabel === 'Pending');
        if (canEdit) {
            btns += `<a href="index.php?page=edit_order&id=${o.id}" class="text-yellow-600 hover:bg-yellow-50 p-2 rounded transition" title="Edit"><i class="fas fa-edit text-lg"></i></a>`;
        }

        // 5. Hapus (Admin BEBAS, Marketing hanya jika PENDING)
        let canDelete = isAdmin || (statusLabel === 'Pending');
        if(canDelete) {
             btns += `<button onclick="updateStatus(${o.id}, 'delete')" class="text-red-500 hover:bg-red-50 p-2 rounded transition" title="Hapus"><i class="fas fa-trash"></i></button>`;
        }

        btns += `</div>`;

        tbody.innerHTML += `
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${date}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-bold text-blue-600 font-mono text-sm">${o.kp_number}</div>
                    ${kiBadge}
                </td>
                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">${o.customer_name}</div></td>
                ${marketingCol}
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">Rp ${parseInt(o.grand_total).toLocaleString('id-ID')}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${statusLabel}</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">${btns}</td>
            </tr>
        `;
    });
}

// FUNGSI: SHOW DETAIL
async function showDetail(id) {
    Swal.showLoading();
    try {
        const res = await fetch(`api/get_order_detail.php?id=${id}`);
        const json = await res.json();
        
        if(json.status === 'success') {
            const d = json.data;
            const o = d.order;
            const ins = d.install;

            document.getElementById('d-kp').innerText = o.kp_number;
            document.getElementById('d-date').innerText = new Date(o.created_at).toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
            document.getElementById('d-cust').innerText = o.customer_name;
            document.getElementById('d-phone').innerText = o.customer_phone;
            document.getElementById('d-wh').innerText = o.warehouse_source;
            document.getElementById('d-addr').innerText = o.customer_address;

            const tbody = document.getElementById('d-items');
            tbody.innerHTML = '';
            let totalGoods = 0;
            d.items.forEach(i => {
                const sub = parseInt(i.subtotal);
                totalGoods += sub;
                tbody.innerHTML += `<tr><td class="px-4 py-2 text-gray-800">${i.product_name}</td><td class="px-4 py-2 text-center text-gray-600">${parseFloat(i.qty)} ${i.unit}</td><td class="px-4 py-2 text-right text-gray-600">Rp ${parseInt(i.deal_price).toLocaleString()}</td><td class="px-4 py-2 text-right font-bold text-gray-800">Rp ${sub.toLocaleString()}</td></tr>`;
            });
            document.getElementById('d-total-goods').innerText = "Rp " + totalGoods.toLocaleString('id-ID');

            const installBox = document.getElementById('d-install-box');
            if(ins) {
                installBox.classList.remove('hidden');
                document.getElementById('d-ki').innerText = o.ki_number;
                document.getElementById('d-mandor').innerText = ins.mandor_name;
                document.getElementById('d-work-date').innerText = ins.work_date;
                document.getElementById('d-area').innerText = ins.area_size;
                document.getElementById('d-install-cost').innerText = "Rp " + parseInt(ins.total_price).toLocaleString('id-ID');
            } else { installBox.classList.add('hidden'); }

            document.getElementById('d-grand-total').innerText = "Rp " + parseInt(o.grand_total).toLocaleString('id-ID');
            
            if(document.getElementById('d-fee-r')) {
                document.getElementById('d-fee-r').innerText = parseInt(o.total_fee_r).toLocaleString();
                document.getElementById('d-fee-dc').innerText = parseInt(o.total_fee_dc).toLocaleString();
            }

            Swal.close();
            document.getElementById('modal-detail').classList.remove('hidden');
        } else { Swal.fire('Error', json.message, 'error'); }
    } catch(e) { console.error(e); Swal.fire('Error', 'Gagal memuat detail', 'error'); }
}

// FUNGSI: GENERATE WA
async function showWA(id) {
    Swal.showLoading();
    try {
        const res = await fetch(`api/get_order_detail.php?id=${id}`);
        const json = await res.json();
        
        if(json.status === 'success') {
            const d = json.data;
            const o = d.order;
            const ins = d.install;
            
            let itemsTxt = '';
            d.items.forEach((i, idx) => {
                itemsTxt += `${idx+1}️⃣ ${i.product_name}\n${parseFloat(i.qty)}${i.unit} x ${parseInt(i.deal_price).toLocaleString()}/m² = ${parseInt(i.subtotal).toLocaleString()}\n`;
            });

            let installTxt = '';
            let mandorTxt = '';
            let kiTxt = '';
            
            if(ins) {
                kiTxt = '\n*' + o.ki_number + '*';
                mandorTxt = 'Nama Mandor : ' + ins.mandor_name;
                installTxt = `3️⃣ Jasa Pemasangan\n${parseFloat(ins.area_size)}m² x ${parseInt(ins.service_price).toLocaleString()} = ${parseInt(ins.total_price).toLocaleString()}\n`;
            }

            const format = `*Format Pemesanan*
_Project by ${o.marketing_name}_

*${o.kp_number}*${kiTxt}

Nama : ${o.customer_name}
${mandorTxt}
No : ${o.customer_phone}
Alamat : ${o.customer_address} ${o.maps_link}

*Rincian :*
${itemsTxt}${installTxt}
*Total Pembayaran Rp ${parseInt(o.grand_total).toLocaleString()}*

Pengiriman : Dari ${o.warehouse_source}
Di pick up : ${new Date(o.delivery_date).toLocaleDateString('id-ID')}
Pembayaran : ${o.pay_status}

Fee R : ${parseInt(o.total_fee_r).toLocaleString()}
Fee Dc : ${parseInt(o.total_fee_dc).toLocaleString()}`;
            
            document.getElementById('wa-content').value = format;
            Swal.close();
            document.getElementById('modal-wa').classList.remove('hidden');
        } else { Swal.fire('Error', json.message, 'error'); }
    } catch(e) { console.error(e); Swal.fire('Error', 'Gagal memuat format WA', 'error'); }
}

function copyWA() {
    const txt = document.getElementById('wa-content');
    txt.select();
    document.execCommand('copy');
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Teks WA tersalin!', showConfirmButton: false, timer: 1500 });
}

function closeDetail() { document.getElementById('modal-detail').classList.add('hidden'); }

function updateStatus(id, action) {
    let title = action === 'delete' ? 'Hapus Permanen?' : (action === 'verify' ? 'Verifikasi?' : 'Tolak?');
    let color = action === 'verify' ? '#10b981' : '#ef4444';
    Swal.fire({
        title: title,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: color,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((res) => {
        if (res.isConfirmed) {
            fetch('api/order_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order_id: id, action: action })
            })
            .then(r => r.json())
            .then(d => {
                if(d.status === 'success') {
                    Swal.fire('Sukses', d.message, 'success');
                    loadOrders();
                } else Swal.fire('Gagal', d.message, 'error');
            });
        }
    });
}

function filterTable() {
    const term = document.getElementById('search').value.toLowerCase();
    const filtered = allOrders.filter(o => o.customer_name.toLowerCase().includes(term) || o.kp_number.toLowerCase().includes(term));
    renderTable(filtered);
}
</script>