<?php
// File: views/input_install.php
require_once 'config/database.php';
$userRole = $_SESSION['user']['role'] ?? '';

// Ambil Order yang BELUM PUNYA Instalasi (ki_number NULL)
$stmt = $pdo->query("SELECT id, kp_number, customer_name, customer_address FROM orders WHERE (ki_number IS NULL OR ki_number = '-') AND order_status != 'Batal' ORDER BY created_at DESC");
$pendingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    <div class="mb-6 border-b-2 border-orange-200 pb-2">
        <h1 class="text-2xl font-bold text-orange-900 uppercase tracking-wider">Buat Faktur Instalasi (KI)</h1>
        <p class="text-sm text-orange-600">Marketing: <span class="font-bold"><?= $_SESSION['user']['name'] ?></span></p>
    </div>

    <form id="installForm" onsubmit="submitInstall(event)" class="space-y-6">
        
        <div class="bg-white rounded-xl shadow-md border-t-4 border-orange-500 overflow-hidden">
            <div class="p-4 bg-orange-50 border-b border-orange-100">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Faktur Barang (KP) Yang Membutuhkan Jasa Pasang</label>
                <select id="sel-order" onchange="fetchOrderDetails()" class="w-full border border-orange-300 rounded-lg p-3 focus:ring-orange-500 bg-white outline-none font-bold text-gray-800" required>
                    <option value="">-- Cari No KP atau Nama Pelanggan --</option>
                    <?php foreach($pendingOrders as $o): ?>
                        <option value="<?= $o['id'] ?>"><?= $o['kp_number'] ?> - <?= strtoupper($o['customer_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="box-detail" class="hidden p-6 bg-gray-50">
                <h3 class="text-sm font-black text-gray-700 uppercase mb-3 border-b pb-2">Rincian Barang Yang Akan Dipasang:</h3>
                <div id="item-list" class="space-y-2"></div>
                <div class="mt-4 pt-3 border-t border-gray-200 text-xs text-gray-500">
                    <i class="fas fa-map-marker-alt text-red-500 mr-1"></i> Alamat: <span id="lbl-alamat" class="font-bold text-gray-800"></span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden" id="box-form" style="display:none;">
            <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h2 class="font-bold text-gray-700"><i class="fas fa-tools mr-2 text-orange-600"></i>Data Pekerjaan Instalasi</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Mandor (Opsional)</label>
                        <input type="text" id="inp-mandor" class="w-full border border-gray-300 rounded p-3 outline-none focus:border-orange-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tgl Estimasi Pasang</label>
                        <input type="date" id="inp-date-install" class="w-full border border-gray-300 rounded p-3 outline-none font-bold" required>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Luas (m²)</label>
                            <input type="number" step="any" min="0.1" id="inp-qty" oninput="calcInstall()" class="w-full border border-gray-300 rounded p-3 outline-none focus:border-orange-500 font-black text-center" required>
                        </div>
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga Jasa / m²</label>
                            <input type="number" id="inp-price" oninput="calcInstall()" class="w-full border border-gray-300 rounded p-3 outline-none focus:border-orange-500 font-bold" value="35000" required>
                        </div>
                    </div>
                    <div class="bg-orange-50 p-3 rounded-lg border border-orange-200">
                        <label class="block text-xs font-bold text-orange-800 uppercase mb-1">Total Tagihan Jasa Bersih</label>
                        <div class="text-3xl font-black text-orange-900" id="txt-total">Rp 0</div>
                        <input type="hidden" id="inp-total" value="0">
                    </div>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="submit" class="w-full md:w-auto py-3.5 px-8 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-black shadow-lg flex items-center justify-center gap-2 transition transform active:scale-95">
                    <i class="fas fa-file-signature"></i> Terbitkan Faktur Instalasi
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('inp-date-install').valueAsDate = new Date();
});

async function fetchOrderDetails() {
    const orderId = document.getElementById('sel-order').value;
    const boxDetail = document.getElementById('box-detail');
    const boxForm = document.getElementById('box-form');
    
    if(!orderId) {
        boxDetail.classList.add('hidden'); boxForm.style.display = 'none'; return;
    }

    try {
        const res = await fetch(`api/get_order_detail.php?id=${orderId}`);
        const json = await res.json();
        
        if(json.status === 'success') {
            const o = json.data.order;
            document.getElementById('lbl-alamat').innerText = o.customer_address || '-';
            
            let htmlItems = '';
            json.data.items.forEach(i => {
                let sizeStr = "";
                let rawName = i.product_name;
                if (i.item_note) {
                    const match = i.item_note.match(/\[Ukuran:\s*(.+?)\]/i);
                    if (match && match[1]) sizeStr = `<span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-[10px] font-black"><i class="fas fa-cut mr-1"></i> ${match[1]}</span>`;
                    rawName = i.item_note.replace(/\s*\[Ukuran:\s*(.+?)\]/i, '');
                }
                htmlItems += `
                    <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                        <div>
                            <div class="font-bold text-gray-800 text-sm">${rawName}</div>
                            <div class="mt-1">${sizeStr}</div>
                        </div>
                        <div class="font-black text-purple-700 bg-purple-50 px-3 py-1 rounded">${parseFloat(i.qty)} ${i.unit}</div>
                    </div>
                `;
            });
            
            document.getElementById('item-list').innerHTML = htmlItems;
            boxDetail.classList.remove('hidden');
            boxForm.style.display = 'block';
        }
    } catch(e) { Swal.fire('Error', 'Gagal menarik data pesanan', 'error'); }
}

function calcInstall() {
    const qty = parseFloat(document.getElementById('inp-qty').value) || 0;
    const price = parseFloat(document.getElementById('inp-price').value) || 0;
    const total = qty * price;
    
    document.getElementById('inp-total').value = total;
    document.getElementById('txt-total').innerText = "Rp " + Math.round(total).toLocaleString('id-ID');
}

async function submitInstall(e) {
    e.preventDefault();
    const orderId = document.getElementById('sel-order').value;
    const total = parseFloat(document.getElementById('inp-total').value) || 0;
    
    if(!orderId) return Swal.fire('Oops', 'Pilih faktur KP dulu', 'warning');
    if(total <= 0) return Swal.fire('Oops', 'Luas dan harga jasa belum diisi dengan benar', 'warning');

    Swal.fire({title: 'Memproses...', allowOutsideClick: false}); Swal.showLoading();

    const payload = {
        order_id: orderId,
        mandor: document.getElementById('inp-mandor').value,
        date: document.getElementById('inp-date-install').value,
        qty: document.getElementById('inp-qty').value,
        price: document.getElementById('inp-price').value,
        total: total
    };

    try {
        const res = await fetch('api/save_install.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
        const result = await res.json();
        if(result.status === 'success') {
            Swal.fire({icon: 'success', title: 'Faktur Instalasi Dibuat!', text: 'Nomor: ' + result.ki_number, timer: 2000, showConfirmButton: false})
            .then(() => { window.location.href = 'index.php?page=faktur_ki'; });
        } else { Swal.fire('Gagal', result.message, 'error'); }
    } catch(err) { Swal.fire('Error', 'Kesalahan Jaringan', 'error'); }
}
</script>