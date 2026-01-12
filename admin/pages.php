<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id']) || !in_array(($_SESSION['role'] ?? ''), ['super_admin', 'editor'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle CRUD Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    if (empty($slug))
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    $content = $_POST['content'] ?? '';
    $lang = $_POST['lang_code'] ?? 'en';
    $m_title = $_POST['meta_title'] ?? '';
    $m_desc = $_POST['meta_description'] ?? '';
    $t_group = $_POST['translation_group'] ?? uniqid('group_', true);

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, meta_title, meta_description, translation_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group]);
            $message = "Page created successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, lang_code=?, meta_title=?, meta_description=?, translation_group=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group, $id]);
            $message = "Page updated successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM pages WHERE id=?")->execute([$id]);
    $message = "Page deleted.";
    $action = 'list';
}

// Fetch Data
$pages = [];
$current_page = null;
if ($action === 'list') {
    $pages = $pdo->query("SELECT * FROM pages ORDER BY id ASC")->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id=?");
    $stmt->execute([$id]);
    $current_page = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Page Manager - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea[name="content"]',
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
            toolbar_mode: 'floating',
            height: 600,
            skin: 'oxide',
            content_css: 'default'
        });
    </script>
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
            <h3 class="text-xl font-bold text-gray-800">Static Page Manager</h3>
            <?php if ($action === 'list'): ?>
                <a href="?action=add"
                    class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition-all">+
                    New Page</a>
            <?php else: ?>
                <a href="?action=list" class="text-gray-500 hover:text-gray-800 font-bold">← Back to List</a>
            <?php endif; ?>
        </header>

        <div class="p-8">
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

            <?php if ($action === 'list'): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Page Title
                                </th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Slug</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($pages as $p): ?>
                                <tr>
                                    <td class="px-8 py-5 font-bold text-gray-800">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </td>
                                    <td class="px-8 py-5 text-gray-500 text-sm">/page.php?slug=
                                        <?php echo htmlspecialchars($p['slug']); ?>
                                    </td>
                                    <td class="px-8 py-5 text-right space-x-2">
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>"
                                            class="text-emerald-600 hover:text-emerald-800 font-bold">Edit</a>
                                        <a href="?action=delete&id=<?php echo $p['id']; ?>" onclick="return confirm('Sure?')"
                                            class="text-red-400 hover:text-red-600 font-bold">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 max-w-4xl mx-auto">
                    <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST"
                        class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Translation Group ID (Advanced)</label>
                                <input type="text" name="translation_group"
                                    value="<?php echo htmlspecialchars($current_page['translation_group'] ?? uniqid('group_', true)); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-500 text-xs focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                <p class="text-[10px] text-gray-400 mt-1">Pages with the same ID are linked as translations of each other.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Page Title</label>
                                <input type="text" name="title"
                                    value="<?php echo htmlspecialchars($current_page['title'] ?? ''); ?>" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Slug</label>
                                <input type="text" name="slug" placeholder="e.g. privacy-policy"
                                    value="<?php echo htmlspecialchars($current_page['slug'] ?? ''); ?>" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Language</label>
                            <select name="lang_code"
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                <option value="en" <?php echo ($current_page['lang_code'] ?? '') == 'en' ? 'selected' : ''; ?>
                                    >English</option>
                                <option value="id" <?php echo ($current_page['lang_code'] ?? '') == 'id' ? 'selected' : ''; ?>
                                    >Indonesia</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Page Content (HTML
                                allowed)</label>
                            <textarea name="content" rows="15" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all"><?php echo htmlspecialchars($current_page['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="border-t pt-6 mt-6">
                            <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4">SEO Settings</h4>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($current_page['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label>
                                    <textarea name="meta_description" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all"><?php echo htmlspecialchars($current_page['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-emerald-600 text-white py-4 rounded-xl font-bold hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all">Save
                            Page</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>