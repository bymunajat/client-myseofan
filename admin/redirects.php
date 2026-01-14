<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle Actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source = $_POST['source_url'] ?? '';
    $target = $_POST['target_url'] ?? '';
    $type = (int) ($_POST['redirect_type'] ?? 301);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO redirects (source_url, target_url, redirect_type, is_active) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$source, $target, $type, $active])) {
            header('Location: redirects.php?msg=added');
            exit;
        }
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE redirects SET source_url = ?, target_url = ?, redirect_type = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$source, $target, $type, $active, $id])) {
            header('Location: redirects.php?msg=updated');
            exit;
        }
    }
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM redirects WHERE id = ?")->execute([$id]);
    header('Location: redirects.php?msg=deleted');
    exit;
}

// Fetch Data
$redirects = $pdo->query("SELECT * FROM redirects ORDER BY created_at DESC")->fetchAll();
$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM redirects WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Redirects Manager - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
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
    <main class="flex-1 min-h-screen pb-20 bg-[#0f172a]">
        <header
            class="bg-[#1e293b] border-b-4 border-fuchsia-500/50 px-8 h-20 flex items-center justify-between shadow-lg shadow-black/20 sticky top-0 z-10">
            <div>
                <h1 class="text-xl font-bold text-white">Redirects Manager</h1>
                <p class="text-xs text-gray-400">Manage 301/302 URL redirects</p>
            </div>
            <a href="?action=add"
                class="bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:shadow-lg hover:shadow-fuchsia-500/40 transition-all shadow-md shadow-fuchsia-900/20">
                + New Redirect
            </a>
        </header>

        <div class="p-8">
            <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="max-w-2xl bg-white p-8 rounded-3xl border border-gray-100 shadow-sm mx-auto">
                    <h2 class="text-2xl font-bold mb-6">
                        <?php echo $action === 'add' ? 'Add New Redirect' : 'Edit Redirect'; ?>
                    </h2>
                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Source URL (e.g.
                                /old-page)</label>
                            <input type="text" name="source_url"
                                value="<?php echo htmlspecialchars($editData['source_url'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Target URL (e.g.
                                /new-page)</label>
                            <input type="text" name="target_url"
                                value="<?php echo htmlspecialchars($editData['target_url'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Redirect Type</label>
                                <select name="redirect_type"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black text-sm">
                                    <option value="301" <?php echo ($editData['redirect_type'] ?? 301) == 301 ? 'selected' : ''; ?>>301 (Permanent)</option>
                                    <option value="302" <?php echo ($editData['redirect_type'] ?? 301) == 302 ? 'selected' : ''; ?>>302 (Temporary)</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-3 pt-8">
                                <input type="checkbox" name="is_active" id="is_active" <?php echo ($editData['is_active'] ?? 1) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-gray-300 text-fuchsia-600
                            focus:ring-fuchsia-500">
                                <label for="is_active" class="text-sm font-bold text-gray-700">Is Active</label>
                            </div>
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="submit"
                                class="flex-1 bg-gray-900 text-white py-3 rounded-xl font-bold hover:bg-black transition-all">Save
                                Redirect</button>
                            <a href="redirects.php"
                                class="px-8 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition-all text-center">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-black uppercase text-black">Source</th>
                                <th class="px-6 py-4 text-xs font-black uppercase text-black">Target</th>
                                <th class="px-6 py-4 text-xs font-black uppercase text-black">Type</th>
                                <th class="px-6 py-4 text-xs font-black uppercase text-black">Status</th>
                                <th class="px-6 py-4 text-xs font-black uppercase text-black text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($redirects as $r): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 font-mono text-sm font-bold text-gray-900">
                                        <?php echo htmlspecialchars($r['source_url']); ?>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-sm font-bold text-gray-900">
                                        <?php echo htmlspecialchars($r['target_url']); ?>
                                    </td>
                                    <td class="px-6 py-4"><span class="px-2 py-1 bg-gray-100 rounded text-[10px] font-black">
                                            <?php echo $r['redirect_type']; ?>
                                        </span></td>
                                    <td class="px-6 py-4">
                                        <?php if ($r['is_active']): ?>
                                            <span class="w-2 h-2 rounded-full bg-fuchsia-500 inline-block mr-2"></span>
                                            <span class="text-[10px] font-black text-fuchsia-700 uppercase">Active</span>
                                        <?php else: ?>
                                            <span class="w-2 h-2 rounded-full bg-gray-300 inline-block mr-2"></span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="?action=edit&id=<?php echo $r['id']; ?>"
                                                class="px-3 py-1.5 hover:bg-fuchsia-50 text-fuchsia-700 font-bold rounded-lg transition-all border border-transparent hover:border-fuchsia-100">Edit</a>
                                            <a href="javascript:void(0);"
                                                onclick="confirmDelete('?action=delete&id=<?php echo $r['id']; ?>', 'This redirect rule will be removed.')"
                                                class="px-3 py-1.5 hover:bg-red-50 text-red-600 font-bold rounded-lg transition-all border border-transparent hover:border-red-100">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($redirects)): ?>
                                <tr>
                                    <td colspan="5"
                                        class="px-6 py-20 text-center text-gray-400 font-bold uppercase tracking-widest">No
                                        redirects found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>