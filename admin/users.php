<?php
/**
 * User Management - REBUILT VERSION 2.1
 * Objective: High-end card UI with robust defaults.
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/Logger.php';

// 1. Security Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Ensure super_admin
try {
    $checkStmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
    $checkStmt->execute([$_SESSION['admin_id']]);
    $currentRole = $checkStmt->fetchColumn();
    $_SESSION['role'] = $currentRole ?: 'author';
} catch (Exception $e) {
    $currentRole = $_SESSION['role'] ?? 'author';
}

if ($currentRole !== 'super_admin') {
    header('Location: dashboard.php');
    exit;
}

// 2. Variables & Actions
$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
if (empty($action))
    $action = 'list';
$id = $_GET['id'] ?? null;

// 3. Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'author';

    if ($action === 'add') {
        try {
            if (empty($username) || empty($password))
                throw new Exception("Username and password are required.");
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)")->execute([$username, $hash, $role]);
            Logger::log('create_user', "Created new admin user: $username (Role: $role)", $_SESSION['admin_id'] ?? 0);
            $message = "User '{$username}' successfully created.";
            $action = 'list';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            if (empty($username))
                throw new Exception("Username cannot be empty.");
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE admins SET username = ?, password_hash = ?, role = ? WHERE id = ?")->execute([$username, $hash, $role, $id]);
            } else {
                $pdo->prepare("UPDATE admins SET username = ?, role = ? WHERE id = ?")->execute([$username, $role, $id]);
            }
            Logger::log('update_user', "Updated user ID: $id ($username)", $_SESSION['admin_id'] ?? 0);
            $message = "Account updated successfully.";
            $action = 'list';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Delete Logic
if ($action === 'delete' && $id) {
    if ($id == $_SESSION['admin_id']) {
        $error = "Security Alert: You cannot delete your own account.";
    } else {
        try {
            $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
            Logger::log('delete_user', "Deleted user ID: $id", $_SESSION['admin_id'] ?? 0);
            $message = "Account permanently deleted.";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    $action = 'list';
}

// 4. Fetch Data
$users = [];
$editData = null;

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT id, username, role FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    if (!$editData)
        $action = 'list';
}

// Always fetch users if we might show the list
if ($action === 'list') {
    try {
        $users = $pdo->query("SELECT * FROM admins ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $users = $pdo->query("SELECT id, username, role FROM admins")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Management - MySeoFan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
        }
    </style>
</head>

<body class="flex">

    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen bg-[#0f172a]">
        <header
            class="bg-[#1e293b] border-b-4 border-fuchsia-500/50 px-8 h-20 flex items-center justify-between shadow-lg shadow-black/20">
            <div>
                <h3 class="text-xl font-bold text-white">Admin Management</h3>
                <p class="text-xs text-gray-400 mt-0.5">Manage system access and roles</p>
            </div>
        </header>

        <div class="p-8">
            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div
                    class="mb-8 p-4 bg-fuchsia-50 text-fuchsia-700 rounded-2xl border border-fuchsia-100 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-8 p-4 bg-red-50 text-red-700 rounded-2xl border border-red-100 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- FORM VIEW -->
                <div class="max-w-xl mx-auto">
                    <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100">
                        <div class="mb-8 text-center">
                            <h3 class="text-2xl font-bold text-gray-800 tracking-tight">
                                <?php echo ($action === 'add' ? 'Create New Admin' : 'Edit Account'); ?>
                            </h3>
                            <p class="text-sm text-gray-400 mt-1">Provide user details and permissions</p>
                        </div>

                        <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST"
                            class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                                <input type="text" name="username" required
                                    value="<?php echo htmlspecialchars($editData['username'] ?? ''); ?>"
                                    class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-fuchsia-500 outline-none transition-all font-bold text-black focus:ring-4 focus:ring-fuchsia-500/10">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"><?php echo ($action === 'add' ? 'Password' : 'New Password (Optional)'); ?></label>
                                <input type="password" name="password" <?php echo ($action === 'add' ? 'required' : ''); ?>
                                    class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-fuchsia-500 outline-none transition-all font-semibold text-black focus:ring-4 focus:ring-fuchsia-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Role Permissions</label>
                                <select name="role"
                                    class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-fuchsia-500 outline-none transition-all appearance-none cursor-pointer font-bold text-black focus:ring-4 focus:ring-fuchsia-500/10">
                                    <option value="super_admin" <?php echo ($editData['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                    <option value="editor" <?php echo ($editData['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                    <option value="author" <?php echo ($editData['role'] ?? '') === 'author' ? 'selected' : ''; ?>>Author</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-4 pt-4">
                                <button type="submit"
                                    class="w-full py-4 bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white rounded-2xl font-bold hover:shadow-lg hover:shadow-fuchsia-500/40 transition-all shadow-md shadow-fuchsia-900/20">
                                    <?php echo ($action === 'add' ? 'Generate Account' : 'Save Changes'); ?>
                                </button>
                                <a href="?action=list"
                                    class="w-full py-4 text-center bg-gray-50 text-gray-400 rounded-2xl font-bold hover:bg-gray-100 transition-all">Go
                                    Back</a>
                            </div>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- LIST VIEW (DEFAULT) -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-lg font-bold text-white uppercase tracking-tighter">System Administrators</h3>
                    </div>
                    <a href="?action=add"
                        class="bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white px-8 py-3.5 rounded-2xl font-bold hover:shadow-xl hover:shadow-fuchsia-500/40 transition-all flex items-center gap-2 shadow-lg shadow-fuchsia-900/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Admin
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php if (empty($users)): ?>
                        <div
                            class="col-span-full py-24 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-gray-100">
                            <p class="text-gray-500 font-bold text-lg">No administrators found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <div
                                class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16 opacity-0 group-hover:opacity-100 transition-opacity">
                                </div>
                                <div class="flex items-start justify-between mb-8 relative">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white flex items-center justify-center font-bold text-2xl shadow-lg shadow-fuchsia-500/30">
                                        <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                    </div>
                                    <span class="px-4 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest <?php
                                    echo $u['role'] === 'super_admin' ? 'bg-purple-50 text-purple-600' : ($u['role'] === 'editor' ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-600');
                                    ?>">
                                        <?php echo str_replace('_', ' ', $u['role']); ?>
                                    </span>
                                </div>
                                <div class="relative mb-8">
                                    <h4 class="text-xl font-bold text-gray-800 mb-1 truncate">
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </h4>
                                    <p class="text-xs text-gray-400">Joined
                                        <?php echo date('d M Y', strtotime($u['created_at'] ?? 'now')); ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 relative border-t border-gray-50 pt-6">
                                    <a href="?action=edit&id=<?php echo $u['id']; ?>"
                                        class="flex-1 text-center py-3.5 text-sm font-bold text-fuchsia-700 bg-fuchsia-50 rounded-2xl hover:bg-fuchsia-600 hover:text-white transition-all duration-300">Edit</a>
                                    <a href="javascript:void(0);"
                                        onclick="confirmDelete('?action=delete&id=<?php echo $u['id']; ?>', 'This administrator account will be permanently deactivated.')"
                                        class="px-4 py-3.5 text-red-100 hover:text-white hover:bg-red-500 rounded-2xl transition-all duration-300 border border-red-50 bg-red-50/50">
                                        <svg class="w-5 h-5 text-red-400 group-hover:text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>