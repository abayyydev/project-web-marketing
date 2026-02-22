<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Master Data Produk & Fee</h1>
        <button onclick="openModal('add')"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>

    <!-- Tabel Produk -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-600 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Kode</th>
                        <th class="px-6 py-3 text-left">Nama Produk</th>
                        <th class="px-6 py-3 text-left">Tipe</th>
                        <th class="px-6 py-3 text-right">Harga Dasar</th>
                        <th class="px-6 py-3 text-right text-blue-600">Fee (Rp)</th>
                        <th class="px-6 py-3 text-center">Kode Fee</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="product-rows" class="bg-white divide-y divide-gray-200">
                    <!-- Data Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL FORM PRODUK -->
<div id="modal-product" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tambah Produk</h3>
                        <div class="mt-4 space-y-3">
                            <input type="hidden" id="p-id">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Kode Produk</label>
                                    <input type="text" id="p-code"
                                        class="w-full border rounded p-2 focus:ring-green-500" placeholder="P01">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Tipe</label>
                                    <select id="p-type" class="w-full border rounded p-2 bg-white">
                                        <option value="goods">Barang Fisik</option>
                                        <option value="service">Jasa</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Nama Produk</label>
                                <input type="text" id="p-name" class="w-full border rounded p-2 focus:ring-green-500"
                                    placeholder="Contoh: Rumput Gajah">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Satuan</label>
                                    <input type="text" id="p-unit" class="w-full border rounded p-2"
                                        placeholder="m2 / sak">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Harga Dasar</label>
                                    <input type="number" id="p-price" class="w-full border rounded p-2" placeholder="0">
                                </div>
                            </div>
                            <div class="p-3 bg-blue-50 rounded border border-blue-100 grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-blue-600 uppercase">Nominal Fee</label>
                                    <input type="number" id="p-fee" class="w-full border border-blue-300 rounded p-2"
                                        placeholder="10500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-blue-600 uppercase">Kode Fee</label>
                                    <input type="text" id="p-feecode" class="w-full border border-blue-300 rounded p-2"
                                        placeholder="R">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="saveProduct()"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Simpan
                </button>
                <button type="button" onclick="closeModal()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentMode = 'add';

    document.addEventListener('DOMContentLoaded', loadProducts);

    async function loadProducts() {
        const tbody = document.getElementById('product-rows');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-400">Loading...</td></tr>';

        try {
            const res = await fetch('api/get_products.php');
            const json = await res.json();

            if (json.status === 'success') {
                tbody.innerHTML = '';
                json.data.forEach(p => {
                    let typeBadge = p.type === 'service'
                        ? '<span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs">Jasa</span>'
                        : '<span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs">Barang</span>';

                    tbody.innerHTML += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono font-bold text-gray-500">${p.code || '-'}</td>
                        <td class="px-6 py-4 font-bold text-gray-800">${p.name} <span class="text-xs text-gray-400 font-normal">(${p.unit})</span></td>
                        <td class="px-6 py-4 text-sm">${typeBadge}</td>
                        <td class="px-6 py-4 text-right text-sm">Rp ${parseInt(p.base_price).toLocaleString()}</td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-blue-600">Rp ${parseInt(p.fee_amount).toLocaleString()}</td>
                        <td class="px-6 py-4 text-center font-mono text-xs">${p.fee_code || '-'}</td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <button onclick='editProduct(${JSON.stringify(p)})' class="text-blue-500 hover:bg-blue-50 p-1.5 rounded"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteProduct(${p.id}, '${p.name}')" class="text-red-500 hover:bg-red-50 p-1.5 rounded"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                });
            }
        } catch (e) { console.error(e); }
    }

    function openModal(mode) {
        currentMode = mode;
        document.getElementById('modal-title').innerText = mode === 'add' ? 'Tambah Produk Baru' : 'Edit Produk';
        document.getElementById('modal-product').classList.remove('hidden');

        if (mode === 'add') {
            // Reset Form
            document.getElementById('p-id').value = '';
            document.querySelectorAll('#modal-product input').forEach(i => i.value = '');
            document.getElementById('p-type').value = 'goods';
        }
    }

    function closeModal() {
        document.getElementById('modal-product').classList.add('hidden');
    }

    function editProduct(p) {
        openModal('edit');
        document.getElementById('p-id').value = p.id;
        document.getElementById('p-code').value = p.code;
        document.getElementById('p-name').value = p.name;
        document.getElementById('p-unit').value = p.unit;
        document.getElementById('p-price').value = p.base_price;
        document.getElementById('p-fee').value = p.fee_amount;
        document.getElementById('p-feecode').value = p.fee_code;
        document.getElementById('p-type').value = p.type;
    }

    async function saveProduct() {
        const payload = {
            action: currentMode,
            id: document.getElementById('p-id').value,
            code: document.getElementById('p-code').value,
            name: document.getElementById('p-name').value,
            unit: document.getElementById('p-unit').value,
            price: document.getElementById('p-price').value,
            fee: document.getElementById('p-fee').value,
            fee_code: document.getElementById('p-feecode').value,
            type: document.getElementById('p-type').value
        };

        if (!payload.name || !payload.price) return Swal.fire('Error', 'Nama dan Harga wajib diisi!', 'warning');

        try {
            const res = await fetch('api/product_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();

            if (json.status === 'success') {
                closeModal();
                loadProducts();
                Swal.fire('Sukses', json.message, 'success');
            } else {
                Swal.fire('Gagal', json.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Gagal koneksi server', 'error');
        }
    }

    function deleteProduct(id, name) {
        Swal.fire({
            title: 'Hapus Produk?',
            text: `Produk "${name}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetch('api/product_action.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'delete', id: id })
                });
                const json = await res.json();

                if (json.status === 'success') {
                    loadProducts();
                    Swal.fire('Terhapus!', json.message, 'success');
                } else {
                    Swal.fire('Gagal!', json.message, 'error');
                }
            }
        });
    }
</script>