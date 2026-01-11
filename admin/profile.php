<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_user = $_POST['username'] ?? '';
    $new_pass = $_POST['password'] ?? '';

    if (!empty($new_user)) {
        try {
            if (!empty($new_pass)) {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$new_user, $hash, $_SESSION['admin_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                $stmt->execute([$new_user, $_SESSION['admin_id']]);
            }
            $_SESSION['username'] = $new_user;
            $message = "Profile updated successfully!";
        } catch (\Exception $e) {
            $error = "Failed to update profile: " . $e->getMessage();
        }
    } else {
        $error = "Username cannot be empty.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile Settings - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
        }

        .sidebar {
            height: 100vh;
            background: #111827;
            color: white;
        }

        .nav-active {
            background: #374151;
            border-left: 4px solid #10b981;
        }
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Admin Profile</h3>
        </header>

        <div class="p-8">
            <div class="max-w-xl mx-auto">
                <?php if ($message): ?>
                    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <form action="" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                            <input type="text" name="username"
                                value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">New Password (leave blank to
                                keep current)</label>
                            <input type="password" name="password"
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                        </div>
                        <button type="submit"
                            class="w-full py-4 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all">
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>

</html>