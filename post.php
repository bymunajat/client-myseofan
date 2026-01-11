<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

// Fetch post
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND lang_code = ?");
$stmt->execute([$slug, $lang]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: blog.php?lang=$lang");
    exit;
}

$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// Fallback logic
$defaults = [
    'en' => ['back' => '← Back'],
    'id' => ['back' => '← Kembali']
];
$t = array_merge($defaults[$lang] ?? $defaults['en'], $translations);

$seoHelper = new SEO_Helper($pdo, 'post', $lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $post['lang_code']; ?>">

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
            background: #fff;
            color: #1a1a1a;
            line-height: 1.8;
        }

        .prose h2 {
            font-size: 2rem;
            font-weight: 900;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
        }

        .prose p {
            margin-bottom: 1.5rem;
            font-size: 1.125rem;
            color: #374151;
            font-weight: 400;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <header class="fixed top-0 left-0 right-0 z-50 glass-header">
        <div class="max-w-4xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php?lang=<?php echo $lang; ?>" class="flex items-center gap-3">
                <div class="flex items-center">
                    <?php if (!empty($settings['logo_path'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-8 w-auto">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2.5"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <span
                            class="ml-2 text-lg font-black tracking-tighter"><?php echo htmlspecialchars($settings['site_name']); ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <a href="blog.php?lang=<?php echo $lang; ?>"
                class="text-sm font-bold text-gray-400 hover:text-emerald-600 transition-colors"><?php echo $t['back']; ?></a>
        </div>
    </header>

    <main class="pt-32 pb-20 px-6">
        <article class="max-w-3xl mx-auto">
            <header class="mb-12">
                <div class="flex items-center gap-4 mb-6 text-xs font-black uppercase tracking-widest text-emerald-600">
                    <span>
                        <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tight leading-tight mb-8">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>
                <?php if ($post['thumbnail']): ?>
                    <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>"
                        class="w-full rounded-[3rem] shadow-2xl shadow-emerald-900/10 mb-12">
                <?php endif; ?>
            </header>

            <div class="prose max-w-none">
                <?php echo $post['content']; // HTML allowed from Admin ?>
            </div>
        </article>
    </main>

    <footer class="bg-gray-50 border-t border-gray-100 py-12 mt-20">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <p class="text-gray-400 font-medium text-sm">&copy; <?php echo date('Y'); ?>
                <?php echo htmlspecialchars($settings['site_name']); ?>.</p>
        </div>
    </footer>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>