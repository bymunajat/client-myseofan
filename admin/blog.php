<?php
session_start();
require_once '../includes/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Persistent Language Logic
$available_langs = [
    'en' => '🇺🇸 English',
    'id' => '🇮🇩 Indonesia',
    'es' => '🇪🇸 Español',
    'fr' => '🇫🇷 Français',
    'de' => '🇩🇪 DE',
    'ja' => '🇯🇵 日本語'
];

// Determine the current filtered language
if (isset($_GET['filter_lang'])) {
    $_curr_lang = $_GET['filter_lang'];
} elseif (isset($_GET['lang_code'])) {
    $_curr_lang = $_GET['lang_code'];
} elseif (isset($_SESSION['last_blog_lang'])) {
    $_curr_lang = $_SESSION['last_blog_lang'];
} else {
    $_curr_lang = 'en';
}

if (!array_key_exists($_curr_lang, $available_langs))
    $_curr_lang = 'en';
$_SESSION['last_blog_lang'] = $_curr_lang;

// Handle CRUD Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    if (empty($slug))
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    $content = $_POST['content'] ?? '';
    $thumbnail = $_POST['thumbnail'] ?? '';
    $lang = $_POST['lang_code'] ?? 'en';
    $m_title = $_POST['meta_title'] ?? '';
    $m_desc = $_POST['meta_description'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $t_group = $_POST['translation_group'] ?? uniqid('group_', true);

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, thumbnail, lang_code, meta_title, meta_description, category, translation_group, status, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $thumbnail, $lang, $m_title, $m_desc, $category, $t_group, $_POST['status'] ?? 'published', $_POST['tags'] ?? '']);
            $message = "Post created successfully!";
            $action = 'list';
            $_curr_lang = $lang;
            $_SESSION['last_blog_lang'] = $lang;
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, content=?, thumbnail=?, lang_code=?, meta_title=?, meta_description=?, category=?, translation_group=?, status=?, tags=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $thumbnail, $lang, $m_title, $m_desc, $category, $t_group, $_POST['status'] ?? 'published', $_POST['tags'] ?? '', $id]);
            $message = "Post updated successfully!";
            $action = 'list';
            $_curr_lang = $lang;
            $_SESSION['last_blog_lang'] = $lang;
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
        $message = "Post deleted.";
    } catch (\Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
    $action = 'list';
}

// Fetch Data
$posts = [];
$current_post = null;
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE lang_code = ? ORDER BY created_at DESC");
    $stmt->execute([$_curr_lang]);
    $posts = $stmt->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=?");
    $stmt->execute([$id]);
    $current_post = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Blog Manager - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b-4 border-emerald-300 px-8 h-20 flex items-center justify-between shadow-sm">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Blog Management</h3>
                <p class="text-xs text-gray-500 mt-0.5">Create and manage blog posts</p>
            </div>
            <?php if ($action === 'list'): ?>
                <a href="?action=add&lang_code=<?php echo $_curr_lang; ?>"
                    class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition-all">+
                    New Post</a>
            <?php else: ?>
                <a href="?action=list&filter_lang=<?php echo $_curr_lang; ?>"
                    class="text-gray-500 hover:text-gray-800 font-bold">← Back to List</a>
            <?php endif; ?>
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
                <!-- Language Navigation Tabs -->
                <div class="flex flex-wrap gap-2 mb-8 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                    <?php foreach ($available_langs as $code => $label): ?>
                        <a href="?filter_lang=<?php echo $code; ?>&action=list"
                            class="px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 <?php echo $_curr_lang === $code ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'text-gray-500 hover:bg-gray-50'; ?>">
                            <?php echo $label; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Title</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Category
                                </th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Date</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center">
                                        <div class="text-gray-400 font-medium mb-4">No posts found in
                                            <?php echo $available_langs[$_curr_lang] ?? $_curr_lang; ?>.
                                        </div>
                                        <a href="?action=add&lang_code=<?php echo $_curr_lang; ?>"
                                            class="text-emerald-600 font-bold hover:underline">Create the first post for this
                                            language →</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($posts as $p): ?>
                                <tr>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['title']); ?></div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter">
                                            /blog/<?php echo htmlspecialchars($p['slug'] ?? ''); ?></div>
                                    </td>
                                    <td class="px-8 py-5 text-gray-500 text-sm">
                                        <span
                                            class="px-2 py-1 bg-gray-100 rounded-lg font-bold text-gray-600 text-[10px] uppercase"><?php echo htmlspecialchars($p['category'] ?? 'General'); ?></span>
                                    </td>
                                    <td class="px-8 py-5 text-gray-400 text-xs">
                                        <?php if (($p['status'] ?? 'published') === 'draft'): ?>
                                            <span
                                                class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider mr-2">Draft</span>
                                        <?php else: ?>
                                            <span
                                                class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider mr-2">Published</span>
                                        <?php endif; ?>
                                        <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
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
                                <label class="block text-sm font-semibold text-gray-700 mb-2">ID Penghubung Bahasa (Linked
                                    ID)</label>
                                <input type="text" name="translation_group"
                                    value="<?php echo htmlspecialchars($current_post['translation_group'] ?? uniqid('group_', true)); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 font-semibold text-black text-sm outline-none font-mono">
                                <p class="text-[10px] text-gray-400 mt-1">ID ini menghubungkan artikel ini dengan versinya
                                    di bahasa lain.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Post Title</label>
                                <input type="text" name="title"
                                    value="<?php echo htmlspecialchars($current_post['title'] ?? ''); ?>" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Slug (Auto if empty)</label>
                                <input type="text" name="slug"
                                    value="<?php echo htmlspecialchars($current_post['slug'] ?? ''); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-mono font-semibold text-black">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Language</label>
                                <select name="lang_code"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black">
                                    <?php foreach ($available_langs as $code => $label): ?>
                                        <option value="<?php echo $code; ?>" <?php echo (($current_post['lang_code'] ?? ($_GET['lang_code'] ?? $_curr_lang)) == $code) ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                <select name="category"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black">
                                    <option value="General" <?php echo ($current_post['category'] ?? '') == 'General' ? 'selected' : ''; ?>>General</option>
                                    <option value="Tutorial" <?php echo ($current_post['category'] ?? '') == 'Tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                                    <option value="News" <?php echo ($current_post['category'] ?? '') == 'News' ? 'selected' : ''; ?>>News</option>
                                    <option value="Tips" <?php echo ($current_post['category'] ?? '') == 'Tips' ? 'selected' : ''; ?>>Tips</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Thumbnail URL</label>
                                <input type="text" name="thumbnail"
                                    value="<?php echo htmlspecialchars($current_post['thumbnail'] ?? ''); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-semibold text-black">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Content (HTML allowed)</label>
                            <textarea name="content" rows="15" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none font-semibold text-black"><?php echo htmlspecialchars($current_post['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                <select name="status"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-bold text-black">
                                    <option value="published" <?php echo ($current_post['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published (Live)</option>
                                    <option value="draft" <?php echo ($current_post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft (Hidden)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tags (comma separated)</label>
                                <input type="text" name="tags"
                                    value="<?php echo htmlspecialchars($current_post['tags'] ?? ''); ?>"
                                    placeholder="e.g. instagram, tips, tutorial"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white outline-none font-semibold text-black">
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4">SEO Settings</h4>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($current_post['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none font-semibold text-black">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label>
                                    <textarea name="meta_description" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none font-semibold text-black"><?php echo htmlspecialchars($current_post['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-emerald-600 text-white py-4 rounded-xl font-bold hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all uppercase tracking-widest">Save
                            Post</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>