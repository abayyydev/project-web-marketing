<div class="max-w-4xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Pengaturan Sistem</h1>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Nomor Urut Transaksi</h2>
        <p class="text-sm text-gray-500 mb-6">
            Atur angka terakhir (Counter). Sistem akan mulai menghitung dari <b>Angka Berikutnya</b>.
            <br>Contoh: Jika diisi <b>5</b>, maka pesanan berikutnya bernomor <b>06</b>.
        </p>

        <form onsubmit="saveSettings(event)" class="space-y-6 max-w-lg">

            <div class="grid grid-cols-2 gap-4 items-center">
                <label class="font-bold text-gray-600">Counter KP (Barang)</label>
                <div class="flex items-center gap-2">
                    <input type="number" id="set-kp"
                        class="w-full border rounded p-2 text-center font-mono font-bold text-blue-600" placeholder="0">
                    <span class="text-xs text-gray-400">Terakhir</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 items-center">
                <label class="font-bold text-gray-600">Counter KI (Instalasi)</label>
                <div class="flex items-center gap-2">
                    <input type="number" id="set-ki"
                        class="w-full border rounded p-2 text-center font-mono font-bold text-orange-600"
                        placeholder="0">
                    <span class="text-xs text-gray-400">Terakhir</span>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-700 transition shadow">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', loadSettings);

    async function loadSettings() {
        // Kita gunakan teknik simple: fetch next id lalu kurangi 1 untuk tau current counter
        // Atau bisa buat API khusus get_settings, tapi biar cepat kita pakai trik ini atau buat API get_settings.php sederhana
        // Agar rapi, mari kita buat API get_next_id me-return 'lastNum' juga? 
        // Ah, lebih baik fetch manual via PHP di file ini atau AJAX.
        // Mari gunakan AJAX ke get_next_id.php lalu kurangi 1 (karena get_next_id mereturn last+1)

        const resKP = await fetch('api/get_next_id.php?type=kp');
        const jsonKP = await resKP.json();
        if (jsonKP.status === 'success') {
            document.getElementById('set-kp').value = jsonKP.seq - 1;
        }

        const resKI = await fetch('api/get_next_id.php?type=ki');
        const jsonKI = await resKI.json();
        if (jsonKI.status === 'success') {
            document.getElementById('set-ki').value = jsonKI.seq - 1;
        }
    }

    async function saveSettings(e) {
        e.preventDefault();
        const kp = document.getElementById('set-kp').value;
        const ki = document.getElementById('set-ki').value;

        try {
            const res = await fetch('api/setting_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ kp_counter: kp, ki_counter: ki })
            });
            const json = await res.json();
            if (json.status === 'success') {
                Swal.fire('Berhasil', 'Nomor urut diperbarui', 'success');
            } else {
                Swal.fire('Gagal', json.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal koneksi', 'error');
        }
    }
</script>