<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Input Pesanan & Instalasi</h1>
    </div>

    <form onsubmit="app.submitOrder(event)" class="space-y-6">
        <!-- 1. DATA PELANGGAN & PESANAN BARANG (KP) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-gray-200 flex justify-between items-center">
                <h2 class="font-bold text-slate-700"><i class="fas fa-user-tag mr-2 text-green-600"></i>Data Pelanggan &
                    Barang</h2>
                <span class="text-xs bg-green-100 px-2 py-1 rounded text-green-700 font-mono font-bold"
                    id="new-kp-id">KP-AUTO</span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Info -->
                <div class="space-y-3">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama
                            Pelanggan</label><input type="text" id="inp-cust" class="input-std" required
                            placeholder="Bpk Pradito"></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. WhatsApp</label><input
                            type="number" id="inp-phone" class="input-std" required placeholder="08xxx"></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alamat
                            Kirim</label><textarea id="inp-addr" rows="2" class="input-std"
                            placeholder="Alamat lengkap..."></textarea></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Link Maps</label><input
                            type="url" id="inp-maps" class="input-std text-blue-600" placeholder="https://maps...">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tgl Kirim
                                Barang</label><input type="date" id="inp-date-send" class="input-std"></div>
                        <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gudang</label><select
                                id="inp-wh" class="input-std bg-white">
                                <option>Rainbow</option>
                                <option>Pusat</option>
                            </select></div>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="bg-yellow-50/50 p-4 rounded-lg border border-yellow-100">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-xs font-bold text-gray-500 uppercase">Rincian Barang (KP)</h3>
                        <span class="text-[10px] text-gray-400 italic">*Harga bisa diedit manual</span>
                    </div>
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
                        class="text-xs bg-white border border-dashed border-gray-300 px-3 py-1.5 rounded text-gray-600 hover:text-green-600 hover:border-green-500 transition w-full text-center font-bold">
                        + Tambah Baris Produk
                    </button>

                    <div class="mt-4 pt-4 border-t border-yellow-200 flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-500">Subtotal Barang:</span>
                        <span class="text-lg font-bold text-gray-800" id="total-goods">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TOGGLE INSTALASI -->
        <div class="flex items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <input type="checkbox" id="toggle-install"
                class="w-6 h-6 text-green-600 rounded focus:ring-green-500 border-gray-300">
            <div>
                <label for="toggle-install" class="font-bold text-gray-800 text-sm md:text-base cursor-pointer">Sertakan
                    Jasa Instalasi?</label>
                <p class="text-xs text-gray-500">Aktifkan untuk membuat Tiket Instalasi (KI).</p>
            </div>
        </div>

        <!-- 3. FORM INSTALASI (KI) -->
        <div id="section-install"
            class="hidden bg-orange-50 rounded-xl shadow-sm border border-orange-200 overflow-hidden">
            <div class="p-4 bg-orange-100 border-b border-orange-200 flex justify-between items-center">
                <h2 class="font-bold text-orange-800"><i class="fas fa-hard-hat mr-2"></i>Form Instalasi (KI)</h2>
                <span
                    class="text-xs bg-white border border-orange-300 px-2 py-1 rounded text-orange-600 font-mono font-bold"
                    id="new-ki-id">KI-AUTO</span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Mandor</label><input
                            type="text" id="inp-mandor" class="input-std bg-white" placeholder="Contoh: Mang Ayi"></div>
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tgl
                            Pengerjaan</label><input type="date" id="inp-date-install" class="input-std bg-white"></div>
                </div>
                <div class="space-y-3">
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Luas (m²)</label>
                            <input type="number" id="inp-install-qty" oninput="app.calcInstall()"
                                class="input-std bg-white" placeholder="0">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga Jasa</label>
                            <input type="number" id="inp-install-price" oninput="app.calcInstall()"
                                class="input-std bg-white" value="35000">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Total Biaya Pasang</label>
                        <input type="text" id="inp-install-total"
                            class="input-std bg-orange-100 font-bold text-orange-800 border-orange-200" readonly
                            value="Rp 0">
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. FOOTER & PEMBAYARAN -->
        <div
            class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Grand Total</label>
                <div class="text-3xl font-bold text-green-700 leading-none" id="grand-total">Rp 0</div>
            </div>
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Pembayaran</label>
                <select id="inp-pay" class="input-std bg-blue-50 border-blue-200 text-blue-800 font-bold">
                    <option>Belum Bayar</option>
                    <option>DP</option>
                    <option>Lunas</option>
                </select>
            </div>
            <div class="w-full md:w-auto flex gap-2">
                <button type="submit"
                    class="px-6 py-3 rounded-lg bg-green-600 hover:bg-green-700 text-white font-bold shadow-lg flex items-center gap-2 transition">
                    <i class="fas fa-save"></i> Simpan Transaksi
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal WA (Sama seperti sebelumnya) -->
<div id="modal-wa" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden mx-4">
        <div class="p-4 border-b bg-green-50 flex justify-between items-center">
            <h3 class="font-bold text-green-800">Format WhatsApp</h3>
            <button onclick="document.getElementById('modal-wa').classList.add('hidden')"
                class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-4">
            <textarea id="wa-content" rows="12"
                class="w-full p-3 border rounded-lg bg-gray-50 font-mono text-xs focus:outline-none text-gray-700"
                readonly></textarea>
        </div>
        <div class="p-4 border-t flex justify-end gap-2 bg-gray-50">
            <button onclick="app.copyToClipboard()"
                class="w-full bg-green-600 text-white font-bold py-2.5 rounded-lg hover:bg-green-700 flex items-center justify-center gap-2 shadow-md">
                <i class="fas fa-copy"></i> Salin Teks
            </button>
        </div>
    </div>
</div>

<script>
    const app = {
        products: [],
        username: "<?= $_SESSION['user']['username'] ?>", // Ambil dari sesi PHP

        init: async function () {
            // 1. Fetch Produk dari API
            try {
                const res = await fetch('api/get_products.php');
                const json = await res.json();
                if (json.status === 'success') {
                    this.products = json.data;
                    this.addCartRow(); // Tambah baris pertama setelah produk loaded
                }
            } catch (e) { console.error("Gagal load produk"); }

            // 2. Set Default Date
            document.getElementById('inp-date-send').addEventListener('change', () => this.generateKP());
            document.getElementById('inp-date-install').addEventListener('change', () => this.generateKI());

            this.generateKP();

            // 3. Toggle Logic
            document.getElementById('toggle-install').addEventListener('change', (e) => {
                const el = document.getElementById('section-install');
                if (e.target.checked) {
                    el.classList.remove('hidden');
                    this.generateKI();
                } else {
                    el.classList.add('hidden');
                    document.getElementById('inp-install-qty').value = '';
                    this.calcInstall();
                }
                this.calcGrandTotal();
            });
        },

        // --- LOGIC SAMA DENGAN PROTOTYPE, TAPI FETCH PRODUK DINAMIS ---

        addCartRow: function () {
            const tbody = document.getElementById('cart-items');
            const rowId = Date.now();

            let options = `<option value="">-- Pilih --</option>`;
            this.products.forEach(p => {
                // Kita simpan ID, Harga, Fee, dan Kode Fee di attribute data
                options += `<option value="${p.id}" data-price="${p.base_price}" data-fee="${p.fee_amount}" data-feecode="${p.fee_code}" data-unit="${p.unit}">${p.name} (${p.unit})</option>`;
            });

            const tr = document.createElement('tr');
            tr.id = `row-${rowId}`;
            tr.innerHTML = `
            <td class="pr-2 pb-2"><select class="w-full px-2 py-1 border rounded text-sm item-select" onchange="app.fillPrice(this)">${options}</select></td>
            <td class="pr-2 pb-2"><input type="number" class="w-full px-2 py-1 border rounded text-sm item-qty" value="1" min="1" oninput="app.calcGoods()"></td>
            <td class="pr-2 pb-2"><input type="number" class="w-full px-2 py-1 border rounded text-sm bg-yellow-50 item-price" oninput="app.calcGoods()"></td>
            <td class="pr-2 pb-2"><input type="text" class="w-full px-2 py-1 border rounded text-sm bg-gray-100 text-right item-total" readonly value="0"></td>
            <td class="pb-2 text-center"><button type="button" onclick="this.closest('tr').remove(); app.calcGoods()" class="text-red-500"><i class="fas fa-trash"></i></button></td>
        `;
            tbody.appendChild(tr);
        },

        fillPrice: function (el) {
            const opt = el.options[el.selectedIndex];
            const price = opt.getAttribute('data-price');
            // Isi input harga dengan harga dasar, tapi bisa diedit
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

                // Hitung Fee (Hidden Logic)
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
            const install = document.getElementById('toggle-install').checked ? (parseFloat(document.getElementById('inp-install-total').dataset.val) || 0) : 0;
            const grand = goods + install;
            document.getElementById('grand-total').innerText = "Rp " + grand.toLocaleString('id-ID');
            return { goods, install, grand };
        },

        generateKP: async function () {
            const dateVal = document.getElementById('inp-date-send').value;
            try {
                const res = await fetch(`api/get_next_id.php?type=kp&date=${dateVal}`);
                const json = await res.json();
                if (json.status === 'success') {
                    document.getElementById('new-kp-id').innerText = json.id;
                }
            } catch (e) { console.error("Gagal generate KP"); }
        },

        generateKI: async function () {
            const dateVal = document.getElementById('inp-date-install').value;
            try {
                const res = await fetch(`api/get_next_id.php?type=ki&date=${dateVal}`);
                const json = await res.json();
                if (json.status === 'success') {
                    document.getElementById('new-ki-id').innerText = json.id;
                }
            } catch (e) { console.error("Gagal generate KI"); }
        },

        // --- SUBMIT KE API ---
        submitOrder: async function (e) {
            e.preventDefault();
            const totals = this.calcGrandTotal();
            const hasInstall = document.getElementById('toggle-install').checked;

            // Kumpulkan Items
            const items = [];
            const rows = document.querySelectorAll('#cart-items tr');
            for (let row of rows) {
                const sel = row.querySelector('.item-select');
                const pid = sel.value;
                if (pid) {
                    const opt = sel.options[sel.selectedIndex];
                    items.push({
                        product_id: pid,
                        name: opt.text, // untuk display WA
                        qty: row.querySelector('.item-qty').value,
                        price: row.querySelector('.item-price').value,
                        sub: parseFloat(row.querySelector('.item-qty').value) * parseFloat(row.querySelector('.item-price').value),
                        unit: opt.getAttribute('data-unit')
                    });
                }
            }

            if (items.length === 0) return alert("Pilih minimal 1 produk");

            const payload = {
                marketing_username: this.username,
                kp_id: document.getElementById('new-kp-id').innerText,
                ki_id: hasInstall ? document.getElementById('new-ki-id').innerText : '-',
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

            // POST KE PHP
            try {
                const res = await fetch('api/save_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.status === 'success') {
                    // SWAL SUKSES
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Pesanan #' + result.order_id + ' telah disimpan.',
                        icon: 'success',
                        confirmButtonText: 'Lihat Format WA',
                        confirmButtonColor: '#10b981'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            this.showWAFormat(payload);
                        }
                    });

                } else {
                    // SWAL ERROR
                    Swal.fire('Gagal!', result.message, 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error!', 'Terjadi kesalahan koneksi server.', 'error');
            }
        },

        showWAFormat: function (data) {
            let itemsTxt = '';
            data.items.forEach((i, idx) => {
                itemsTxt += `${idx + 1}️⃣ ${i.name}\n${i.qty}${i.unit} x ${parseInt(i.price).toLocaleString()}/m² = ${i.sub.toLocaleString()}\n`;
            });

            let installTxt = '';
            if (data.ki_id !== '-') {
                installTxt = `3️⃣ Jasa Pemasangan\n${data.install_info.qty}m² x ${parseInt(data.install_info.price).toLocaleString()} = ${data.install_info.total.toLocaleString()}\n`;
            }

            const format = `*Format Pemesanan*
_Project by ${data.marketing_username}_

*${data.kp_id}* ${data.ki_id !== '-' ? '\n*' + data.ki_id + '*' : ''}

Nama : ${data.customer}
${data.ki_id !== '-' ? 'Nama Mandor : ' + data.install_info.mandor : ''}
No : ${data.phone}
Alamat : ${data.address} ${data.maps}

*Rincian :*
${itemsTxt}${installTxt}
*Total Pembayaran Rp ${data.totals.grand.toLocaleString()}*

Pengiriman : Dari ${data.wh}
Di pick up : ${data.date_send}
Pembayaran : ${data.pay_status}

Fee R : ${parseInt(data.fees.r).toLocaleString()}
Fee Dc : ${parseInt(data.fees.dc).toLocaleString()}`;

            document.getElementById('wa-content').value = format;
            document.getElementById('modal-wa').classList.remove('hidden');
        },

        copyToClipboard: function () {
            document.getElementById('wa-content').select();
            document.execCommand('copy');
            alert("Teks WA tersalin!");
        }
    };

    document.addEventListener('DOMContentLoaded', () => app.init());
</script>