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

$available_langs = [
    'en' => ['label' => 'English', 'flag' => '🇺🇸'],
    'id' => ['label' => 'Indonesia', 'flag' => '🇮🇩'],
    'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
    'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
    'ja' => ['label' => '日本語', 'flag' => '🇯🇵']
];

if (isset($_GET['filter_lang'])) {
    $_curr_lang = $_GET['filter_lang'];
    $_SESSION['last_blog_lang'] = $_curr_lang;
} elseif (isset($_SESSION['last_blog_lang'])) {
    $_curr_lang = $_SESSION['last_blog_lang'];
} else {
    $_curr_lang = 'en';
}

if (!array_key_exists($_curr_lang, $available_langs))
    $_curr_lang = 'en';

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

    $excerpt = $_POST['excerpt'] ?? '';
    $author_id = $_SESSION['admin_id'] ?? 1;

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, thumbnail, lang_code, meta_title, meta_description, category, translation_group, status, tags, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $excerpt, $thumbnail, $lang, $m_title, $m_desc, $category, $t_group, $_POST['status'] ?? 'published', $_POST['tags'] ?? '', $author_id]);
            $message = "Post created successfully!";
            $action = 'list';
            $_curr_lang = $lang;
            $_SESSION['last_blog_lang'] = $lang;
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, content=?, excerpt=?, thumbnail=?, lang_code=?, meta_title=?, meta_description=?, category=?, translation_group=?, status=?, tags=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $excerpt, $thumbnail, $lang, $m_title, $m_desc, $category, $t_group, $_POST['status'] ?? 'published', $_POST['tags'] ?? '', $id]);
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

// Handle Blog Cloning
if ($action === 'clone' && $id && isset($_GET['target_lang'])) {
    $target_lang = $_GET['target_lang'];
    try {
        // Fetch original
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $original = $stmt->fetch();

        if ($original) {
            // Check collision
            $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE translation_group = ? AND lang_code = ?");
            $stmt->execute([$original['translation_group'], $target_lang]);
            if ($stmt->fetch()) {
                $error = "A post in " . $available_langs[$target_lang]['label'] . " already exists for this group.";
            } else {
                // Insert clone
                $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, thumbnail, lang_code, meta_title, meta_description, category, translation_group, status, tags, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $new_slug = $original['slug'] . '-' . $target_lang . '-' . rand(100, 999);

                $stmt->execute([
                    $original['title'],
                    $new_slug,
                    $original['content'],
                    $original['excerpt'],
                    $original['thumbnail'],
                    $target_lang,
                    $original['meta_title'],
                    $original['meta_description'],
                    $original['category'],
                    $original['translation_group'],
                    'draft', // Set to draft as a precaution
                    $original['tags'],
                    $original['author_id']
                ]);
                $message = "Post cloned to " . $available_langs[$target_lang]['label'] . " successfully!";
                $_curr_lang = $target_lang;
                $action = 'list';
            }
        }
    } catch (\Exception $e) {
        $error = "Clone failed: " . $e->getMessage();
        $action = 'list';
    }
}

// AJAX Category Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_category') {
    $cat_name = $_POST['name'] ?? '';
    if (!empty($cat_name)) {
        $cat_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat_name)));
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, lang_code) VALUES (?, ?, 'global')");
            $stmt->execute([$cat_name, $cat_slug]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'name' => $cat_name]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}

// Fetch Data
// Pagination
$per_page = 10;
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

if ($action === 'list') {
    // Total count for pagination
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE lang_code = ?");
    $stmt_count->execute([$_curr_lang]);
    $total_posts = $stmt_count->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);

    $stmt = $pdo->prepare("SELECT b.*, a.username as author_name 
                           FROM blog_posts b
                           LEFT JOIN admins a ON b.author_id = a.id
                           WHERE b.lang_code = ? 
                           ORDER BY b.created_at DESC
                           LIMIT ? OFFSET ?");
    $stmt->execute([$_curr_lang, $per_page, $offset]);
    $posts = $stmt->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=?");
    $stmt->execute([$id]);
    $current_post = $stmt->fetch();
}

$categories = [];
$stmt_cats = $pdo->query("SELECT DISTINCT name FROM categories ORDER BY name ASC");
$categories = $stmt_cats->fetchAll();
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
                <h3 class="text-xl font-bold text-gray-800">Posts</h3>
                <p class="text-xs text-gray-500 mt-0.5">Manage your multilingual content</p>
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
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest">Title</th>
                                <th
                                    class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest text-center">
                                    Languages</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest">Category
                                </th>
                                <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest">Date</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center">
                                        <div class="text-gray-400 font-medium mb-4">No posts found.</div>
                                        <a href="?action=add" class="text-emerald-600 font-bold hover:underline">Create the
                                            first post →</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($posts as $p): ?>
                                <tr>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-gray-900 leading-tight mb-1"><?php echo htmlspecialchars($p['title']); ?></div>
                                        <div class="text-[10px] text-gray-500 font-bold uppercase tracking-tight mb-2">
                                            /blog/<?php echo htmlspecialchars($p['slug'] ?? ''); ?></div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-[10px] font-black border border-emerald-200">
                                                <?php echo strtoupper(substr($p['author_name'] ?? 'A', 0, 1)); ?>
                                            </div>
                                            <span class="text-[11px] font-bold text-gray-600">by <?php echo htmlspecialchars($p['author_name'] ?? 'Admin'); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-gray-700 text-sm">
                                        <span
                                            class="px-2 py-1 bg-gray-100 rounded-lg font-bold text-gray-800 text-[10px] uppercase"><?php echo htmlspecialchars($p['category'] ?? 'General'); ?></span>
                                    </td>
                                    <td class="px-8 py-5 text-gray-700 text-xs font-bold">
                                        <?php if (($p['status'] ?? 'published') === 'draft'): ?>
                                            <span
                                                class="bg-gray-200 text-gray-900 px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider mr-2 border border-gray-300">Draft</span>
                                        <?php else: ?>
                                            <span
                                                class="bg-emerald-100 text-emerald-900 px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider mr-2 border border-emerald-200">Published</span>
                                        <?php endif; ?>
                                        <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                                    </td>
                                    <td class="px-8 py-5 text-right space-x-2">
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>"
                                            class="text-emerald-600 hover:text-emerald-800 font-bold">Edit</a>
                                        <a href="javascript:void(0);"
                                            onclick="confirmDelete('?action=delete&id=<?php echo $p['id']; ?>', 'This blog post will be permanently removed.')"
                                            class="text-red-400 hover:text-red-600 font-bold">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages > 1): ?>
                        <div class="px-8 py-6 bg-gray-50 border-t flex items-center justify-between">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">
                                showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_posts); ?> of <?php echo $total_posts; ?> posts
                            </div>
                            <div class="flex gap-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&filter_lang=<?php echo $_curr_lang; ?>"
                                        class="px-4 py-2 bg-white border-2 border-gray-200 rounded-xl font-bold text-gray-700 hover:border-emerald-600 hover:text-emerald-700 transition-all shadow-sm">Previous</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&filter_lang=<?php echo $_curr_lang; ?>"
                                        class="px-4 py-2 rounded-xl font-bold transition-all shadow-sm <?php echo $i === $page ? 'bg-emerald-600 text-white shadow-emerald-200' : 'bg-white border-2 border-gray-200 text-gray-700 hover:border-emerald-600 hover:text-emerald-700'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&filter_lang=<?php echo $_curr_lang; ?>"
                                        class="px-4 py-2 bg-white border-2 border-gray-200 rounded-xl font-bold text-gray-700 hover:border-emerald-600 hover:text-emerald-700 transition-all shadow-sm">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl border-2 border-emerald-200 max-w-4xl mx-auto mb-10">
                    <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST"
                        class="space-y-8">
                        <div class="grid md:grid-cols-2 gap-8">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-black text-gray-900 uppercase tracking-widest">Category</label>
                                    <button type="button" onclick="quickAddCategory()" class="text-[10px] font-black text-emerald-600 hover:text-emerald-700 uppercase tracking-wider">+ Quick Add</button>
                                </div>
                                <select name="category" id="categorySelect"
                                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-900 transition-all appearance-none cursor-pointer shadow-sm">
                                    <?php if (empty($categories)): ?>
                                        <option value="General">General</option>
                                    <?php else: ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                                <?php echo ($current_post['category'] ?? '') == $cat['name'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Thumbnail
                                    URL</label>
                                <input type="text" name="thumbnail" placeholder="https://..."
                                    value="<?php echo htmlspecialchars($current_post['thumbnail'] ?? ''); ?>"
                                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-800 transition-all shadow-sm">
                            </div>
                        </div>

                        </div>
                        <div>
                            <label
                                class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Post Excerpt (Brief Summary)</label>
                            <textarea name="excerpt" rows="3"
                                class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-900 transition-all shadow-sm"><?php echo htmlspecialchars($current_post['excerpt'] ?? ''); ?></textarea>
                        </div>
                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <input type="hidden" name="translation_group"
                                    value="<?php echo htmlspecialchars($_GET['translation_group'] ?? ($current_post['translation_group'] ?? uniqid('group_', true))); ?>">
                                <input type="hidden" name="lang_code"
                                    value="<?php echo htmlspecialchars($current_post['lang_code'] ?? ($_GET['lang_code'] ?? $_curr_lang)); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Post
                                    Title</label>
                                <input type="text" name="title"
                                    value="<?php echo htmlspecialchars($current_post['title'] ?? ''); ?>" required
                                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-900 transition-all shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Slug
                                    (Auto if empty)</label>
                                <input type="text" name="slug"
                                    value="<?php echo htmlspecialchars($current_post['slug'] ?? ''); ?>"
                                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-mono font-bold text-gray-800 transition-all shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Content</label>
                            <div
                                class="rounded-2xl border-2 border-gray-400 overflow-hidden focus-within:border-emerald-600 focus-within:ring-4 focus-within:ring-emerald-100 transition-all shadow-sm">
                                <textarea name="content" id="contentEditor" rows="15"
                                    class="w-full px-5 py-4 bg-white outline-none font-bold text-gray-900"><?php echo htmlspecialchars($current_post['content'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-8 pt-8 border-t-4 border-gray-200">
                            <div>
                                <label
                                    class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Status</label>
                                <select name="status"
                                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-black text-gray-900 appearance-none cursor-pointer shadow-sm">
                                    <option value="published" <?php echo ($current_post['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published (Live)</option>
                                    <option value="draft" <?php echo ($current_post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft (Hidden)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Tags
                                    (comma separated)</label>
                                <input type="text" name="tags"
                                    value="<?php echo htmlspecialchars($current_post['tags'] ?? ''); ?>"
                                    placeholder="e.g. instagram, tips, tutorial"
                                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-800 transition-all shadow-sm">
                            </div>
                        </div>

                        <div class="pt-10 bg-emerald-50/20 p-8 rounded-[2.5rem] border-2 border-dashed border-emerald-400">
                            <h4
                                class="font-black text-emerald-800 uppercase tracking-[0.2em] text-sm mb-8 flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                SEO Configuration
                            </h4>
                            <div class="grid md:grid-cols-2 gap-8">
                                <div <div
                                    class="bg-white p-6 rounded-3xl border-2 border-gray-400 shadow-sm hover:shadow-md transition-shadow">
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-3 uppercase tracking-widest">Meta
                                        Title</label>
                                    <textarea name="meta_title" rows="3"
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-900 transition-all bg-gray-50/50"><?php echo htmlspecialchars($current_post['meta_title'] ?? ''); ?></textarea>
                                </div>
                                <div
                                    class="bg-white p-6 rounded-3xl border-2 border-gray-400 shadow-sm hover:shadow-md transition-shadow">
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-3 uppercase tracking-widest">Meta
                                        Description</label>
                                    <textarea name="meta_description" rows="3"
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-800 leading-relaxed transition-all bg-gray-50/50"><?php echo htmlspecialchars($current_post['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">

                            <button type="submit"
                                class="w-full bg-emerald-600 text-white py-6 rounded-2xl font-black hover:bg-emerald-700 shadow-2xl shadow-emerald-200 hover:shadow-emerald-300 transition-all uppercase tracking-widest text-xl flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Post
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    </script>
</body>

</html>