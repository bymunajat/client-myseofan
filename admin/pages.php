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
    $in_header = isset($_POST['show_in_header']) ? 1 : 0;
    $in_footer = isset($_POST['show_in_footer']) ? 1 : 0;
    $order = (int) ($_POST['menu_order'] ?? 0);

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, meta_title, meta_description, translation_group, show_in_header, show_in_footer, menu_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group, $in_header, $in_footer, $order]);
            $message = "Page created successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, lang_code=?, meta_title=?, meta_description=?, translation_group=?, show_in_header=?, show_in_footer=?, menu_order=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group, $in_header, $in_footer, $order, $id]);
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
$cu_p = null;
if ($action === 'list') {
    $pages = $pdo->query("SELECT * FROM pages ORDER BY menu_order ASC, id ASC")->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id=?");
    $stmt->execute([$id]);
    $cu_p = $stmt->fetch();
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
            height: 500,
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
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Order</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Page Title
                                </th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Visibility
                                </th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($pages as $p): ?>
                                <tr>
                                    <td class="px-8 py-5 text-gray-400 font-bold"><?php echo $p['menu_order']; ?></td>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title']); ?></div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter">
                                            /page.php?slug=<?php echo htmlspecialchars($p['slug'] ?? ''); ?></div>
                                    </td>
                                    <td class="px-8 py-5 space-x-2">
                                        <span
                                            class="px-2 py-1 text-[10px] font-black uppercase rounded <?php echo $p['show_in_header'] ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'; ?>">Header</span>
                                        <span
                                            class="px-2 py-1 text-[10px] font-black uppercase rounded <?php echo $p['show_in_footer'] ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'; ?>">Footer</span>
                                        <span
                                            class="px-2 py-1 text-[10px] font-black uppercase rounded bg-purple-100 text-purple-600"><?php echo $p['lang_code']; ?></span>
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
                            <div
                                class="md:col-span-2 p-6 bg-gray-50 rounded-2xl border border-gray-100 flex flex-wrap gap-8">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="show_in_header" value="1" <?php echo ($cu_p['show_in_header'] ?? 0) ? 'checked' : ''; ?>
                                        class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span
                                        class="text-sm font-bold text-gray-700 group-hover:text-emerald-600 transition-colors">Show
                                        in Header</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="show_in_footer" value="1" <?php echo ($cu_p['show_in_footer'] ?? 0) ? 'checked' : ''; ?>
                                        class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span
                                        class="text-sm font-bold text-gray-700 group-hover:text-emerald-600 transition-colors">Show
                                        in Footer</span>
                                </label>
                                <div class="flex items-center gap-3 ml-auto">
                                    <span class="text-sm font-bold text-gray-500">Menu Order:</span>
                                    <input type="number" name="menu_order" value="<?php echo $cu_p['menu_order'] ?? 0; ?>"
                                        class="w-20 px-3 py-1 rounded-lg border border-gray-200 text-sm font-bold outline-none focus:border-emerald-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Translation Group ID</label>
                                <input type="text" name="translation_group"
                                    value="<?php echo htmlspecialchars($cu_p['translation_group'] ?? uniqid('group_', true)); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-500 text-xs outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Language</label>
                                <select name="lang_code"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none">
                                    <option value="en" <?php echo ($cu_p['lang_code'] ?? '') == 'en' ? 'selected' : ''; ?>>
                                        English</option>
                                    <option value="id" <?php echo ($cu_p['lang_code'] ?? '') == 'id' ? 'selected' : ''; ?>>
                                        Indonesia</option>
                                    <option value="es" <?php echo ($cu_p['lang_code'] ?? '') == 'es' ? 'selected' : ''; ?>>
                                        Español</option>
                                    <option value="fr" <?php echo ($cu_p['lang_code'] ?? '') == 'fr' ? 'selected' : ''; ?>>
                                        Français</option>
                                    <option value="de" <?php echo ($cu_p['lang_code'] ?? '') == 'de' ? 'selected' : ''; ?>>
                                        Deutsch</option>
                                    <option value="ja" <?php echo ($cu_p['lang_code'] ?? '') == 'ja' ? 'selected' : ''; ?>>日本語
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Page Title</label>
                                <input type="text" name="title"
                                    value="<?php echo htmlspecialchars($cu_p['title'] ?? ''); ?>" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Slug</label>
                                <input type="text" name="slug" value="<?php echo htmlspecialchars($cu_p['slug'] ?? ''); ?>"
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Content (HTML allowed)</label>
                            <textarea name="content" rows="15"
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none"><?php echo htmlspecialchars($cu_p['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="border-t pt-6">
                            <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4">SEO Settings</h4>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($cu_p['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label>
                                    <textarea name="meta_description" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none"><?php echo htmlspecialchars($cu_p['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-emerald-600 text-white py-4 rounded-xl font-bold hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all">Save
                            Page & Navigation</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>