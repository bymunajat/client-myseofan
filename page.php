<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

// Fetch page (ensure it matches the language too if possible, but schema has slugs per language)
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND lang_code = ?");
$stmt->execute([$slug, $lang]);
$page = $stmt->fetch();

// Try without lang filter if not found
if (!$page) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
}

if (!$page) {
    header("Location: index.php?lang=$lang");
    exit;
}

$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// Fallback logic
$defaults = [
    'en' => ['home' => 'Home', 'blog' => 'Blog'],
    'id' => ['home' => 'Beranda', 'blog' => 'Blog']
];
$t = array_merge($defaults[$lang] ?? $defaults['en'], $translations);

$seoHelper = new SEO_Helper($pdo, 'page', $lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $page['lang_code']; ?>">

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
            line-height: 1.8;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .content-card {
            background: white;
            border-radius: 3rem;
            border: 1px solid rgba(0, 0, 0, 0.03);
            shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
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
            <nav class="flex items-center gap-6">
                <a href="index.php?lang=<?php echo $lang; ?>"
                    class="text-sm font-bold text-gray-500 hover:text-emerald-600 transition-colors"><?php echo $t['home']; ?></a>
                <a href="blog.php?lang=<?php echo $lang; ?>"
                    class="text-sm font-bold text-gray-500 hover:text-emerald-600 transition-colors"><?php echo $t['blog']; ?></a>
            </nav>
        </div>
    </header>

    <main class="pt-32 pb-20 px-6">
        <div class="max-w-3xl mx-auto content-card p-10 md:p-16">
            <h1 class="text-3xl md:text-5xl font-black tracking-tight mb-10 border-b border-gray-100 pb-8">
                <?php echo htmlspecialchars($page['title']); ?>
            </h1>
            <div class="prose max-w-none text-gray-600">
                <?php echo $page['content']; ?>
            </div>
        </div>
    </main>

    <footer class="py-12 text-center text-gray-400 text-sm">
        &copy;
        <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name']); ?>.
    </footer>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>