<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT Sigma Media Asia</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Animasi Latar Belakang (Blobs) */
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
        .swal2-popup { font-family: 'Inter', sans-serif !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Dekorasi Background (Lingkaran Abstrak) -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
    <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>

    <!-- Kotak Utama Login -->
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-4xl flex overflow-hidden z-10 transform transition-all">
        
        <!-- Bagian Kiri: FORM LOGIN -->
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-white">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                   <div class="w-8 h-8 rounded-lg shadow-md overflow-hidden">
                <img src="img/logosigma.png" alt="Sigma ERP Logo" class="w-full h-full object-cover">
            </div>
                    <h1 class="text-2xl font-black text-purple-900 tracking-tight uppercase">Sigma ERP</h1>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang! 👋</h2>
                <p class="text-gray-500 text-sm leading-relaxed">Silakan masukkan username dan password Anda untuk masuk ke sistem perusahaan.</p>
            </div>

            <form id="loginForm" class="space-y-5">
                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" id="username" class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition font-medium bg-gray-50 focus:bg-white shadow-sm" placeholder="Masukkan username" required>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="password" id="password" class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition font-medium bg-gray-50 focus:bg-white shadow-sm" placeholder="••••••••" required>
                    </div>
                </div>
                
                <button type="submit" id="btn-submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl transition transform active:scale-95 shadow-lg shadow-purple-200 flex items-center justify-center gap-2 mt-6">
                    Masuk Sistem <i class="fas fa-arrow-right text-sm"></i>
                </button>
            </form>
            
            <div class="mt-10 text-center text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                &copy; <?= date('Y') ?> PT Sigma Media Asia
            </div>
        </div>

        <!-- Bagian Kanan: GAMBAR ESTETIK (Tersembunyi di layar HP) -->
        <div class="hidden md:block w-1/2 relative bg-purple-900">
            <!-- Gambar dari Unsplash bertema modern office / architectural tech -->
            <img src="https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1567&q=80" alt="Corporate" class="absolute inset-0 w-full h-full object-cover opacity-50 mix-blend-overlay">
            
            <!-- Gradasi Ungu -->
            <div class="absolute inset-0 bg-gradient-to-t from-purple-900 via-purple-900/60 to-purple-800/20"></div>
            
            <div class="absolute bottom-0 left-0 right-0 p-12 text-white">
                <h3 class="text-3xl font-black mb-3 leading-tight tracking-tight">Kelola Bisnis<br>Lebih Cepat & Akurat.</h3>
                <p class="text-purple-200 text-sm leading-relaxed">Sistem ERP terintegrasi untuk manajemen order, stok gudang, dan pengerjaan lapangan secara real-time.</p>
                
                <div class="flex gap-2 mt-6">
                    <div class="w-2 h-2 rounded-full bg-white"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-500/50"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-500/50"></div>
                </div>
            </div>
        </div>
        
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const u = document.getElementById('username').value;
            const p = document.getElementById('password').value;
            
            const btn = document.getElementById('btn-submit');
            const originalText = btn.innerHTML;
            
            // Animasi loading pada tombol
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin text-lg"></i> Memverifikasi...';
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            btn.disabled = true;

            try {
                const res = await fetch('api/login_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: u, password: p })
                });
                const result = await res.json();

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil!',
                        text: 'Mengalihkan ke dashboard...',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: result.message,
                        confirmButtonColor: '#9333ea'
                    });
                    btn.innerHTML = originalText;
                    btn.classList.remove('opacity-80', 'cursor-not-allowed');
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'warning',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat menghubungi server. Periksa koneksi internet Anda.',
                    confirmButtonColor: '#9333ea'
                });
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>