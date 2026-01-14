<?php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'super_admin';
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Database connection failed. Using hardcoded login check...';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MySeoFan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
        }

        .glass {
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full px-6">
        <div class="text-center mb-10">
            <h1
                class="text-3xl font-black bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 bg-clip-text text-transparent mb-2">
                MySeoFan Admin</h1>
            <p class="text-gray-400 font-medium">Sign in to manage your downloader</p>
        </div>

        <div class="glass p-8 rounded-3xl shadow-xl">
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-medium">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-fuchsia-500/10 focus:border-fuchsia-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-fuchsia-500/10 focus:border-fuchsia-500 outline-none transition-all">
                </div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-fuchsia-500/30 hover:scale-[1.02] transition-all">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center mt-8 text-sm text-gray-500">
            &copy;
            <?php echo date('Y'); ?> MySeoFan Studio
        </p>
    </div>
</body>

</html>