<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT Sigma Media</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-green-700 to-teal-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <div
                class="bg-green-100 text-green-700 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fas fa-leaf"></i></div>
            <h1 class="text-2xl font-bold text-gray-800">PT Sigma Media Login</h1>
        </div>

        <form id="loginForm" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-600 mb-1">Username</label>
                <input type="text" id="username"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-600 mb-1">Password</label>
                <input type="password" id="password"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 outline-none" required>
            </div>
            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition shadow-lg">Masuk</button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const u = document.getElementById('username').value;
            const p = document.getElementById('password').value;

            try {
                const res = await fetch('api/login_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: u, password: p })
                });
                const result = await res.json();

                if (result.status === 'success') {
                    window.location.href = 'index.php'; // Redirect ke dashboard
                } else {
                    alert(result.message);
                }
            } catch (err) {
                console.error(err);
                alert("Terjadi kesalahan sistem");
            }
        });
    </script>
</body>

</html>