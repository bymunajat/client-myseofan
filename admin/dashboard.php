<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$settings = getSiteSettings($pdo);

// Fetch Stats
$postCount = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE lang_code = 'en'")->fetchColumn();
$pageCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE lang_code = 'en'")->fetchColumn();
$pageCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE lang_code = 'en'")->fetchColumn();
$logCount = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$recentPosts = $pdo->query("SELECT title, created_at FROM blog_posts WHERE lang_code = 'en' ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MySeoFan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
            /* Slate 900 - Dark Mode Background */
            color: #f8fafc;
            /* Slate 50 Text */
            min-height: 100vh;
        }
    </style>
</head>

<body class="flex">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 min-h-screen bg-[#0f172a]">
        <header
            class="bg-[#1e293b] border-b-4 border-fuchsia-500/50 px-8 h-20 flex items-center justify-between shadow-lg shadow-black/20">
            <div>
                <h3 class="text-xl font-bold text-white">Overview</h3>
                <p class="text-xs text-gray-400 mt-0.5">Quick stats and recent activity</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-400">Hello,
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <div
                    class="w-10 h-10 bg-slate-800 text-fuchsia-400 border border-fuchsia-500/30 rounded-full flex items-center justify-center font-bold shadow-inner">
                    A</div>
            </div>
        </header>

        <div class="p-8">
            <!-- Stats -->
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:border-fuchsia-200 transition-all">
                    <p class="text-sm text-gray-500 font-medium mb-1">Blog Posts</p>
                    <h4
                        class="text-3xl font-black bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 bg-clip-text text-transparent">
                        <?php echo $postCount; ?>
                    </h4>
                </div>
                <div
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:border-blue-200 transition-all">
                    <p class="text-sm text-gray-500 font-medium mb-1">Static Pages</p>
                    <h4 class="text-3xl font-bold text-blue-600"><?php echo $pageCount; ?></h4>
                </div>
                <div
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:border-orange-200 transition-all">
                    <p class="text-sm text-gray-500 font-medium mb-1">System Activities</p>
                    <h4 class="text-3xl font-bold text-orange-500"><?php echo $logCount; ?></h4>
                </div>
                <div
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:border-gray-300 transition-all">
                    <p class="text-sm text-gray-500 font-medium mb-1">PHP Version</p>
                    <h4 class="text-3xl font-bold text-gray-800"><?php echo phpversion(); ?></h4>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-10">
                <!-- Welcome Card -->
                <!-- Welcome Card -->
                <div
                    class="md:col-span-2 bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-fuchsia-200">
                    <div class="relative z-10">
                        <h2 class="text-3xl font-bold mb-4">Welcome to Your CMS</h2>
                        <p class="opacity-90 leading-relaxed max-w-md">
                            Your content platform is now fully dynamic. Manage translations, articles, and SEO from one
                            centralized hub.
                        </p>
                        <div class="flex gap-4 mt-8">
                            <a href="../index.php" target="_blank"
                                class="px-6 py-3 bg-white text-fuchsia-600 rounded-xl font-bold hover:bg-gray-100 transition-all">
                                Live Site
                            </a>
                            <a href="blog.php?action=add"
                                class="px-6 py-3 bg-fuchsia-500 text-white rounded-xl font-bold hover:bg-fuchsia-400 transition-all border border-fuchsia-400/50">
                                New Post
                            </a>
                        </div>
                    </div>
                    <div
                        class="absolute -right-20 -bottom-20 w-80 h-80 bg-fuchsia-500 rounded-full opacity-50 blur-3xl">
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-[#1e293b] p-8 rounded-[2.5rem] shadow-lg border border-slate-700/50">
                    <h4 class="text-lg font-bold mb-6 text-white border-b border-slate-700 pb-4">Recent Blog Posts</h4>
                    <div class="space-y-4">
                        <?php foreach ($recentPosts as $p): ?>
                            <div class="flex items-start gap-4 pb-4 border-b border-slate-700 last:border-0 last:pb-0">
                                <div
                                    class="w-2 h-2 mt-2 bg-gradient-to-r from-violet-500 to-fuchsia-500 rounded-full shadow-[0_0_10px_rgba(232,121,249,0.5)]">
                                </div>
                                <div>
                                    <p
                                        class="font-bold text-gray-200 line-clamp-1 hover:text-fuchsia-400 transition-colors cursor-default">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($recentPosts)): ?>
                            <p class="text-gray-500 text-sm italic">No posts yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>