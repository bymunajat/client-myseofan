<?php
session_start();
require_once '../includes/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id']) || !in_array(($_SESSION['role'] ?? ''), ['super_admin', 'editor'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$_curr_lang = 'en';
$_SESSION['last_page_lang'] = 'en';

$_curr_lang = 'en';
$_SESSION['last_page_lang'] = 'en';

// AJAX Reorder Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder') {
    $order = $_POST['order'] ?? [];
    try {
        $pdo->beginTransaction();
        foreach ($order as $index => $id) {
            $stmt = $pdo->prepare("UPDATE pages SET menu_order = ? WHERE id = ?");
            $stmt->execute([$index, $id]);
        }
        $pdo->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } catch (\Exception $e) {
        $pdo->rollBack();
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

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
    $f_section = $_POST['footer_section'] ?? 'legal';

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, meta_title, meta_description, translation_group, show_in_header, show_in_footer, menu_order, footer_section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group, $in_header, $in_footer, $order, $f_section]);
            $message = "Page created successfully!";
            $action = 'list';
            $_curr_lang = $lang;
            $_SESSION['last_page_lang'] = $lang;
        } catch (\Exception $e) {
            $error = "DB Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, lang_code=?, meta_title=?, meta_description=?, translation_group=?, show_in_header=?, show_in_footer=?, menu_order=?, footer_section=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group, $in_header, $in_footer, $order, $f_section, $id]);
            $message = "Page updated successfully!";
            $action = 'list';
            $_curr_lang = $lang;
            $_SESSION['last_page_lang'] = $lang;
        } catch (\Exception $e) {
            $error = "DB Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM pages WHERE id=?")->execute([$id]);
        $message = "Page deleted.";
    } catch (\Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
    $action = 'list';
}

// Fetch Data for Display
$pages = [];
$cu_p = null;
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE lang_code = ? ORDER BY title ASC");
        $stmt->execute([$_curr_lang]);
        $pages = $stmt->fetchAll();
    } catch (\Exception $e) {
        $error = "Query Error: " . $e->getMessage();
    }
} elseif (($action === 'edit' || $action === 'add') && $id) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id=?");
    $stmt->execute([$id]);
    $cu_p = $stmt->fetch();
}

$page_title = "Page Content Manager";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?> - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">
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
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 25%, #6ee7b7 50%, #86efac 100%);
            min-height: 100vh;
        }

        .sidebar {
            height: 100vh;
            background: #065f46;
            color: white;
        }

        .nav-active {
            background: #047857;
            border-left: 4px solid #34d399;
        }

        .sortable-ghost {
            background-color: #f0fdf4;
            opacity: 0.5;
        }

        .drag-handle {
            cursor: grab;
        }

        .drag-handle:active {
            cursor: grabbing;
        }
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b-4 border-emerald-300 px-8 h-20 flex items-center justify-between shadow-sm">
            <div>
                <h3 class="text-xl font-bold text-gray-800"><?php echo $page_title; ?></h3>
                <p class="text-xs text-gray-500 mt-0.5">Manage static pages and content</p>
            </div>
            <div class="flex items-center gap-4">
                <?php if ($action === 'list'): ?>
                    <a href="?action=add"
                        class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="3" d="M12 4v16m8-8H4" />
                        </svg>
                        New Page
                    </a>
                <?php else: ?>
                    <a href="?action=list&filter_lang=<?php echo $_curr_lang; ?>"
                        class="text-gray-500 hover:text-gray-800 font-bold">← Back to List</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium border border-emerald-100">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium border border-red-100">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Language tabs removed -->

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b text-center">
                            <tr>
                                <th class="w-16 py-4"></th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-left">
                                    Page Title</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($pages)): ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center">
                                        <div class="text-gray-400 font-medium mb-4">No pages found.</div>
                                        <div class="flex justify-center gap-4">
                                            <a href="?action=add"
                                                class="bg-emerald-50 text-emerald-600 px-4 py-2 rounded-lg font-bold hover:bg-emerald-100">Create
                                                the first page →</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($pages as $p): ?>
                                <tr data-id="<?php echo $p['id']; ?>" class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 border-r border-gray-50">
                                        <div
                                            class="drag-handle flex items-center justify-center text-gray-300 hover:text-emerald-500 transition-colors">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title']); ?></div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter">
                                            /page.php?slug=<?php echo htmlspecialchars($p['slug'] ?? ''); ?></div>
                                    </td>
                                    <td class="px-8 py-5 text-right space-x-2 text-sm">
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>"
                                            class="text-emerald-600 hover:text-emerald-800 font-bold">Edit</a>
                                        <a href="javascript:void(0);"
                                            onclick="confirmDelete('?action=delete&id=<?php echo $p['id']; ?>', 'This static page will be permanently removed.')"
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
                                class="md:col-span-2 p-6 bg-gray-50 rounded-2xl border border-gray-100 flex flex-wrap items-center gap-8">
                                <div class="text-sm text-gray-600">
                                    <p class="font-bold text-gray-800">Page Content Only</p>
                                    <p class="text-xs">To manage where this page appears, use the <a href="menus.php"
                                            class="text-emerald-600 underline">Menu Manager</a>.</p>
                                </div>
                            </div>
                            <!-- Hidden Linked ID -->
                            <input type="hidden" name="translation_group"
                                value="<?php echo htmlspecialchars($cu_p['translation_group'] ?? uniqid('group_', true)); ?>">

                            <input type="hidden" name="lang_code" value="en">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Page Title</label>
                                <input type="text" name="title"
                                    value="<?php echo htmlspecialchars($cu_p['title'] ?? ''); ?>" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Slug</label>
                                <input type="text" name="slug" value="<?php echo htmlspecialchars($cu_p['slug'] ?? ''); ?>"
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-mono font-semibold text-black">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Content (HTML allowed)</label>
                            <textarea name="content" rows="15"
                                class="tinymce-editor w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none font-semibold text-black"><?php echo htmlspecialchars($cu_p['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="border-t pt-6">
                            <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4">SEO Settings</h4>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($cu_p['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none font-semibold text-black">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label>
                                    <textarea name="meta_description" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none font-semibold text-black"><?php echo htmlspecialchars($cu_p['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-emerald-600 text-white py-4 rounded-xl font-bold hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all uppercase tracking-widest">Save
                            Page & Navigation</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div id="toast"
        class="fixed bottom-8 right-8 bg-gray-900 text-white px-6 py-3 rounded-2xl shadow-2xl transition-all duration-300 transform translate-y-20 opacity-0 pointer-events-none z-[100] flex items-center gap-3">
        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
        <span class="font-bold text-sm"></span>
    </div>

    <script>
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const span = toast.querySelector('span');
            const dot = toast.querySelector('div');

            span.innerText = msg;
            dot.className = `w-2 h-2 rounded-full animate-pulse ${type === 'success' ? 'bg-emerald-500' : 'bg-red-500'}`;

            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

            });
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea[name="content"]',
            plugins: 'link lists code table autoresize',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | link table code',
            height: 500,
            branding: false,
            promotion: false,
            skin: 'oxide',
            content_css: 'default'
        });
    </script>
</body>

</html>