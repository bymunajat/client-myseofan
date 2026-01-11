<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';

$lang = $_GET['lang'] ?? 'en';
$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// Fallback logic
$defaults = [
    'en' => [
        'home' => 'Home',
        'blog' => 'Blog',
        'blog_title' => 'Latest <span class="text-emerald-600">Articles</span>',
        'blog_subtitle' => 'Tips, tricks, and updates about Instagram media archiving.',
        'read_more' => 'Read Story',
        'coming_soon' => 'Coming Soon'
    ],
    'id' => [
        'home' => 'Beranda',
        'blog' => 'Blog',
        'blog_title' => 'Artikel <span class="text-emerald-600">Terbaru</span>',
        'blog_subtitle' => 'Tips, trik, dan pembaruan tentang pengarsipan media Instagram.',
        'read_more' => 'Baca Artikel',
        'coming_soon' => 'Segera Hadir'
    ]
];
$t = array_merge($defaults[$lang] ?? $defaults['en'], $translations);

$seoHelper = new SEO_Helper($pdo, 'blog', $lang);

// Fetch posts for current language
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE lang_code = ? ORDER BY created_at DESC");
$stmt->execute([$lang]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seoHelper->getTitle(); ?></title>
    <meta name="description" content="<?php echo $seoHelper->getDescription(); ?>">
    <?php echo $seoHelper->getOGTags(); ?>
    <?php echo $seoHelper->getSchemaMarkup(); ?>
    <?php if (!empty($settings['favicon_path'])): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($settings['favicon_path']); ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <?php echo $settings['header_code'] ?? ''; ?>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #fafafa;
            color: #1a1a1a;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .blog-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .blog-card:hover {
            transform: translateY(-8px);
        }
    </style>
</head>

<body class="bg-gray-50/50">
    <!-- Navigation (Simplified from index) -->
    <header class="fixed top-0 left-0 right-0 z-50 glass-header">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php?lang=<?php echo $lang; ?>" class="flex items-center gap-3 group">
                <div class="flex items-center">
                    <?php if (!empty($settings['logo_path'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-10 w-auto">
                    <?php else: ?>
                        <div
                            class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200 group-hover:rotate-12 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2.5"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <span
                            class="ml-3 text-xl font-black tracking-tighter text-gray-800"><?php echo htmlspecialchars($settings['site_name']); ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <nav class="flex items-center gap-8">
                <a href="index.php?lang=<?php echo $lang; ?>"
                    class="text-sm font-bold text-gray-500 hover:text-emerald-600 transition-colors"><?php echo $t['home']; ?></a>
                <a href="blog.php?lang=<?php echo $lang; ?>"
                    class="text-sm font-bold text-emerald-600"><?php echo $t['blog']; ?></a>
            </nav>
        </div>
    </header>

    <main class="pt-32 pb-20 px-6">
        <div class="max-w-7xl mx-auto">
            <header class="mb-16 text-center">
                <h1 class="text-5xl md:text-6xl font-black tracking-tight mb-6"><?php echo $t['blog_title']; ?></h1>
                <p class="text-gray-500 font-medium max-w-2xl mx-auto text-lg leading-relaxed">
                    <?php echo $t['blog_subtitle']; ?>
                </p>
            </header>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach ($posts as $p): ?>
                    <article
                        class="blog-card bg-white rounded-[2.5rem] overflow-hidden border border-gray-100 shadow-sm flex flex-col h-full">
                        <div class="aspect-[16/10] bg-gray-100 relative group overflow-hidden">
                            <?php if ($p['thumbnail']): ?>
                                <img src="<?php echo htmlspecialchars($p['thumbnail']); ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-emerald-600/20">
                                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-8 flex flex-col flex-1">
                            <div
                                class="flex items-center gap-4 mb-4 text-xs font-black uppercase tracking-widest text-gray-400">
                                <span>
                                    <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                                </span>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <span class="text-emerald-600">
                                    <?php echo $p['lang_code']; ?>
                                </span>
                            </div>
                            <h2
                                class="text-2xl font-black mb-4 group-hover:text-emerald-600 transition-colors leading-tight">
                                <a href="post.php?slug=<?php echo $p['slug']; ?>&lang=<?php echo $lang; ?>">
                                    <?php echo htmlspecialchars($p['title']); ?>
                                </a>
                            </h2>
                            <div class="mt-auto pt-6 flex items-center justify-between">
                                <a href="post.php?slug=<?php echo $p['slug']; ?>&lang=<?php echo $lang; ?>"
                                    class="text-sm font-black text-gray-900 flex items-center gap-2 group/btn">
                                    <?php echo $t['read_more']; ?>
                                    <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if (empty($posts)): ?>
                <div class="text-center py-20 bg-white rounded-[3rem] border border-dashed border-gray-200">
                    <p class="text-gray-400 font-bold uppercase tracking-widest">
                        <?php echo $t['coming_soon']; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-gray-400 font-medium text-sm">&copy; <?php echo date('Y'); ?>
                <?php echo htmlspecialchars($settings['site_name']); ?>. Made for creators.
            </p>
        </div>
    </footer>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>