<?php
if(($_SESSION['user']['role'] ?? '') !== 'super_admin') {
    echo "<div class='text-center py-20 text-red-500 font-bold'>Akses Ditolak!</div>"; return;
}
?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Kelola Cabang Gudang</h1>
            <p class="text-sm text-gray-500">Beranda > Database > <span class="text-purple-600 font-bold">Gudang</span></p>
        </div>
        <button onclick="addGudang()" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md transition flex items-center gap-2 text-sm">
            <i class="fas fa-plus"></i> Tambah Cabang
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-md border-t-4 border-purple-600 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4 text-left w-20">ID</th>
                        <th class="px-6 py-4 text-left w-1/4">Nama Cabang</th>
                        <th class="px-6 py-4 text-left">Alamat Gudang</th>
                        <th class="px-6 py-4 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody id="wh-rows" class="divide-y divide-gray-100 text-sm font-medium">
                    <tr><td colspan="4" class="text-center py-8 text-gray-400">Loading data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadWh);

async function loadWh() {
    try {
        const res = await fetch('api/warehouse_action.php?action=get');
        const json = await res.json();
        const tbody = document.getElementById('wh-rows');
        tbody.innerHTML = '';
        
        if (json.status === 'success' && json.data.length > 0) {
            json.data.forEach(w => {
                const address = w.address ? w.address : '<span class="text-gray-400 italic">Belum ada alamat</span>';
                
                // Mencegah error kutip saat mem-passing object JSON ke fungsi onclick
                const whDataStr = JSON.stringify(w).replace(/'/g, "&#39;").replace(/"/g, "&quot;");

                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500 font-mono">#${w.id}</td>
                        <td class="px-6 py-4 text-gray-800 font-bold uppercase">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>${w.name}
                        </td>
                        <td class="px-6 py-4 text-gray-600 text-xs">${address}</td>
                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                            <button onclick='editWh(${whDataStr})' class="text-blue-500 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded transition text-xs font-bold flex items-center justify-center gap-1 w-full">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteWh(${w.id}, '${w.name}')" class="text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded transition text-xs font-bold flex items-center justify-center gap-1 w-full">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-400">Belum ada data cabang. Silakan tambah cabang baru.</td></tr>';
        }
    } catch(e) {
        console.error(e);
        document.getElementById('wh-rows').innerHTML = '<tr><td colspan="4" class="text-center py-8 text-red-500">Gagal memuat database gudang.</td></tr>';
    }
}

function addGudang() {
    Swal.fire({
        title: 'Tambah Cabang Baru',
        html: `
            <div class="text-left space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Cabang / Gudang</label>
                    <input id="swal-input-name" class="w-full border border-gray-300 rounded p-3 focus:border-purple-500 outline-none" placeholder="Contoh: Makassar">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alamat Lengkap</label>
                    <textarea id="swal-input-address" rows="3" class="w-full border border-gray-300 rounded p-3 focus:border-purple-500 outline-none" placeholder="Contoh: Jl. Perintis Kemerdekaan No. 10"></textarea>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan Cabang',
        confirmButtonColor: '#9333ea',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const name = document.getElementById('swal-input-name').value;
            const address = document.getElementById('swal-input-address').value;
            if (!name) {
                Swal.showValidationMessage('Nama cabang tidak boleh kosong!');
                return false;
            }
            return { name: name, address: address };
        }
    }).then((res) => {
        if(res.isConfirmed && res.value) {
            Swal.showLoading();
            fetch('api/warehouse_action.php', { 
                method: 'POST', 
                headers: {'Content-Type':'application/json'}, 
                body: JSON.stringify({action:'add', name: res.value.name, address: res.value.address}) 
            })
            .then(r => r.json())
            .then(d => { 
                if(d.status === 'success') {
                    Swal.fire('Sukses!', d.message, 'success');
                    loadWh(); 
                } else {
                    Swal.fire('Gagal', d.message, 'error'); 
                }
            }).catch(() => Swal.fire('Error', 'Gagal menghubungi server.', 'error'));
        }
    });
}

function editWh(w) {
    Swal.fire({
        title: 'Edit Cabang / Gudang',
        html: `
            <div class="text-left space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Cabang / Gudang</label>
                    <input id="swal-edit-name" class="w-full border border-gray-300 rounded p-3 focus:border-purple-500 outline-none" value="${w.name}">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alamat Lengkap</label>
                    <textarea id="swal-edit-address" rows="3" class="w-full border border-gray-300 rounded p-3 focus:border-purple-500 outline-none">${w.address || ''}</textarea>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan Perubahan',
        confirmButtonColor: '#3b82f6',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const name = document.getElementById('swal-edit-name').value;
            const address = document.getElementById('swal-edit-address').value;
            if (!name) {
                Swal.showValidationMessage('Nama cabang tidak boleh kosong!');
                return false;
            }
            return { id: w.id, name: name, address: address };
        }
    }).then((res) => {
        if(res.isConfirmed && res.value) {
            Swal.showLoading();
            fetch('api/warehouse_action.php', { 
                method: 'POST', 
                headers: {'Content-Type':'application/json'}, 
                body: JSON.stringify({action:'edit', id: res.value.id, name: res.value.name, address: res.value.address}) 
            })
            .then(r => r.json())
            .then(d => { 
                if(d.status === 'success') {
                    Swal.fire('Sukses!', d.message, 'success');
                    loadWh(); 
                } else {
                    Swal.fire('Gagal', d.message, 'error'); 
                }
            }).catch(() => Swal.fire('Error', 'Gagal menghubungi server.', 'error'));
        }
    });
}

function deleteWh(id, name) {
    Swal.fire({
        title: `Hapus Gudang ${name}?`, 
        text: 'PERINGATAN: Seluruh stok barang di gudang ini akan ikut terhapus secara permanen!', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonColor: '#ef4444', 
        confirmButtonText: 'Ya, Hapus Permanen',
        cancelButtonText: 'Batal'
    })
    .then((res) => {
        if(res.isConfirmed) {
            Swal.showLoading();
            fetch('api/warehouse_action.php', { 
                method: 'POST', 
                headers: {'Content-Type':'application/json'}, 
                body: JSON.stringify({action:'delete', id: id}) 
            })
            .then(r => r.json())
            .then(d => {
                if(d.status === 'success') {
                    Swal.fire('Dihapus!', d.message, 'success');
                    loadWh();
                } else {
                    Swal.fire('Gagal!', d.message, 'error');
                }
            });
        }
    });
}
</script>