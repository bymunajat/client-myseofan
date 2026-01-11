<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$settings = getSiteSettings($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = $_POST['site_name'] ?? '';
    $header_code = $_POST['header_code'] ?? '';
    $footer_code = $_POST['footer_code'] ?? '';

    // Handle Uploads
    $logo_path = $settings['logo_path'];
    $favicon_path = $settings['favicon_path'];

    if (!empty($_FILES['logo']['name'])) {
        $logo_path = 'uploads/' . time() . '_' . $_FILES['logo']['name'];
        move_uploaded_file($_FILES['logo']['tmp_name'], '../' . $logo_path);
    }
    if (!empty($_FILES['favicon']['name'])) {
        $favicon_path = 'uploads/' . time() . '_' . $_FILES['favicon']['name'];
        move_uploaded_file($_FILES['favicon']['tmp_name'], '../' . $favicon_path);
    }

    if ($pdo) {
        $stmt = $pdo->prepare("UPDATE site_settings SET site_name = ?, logo_path = ?, favicon_path = ?, header_code = ?, footer_code = ? WHERE id = 1");
        if ($stmt->execute([$site_name, $logo_path, $favicon_path, $header_code, $footer_code])) {
            $message = 'Settings updated successfully!';
            $settings = getSiteSettings($pdo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Site Settings - MySeoFan Admin</title>
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
    <!-- Sidebar -->
    <aside class="w-64 sidebar hidden md:block">
        <div class="p-8">
            <h2 class="text-xl font-bold text-emerald-500">MySeoFan Admin</h2>
        </div>
        <nav class="mt-4 px-4 space-y-2">
            <a href="dashboard.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="profile.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Admin Profile</span>
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-active text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Site Settings</span>
            </a>
            <a href="seo.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>SEO Manager</span>
            </a>
            <a href="media.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Media Library</span>
            </a>
            <a href="translations.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 11.37 9.19 15.683 3 20" />
                </svg>
                <span>Translations</span>
            </a>
            <a href="blog.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h3" />
                </svg>
                <span>Blog Posts</span>
            </a>
            <a href="pages.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>Page Manager</span>
            </a>
            <a href="logout.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-12 overflow-y-auto">
        <h1 class="text-3xl font-bold mb-8">Site Settings</h1>

        <?php if ($message): ?>
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 max-w-4xl">
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Website Name</label>
                        <input type="text" name="site_name"
                            value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Logo</label>
                        <input type="file" name="logo"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <?php if (!empty($settings['logo_path'])): ?>
                            <img src="../<?php echo $settings['logo_path']; ?>" class="mt-2 h-10">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Favicon</label>
                        <input type="file" name="favicon"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <?php if (!empty($settings['favicon_path'])): ?>
                            <img src="../<?php echo $settings['favicon_path']; ?>" class="mt-2 h-8">
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Header Code Injection (e.g. Google
                        Analytics)</label>
                    <textarea name="header_code" rows="4"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-emerald-500 font-mono text-sm"><?php echo htmlspecialchars($settings['header_code'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Footer Code Injection</label>
                    <textarea name="footer_code" rows="4"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-emerald-500 font-mono text-sm"><?php echo htmlspecialchars($settings['footer_code'] ?? ''); ?></textarea>
                </div>

                <button type="submit"
                    class="bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">
                    Save Changes
                </button>
            </form>
        </div>
    </main>
</body>

</html>