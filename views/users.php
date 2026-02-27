<?php
if(($_SESSION['user']['role'] ?? '') !== 'super_admin') {
    echo "<div class='text-center py-20 text-red-500 font-bold'>Akses Ditolak! Khusus Super Admin.</div>"; return;
}
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Manajemen User</h1>
            <p class="text-sm text-gray-500">Kelola akun karyawan, marketing, dan admin cabang.</p>
        </div>
        <button onclick="openModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-lg font-bold shadow transition flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Tambah User Baru
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-md border-t-4 border-purple-600 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50 text-purple-800 text-[11px] uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Nama Lengkap</th>
                        <th class="px-6 py-3 text-left">Username (Login)</th>
                        <th class="px-6 py-3 text-left">Role / Jabatan</th>
                        <th class="px-6 py-3 text-left">Cabang Penugasan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-rows" class="divide-y divide-gray-100 text-sm font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT USER -->
<div id="modal-user" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-purple-900" id="modal-title">Tambah User</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="u-id">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="u-name" class="w-full border rounded-lg p-2.5 outline-none focus:ring-purple-500" placeholder="John Doe">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Username Login</label>
                <input type="text" id="u-username" class="w-full border rounded-lg p-2.5 outline-none focus:ring-purple-500 font-mono" placeholder="johndoe">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                <input type="password" id="u-password" class="w-full border rounded-lg p-2.5 outline-none focus:ring-purple-500" placeholder="Biarkan kosong jika tidak diubah (untuk edit)">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Role / Jabatan</label>
                <select id="u-role" onchange="checkRole()" class="w-full border rounded-lg p-2.5 outline-none focus:ring-purple-500 font-bold text-purple-700 bg-purple-50">
                    <option value="marketing">Marketing (Sales)</option>
                    <option value="admin_gudang">Admin Gudang (Cabang)</option>
                    <option value="keuangan">Admin Keuangan (Pusat)</option>
                    <option value="super_admin">Super Admin (Pemilik)</option>
                </select>
            </div>
            <!-- MUNCUL JIKA PILIH ADMIN GUDANG -->
            <div id="box-warehouse" class="hidden bg-orange-50 p-3 rounded border border-orange-200">
                <label class="block text-xs font-bold text-orange-800 uppercase mb-1">Tugaskan di Cabang Mana?</label>
                <select id="u-warehouse" class="w-full border rounded-lg p-2.5 outline-none focus:ring-orange-500">
                    <option value="">-- Pilih Cabang --</option>
                </select>
                <p class="text-[10px] text-gray-500 mt-1">Admin ini hanya bisa melihat data milik cabang ini.</p>
            </div>

            <button onclick="saveUser()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-lg shadow mt-2 transition active:scale-95">
                <i class="fas fa-save mr-2"></i> Simpan Data User
            </button>
        </div>
    </div>
</div>

<script>
let allUsers = [];

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    loadCabang();
});

async function loadCabang() {
    try {
        const res = await fetch('api/warehouse_action.php?action=get');
        const json = await res.json();
        if(json.status === 'success') {
            const sel = document.getElementById('u-warehouse');
            json.data.forEach(w => sel.innerHTML += `<option value="${w.name}">${w.name}</option>`);
        }
    } catch(e) {}
}

async function loadUsers() {
    try {
        const res = await fetch('api/user_action.php?action=get');
        const json = await res.json();
        if(json.status === 'success') {
            allUsers = json.data;
            renderTable();
        }
    } catch(e) { console.error(e); }
}

function renderTable() {
    const tbody = document.getElementById('user-rows');
    tbody.innerHTML = '';
    
    allUsers.forEach(u => {
        let badgeClass = '';
        let roleName = u.role.replace('_', ' ').toUpperCase();
        
        if (u.role === 'super_admin') badgeClass = 'bg-red-100 text-red-800 border-red-200';
        else if (u.role === 'keuangan') badgeClass = 'bg-green-100 text-green-800 border-green-200';
        else if (u.role === 'admin_gudang') badgeClass = 'bg-orange-100 text-orange-800 border-orange-200';
        else badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';

        let whText = (u.role === 'admin_gudang' && u.warehouse_name) ? `<span class="font-bold text-orange-700"><i class="fas fa-map-marker-alt mr-1"></i>${u.warehouse_name}</span>` : '<span class="text-gray-400 text-xs">Pusat / Nasional</span>';

        tbody.innerHTML += `
            <tr class="hover:bg-purple-50/30 transition border-b border-gray-50">
                <td class="px-6 py-4 text-gray-900 font-bold uppercase">${u.full_name}</td>
                <td class="px-6 py-4 text-gray-600 font-mono text-xs">${u.username}</td>
                <td class="px-6 py-4"><span class="px-2 py-1 text-[10px] font-bold rounded border ${badgeClass}">${roleName}</span></td>
                <td class="px-6 py-4 text-xs">${whText}</td>
                <td class="px-6 py-4 text-center">
                    <button onclick="openModal(${u.id})" class="text-blue-500 hover:bg-blue-50 p-2 rounded transition"><i class="fas fa-edit"></i> Edit</button>
                    ${u.username !== 'admin' ? `<button onclick="deleteUser(${u.id})" class="text-red-500 hover:bg-red-50 p-2 rounded transition ml-2"><i class="fas fa-trash"></i> Hapus</button>` : ''}
                </td>
            </tr>
        `;
    });
}

function checkRole() {
    const role = document.getElementById('u-role').value;
    document.getElementById('box-warehouse').classList.toggle('hidden', role !== 'admin_gudang');
}

function openModal(id = null) {
    document.getElementById('modal-title').innerText = id ? 'Edit User' : 'Tambah User Baru';
    let u = id ? allUsers.find(x => x.id === id) : null;
    
    document.getElementById('u-id').value = id || '';
    document.getElementById('u-name').value = u ? u.full_name : '';
    document.getElementById('u-username').value = u ? u.username : '';
    document.getElementById('u-password').value = '';
    document.getElementById('u-role').value = u ? u.role : 'marketing';
    document.getElementById('u-warehouse').value = u ? (u.warehouse_name || '') : '';
    
    if(u && u.username === 'admin') document.getElementById('u-role').disabled = true; // Protect root admin
    else document.getElementById('u-role').disabled = false;

    checkRole();
    document.getElementById('modal-user').classList.remove('hidden');
}

function closeModal() { document.getElementById('modal-user').classList.add('hidden'); }

async function saveUser() {
    const role = document.getElementById('u-role').value;
    const wh = document.getElementById('u-warehouse').value;

    if (role === 'admin_gudang' && !wh) return Swal.fire('Error', 'Admin Gudang wajib memilih Cabang Penugasan!', 'warning');

    let payload = {
        action: document.getElementById('u-id').value ? 'edit' : 'add',
        id: document.getElementById('u-id').value,
        full_name: document.getElementById('u-name').value,
        username: document.getElementById('u-username').value,
        password: document.getElementById('u-password').value,
        role: role,
        warehouse_name: role === 'admin_gudang' ? wh : null // Hapus data wh jika bukan admin gudang
    };

    if(!payload.id && !payload.password) return Swal.fire('Error', 'Password wajib diisi untuk user baru', 'error');

    Swal.fire({title: 'Menyimpan...', allowOutsideClick: false}); Swal.showLoading();

    try {
        const res = await fetch('api/user_action.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
        const d = await res.json();
        if(d.status === 'success') { closeModal(); loadUsers(); Swal.fire('Tersimpan', d.message, 'success'); }
        else { Swal.fire('Error', d.message, 'error'); }
    } catch(e) { Swal.fire('Error', 'Koneksi gagal', 'error'); }
}

function deleteUser(id) {
    Swal.fire({title: 'Hapus User?', text: 'Akun ini tidak akan bisa login lagi.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', confirmButtonColor: '#ef4444'})
    .then(r => { 
        if(r.isConfirmed) { 
            fetch('api/user_action.php', {method:'POST', body: JSON.stringify({action:'delete', id:id})})
            .then(res => res.json()).then(data => {
                if(data.status === 'success') loadUsers();
                else Swal.fire('Gagal', data.message, 'error');
            });
        } 
    });
}
</script>