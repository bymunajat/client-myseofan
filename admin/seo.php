<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_id = $_POST['page_identifier'] ?? 'home';
    $lang = $_POST['lang_code'] ?? 'en';
    $title = $_POST['meta_title'] ?? '';
    $desc = $_POST['meta_description'] ?? '';
    $og_image = $_POST['og_image'] ?? '';
    $schema = $_POST['schema_markup'] ?? '';

    if ($pdo) {
        try {
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM seo_data WHERE page_identifier = ? AND lang_code = ?");
            $stmt->execute([$page_id, $lang]);
            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE seo_data SET meta_title = ?, meta_description = ?, og_image = ?, schema_markup = ? WHERE page_identifier = ? AND lang_code = ?");
                $stmt->execute([$title, $desc, $og_image, $schema, $page_id, $lang]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO seo_data (page_identifier, lang_code, meta_title, meta_description, og_image, schema_markup) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$page_id, $lang, $title, $desc, $og_image, $schema]);
            }
            $message = 'SEO data updated successfully for ' . strtoupper($lang) . '!';
        } catch (\Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all pages meta data
$pages = ['home', 'video', 'reels', 'image'];
$seo_data = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM seo_data");
    while ($row = $stmt->fetch()) {
        $seo_data[$row['page_identifier']][$row['lang_code']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SEO Manager - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
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
    <!-- Sidebar (Same as dashboard) -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">SEO Management</h3>
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

            <div class="space-y-8">
                <?php foreach ($pages as $page): ?>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                        <h2 class="text-2xl font-bold mb-6 capitalize text-emerald-600">Page:
                            <?php echo $page; ?>
                        </h2>

                        <div class="grid md:grid-cols-2 gap-10">
                            <!-- English Form -->
                            <form action="" method="POST" class="space-y-4">
                                <input type="hidden" name="page_identifier" value="<?php echo $page; ?>">
                                <input type="hidden" name="lang_code" value="en">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="text-xs font-black uppercase tracking-widest bg-emerald-100 text-emerald-600 px-2 py-1 rounded">English</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($seo_data[$page]['en']['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta
                                        Description</label>
                                    <textarea name="meta_description" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all"><?php echo htmlspecialchars($seo_data[$page]['en']['meta_description'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">OG Image URL</label>
                                    <input type="text" name="og_image"
                                        value="<?php echo htmlspecialchars($seo_data[$page]['en']['og_image'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Custom Schema
                                        (JSON-LD)</label>
                                    <textarea name="schema_markup" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all font-mono text-xs"><?php echo htmlspecialchars($seo_data[$page]['en']['schema_markup'] ?? ''); ?></textarea>
                                </div>
                                <button type="submit"
                                    class="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-emerald-600 transition-all">Update
                                    English</button>
                            </form>

                            <!-- Indonesia Form -->
                            <form action="" method="POST" class="space-y-4">
                                <input type="hidden" name="page_identifier" value="<?php echo $page; ?>">
                                <input type="hidden" name="lang_code" value="id">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="text-xs font-black uppercase tracking-widest bg-blue-100 text-blue-600 px-2 py-1 rounded">Indonesia</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        value="<?php echo htmlspecialchars($seo_data[$page]['id']['meta_title'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta
                                        Description</label>
                                    <textarea name="meta_description" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all"><?php echo htmlspecialchars($seo_data[$page]['id']['meta_description'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">OG Image URL</label>
                                    <input type="text" name="og_image"
                                        value="<?php echo htmlspecialchars($seo_data[$page]['id']['og_image'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Custom Schema
                                        (JSON-LD)</label>
                                    <textarea name="schema_markup" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all font-mono text-xs"><?php echo htmlspecialchars($seo_data[$page]['id']['schema_markup'] ?? ''); ?></textarea>
                                </div>
                                <button type="submit"
                                    class="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-emerald-600 transition-all">Update
                                    Indonesia</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>

</html>