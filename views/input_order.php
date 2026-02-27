<?php
// Buka akses untuk Marketing & Admin
$userRole = $_SESSION['user']['role'] ?? '';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    <div class="mb-6 border-b-2 border-purple-200 pb-2">
        <h1 class="text-2xl font-bold text-purple-900 uppercase tracking-wider">Buat Faktur Penjualan</h1>
        <p class="text-sm text-purple-600">Marketing: <span class="font-bold"><?= $_SESSION['user']['name'] ?></span></p>
    </div>

    <form id="orderForm" onsubmit="app.submitOrder(event)" class="space-y-6">
        
        <!-- 1. PENGATURAN AWAL -->
        <div class="bg-white rounded-xl shadow-md border-t-4 border-purple-600 overflow-hidden">
            <div class="p-4 bg-purple-50 border-b border-purple-100">
                <h2 class="font-bold text-purple-800"><i class="fas fa-tags mr-2"></i>Sumber & Gudang</h2>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Brand</label>
                    <select id="inp-brand" class="w-full border border-gray-300 rounded p-2 focus:ring-purple-500 bg-white outline-none" required>
                        <option value="Sigma">Sigma</option>
                        <option value="Vendu">Vendu</option>
                        <option value="Green Grass">Green Grass</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sumber Trafik</label>
                    <select id="inp-traffic" onchange="app.checkTraffic()" class="w-full border border-gray-300 rounded p-2 focus:ring-purple-500 bg-white outline-none" required>
                        <option value="Organik">Organik / WA</option>
                        <option value="Ads">Ads / Iklan</option>
                        <option value="TikTok">TikTok</option>
                        <option value="Shopee">Shopee</option>
                        <option value="Lazada">Lazada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Gudang Cabang</label>
                    <select id="inp-wh" onchange="app.calcGoods()" class="w-full border border-purple-400 rounded p-2 focus:ring-purple-500 bg-purple-50 font-bold text-purple-800 shadow-sm outline-none" required>
                        <option value="">Loading Gudang...</option>
                    </select>
                </div>
                <div id="shopee-fee-box" class="hidden md:col-span-3 bg-red-50 p-3 rounded border border-red-200">
                    <label class="block text-xs font-bold text-red-600 uppercase mb-1">Potongan Admin Marketplace (Rp)</label>
                    <input type="number" id="inp-mp-fee" oninput="app.calcGrandTotal()" class="w-full md:w-1/3 border-red-300 rounded p-2 text-red-700 font-bold outline-none" value="0">
                </div>
            </div>
        </div>

        <!-- 2. DATA PELANGGAN -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h2 class="font-bold text-gray-700"><i class="fas fa-user mr-2 text-purple-600"></i>Data Pelanggan (KP)</h2>
                <span class="text-xs bg-purple-100 px-2 py-1 rounded text-purple-800 font-mono font-bold border border-purple-200" id="new-kp-id">Loading ID...</span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pelanggan</label><input type="text" id="inp-cust" class="w-full border border-gray-300 rounded p-2 focus:ring-purple-500 outline-none" required></div>
                <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. WhatsApp</label><input type="number" id="inp-phone" class="w-full border border-gray-300 rounded p-2 focus:ring-purple-500 outline-none" required></div>
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alamat Kirim Lengkap</label><textarea id="inp-addr" rows="2" class="w-full border border-gray-300 rounded p-2 focus:ring-purple-500 outline-none"></textarea></div>
                <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Link Maps (Opsional)</label><input type="url" id="inp-maps" class="w-full border border-gray-300 rounded p-2 text-blue-600 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tgl Kirim Barang</label><input type="date" id="inp-date-send" class="w-full border border-gray-300 rounded p-2 outline-none"></div>
            </div>
        </div>
                
        <!-- 3. RINCIAN BARANG & FEE -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-4 bg-purple-50 border-b border-purple-100">
                <h3 class="font-bold text-purple-800"><i class="fas fa-box-open mr-2"></i>Rincian Produk (Barang) & Fee</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm mb-3 min-w-[700px]">
                        <thead>
                            <tr class="text-left text-[10px] text-gray-500 uppercase tracking-wider bg-gray-100">
                                <th class="p-3 rounded-tl w-2/5">Nama Produk & Ukuran Potong</th>
                                <th class="p-3 w-1/6">Qty (Desimal)</th>
                                <th class="p-3 w-1/4">Harga Nego (Rp)</th>
                                <th class="p-3 w-1/4 text-right">Subtotal</th>
                                <th class="p-3 w-12 text-center rounded-tr">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <button type="button" onclick="app.addCartRow()" class="text-xs bg-white border border-dashed border-purple-400 px-3 py-2.5 rounded-lg text-purple-700 hover:bg-purple-50 transition w-full text-center font-bold mt-2">
                    <i class="fas fa-plus mr-1"></i> Tambah Baris Produk
                </button>
                
                <div class="mt-6 pt-5 border-t border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- INPUT FEE MANUAL -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Input Fee Rumput / MS (Rp)</label>
                        <input type="number" id="inp-fee-r" class="w-full border border-gray-300 rounded p-2 focus:ring-purple-500 font-bold text-gray-700 outline-none" value="0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Input Fee DC (Rp)</label>
                        <input type="number" id="inp-fee-dc" class="w-full border border-gray-300 rounded p-2 focus:ring-purple-500 font-bold text-gray-700 outline-none" value="0">
                    </div>
                    <!-- SUBTOTAL BARANG -->
                    <div class="text-right">
                        <span class="text-sm font-bold text-gray-600 block mb-1">Total Barang Saja:</span>
                        <span class="text-2xl font-black text-purple-900" id="total-goods">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. TOGGLE INSTALASI -->
        <div class="flex items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50 transition" onclick="document.getElementById('toggle-install').click()">
            <input type="checkbox" id="toggle-install" onclick="event.stopPropagation()" class="w-6 h-6 text-purple-600 rounded focus:ring-purple-500 border-gray-300">
            <div>
                <label class="font-bold text-gray-800 text-sm md:text-base cursor-pointer">Sertakan Jasa Instalasi?</label>
                <p class="text-xs text-gray-500">Centang untuk membuat Tiket Instalasi terpisah (Membuat Faktur KI).</p>
            </div>
        </div>

        <!-- 5. FORM INSTALASI (KI) -->
        <div id="section-install" class="hidden bg-orange-50 rounded-xl shadow-sm border border-orange-200 overflow-hidden">
            <div class="p-4 bg-orange-100 border-b border-orange-200 flex justify-between items-center">
                <h2 class="font-bold text-orange-800"><i class="fas fa-tools mr-2"></i>Data Instalasi (KI) - Ditagih Terpisah</h2>
                <span class="text-xs bg-white border border-orange-300 px-2 py-1 rounded text-orange-600 font-mono font-bold" id="new-ki-id">Loading ID...</span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Mandor (Opsional)</label><input type="text" id="inp-mandor" class="w-full border border-gray-300 rounded p-2 bg-white outline-none focus:border-orange-500"></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tgl Pengerjaan</label><input type="date" id="inp-date-install" class="w-full border border-gray-300 rounded p-2 bg-white outline-none"></div>
                </div>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Luas (m²)</label>
                            <input type="number" step="any" min="0.1" id="inp-install-qty" oninput="app.calcInstall()" class="w-full border border-gray-300 rounded p-2 bg-white outline-none focus:border-orange-500">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga Jasa/m²</label>
                            <input type="number" id="inp-install-price" oninput="app.calcInstall()" class="w-full border border-gray-300 rounded p-2 bg-white outline-none focus:border-orange-500" value="35000">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-orange-800 uppercase mb-1">Total Tagihan Instalasi</label>
                        <input type="text" id="inp-install-total" class="w-full border-orange-300 rounded p-2 bg-orange-100 font-bold text-orange-900 text-lg outline-none" readonly value="Rp 0">
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. FOOTER PEMISAH KP -->
        <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-gray-800 flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-5 pointer-events-none"><i class="fas fa-file-invoice text-9xl"></i></div>
            <div class="w-full md:w-2/3 relative z-10">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">TOTAL TAGIHAN BARANG (KP) BERSIH</label>
                <div class="text-4xl font-black text-purple-700 leading-none mb-1" id="grand-total">Rp 0</div>
                <div class="text-sm font-bold text-orange-600 h-5" id="info-pisah"></div>
            </div>
            <div class="w-full md:w-1/3 flex justify-end relative z-10">
                <button type="submit" class="w-full md:w-auto py-4 px-8 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold shadow-lg flex items-center justify-center gap-2 transition transform active:scale-95">
                    <i class="fas fa-paper-plane"></i> Proses Faktur
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const app = {
    products: [],
    username: "<?= $_SESSION['user']['username'] ?>",

    init: async function() {
        try {
            const resW = await fetch('api/warehouse_action.php?action=get');
            const dataW = await resW.json();
            const whSelect = document.getElementById('inp-wh');
            whSelect.innerHTML = '<option value="">Pilih Gudang...</option>';
            dataW.data.forEach(w => { whSelect.innerHTML += `<option value="${w.name}">${w.name}</option>`; });
        } catch(e) {}

        try {
            const resP = await fetch('api/get_products.php');
            const jsonP = await resP.json();
            if(jsonP.status === 'success') { this.products = jsonP.data; this.addCartRow(); }
        } catch(e) {}

        const today = new Date();
        document.getElementById('inp-date-send').valueAsDate = today;
        document.getElementById('inp-date-install').valueAsDate = today;
        
        this.generateKP(); 

        document.getElementById('inp-date-send').addEventListener('change', () => this.generateKP());
        document.getElementById('inp-date-install').addEventListener('change', () => this.generateKI());

        document.getElementById('toggle-install').addEventListener('change', (e) => {
            const el = document.getElementById('section-install');
            if(e.target.checked) { el.classList.remove('hidden'); this.generateKI(); } 
            else { el.classList.add('hidden'); document.getElementById('inp-install-qty').value = ''; this.calcInstall(); }
            this.calcGrandTotal();
        });
    },

    checkTraffic: function() {
        const val = document.getElementById('inp-traffic').value;
        const box = document.getElementById('shopee-fee-box');
        if(val === 'Shopee' || val === 'Lazada') box.classList.remove('hidden');
        else { box.classList.add('hidden'); document.getElementById('inp-mp-fee').value = 0; }
        this.calcGrandTotal();
    },

    generateKP: async function() {
        const dateVal = document.getElementById('inp-date-send').value;
        const display = document.getElementById('new-kp-id');
        display.innerText = "Loading...";
        try {
            const res = await fetch(`api/get_next_id.php?type=kp&date=${dateVal}`);
            const json = await res.json();
            if(json.status === 'success') display.innerText = json.id;
        } catch(e) { display.innerText = "ERR"; }
    },
    
    generateKI: async function() {
         const dateVal = document.getElementById('inp-date-install').value;
         const display = document.getElementById('new-ki-id');
         display.innerText = "Loading...";
         try {
            const res = await fetch(`api/get_next_id.php?type=ki&date=${dateVal}`);
            const json = await res.json();
            if(json.status === 'success') display.innerText = json.id;
        } catch(e) { display.innerText = "ERR"; }
    },

    addCartRow: function() {
        const tbody = document.getElementById('cart-items');
        let options = `<option value="">-- Pilih Produk --</option>`;
        this.products.filter(p => p.type === 'goods').forEach(p => {
            options += `<option value="${p.id}" data-price="${p.base_price}" data-unit="${p.unit}">${p.name}</option>`;
        });

        const tr = document.createElement('tr');
        // Penambahan field "item-size" untuk mencatat detail potongan
        tr.innerHTML = `
            <td class="pr-2 pb-2 pt-2 align-top">
                <select class="w-full p-2.5 border border-gray-300 rounded-lg text-sm item-select outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" onchange="app.fillPrice(this)">${options}</select>
                <input type="text" class="w-full mt-2 p-2 border border-purple-200 bg-purple-50 rounded-lg text-xs item-size outline-none focus:border-purple-500 font-medium placeholder-purple-300 text-purple-800" placeholder="Ukuran Potong (Opsional) Cth: 2m x 5m">
            </td>
            <td class="pr-2 pb-2 pt-2 align-top">
                <input type="number" step="any" min="0.01" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm item-qty text-center outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" oninput="app.calcGoods()">
                <div class="text-[9px] text-gray-500 mt-1 stock-info text-center font-bold"></div>
            </td>
            <td class="pr-2 pb-2 pt-2 align-top"><input type="number" class="w-full p-2.5 border border-purple-300 rounded-lg text-sm bg-purple-50 item-price font-bold text-purple-700 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" oninput="app.calcGoods()"></td>
            <td class="pr-2 pb-2 pt-2 align-top"><input type="text" class="w-full p-2.5 border-0 bg-transparent text-sm text-right item-total font-bold text-gray-700" readonly value="0"></td>
            <td class="pb-2 pt-2 text-center align-top"><button type="button" onclick="this.closest('tr').remove(); app.calcGoods()" class="text-red-500 hover:bg-red-50 p-2.5 rounded-lg transition"><i class="fas fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
    },

    fillPrice: function(el) {
        const opt = el.options[el.selectedIndex];
        const price = opt.getAttribute('data-price');
        
        el.closest('tr').querySelector('.item-qty').value = 1;
        el.closest('tr').querySelector('.item-price').value = price || '';
        
        this.calcGoods();
    },

    calcGoods: function() {
        let total = 0;
        const currentWh = document.getElementById('inp-wh').value;

        document.querySelectorAll('#cart-items tr').forEach(row => {
            const qtyInput = row.querySelector('.item-qty');
            const stockInfo = row.querySelector('.stock-info');
            let qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            
            const sel = row.querySelector('.item-select');
            const pid = sel.value;
            
            if(pid) {
                const product = this.products.find(p => p.id == pid);
                if(product && product.type === 'goods') {
                    const maxStock = parseFloat(product.stocks[currentWh]) || 0;
                    stockInfo.innerText = `Stok: ${maxStock}`;
                    
                    if(qty > maxStock) {
                        Swal.fire('Stok Kurang!', `Sisa di Gudang ${currentWh} hanya ${maxStock} ${product.unit}`, 'warning');
                        qtyInput.value = maxStock;
                        qty = maxStock;
                    }
                }

                const sub = qty * price;
                row.querySelector('.item-total').value = sub.toLocaleString('id-ID');
                total += sub;
            }
        });

        const display = document.getElementById('total-goods');
        display.innerText = "Rp " + Math.round(total).toLocaleString('id-ID');
        display.dataset.val = total;
        
        this.calcGrandTotal();
    },

    calcInstall: function() {
        const qty = parseFloat(document.getElementById('inp-install-qty').value) || 0;
        const price = parseFloat(document.getElementById('inp-install-price').value) || 0;
        const total = qty * price;
        const display = document.getElementById('inp-install-total');
        display.value = "Rp " + Math.round(total).toLocaleString('id-ID');
        display.dataset.val = total;
        this.calcGrandTotal();
    },

    calcGrandTotal: function() {
        const goods = parseFloat(document.getElementById('total-goods').dataset.val) || 0;
        const mpFee = parseFloat(document.getElementById('inp-mp-fee').value) || 0;
        const grandKP = goods - mpFee;
        
        const hasInstall = document.getElementById('toggle-install').checked;
        const installKI = hasInstall ? (parseFloat(document.getElementById('inp-install-total').dataset.val) || 0) : 0;
        
        document.getElementById('grand-total').innerText = "Rp " + Math.round(grandKP).toLocaleString('id-ID');
        
        const infoPisah = document.getElementById('info-pisah');
        if (installKI > 0) {
            infoPisah.innerHTML = `<i class="fas fa-plus-circle"></i> Nilai Jasa Instalasi (KI): Rp ${Math.round(installKI).toLocaleString('id-ID')} <span class="text-xs text-gray-400 font-normal">(Ditagih Terpisah)</span>`;
        } else {
            infoPisah.innerHTML = '';
        }

        return { goods, install: installKI, mpFee, grandKP };
    },

    submitOrder: async function(e) {
        e.preventDefault();
        const totalsData = this.calcGrandTotal();
        const hasInstall = document.getElementById('toggle-install').checked;

        const items = [];
        const rows = document.querySelectorAll('#cart-items tr');
        for(let row of rows) {
            const sel = row.querySelector('.item-select');
            const pid = sel.value;
            if(pid) {
                const opt = sel.options[sel.selectedIndex];
                const sizeInput = row.querySelector('.item-size').value.trim();
                
                // MENGGABUNGKAN UKURAN KE NAMA PRODUK
                const finalProductName = sizeInput ? `${opt.text} [Ukuran: ${sizeInput}]` : opt.text;

                items.push({
                    product_id: pid, 
                    name: finalProductName, // Nama yang dikirim ke database sudah mengandung ukuran
                    qty: row.querySelector('.item-qty').value,
                    price: row.querySelector('.item-price').value,
                    sub: parseFloat(row.querySelector('.item-qty').value) * parseFloat(row.querySelector('.item-price').value)
                });
            }
        }

        if(items.length === 0) return Swal.fire('Oops', "Pilih minimal 1 produk", 'warning');

        Swal.fire({title: 'Memproses Pemesanan...', allowOutsideClick: false});
        Swal.showLoading();

        // Ambil Inputan Fee Manual
        const feeR = parseFloat(document.getElementById('inp-fee-r').value) || 0;
        const feeDc = parseFloat(document.getElementById('inp-fee-dc').value) || 0;

        const payload = {
            marketing_username: this.username,
            kp_id: document.getElementById('new-kp-id').innerText,
            ki_id: hasInstall ? document.getElementById('new-ki-id').innerText : '-',
            brand: document.getElementById('inp-brand').value,
            traffic: document.getElementById('inp-traffic').value,
            wh: document.getElementById('inp-wh').value,
            customer: document.getElementById('inp-cust').value,
            phone: document.getElementById('inp-phone').value,
            address: document.getElementById('inp-addr').value,
            maps: document.getElementById('inp-maps').value,
            date_send: document.getElementById('inp-date-send').value,
            items: items,
            install_info: hasInstall ? {
                mandor: document.getElementById('inp-mandor').value,
                date: document.getElementById('inp-date-install').value,
                qty: document.getElementById('inp-install-qty').value,
                price: document.getElementById('inp-install-price').value,
                total: totalsData.install
            } : null,
            totals: { grand: totalsData.grandKP, marketplace_fee: totalsData.mpFee },
            fees: { r: feeR, dc: feeDc }
        };

        try {
            const res = await fetch('api/save_order.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
            const result = await res.json();
            
            if(result.status === 'success') {
                Swal.fire({icon: 'success', title: 'Faktur Dibuat!', timer: 1500, showConfirmButton: false})
                .then(() => { window.location.href = 'index.php?page=input_order'; });
            } else {
                Swal.fire('Sistem Menolak!', result.message, 'error');
            }
        } catch(err) { Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error'); }
    }
};

document.addEventListener('DOMContentLoaded', () => app.init());
</script>