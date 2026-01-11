<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
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
    $thumbnail = $_POST['thumbnail'] ?? '';
    $lang = $_POST['lang_code'] ?? 'en';
    $m_title = $_POST['meta_title'] ?? '';
    $m_desc = $_POST['meta_description'] ?? '';
    $category = $_POST['category'] ?? 'General';

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, thumbnail, lang_code, meta_title, meta_description, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $thumbnail, $lang, $m_title, $m_desc, $category]);
            $message = "Post created successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, content=?, thumbnail=?, lang_code=?, meta_title=?, meta_description=?, category=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $thumbnail, $lang, $m_title, $m_desc, $category, $id]);
            $message = "Post updated successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
    $message = "Post deleted.";
    $action = 'list';
}

// Fetch Data
$posts = [];
$current_post = null;
if ($action === 'list') {
    $posts = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();
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

        .nav-active {
            background: #374151;
            border-left: 4px solid #10b981;
        }
    </style>
</head>

<body class="flex">
    <aside class="sidebar w-64 hidden md:block">
        <div class="p-8">
            <h2 class="text-xl font-bold text-emerald-500">MySeoFan Admin</h2>
        </div>
        <nav class="mt-4 px-4 space-y-2">
            <a href="dashboard.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><svg
                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg><span>Dashboard</span></a>
            <a href="profile.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><svg
                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg><span>Admin Profile</span></a>
            <a href="settings.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><svg
                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg><span>Site Settings</span></a>
            <a href="seo.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><svg
                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg><span>SEO Manager</span></a>
            <a href="media.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><svg
                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg><span>Media Library</span></a>
            <a href="blog.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-active"><svg class="w-5 h-5"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h3" />
                </svg><span>Blog Posts</span></a>
            <a href="pages.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><svg
                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg><span>Page Manager</span></a>
            <a href="logout.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-all"><svg
                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg><span>Logout</span></a>
        </nav>
    </aside>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Blog Management</h3>
            <?php if ($action === 'list'): ?>
                <a href="?action=add"
                    class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition-all">+
                    New Post</a>
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
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Title</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Category</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Lang</th>
                                <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($posts as $p): ?>
                                <tr>
                                    <td class="px-8 py-5 font-bold text-gray-800">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </td>
                                    <td class="px-8 py-5 text-gray-500 text-sm">
                                        <?php echo htmlspecialchars($p['category'] ?? 'General'); ?>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span
                                            class="px-2 py-1 text-[10px] font-black uppercase rounded <?php echo $p['lang_code'] == 'en' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600'; ?>">
                                            <?php echo $p['lang_code']; ?>
                                        </span>
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
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Post Title</label>
                                <input type="text" name="title"
                                    value="<?php echo htmlspecialchars($current_post['title'] ?? ''); ?>" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Slug (Auto if empty)</label>
                                <input type="text" name="slug"
                                    value="<?php echo htmlspecialchars($current_post['slug'] ?? ''); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Language</label>
                                <select name="lang_code"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                    <option value="en" <?php echo ($current_post['lang_code'] ?? '') == 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="id" <?php echo ($current_post['lang_code'] ?? '') == 'id' ? 'selected' : ''; ?>>Indonesia</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                <select name="category"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                    <option value="General" <?php echo ($current_post['category'] ?? '') == 'General' ? 'selected' : ''; ?>>General</option>
                                    <option value="Tutorial" <?php echo ($current_post['category'] ?? '') == 'Tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                                    <option value="News" <?php echo ($current_post['category'] ?? '') == 'News' ? 'selected' : ''; ?>>News</option>
                                    <option value="Tips" <?php echo ($current_post['category'] ?? '') == 'Tips' ? 'selected' : ''; ?>>Tips</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Thumbnail URL</label>
                                <input type="text" name="thumbnail"
                                    value="<?php echo htmlspecialchars($current_post['thumbnail'] ?? ''); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Content (HTML allowed)</label>
                            <textarea name="content" rows="10" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all"><?php echo htmlspecialchars($current_post['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="border-t pt-6 mt-6">
                            <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4">SEO Settings</h4>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($current_post['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label>
                                    <textarea name="meta_description" rows="2" id="meta_desc"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-emerald-500 outline-none transition-all"><?php echo htmlspecialchars($current_post['meta_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <!-- Google Preview -->
                            <div class="mt-6 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase mb-4">Google Search Preview</p>
                                <div class="max-w-md">
                                    <p class="text-[14px] text-[#202124] mb-1 truncate" id="preview_title">
                                        <?php echo $current_post['meta_title'] ?? 'Post Title - MySeoFan'; ?>
                                    </p>
                                    <p class="text-[12px] text-[#006621] mb-1 truncate">
                                        myseofan.com › blog › <?php echo $current_post['slug'] ?? 'post-slug'; ?>
                                    </p>
                                    <p class="text-[13px] text-[#545454] line-clamp-2" id="preview_desc">
                                        <?php echo $current_post['meta_description'] ?? 'Your post description will appear here in Google search results...'; ?>
                                    </p>
                                </div>
                            </div>
                            <script>
                                const titleInput = document.querySelector('input[name="meta_title"]');
                                const descInput = document.querySelector('textarea[name="meta_description"]');
                                const pTitle = document.getElementById('preview_title');
                                const pDesc = document.getElementById('preview_desc');

                                const updatePreview = () => {
                                    pTitle.textContent = titleInput.value || 'Post Title - MySeoFan';
                                    pDesc.textContent = descInput.value || 'Your post description will appear here in Google search results...';
                                };

                                titleInput.addEventListener('input', updatePreview);
                                descInput.addEventListener('input', updatePreview);
                            </script>
                        </div>
                        <button type="submit"
                            class="w-full bg-emerald-600 text-white py-4 rounded-xl font-bold hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all">Save
                            Post</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>