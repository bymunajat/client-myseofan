<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$uploadsDir = '../uploads/';

// Handle Deletion
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $filePath = $uploadsDir . $file;
    if (file_exists($filePath) && is_file($filePath)) {
        if (unlink($filePath)) {
            $message = "File deleted successfully.";
        } else {
            $error = "Failed to delete file.";
        }
    }
}

// Get Files
$files = [];
if (is_dir($uploadsDir)) {
    $files = array_diff(scandir($uploadsDir), array('.', '..', '.gitignore'));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Media Library - MySeoFan Admin</title>
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
    <aside class="sidebar w-64 hidden md:block">
        <div class="p-8">
            <h2 class="text-xl font-bold text-emerald-500">MySeoFan Admin</h2>
        </div>
        <nav class="mt-4 px-4 space-y-2">
            <a href="dashboard.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><span>Dashboard</span></a>
            <a href="settings.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><span>Site
                    Settings</span></a>
            <a href="seo.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><span>SEO
                    Manager</span></a>
            <a href="media.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-active"><span>Media
                    Library</span></a>
            <a href="translations.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><span>Translations</span></a>
            <a href="blog.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><span>Blog
                    Posts</span></a>
            <a href="pages.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition-all text-gray-400 hover:text-white"><span>Page
                    Manager</span></a>
            <a href="logout.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-all"><span>Logout</span></a>
        </nav>
    </aside>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Media Library</h3>
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

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <?php foreach ($files as $f): ?>
                    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden group relative">
                        <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                            <?php
                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])): ?>
                                <img src="../uploads/<?php echo $f; ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                            <?php else: ?>
                                <span class="text-xs font-bold text-gray-400 uppercase">
                                    <?php echo $ext; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <p class="text-[10px] text-gray-400 truncate mb-2">
                                <?php echo htmlspecialchars($f); ?>
                            </p>
                            <a href="?delete=<?php echo urlencode($f); ?>" onclick="return confirm('Delete this file?')"
                                class="text-[10px] font-bold text-red-400 hover:text-red-600 uppercase">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($files)): ?>
                    <div
                        class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed text-gray-400 font-bold uppercase tracking-widest">
                        Library is empty
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>