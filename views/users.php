<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
        <button onclick="openModal('add')"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition">
            <i class="fas fa-user-plus"></i> Tambah User
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-600 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Username</th>
                        <th class="px-6 py-3 text-left">Nama Lengkap</th>
                        <th class="px-6 py-3 text-left">Role</th>
                        <th class="px-6 py-3 text-center">Terdaftar</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-rows" class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL FORM USER -->
<div id="modal-user"
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden">
        <div class="p-4 border-b bg-gray-50">
            <h3 class="font-bold text-gray-800" id="modal-title">Tambah User</h3>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="u-id">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Username</label>
                <input type="text" id="u-username"
                    class="w-full border rounded p-2.5 bg-gray-50 focus:bg-white focus:ring-blue-500 transition"
                    placeholder="Contoh: marketing2">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="u-fullname" class="w-full border rounded p-2.5 focus:ring-blue-500 transition"
                    placeholder="Nama Staff">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Role</label>
                <select id="u-role" class="w-full border rounded p-2.5 bg-white focus:ring-blue-500">
                    <option value="marketing">Marketing</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                <input type="text" id="u-password" class="w-full border rounded p-2.5 focus:ring-blue-500 transition"
                    placeholder="Isi untuk ubah password">
                <p class="text-[10px] text-gray-400 mt-1 italic" id="pass-hint">*Biarkan kosong jika tidak ingin
                    mengganti password</p>
            </div>
        </div>
        <div class="p-4 bg-gray-50 border-t flex justify-end gap-2">
            <button onclick="closeModal()"
                class="px-4 py-2 rounded text-gray-600 hover:bg-gray-200 font-medium">Batal</button>
            <button onclick="saveUser()"
                class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 font-bold shadow">Simpan</button>
        </div>
    </div>
</div>

<script>
    let currentMode = 'add';

    document.addEventListener('DOMContentLoaded', loadUsers);

    async function loadUsers() {
        const tbody = document.getElementById('user-rows');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-400">Loading...</td></tr>';

        try {
            const res = await fetch('api/get_users.php');
            const json = await res.json();

            if (json.status === 'success') {
                tbody.innerHTML = '';
                json.data.forEach(u => {
                    let roleBadge = u.role === 'admin'
                        ? '<span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">Admin</span>'
                        : '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Marketing</span>';

                    let date = new Date(u.created_at).toLocaleDateString('id-ID');

                    tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-sm font-bold text-gray-600">${u.username}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">${u.full_name}</td>
                        <td class="px-6 py-4">${roleBadge}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">${date}</td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <button onclick='editUser(${JSON.stringify(u)})' class="text-blue-500 hover:bg-blue-50 p-2 rounded"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteUser(${u.id}, '${u.username}')" class="text-red-500 hover:bg-red-50 p-2 rounded"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                });
            }
        } catch (e) { console.error(e); }
    }

    function openModal(mode) {
        currentMode = mode;
        document.getElementById('modal-title').innerText = mode === 'add' ? 'Tambah User Baru' : 'Edit User';
        document.getElementById('modal-user').classList.remove('hidden');

        if (mode === 'add') {
            document.getElementById('u-id').value = '';
            document.getElementById('u-username').value = '';
            document.getElementById('u-username').disabled = false;
            document.getElementById('u-fullname').value = '';
            document.getElementById('u-password').value = '';
            document.getElementById('pass-hint').classList.add('hidden');
        }
    }

    function closeModal() {
        document.getElementById('modal-user').classList.add('hidden');
    }

    function editUser(u) {
        openModal('edit');
        document.getElementById('u-id').value = u.id;
        document.getElementById('u-username').value = u.username;
        document.getElementById('u-username').disabled = true; // Username tidak boleh diganti
        document.getElementById('u-fullname').value = u.full_name;
        document.getElementById('u-role').value = u.role;
        document.getElementById('u-password').value = ''; // Reset field password
        document.getElementById('pass-hint').classList.remove('hidden');
    }

    async function saveUser() {
        const payload = {
            action: currentMode,
            id: document.getElementById('u-id').value,
            username: document.getElementById('u-username').value,
            fullname: document.getElementById('u-fullname').value,
            role: document.getElementById('u-role').value,
            password: document.getElementById('u-password').value
        };

        if (currentMode === 'add' && !payload.password) return Swal.fire('Error', 'Password wajib diisi untuk user baru', 'warning');
        if (!payload.username || !payload.fullname) return Swal.fire('Error', 'Data tidak lengkap', 'warning');

        try {
            const res = await fetch('api/user_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();

            if (json.status === 'success') {
                closeModal();
                loadUsers();
                Swal.fire('Sukses', json.message, 'success');
            } else {
                Swal.fire('Gagal', json.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Gagal koneksi server', 'error');
        }
    }

    function deleteUser(id, name) {
        Swal.fire({
            title: 'Hapus User?',
            text: `Akun "${name}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetch('api/user_action.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'delete', id: id })
                });
                const json = await res.json();

                if (json.status === 'success') {
                    loadUsers();
                    Swal.fire('Terhapus!', json.message, 'success');
                } else {
                    Swal.fire('Gagal!', json.message, 'error');
                }
            }
        });
    }
</script>