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
    $_SESSION['last_cat_lang'] = $_curr_lang;
} elseif (isset($_SESSION['last_cat_lang'])) {
    $_curr_lang = $_SESSION['last_cat_lang'];
} else {
    $_curr_lang = 'en';
}

if (!array_key_exists($_curr_lang, $available_langs))
    $_curr_lang = 'en';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    if (empty($slug))
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $lang = $_POST['lang_code'] ?? 'en';

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, lang_code) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $lang]);
            $message = "Category added successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, lang_code=? WHERE id=?");
            $stmt->execute([$name, $slug, $lang, $id]);
            $message = "Category updated successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
        $message = "Category deleted.";
    } catch (\Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
    $action = 'list';
}

// Fetch Data
$categories = [];
$current_cat = null;
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE lang_code = ? ORDER BY name ASC");
    $stmt->execute([$_curr_lang]);
    $categories = $stmt->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$id]);
    $current_cat = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f3f4f6; min-height: 100vh; }
        .sidebar { height: 100vh; background: #111827; color: white; }
        .nav-active { background: #047857; border-left: 4px solid #34d399; }
    </style>
</head>
<body class="flex bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b px-8 h-20 flex items-center justify-between shadow-sm">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Post Categories</h3>
                <p class="text-xs text-gray-500 mt-0.5">Organize your content</p>
            </div>
            <a href="?action=add&lang_code=<?php echo $_curr_lang; ?>" class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition-all">+ New Category</a>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium border border-emerald-100"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium border border-red-100"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="flex gap-8">
                <!-- List View (Left) -->
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2 scrollbar-thin">
                        <?php foreach ($available_langs as $code => $l): ?>
                            <a href="?filter_lang=<?php echo $code; ?>" class="px-5 py-2.5 rounded-2xl font-bold transition-all flex items-center gap-2 whitespace-nowrap shadow-sm <?php echo $_curr_lang === $code ? 'bg-emerald-600 text-white shadow-emerald-200' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-100'; ?>">
                                <span class="text-xs uppercase"><?php echo $code; ?></span>
                                <span><?php echo $l['label']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest">Name</th>
                                    <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest">Slug</th>
                                    <th class="px-8 py-4 text-xs font-black text-gray-700 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 font-bold text-gray-900">
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="3" class="px-8 py-10 text-center text-gray-400">No categories found for this language.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-8 py-4"><?php echo htmlspecialchars($cat['name']); ?></td>
                                        <td class="px-8 py-4 text-xs font-mono text-gray-500"><?php echo htmlspecialchars($cat['slug']); ?></td>
                                        <td class="px-8 py-4 text-right space-x-3">
                                            <a href="?action=edit&id=<?php echo $cat['id']; ?>" class="text-emerald-600 hover:underline">Edit</a>
                                            <a href="javascript:void(0);" onclick="confirmDelete('?action=delete&id=<?php echo $cat['id']; ?>', 'Deleting this category will not delete the posts using it.')" class="text-red-400 hover:text-red-600">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Form View (Right) -->
                <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="w-96">
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-emerald-100 flex-shrink-0 sticky top-28">
                        <h4 class="text-lg font-black text-gray-900 mb-6"><?php echo $action === 'add' ? 'Add New Category' : 'Edit Category'; ?></h4>
                        <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST" class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Language</label>
                                <select name="lang_code" class="w-full px-5 py-3 rounded-xl border-2 border-gray-100 focus:border-emerald-600 outline-none font-bold text-gray-900 transition-all">
                                    <?php foreach ($available_langs as $code => $l): ?>
                                        <option value="<?php echo $code; ?>" <?php echo (isset($current_cat['lang_code']) && $current_cat['lang_code'] == $code) || ($_curr_lang == $code) ? 'selected' : ''; ?>><?php echo $l['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Category Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($current_cat['name'] ?? ''); ?>" required placeholder="e.g. Tips & Tricks" class="w-full px-5 py-3 rounded-xl border-2 border-gray-100 focus:border-emerald-600 outline-none font-bold text-gray-900 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Slug (Auto if empty)</label>
                                <input type="text" name="slug" value="<?php echo htmlspecialchars($current_cat['slug'] ?? ''); ?>" placeholder="tips-tricks" class="w-full px-5 py-3 rounded-xl border-2 border-gray-100 focus:border-emerald-600 outline-none font-mono font-bold text-gray-900 transition-all">
                            </div>
                            <div class="pt-4 flex gap-3">
                                <button type="submit" class="flex-1 bg-emerald-600 text-white py-3 rounded-xl font-black hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200 uppercase tracking-widest text-[10px]">Save Category</button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="?action=list" class="px-5 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition-all flex items-center justify-center text-[10px] uppercase tracking-widest">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
