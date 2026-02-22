<?php
$id = $_GET['id'] ?? 0;
?>
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Edit Pesanan</h1>
        <a href="index.php?page=my_orders" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left"></i>
            Kembali</a>
    </div>

    <form onsubmit="app.updateOrder(event)" class="space-y-6">
        <input type="hidden" id="order-id" value="<?= $id ?>">

        <!-- DATA PELANGGAN -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 bg-yellow-50 border-b border-yellow-200 flex justify-between items-center">
                <h2 class="font-bold text-yellow-800"><i class="fas fa-edit mr-2"></i>Edit Data Pesanan</h2>
                <span
                    class="text-xs bg-white px-2 py-1 rounded border border-yellow-300 font-mono font-bold text-gray-600"
                    id="kp-display">Loading...</span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase">Nama Pelanggan</label><input
                            type="text" id="inp-cust" class="input-std"></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase">No. WhatsApp</label><input
                            type="number" id="inp-phone" class="input-std"></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase">Alamat</label><textarea
                            id="inp-addr" rows="2" class="input-std"></textarea></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase">Maps</label><input type="text"
                            id="inp-maps" class="input-std text-blue-600"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-bold text-gray-500 uppercase">Tgl Kirim</label><input
                                type="date" id="inp-date-send" class="input-std"></div>
                        <div><label class="block text-xs font-bold text-gray-500 uppercase">Gudang</label><select
                                id="inp-wh" class="input-std bg-white">
                                <option>Rainbow</option>
                                <option>Pusat</option>
                            </select></div>
                    </div>
                </div>
                <!-- Cart Items -->
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Rincian Barang</h3>
                    <table class="w-full text-sm mb-3">
                        <thead>
                            <tr class="text-left text-[10px] text-gray-400 uppercase tracking-wider">
                                <th class="pb-2 w-4/12">Produk</th>
                                <th class="pb-2 w-2/12">Qty</th>
                                <th class="pb-2 w-3/12">Harga (Nego)</th>
                                <th class="pb-2 w-2/12 text-right">Total</th>
                                <th class="w-1/12"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-items"></tbody>
                    </table>
                    <button type="button" onclick="app.addCartRow()"
                        class="text-xs text-blue-600 font-bold hover:underline">+ Tambah Barang</button>
                    <div class="mt-4 pt-4 border-t flex justify-between font-bold text-gray-700">
                        <span>Subtotal:</span><span id="total-goods">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- INSTALASI -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center gap-2 mb-4">
                <input type="checkbox" id="toggle-install" class="w-5 h-5 text-green-600 rounded">
                <label for="toggle-install" class="font-bold text-gray-700">Ada Jasa Instalasi?</label>
                <span class="ml-auto text-xs font-mono bg-gray-100 px-2 py-1 rounded" id="ki-display">-</span>
            </div>
            <div id="section-install" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase">Mandor</label><input type="text" id="inp-mandor"
                        class="input-std">
                    <label class="text-xs font-bold uppercase">Tgl Pasang</label><input type="date"
                        id="inp-date-install" class="input-std">
                </div>
                <div class="space-y-2">
                    <div class="flex gap-2">
                        <div class="w-1/2"><label class="text-xs font-bold uppercase">Luas (m²)</label><input
                                type="number" id="inp-install-qty" class="input-std" oninput="app.calcInstall()"></div>
                        <div class="w-1/2"><label class="text-xs font-bold uppercase">Harga Jasa</label><input
                                type="number" id="inp-install-price" class="input-std" oninput="app.calcInstall()">
                        </div>
                    </div>
                    <label class="text-xs font-bold uppercase">Total Jasa</label>
                    <input type="text" id="inp-install-total" class="input-std bg-gray-100 font-bold" readonly>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex justify-between items-center">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase">Grand Total</label>
                <div class="text-2xl font-bold text-green-700" id="grand-total">Rp 0</div>
            </div>
            <div class="flex gap-2">
                <select id="inp-pay" class="input-std w-40 font-bold">
                    <option>Belum Bayar</option>
                    <option>DP</option>
                    <option>Lunas</option>
                </select>
                <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-6 py-2 rounded-lg shadow transition transform hover:scale-105">Update
                    Pesanan</button>
            </div>
        </div>
    </form>
</div>

<script>
    const app = {
        products: [],
        username: "<?= $_SESSION['user']['username'] ?>",

        init: async function () {
            // 1. Load Master Produk
            try {
                const resP = await fetch('api/get_products.php');
                const jsonP = await resP.json();
                this.products = jsonP.data;
            } catch (e) { console.error("Gagal load produk"); }

            // 2. Load Data Pesanan Existing
            const id = document.getElementById('order-id').value;
            try {
                const resO = await fetch(`api/get_order_detail.php?id=${id}`);
                const jsonO = await resO.json();

                if (jsonO.status === 'success') {
                    this.fillForm(jsonO.data);
                } else {
                    Swal.fire('Error', 'Gagal memuat data pesanan', 'error');
                }
            } catch (e) { console.error(e); }

            // Listener
            document.getElementById('toggle-install').addEventListener('change', (e) => {
                document.getElementById('section-install').classList.toggle('hidden', !e.target.checked);
                if (!e.target.checked) {
                    document.getElementById('inp-install-total').value = 0;
                    document.getElementById('inp-install-total').dataset.val = 0;
                } else {
                    this.calcInstall();
                }
                this.calcGrandTotal();
            });
        },

        fillForm: function (data) {
            const o = data.order;
            const ins = data.install;

            // Header
            document.getElementById('kp-display').innerText = o.kp_number;
            document.getElementById('inp-cust').value = o.customer_name;
            document.getElementById('inp-phone').value = o.customer_phone;
            document.getElementById('inp-addr').value = o.customer_address;
            document.getElementById('inp-maps').value = o.maps_link;
            document.getElementById('inp-date-send').value = o.delivery_date;
            document.getElementById('inp-wh').value = o.warehouse_source;
            document.getElementById('inp-pay').value = o.pay_status;

            // Items
            const tbody = document.getElementById('cart-items');
            tbody.innerHTML = '';
            data.items.forEach(item => {
                this.addCartRow(item);
            });
            // Jika kosong, tambah 1 baris
            if (data.items.length === 0) this.addCartRow();

            this.calcGoods();

            // Install
            if (ins) {
                document.getElementById('toggle-install').checked = true;
                document.getElementById('section-install').classList.remove('hidden');
                document.getElementById('ki-display').innerText = o.ki_number || '-';

                document.getElementById('inp-mandor').value = ins.mandor_name;
                document.getElementById('inp-date-install').value = ins.work_date;
                document.getElementById('inp-install-qty').value = parseFloat(ins.area_size);
                document.getElementById('inp-install-price').value = parseFloat(ins.service_price);
                this.calcInstall();
            } else {
                document.getElementById('ki-display').innerText = "Buat Baru (Otomatis)";
            }

            this.calcGrandTotal();
        },

        addCartRow: function (item = null) {
            const tbody = document.getElementById('cart-items');
            let options = `<option value="">-- Pilih --</option>`;
            this.products.forEach(p => {
                const selected = (item && item.product_id == p.id) ? 'selected' : '';
                options += `<option value="${p.id}" ${selected} data-price="${p.base_price}" data-fee="${p.fee_amount}" data-feecode="${p.fee_code}" data-unit="${p.unit}">${p.name}</option>`;
            });

            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td class="pr-2 pb-2"><select class="input-std item-select py-1 text-sm" onchange="app.fillPrice(this)">${options}</select></td>
            <td class="pr-2 pb-2 w-20"><input type="number" class="input-std item-qty py-1 text-sm" value="${item ? parseFloat(item.qty) : 1}" oninput="app.calcGoods()"></td>
            <td class="pr-2 pb-2 w-32"><input type="number" class="input-std item-price py-1 text-sm bg-yellow-50" value="${item ? parseFloat(item.deal_price) : 0}" oninput="app.calcGoods()"></td>
            <td class="pr-2 pb-2 w-32"><input type="text" class="input-std item-total bg-gray-100 text-right py-1 text-sm border-0" readonly value="0"></td>
            <td class="pb-2 text-center"><button type="button" onclick="this.closest('tr').remove(); app.calcGoods()" class="text-red-500 hover:bg-red-50 p-1 rounded"><i class="fas fa-trash"></i></button></td>
        `;
            tbody.appendChild(tr);
        },

        fillPrice: function (el) {
            const opt = el.options[el.selectedIndex];
            const price = opt.getAttribute('data-price');
            el.closest('tr').querySelector('.item-price').value = price || 0;
            this.calcGoods();
        },

        calcGoods: function () {
            let total = 0;
            let feeR = 0; let feeDc = 0;

            document.querySelectorAll('#cart-items tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const sub = qty * price;

                row.querySelector('.item-total').value = sub.toLocaleString('id-ID');
                total += sub;

                const sel = row.querySelector('.item-select');
                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.value) {
                    const feeAmt = parseFloat(opt.getAttribute('data-fee')) || 0;
                    const feeCode = opt.getAttribute('data-feecode');
                    if (feeCode === 'R') feeR += (feeAmt * qty);
                    if (feeCode === 'Dc') feeDc += (feeAmt * qty);
                }
            });

            const display = document.getElementById('total-goods');
            display.innerText = "Rp " + total.toLocaleString('id-ID');
            display.dataset.val = total;
            display.dataset.feeR = feeR;
            display.dataset.feeDc = feeDc;
            this.calcGrandTotal();
        },

        calcInstall: function () {
            const qty = parseFloat(document.getElementById('inp-install-qty').value) || 0;
            const price = parseFloat(document.getElementById('inp-install-price').value) || 0;
            const total = qty * price;
            const display = document.getElementById('inp-install-total');
            display.value = "Rp " + total.toLocaleString('id-ID');
            display.dataset.val = total;
            this.calcGrandTotal();
        },

        calcGrandTotal: function () {
            const goods = parseFloat(document.getElementById('total-goods').dataset.val) || 0;
            const installVal = document.getElementById('inp-install-total').dataset.val || 0;
            // Logic fix: Jika checkbox unchecked, abaikan nilai install meskipun ada angka di input
            const hasInstall = document.getElementById('toggle-install').checked;
            const install = hasInstall ? parseFloat(installVal) : 0;

            const grand = goods + install;
            document.getElementById('grand-total').innerText = "Rp " + grand.toLocaleString('id-ID');
            return { goods, install, grand };
        },

        updateOrder: async function (e) {
            e.preventDefault();
            const totals = this.calcGrandTotal();
            const hasInstall = document.getElementById('toggle-install').checked;

            const items = [];
            const rows = document.querySelectorAll('#cart-items tr');
            for (let row of rows) {
                const sel = row.querySelector('.item-select');
                const pid = sel.value;
                if (pid) {
                    const opt = sel.options[sel.selectedIndex];
                    items.push({
                        product_id: pid,
                        name: opt.text,
                        qty: row.querySelector('.item-qty').value,
                        price: row.querySelector('.item-price').value,
                        sub: parseFloat(row.querySelector('.item-qty').value) * parseFloat(row.querySelector('.item-price').value),
                        unit: opt.getAttribute('data-unit')
                    });
                }
            }

            if (items.length === 0) return Swal.fire('Error', "Pilih minimal 1 produk", 'error');

            const payload = {
                order_id: document.getElementById('order-id').value,
                marketing_username: this.username,
                ki_id: (hasInstall && document.getElementById('ki-display').innerText !== 'Buat Baru (Otomatis)')
                    ? document.getElementById('ki-display').innerText
                    : (hasInstall ? 'NEW' : '-'), // Flag logic handled in backend
                customer: document.getElementById('inp-cust').value,
                phone: document.getElementById('inp-phone').value,
                address: document.getElementById('inp-addr').value,
                maps: document.getElementById('inp-maps').value,
                wh: document.getElementById('inp-wh').value,
                date_send: document.getElementById('inp-date-send').value,
                pay_status: document.getElementById('inp-pay').value,
                items: items,
                install_info: hasInstall ? {
                    mandor: document.getElementById('inp-mandor').value,
                    date: document.getElementById('inp-date-install').value,
                    qty: document.getElementById('inp-install-qty').value,
                    price: document.getElementById('inp-install-price').value,
                    total: totals.install
                } : null,
                totals: totals,
                fees: {
                    r: document.getElementById('total-goods').dataset.feeR,
                    dc: document.getElementById('total-goods').dataset.feeDc
                }
            };

            try {
                const res = await fetch('api/update_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: result.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ke Riwayat',
                        cancelButtonText: 'Tetap di sini'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            window.location.href = 'index.php?page=my_orders';
                        }
                    });
                } else {
                    Swal.fire('Gagal!', result.message, 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error!', 'Terjadi kesalahan koneksi server.', 'error');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => app.init());
</script>