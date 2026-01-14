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

        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
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
                    <a href="?action=add&filter_lang=<?php echo $_curr_lang; ?>"
                        class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="3" d="M12 4v16m8-8H4" />
                        </svg>
                        New Page
                    </a>
                <?php else: ?>
                    <a href="?action=list&filter_lang=<?php echo $_curr_lang; ?>"
                        class="text-gray-500 hover:text-gray-800 font-bold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div
                    class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium border border-emerald-100 flex items-center gap-2">
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
                        <thead class="bg-gray-50 border-b text-center">
                            <tr>
                                <th class="w-16 py-4"></th>
                                <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest text-left">
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
                                                class="bg-emerald-50 text-emerald-600 px-4 py-2 rounded-lg font-bold hover:bg-emerald-100">Create
                                                page</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($pages as $p): ?>
                                <tr data-id="<?php echo $p['id']; ?>" class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 border-r border-gray-50">
                                        <div class="flex items-center justify-center text-gray-500 font-bold">
                                            <span class="text-xs font-mono">#<?php echo $p['id']; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-gray-800 text-lg">
                                            <?php echo htmlspecialchars($p['title']); ?>
                                        </div>
                                        <div class="text-xs text-emerald-600 font-mono mt-0.5">
                                            /<?php echo htmlspecialchars($p['slug'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right flex items-center justify-end gap-3">


                                        <a href="?action=edit&id=<?php echo $p['id']; ?>"
                                            class="text-sm font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-4 py-1.5 rounded-lg transition-colors">Edit</a>

                                        <a href="javascript:void(0);"
                                            onclick="confirmDelete('?action=delete&id=<?php echo $p['id']; ?>', 'This page will be permanently deleted.')"
                                            class="text-sm font-bold text-red-400 hover:text-red-600 hover:bg-red-50 px-4 py-1.5 rounded-lg transition-colors">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 max-w-5xl mx-auto">
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
                                        <span class="text-gray-400 font-normal">in
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
                            <!-- Left Column: Core Info -->
                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Page
                                        Title</label>
                                    <input type="text" name="title"
                                        value="<?php echo htmlspecialchars($cu_p['title'] ?? ''); ?>" required
                                        placeholder="e.g. Terms of Service"
                                        class="w-full px-5 py-4 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none font-bold text-lg text-gray-800 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">URL
                                        Slug</label>
                                    <div class="flex items-center">
                                        <span
                                            class="bg-gray-100 text-gray-500 px-4 py-4 rounded-l-2xl border-y border-l border-gray-200 font-mono text-sm">/</span>
                                        <input type="text" name="slug"
                                            value="<?php echo htmlspecialchars($cu_p['slug'] ?? ''); ?>"
                                            placeholder="terms-of-service"
                                            class="w-full px-5 py-4 rounded-r-2xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none font-mono font-semibold text-gray-700 transition-all">
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 ml-2">Leave empty to auto-generate from title.
                                    </p>
                                </div>
                            </div>

                            <!-- Right Column: SEO -->
                            <div class="bg-emerald-50/50 p-6 rounded-3xl border border-emerald-100 space-y-6">
                                <h4
                                    class="flex items-center gap-2 text-xs font-black text-emerald-600 uppercase tracking-widest">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    SEO Configuration
                                </h4>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($cu_p['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-emerald-200 bg-white focus:border-emerald-500 outline-none font-semibold text-gray-800">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Meta
                                        Description</label>
                                    <textarea name="meta_description" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border border-emerald-200 bg-white focus:border-emerald-500 outline-none font-medium text-gray-600 text-sm leading-relaxed"><?php echo htmlspecialchars($cu_p['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Editor -->
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Page
                                Content</label>
                            <div class="rounded-2xl overflow-hidden border border-gray-200">
                                <textarea name="content" id="contentEditor"
                                    class="tinymce-editor"><?php echo htmlspecialchars($cu_p['content'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-6 border-t border-gray-100">
                            <div class="flex flex-col gap-4 flex-1">
                                <button type="button" onclick="autoTranslateAll()" id="translateBtn"
                                    class="w-full bg-gradient-to-r from-purple-600 to-emerald-600 text-white py-4 rounded-xl font-bold hover:from-purple-700 hover:to-emerald-700 shadow-xl transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 translate-icon" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span id="translateText">✨ Auto Translate Content</span>
                                </button>
                                <button type="submit"
                                    class="w-full bg-gray-900 text-white py-4 rounded-xl font-bold hover:bg-emerald-600 shadow-xl shadow-gray-200 hover:shadow-emerald-200 transition-all uppercase tracking-widest text-sm flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Page
                                </button>
                            </div>
                            <a href="?action=list&filter_lang=<?php echo $_curr_lang; ?>"
                                class="px-8 py-4 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition-all uppercase tracking-widest text-sm">Cancel</a>
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
        async function translateText(text, targetLang) {
            if (!text || text.trim() === '') return '';
            const formData = new FormData();
            formData.append('text', text);
            formData.append('lang', targetLang);

            try {
                const response = await fetch('ajax_translate.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                return data.translatedText || text;
            } catch (e) {
                console.error('Translation error:', e);
                return text;
            }
        }

        async function autoTranslateAll() {
            const btn = document.getElementById('translateBtn');
            const btnText = document.getElementById('translateText');
            const targetLang = document.getElementsByName('lang_code')[0].value;

            if (targetLang === 'en') {
                showToast('Target language is English. No translation needed.', 'error');
                return;
            }

            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btnText.innerText = 'Translating...';
            showToast('Translating content... Please wait.');

            try {
                // 1. Title
                const titleInput = document.getElementsByName('title')[0];
                const translatedTitle = await translateText(titleInput.value, targetLang);
                titleInput.value = translatedTitle;

                // 1b. Slug
                const slugInput = document.getElementsByName('slug')[0];
                slugInput.value = translatedTitle.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/(^-|-$)/g, '');

                // 2. SEO Title
                const seoTitle = document.getElementsByName('meta_title')[0];
                seoTitle.value = await translateText(seoTitle.value, targetLang);

                // 3. SEO Desc
                const seoDesc = document.getElementsByName('meta_description')[0];
                seoDesc.value = await translateText(seoDesc.value, targetLang);

                // 4. Content (TinyMCE)
                if (tinymce.get('contentEditor')) {
                    const content = tinymce.get('contentEditor').getContent();
                    const translatedContent = await translateText(content, targetLang);
                    tinymce.get('contentEditor').setContent(translatedContent);
                }

                showToast('✨ Translation Complete!');
                btnText.innerText = '✨ Success!';
                setTimeout(() => {
                    btnText.innerText = '✨ Auto Translate Content';
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }, 2000);
            } catch (err) {
                showToast('Translation failed. Please try again.', 'error');
                btnText.innerText = '✨ Auto Translate Content';
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

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