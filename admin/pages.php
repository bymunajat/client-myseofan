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

// Available Languages
$available_langs = [
    'en' => ['label' => 'English', 'flag' => '🇺🇸'],
    'id' => ['label' => 'Indonesia', 'flag' => '🇮🇩'],
    'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
    'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
    'ja' => ['label' => '日本語', 'flag' => '🇯🇵']
];

// Determine Active Language for Filtering
if (isset($_GET['filter_lang'])) {
    $_curr_lang = $_GET['filter_lang'];
    $_SESSION['last_page_lang'] = $_curr_lang;
} elseif (isset($_SESSION['last_page_lang'])) {
    $_curr_lang = $_SESSION['last_page_lang'];
} else {
    $_curr_lang = 'en';
}

if (!array_key_exists($_curr_lang, $available_langs))
    $_curr_lang = 'en';


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

// Handle Page Cloning
if ($action === 'clone' && $id && isset($_GET['target_lang'])) {
    $target_lang = $_GET['target_lang'];
    try {
        // Fetch original
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $original = $stmt->fetch();

        if ($original) {
            // Check collision
            $stmt = $pdo->prepare("SELECT id FROM pages WHERE translation_group = ? AND lang_code = ?");
            $stmt->execute([$original['translation_group'], $target_lang]);
            if ($stmt->fetch()) {
                $error = "A page in " . $available_langs[$target_lang]['label'] . " already exists for this group.";
            } else {
                // Insert clone
                $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, meta_title, meta_description, translation_group, show_in_header, show_in_footer, menu_order, footer_section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                // Append lang code to slug to avoid unique constraint if slug is unique (though schema might allow same slug diff lang, let's be safe)
                $new_slug = $original['slug'] . '-' . $target_lang;
                // Modify title slightly to indicate clone? No, usually keep same.

                $stmt->execute([
                    $original['title'],
                    $new_slug,
                    $original['content'],
                    $target_lang,
                    $original['meta_title'],
                    $original['meta_description'],
                    $original['translation_group'],
                    $original['show_in_header'],
                    $original['show_in_footer'],
                    $original['menu_order'],
                    $original['footer_section']
                ]);
                $message = "Page cloned to " . $available_langs[$target_lang]['label'] . " successfully!";
                $_curr_lang = $target_lang; // Switch view to target
                $action = 'list';
            }
        }
    } catch (\Exception $e) {
        $error = "Clone failed: " . $e->getMessage();
        $action = 'list';
    }
}

// Handle CRUD Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'reorder') {
    $title = $_POST['title'] ?? '';
    // Auto slug if empty
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

$page_title = "Pages";
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
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
            /* Slate 900 */
            color: #f8fafc;
            min-height: 100vh;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen bg-[#0f172a]">
        <header class="bg-[#1e293b] border-b-4 border-fuchsia-500/50 px-8 h-20 flex items-center justify-between shadow-lg shadow-black/20">
            <div>
                <h3 class="text-xl font-bold text-white"><?php echo $page_title; ?></h3>
                <p class="text-xs text-gray-400 mt-0.5">Manage static pages and content</p>
            </div>
            <div class="flex items-center gap-4">
                <?php if ($action === 'list'): ?>
                    <a href="?action=add&filter_lang=<?php echo $_curr_lang; ?>"
                        class="bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-fuchsia-500/30 hover:shadow-fuchsia-500/50 hover:scale-[1.02] transition-all text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="3" d="M12 4v16m8-8H4" />
                        </svg>
                        New Page
                    </a>
                <?php else: ?>
                    <a href="?action=list&filter_lang=<?php echo $_curr_lang; ?>"
                        class="bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-fuchsia-500/30 hover:shadow-fuchsia-500/50 hover:scale-[1.02] transition-all text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div
                    class="bg-fuchsia-50 text-fuchsia-700 p-4 rounded-xl mb-6 font-bold border border-fuchsia-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div
                    class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium border border-red-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100 text-center">
                            <tr>
                                <th class="w-16 py-4"></th>
                                <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest text-left">
                                    Page Title</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($pages)): ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-12 text-center">
                                        <div class="text-gray-400 font-medium mb-4">No pages found in
                                            <?php echo $available_langs[$_curr_lang]['label']; ?>.
                                        </div>
                                        <div class="flex justify-center gap-4">
                                            <a href="?action=add&filter_lang=<?php echo $_curr_lang; ?>"
                                                class="bg-fuchsia-50 text-fuchsia-600 px-4 py-2 rounded-lg font-bold hover:bg-fuchsia-100">Create
                                                page</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($pages as $p): ?>
                                <tr data-id="<?php echo $p['id']; ?>" class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 border-r border-gray-50">
                                        <div class="flex items-center justify-center text-gray-400 font-bold">
                                            <span class="text-xs font-mono">#<?php echo $p['id']; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-gray-900 text-lg">
                                            <?php echo htmlspecialchars($p['title']); ?>
                                        </div>
                                        <div class="text-xs font-bold text-gray-400 font-mono mt-1">
                                            /<?php echo htmlspecialchars($p['slug']); ?>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right space-x-4">
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>&filter_lang=<?php echo $_curr_lang; ?>"
                                            class="text-emerald-600 hover:text-emerald-800 font-bold text-sm uppercase tracking-wider">Edit</a>
                                        <a href="javascript:void(0);"
                                            onclick="confirmDelete('?action=delete&id=<?php echo $p['id']; ?>&filter_lang=<?php echo $_curr_lang; ?>', 'Are you sure? This cannot be undone.')"
                                            class="text-red-400 hover:text-red-600 font-bold text-sm uppercase tracking-wider">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="bg-white p-8 rounded-3xl shadow-2xl border-2 border-emerald-200 max-w-5xl mx-auto">
                    <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST"
                        class="space-y-8">

                        <!-- Header / Language Info -->
                        <div class="flex items-center justify-between pb-6 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-3xl"><?php echo $available_langs[$cu_p['lang_code'] ?? $_curr_lang]['flag']; ?></span>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-800">
                                        <?php echo $action === 'edit' ? 'Editing Page' : 'New Page'; ?>
                                        <span class="text-gray-500 font-normal">in
                                            <?php echo $available_langs[$cu_p['lang_code'] ?? $_curr_lang]['label']; ?></span>
                                    </h2>
                                </div>
                            </div>

                            <input type="hidden" name="lang_code"
                                value="<?php echo htmlspecialchars($cu_p['lang_code'] ?? ($_GET['filter_lang'] ?? $_curr_lang)); ?>">
                            <input type="hidden" name="translation_group"
                                value="<?php echo htmlspecialchars($_GET['translation_group'] ?? ($cu_p['translation_group'] ?? uniqid('group_', true))); ?>">
                        </div>

                        <div class="grid md:grid-cols-2 gap-8">
                            <!-- Left Column: Core Info (Blue Card) -->
                            <div
                                class="bg-white p-8 rounded-[2rem] border-4 border-blue-600 shadow-xl space-y-6 relative overflow-hidden">
                                <h4
                                    class="flex items-center gap-3 text-lg font-black text-blue-700 uppercase tracking-widest mb-6 border-b-4 border-blue-100 pb-4">
                                    <span
                                        class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-width="3"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                    Basic Information
                                </h4>
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 uppercase tracking-widest mb-3">Page
                                        Title</label>
                                    <input type="text" name="title"
                                        value="<?php echo htmlspecialchars($cu_p['title'] ?? ''); ?>" required
                                        placeholder="e.g. Terms of Service"
                                        class="w-full px-6 py-5 rounded-2xl border-4 border-gray-900 bg-white text-xl font-bold text-gray-900 outline-none placeholder-gray-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-black text-gray-900 uppercase tracking-widest mb-3">URL
                                        Slug</label>
                                    <div
                                        class="flex items-center rounded-2xl overflow-hidden border-4 border-gray-900 bg-white group transition-colors">
                                        <span
                                            class="bg-gray-50 text-gray-500 px-5 py-5 border-r-4 border-gray-900 font-mono text-sm font-bold">/</span>
                                        <input type="text" name="slug"
                                            value="<?php echo htmlspecialchars($cu_p['slug'] ?? ''); ?>"
                                            placeholder="terms-of-service"
                                            class="w-full px-6 py-5 bg-transparent outline-none font-mono font-bold text-gray-900 placeholder-gray-400">
                                    </div>
                                    <p class="text-xs font-bold text-blue-600 mt-3 ml-1 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        Auto-generated if left empty
                                    </p>
                                </div>
                            </div>

                            <!-- Right Column: SEO (Fuchsia Card) -->
                            <div class="bg-white p-8 rounded-[2rem] border-4 border-fuchsia-500 shadow-xl space-y-6">
                                <h4
                                    class="flex items-center gap-3 text-lg font-black text-fuchsia-700 uppercase tracking-widest mb-6 border-b-4 border-fuchsia-100 pb-4">
                                    <span
                                        class="w-10 h-10 rounded-xl bg-fuchsia-100 flex items-center justify-center text-fuchsia-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </span>
                                    SEO Configuration
                                </h4>
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 uppercase tracking-widest mb-3">Meta
                                        Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($cu_p['meta_title'] ?? ''); ?>"
                                        class="w-full px-6 py-5 rounded-2xl border-4 border-gray-900 bg-white text-lg font-bold text-gray-900 outline-none">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 uppercase tracking-widest mb-3">Meta
                                        Description</label>
                                    <textarea name="meta_description" rows="3"
                                        class="w-full px-6 py-5 rounded-2xl border-4 border-gray-900 bg-white text-base font-bold text-gray-900 outline-none"><?php echo htmlspecialchars($cu_p['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Editor (Indigo Card) -->
                        <div class="mt-10 bg-white p-8 rounded-[2rem] border-4 border-indigo-500 shadow-xl relative">
                            <h4
                                class="flex items-center gap-3 text-lg font-black text-indigo-700 uppercase tracking-widest mb-6 border-b-4 border-indigo-100 pb-4">
                                <span
                                    class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-width="3" d="M4 6h16M4 12h16M4 18h7"></path>
                                    </svg>
                                </span>
                                Page Content
                            </h4>
                            <div class="rounded-2xl overflow-hidden border-4 border-gray-900 bg-white">
                                <textarea name="content" id="contentEditor"
                                    class="tinymce-editor"><?php echo htmlspecialchars($cu_p['content'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-6 border-t border-gray-100">
                            <div class="flex-1">
                                <button type="submit"
                                    class="w-full bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white py-4 rounded-xl font-bold hover:shadow-lg hover:shadow-fuchsia-500/30 transition-all uppercase tracking-widest text-sm flex items-center justify-center gap-2 transform hover:scale-[1.02]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Page
                                </button>
                                </button>
                            </div>
                            <a href="?action=list&filter_lang=<?php echo $_curr_lang; ?>"
                                class="px-8 py-4 rounded-xl font-bold text-gray-400 hover:bg-gray-50 hover:text-gray-900 transition-all uppercase tracking-widest text-sm border-2 border-gray-200">Cancel</a>
                        </div>
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
        // Translation functions removed

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
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#contentEditor',
            plugins: 'link lists code table autoresize searchreplace visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright | indent outdent | bullist numlist | link table | code',
            height: 600,
            branding: false,
            promotion: false,
            menubar: false,
            skin: 'oxide',
            content_css: 'default',
            content_style: "@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap'); body { font-family: 'Outfit', sans-serif; font-size: 16px; color: #333; line-height: 1.6; padding: 20px; } h1,h2,h3 { font-weight: 700; color: #111; } a { color: #10b981; text-decoration: none; }"
        });
    </script>
</body>

</html>