<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$settings = getSiteSettings($pdo);

// Fetch Stats
$postCount = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$pageCount = $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
$transCount = $pdo->query("SELECT COUNT(*) FROM translations")->fetchColumn();
$recentPosts = $pdo->query("SELECT title, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
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
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b-4 border-emerald-300 px-8 h-20 flex items-center justify-between shadow-sm">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Overview</h3>
                <p class="text-xs text-gray-500 mt-0.5">Quick stats and recent activity</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-500">Hello,
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <div
                    class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center font-bold">
                    A</div>
            </div>
        </header>

        <div class="p-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium mb-1">Blog Posts</p>
                    <h4 class="text-3xl font-bold text-emerald-600"><?php echo $postCount; ?></h4>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium mb-1">Static Pages</p>
                    <h4 class="text-3xl font-bold text-blue-600"><?php echo $pageCount; ?></h4>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium mb-1">Translations</p>
                    <h4 class="text-3xl font-bold text-purple-600"><?php echo $transCount; ?></h4>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium mb-1">PHP Version</p>
                    <h4 class="text-3xl font-bold text-gray-800"><?php echo phpversion(); ?></h4>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-10">
                <!-- Welcome Card -->
                <div
                    class="md:col-span-2 bg-emerald-600 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-emerald-200">
                    <div class="relative z-10">
                        <h2 class="text-3xl font-bold mb-4">Welcome to Your CMS</h2>
                        <p class="opacity-90 leading-relaxed max-w-md">
                            Your content platform is now fully dynamic. Manage translations, articles, and SEO from one
                            centralized hub.
                        </p>
                        <div class="flex gap-4 mt-8">
                            <a href="../index.php" target="_blank"
                                class="px-6 py-3 bg-white text-emerald-600 rounded-xl font-bold hover:bg-gray-100 transition-all">
                                Live Site
                            </a>
                            <a href="blog.php?action=add"
                                class="px-6 py-3 bg-emerald-500 text-white rounded-xl font-bold hover:bg-emerald-400 transition-all border border-emerald-400/50">
                                New Post
                            </a>
                        </div>
                    </div>
                    <div
                        class="absolute -right-20 -bottom-20 w-80 h-80 bg-emerald-500 rounded-full opacity-50 blur-3xl">
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <h4 class="text-lg font-bold mb-6">Recent Blog Posts</h4>
                    <div class="space-y-4">
                        <?php foreach ($recentPosts as $p): ?>
                            <div class="flex items-start gap-4 pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                                <div class="w-2 h-2 mt-2 bg-emerald-500 rounded-full"></div>
                                <div>
                                    <p class="font-bold text-gray-800 line-clamp-1">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($recentPosts)): ?>
                            <p class="text-gray-400 text-sm">No posts yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>