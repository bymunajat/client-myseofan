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
    // Use admin_id from session (set in login.php)
    $author_id = $_POST['author_id'] ?? ($_SESSION['admin_id'] ?? 1);

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
            $stmt = $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, content=?, excerpt=?, thumbnail=?, lang_code=?, meta_title=?, meta_description=?, category=?, translation_group=?, status=?, tags=?, author_id=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $excerpt, $thumbnail, $lang, $m_title, $m_desc, $category, $t_group, $_POST['status'] ?? 'published', $_POST['tags'] ?? '', $author_id, $id]);
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
        // 1. Fetch translation group first
        $stmt = $pdo->prepare("SELECT translation_group FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetchColumn();

        if ($group) {
            // 2. Delete ALL posts in this group (Cascading Delete)
            $delStmt = $pdo->prepare("DELETE FROM blog_posts WHERE translation_group = ?");
            $delStmt->execute([$group]);
            $count = $delStmt->rowCount();
            $message = "Post and its translations deleted (" . $count . " items removed).";
        } else {
            // Fallback for legacy items without group
            $pdo->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
            $message = "Post deleted.";
        }
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
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $per_page;

if ($action === 'list') {
    // Search Logic
    $search = trim($_GET['search'] ?? '');
    $search_param = "%$search%";
    $where_sql = "WHERE 1=1";
    $params = [];

    // Only filter by language if NOT global (implied logic: simplify to just search all if needed, but keeping lang consistency)
    // Actually, user wants single stream, so generally we just show all.
    // But to be safe let's search across everything since we removed language tabs.

    if (!empty($search)) {
        $where_sql .= " AND (title LIKE ? OR slug LIKE ?)";
        $params[] = $search_param;
        $params[] = $search_param;
    }

    // Total count for pagination
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM blog_posts $where_sql");
    $stmt_count->execute($params);
    $total_posts = $stmt_count->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);

    // Fetch Posts
    $stmt = $pdo->prepare("SELECT b.*, a.username as author_name 
                           FROM blog_posts b
                           LEFT JOIN admins a ON b.author_id = a.id
                           $where_sql 
                           ORDER BY b.created_at DESC
                           LIMIT ? OFFSET ?");

    // Merge params for final query
    $params[] = $per_page;
    $params[] = $offset;

    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=?");
    $stmt->execute([$id]);
    $current_post = $stmt->fetch();
}

$categories = [];
$stmt_cats = $pdo->query("SELECT DISTINCT name FROM categories ORDER BY name ASC");
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

$admins = [];
try {
    $stmt_admins = $pdo->query("SELECT id, username FROM admins ORDER BY username ASC");
    $admins = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    // Silent fail or log
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
                <h3 class="text-xl font-bold text-gray-800">Posts</h3>
                <p class="text-xs text-gray-500 mt-0.5">Manage your blog posts and articles</p>
            </div>
            <?php if ($action === 'list'): ?>
                <div class="flex items-center gap-4">
                    <form method="GET" action="" class="relative group">
                        <input type="hidden" name="action" value="list">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>"
                            placeholder="Search posts..."
                            class="pl-11 pr-4 py-2.5 rounded-2xl border-2 border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 outline-none text-gray-700 font-bold placeholder-gray-400 transition-all w-64 focus:w-72 shadow-sm">
                        <svg class="w-5 h-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors absolute left-4 top-1/2 -translate-y-1/2"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </form>
                    <a href="?action=add"
                        class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="3" d="M12 4v16m8-8H4" />
                        </svg>
                        New Post
                    </a>
                </div>
            <?php else: ?>
                <a href="?action=list" class="text-gray-500 hover:text-gray-800 font-bold">← Back to List</a>
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
                                        <div class="font-bold text-gray-900 leading-tight mb-1">
                                            <?php echo htmlspecialchars($p['title']); ?>
                                        </div>
                                        <div class="text-[10px] text-gray-500 font-bold uppercase tracking-tight mb-2">
                                            /blog/<?php echo htmlspecialchars($p['slug'] ?? ''); ?></div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-[10px] font-black border border-emerald-200">
                                                <?php echo strtoupper(substr($p['author_name'] ?? 'A', 0, 1)); ?>
                                            </div>
                                            <span class="text-[11px] font-bold text-gray-600">by
                                                <?php echo htmlspecialchars($p['author_name'] ?? 'Admin'); ?></span>
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
                                showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_posts); ?> of
                                <?php echo $total_posts; ?> posts
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
                        <!-- 1. Top Section: Title & Slug -->
                        <div class="grid md:grid-cols-2 gap-8">
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

                        <!-- 2. Main Layout Grid -->
                        <div class="grid lg:grid-cols-3 gap-8">

                            <!-- Left Column: Content & SEO (Span 2) -->
                            <div class="lg:col-span-2 space-y-8">
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Content</label>
                                    <div
                                        class="rounded-2xl border-2 border-gray-400 overflow-hidden focus-within:border-emerald-600 focus-within:ring-4 focus-within:ring-emerald-100 transition-all shadow-sm">
                                        <textarea name="content" id="contentEditor" rows="20"
                                            class="w-full px-5 py-4 bg-white outline-none font-bold text-gray-900"><?php echo htmlspecialchars($current_post['content'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Excerpt</label>
                                    <textarea name="excerpt" rows="3"
                                        class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-900 transition-all shadow-sm"><?php echo htmlspecialchars($current_post['excerpt'] ?? ''); ?></textarea>
                                </div>

                                <div class="bg-emerald-50/20 p-6 rounded-[2rem] border-2 border-dashed border-emerald-400">
                                    <h4
                                        class="font-black text-emerald-800 uppercase tracking-widest text-xs mb-6 flex items-center gap-2">
                                        <span
                                            class="w-8 h-8 bg-emerald-600 text-white rounded-lg flex items-center justify-center shadow-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        SEO Settings
                                    </h4>
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-xs font-black text-gray-700 mb-2 uppercase">Meta
                                                Title</label>
                                            <input type="text" name="meta_title"
                                                value="<?php echo htmlspecialchars($current_post['meta_title'] ?? ''); ?>"
                                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-emerald-500 outline-none font-bold text-gray-800 bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-700 mb-2 uppercase">Meta
                                                Description</label>
                                            <textarea name="meta_description" rows="3"
                                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-emerald-500 outline-none font-bold text-gray-800 bg-white"><?php echo htmlspecialchars($current_post['meta_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Meta & Sidebar (Span 1) -->
                            <div class="space-y-8">

                                <!-- Author Selection -->
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Author</label>
                                    <select name="author_id"
                                        class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-900 appearance-none cursor-pointer shadow-sm">
                                        <?php
                                        $current_author = $current_post['author_id'] ?? $_SESSION['admin_id'];
                                        foreach ($admins as $admin):
                                            ?>
                                            <option value="<?php echo $admin['id']; ?>" <?php echo $current_author == $admin['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($admin['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Status</label>
                                    <select name="status"
                                        class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-black text-gray-900 appearance-none cursor-pointer shadow-sm">
                                        <option value="published" <?php echo ($current_post['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published (Live)</option>
                                        <option value="draft" <?php echo ($current_post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft (Hidden)</option>
                                    </select>
                                </div>

                                <!-- Image Upload -->
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Featured
                                        Image</label>
                                    <input type="hidden" name="thumbnail" id="thumbnailInput"
                                        value="<?php echo htmlspecialchars($current_post['thumbnail'] ?? ''); ?>">

                                    <div id="uploadArea"
                                        class="border-2 border-dashed border-gray-400 rounded-2xl p-6 text-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-50 transition-all group relative overflow-hidden bg-white">
                                        <input type="file" id="fileInput" accept="image/*"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                                        <!-- Placeholder State -->
                                        <div id="uploadPlaceholder"
                                            class="<?php echo !empty($current_post['thumbnail']) ? 'hidden' : ''; ?>">
                                            <div
                                                class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-emerald-200 group-hover:text-emerald-700 transition-colors">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-xs font-bold text-gray-500 group-hover:text-emerald-700">
                                                    Click to Upload</p>
                                                <p class="text-[10px] text-gray-400">JPG, PNG, WEBP</p>
                                            </div>
                                        </div>

                                        <!-- Preview State -->
                                        <div id="uploadPreview"
                                            class="<?php echo !empty($current_post['thumbnail']) ? '' : 'hidden'; ?> relative h-40 w-full">
                                            <img src="<?php echo !empty($current_post['thumbnail']) ? '../' . htmlspecialchars($current_post['thumbnail']) : ''; ?>"
                                                id="previewImg"
                                                class="w-full h-full object-cover rounded-xl shadow-sm border border-gray-100">
                                            <button type="button" onclick="removeImage(event)"
                                                class="absolute top-2 right-2 bg-white text-red-500 p-1.5 rounded-lg shadow-md hover:bg-red-50 transition-colors border border-gray-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Loading -->
                                        <div id="uploadLoading"
                                            class="hidden absolute inset-0 bg-white/90 flex flex-col items-center justify-center z-10">
                                            <svg class="animate-spin h-8 w-8 text-emerald-600 mb-2"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            <span class="text-xs font-bold text-emerald-600">Uploading...</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label
                                            class="block text-sm font-black text-gray-900 uppercase tracking-widest">Category</label>
                                        <button type="button" onclick="quickAddCategory()"
                                            class="text-[10px] font-black text-emerald-600 hover:text-emerald-700 uppercase tracking-wider bg-emerald-50 px-2 py-1 rounded-lg hover:bg-emerald-100 transition-colors">+
                                            NEW</button>
                                    </div>
                                    <select name="category" id="categorySelect"
                                        class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-900 transition-all appearance-none cursor-pointer shadow-sm">
                                        <?php if (empty($categories)): ?>
                                            <option value="General">General</option>
                                        <?php else: ?>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($current_post['category'] ?? '') == $cat['name'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Tags -->
                                <div>
                                    <label
                                        class="block text-sm font-black text-gray-900 mb-2 uppercase tracking-widest">Tags</label>
                                    <input type="text" name="tags" placeholder="tech, news, updates"
                                        value="<?php echo htmlspecialchars($current_post['tags'] ?? ''); ?>"
                                        class="w-full px-5 py-4 rounded-2xl border-2 border-gray-400 bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 outline-none font-bold text-gray-800 transition-all shadow-sm">
                                </div>

                                <!-- Hidden Fields -->
                                <input type="hidden" name="translation_group"
                                    value="<?php echo htmlspecialchars($_GET['translation_group'] ?? ($current_post['translation_group'] ?? uniqid('group_', true))); ?>">
                                <input type="hidden" name="lang_code"
                                    value="<?php echo htmlspecialchars($current_post['lang_code'] ?? ($_GET['lang_code'] ?? $_curr_lang)); ?>">

                                <!-- Submit -->
                            </div>
                        </div>

                        <!-- Submit Button (Full Width Bottom) -->
                        <div class="pt-8 border-t-2 border-gray-100">
                            <button type="submit"
                                class="w-full bg-emerald-600 text-white py-6 rounded-2xl font-black hover:bg-emerald-700 shadow-2xl shadow-emerald-200 hover:shadow-emerald-300 transition-all uppercase tracking-widest text-xl flex items-center justify-center gap-3 group">
                                <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Post
                            </button>
                        </div>
                </div>
            </div>
            </form>
            </div>
        <?php endif; ?>
        </div>
        <!-- Category Modal -->
        <div id="categoryModal" class="fixed inset-0 z-50 hidden">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop">
            </div>

            <!-- Modal Content -->
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md transform scale-95 opacity-0 transition-all duration-300 relative z-10 p-8"
                    id="modalPanel">
                    <div class="text-center mb-6">
                        <div
                            class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900">New Category</h3>
                        <p class="text-sm text-gray-500 mt-2">Create a new category for your posts.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-900 uppercase tracking-widest mb-2">Category
                                Name</label>
                            <input type="text" id="newCategoryInput" placeholder="e.g. Travel, Tech, Life"
                                class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-50 outline-none font-bold text-gray-900 transition-all">
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <button type="button" onclick="closeCategoryModal()"
                                class="w-full py-4 rounded-xl font-bold text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-all">
                                Cancel
                            </button>
                            <button type="button" onclick="saveNewCategory()"
                                class="w-full py-4 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all flex items-center justify-center gap-2">
                                <span>Create</span>
                                <svg id="catLoading" class="hidden animate-spin h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
        <script>
            // Modal Logic
            const modal = document.getElementById('categoryModal');
            const backdrop = document.getElementById('modalBackdrop');
            const panel = document.getElementById('modalPanel');
            const input = document.getElementById('newCategoryInput');

            function quickAddCategory() {
                modal.classList.remove('hidden');
                // Animate In
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                    input.focus();
                }, 10);
            }

            function closeCategoryModal() {
                // Animate Out
                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    input.value = '';
                }, 300);
            }

            function saveNewCategory() {
                const name = input.value.trim();
                if (!name) return;

                const btn = document.querySelector('#categoryModal button[onclick="saveNewCategory()"]');
                const loader = document.getElementById('catLoading');
                const btnText = btn.querySelector('span');

                // Loading State
                btn.disabled = true;
                loader.classList.remove('hidden');
                btnText.textContent = 'Saving...';

                // AJAX Request
                const formData = new FormData();
                formData.append('action', 'add_category');
                formData.append('name', name);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Add to dropdown
                            const select = document.getElementById('categorySelect');
                            const option = document.createElement('option');
                            option.text = data.name;
                            option.value = data.name;
                            option.selected = true;
                            select.add(option);

                            closeCategoryModal();
                        } else {
                            alert('Error: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(err => {
                        alert('Network error occurred.');
                        console.error(err);
                    })
                    .finally(() => {
                        // Reset State
                        btn.disabled = false;
                        loader.classList.add('hidden');
                        btnText.textContent = 'Create';
                    });
            }

            // Close on backdrop click
            backdrop.addEventListener('click', closeCategoryModal);

            tinymce.init({
                selector: '#contentEditor',
                plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
                menubar: 'file edit view insert format tools table help',
                toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
                toolbar_sticky: true,
                height: 600,
                image_caption: true,
                quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
                noneditable_noneditable_class: 'mceNonEditable',
                contextmenu: 'link image imagetools table',
                promotion: false,
                branding: false,
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        </script>
</body>

</html>