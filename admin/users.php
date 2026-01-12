<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle CRUD Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'author';

    if ($action === 'add') {
        try {
            if (empty($user) || empty($pass)) {
                throw new Exception("Username and Password are required.");
            }
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->execute([$user, $hash, $role]);
            $message = "User created successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    if ($id == $_SESSION['admin_id']) {
        $error = "You cannot delete yourself.";
    } else {
        $pdo->prepare("DELETE FROM admins WHERE id=?")->execute([$id]);
        $message = "User deleted.";
    }
    $action = 'list';
}

// Fetch Data
$users = [];
if ($action === 'list') {
    $users = $pdo->query("SELECT id, username, role, created_at FROM admins ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Users - MySeoFan Admin</title>
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
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">Super Admin Mode</span>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-2xl border border-red-100">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-lg font-bold text-gray-700">Administrator Accounts</h3>
                    <a href="?action=add"
                        class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-emerald-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New User
                    </a>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-6 py-4 text-sm font-bold text-gray-600">Username</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-600">Role</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?php
                                        echo $u['role'] === 'super_admin' ? 'bg-purple-50 text-purple-600' : ($u['role'] === 'editor' ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-600');
                                        ?> uppercase">
                                            <?php echo $u['role']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="?action=delete&id=<?php echo $u['id']; ?>"
                                            onclick="return confirm('Silahkan konfirmasi penghapusan user ini?')"
                                            class="text-red-400 hover:text-red-600 transition-colors">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($action === 'add'): ?>
                <div class="max-w-2xl bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-8">Add New Administrator</h3>
                    <form action="?action=add" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 focus:border-emerald-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 focus:border-emerald-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Role Permissions</label>
                            <select name="role"
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 focus:border-emerald-500 outline-none transition-all">
                                <option value="super_admin">Super Admin (Full Access)</option>
                                <option value="editor">Editor (Blog, Pages, Media, Translations)</option>
                                <option value="author">Author (Blog Posts Only)</option>
                            </select>
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="submit"
                                class="bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-emerald-700 transition-all">Save
                                Account</button>
                            <a href="?action=list"
                                class="bg-gray-100 text-gray-600 px-8 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>