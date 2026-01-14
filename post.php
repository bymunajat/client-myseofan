<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

// 1. Fetch the requested post by slug (Slug is Unique)
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    // 404 - Redirect to blog
    header("Location: blog.php?lang=$lang");
    exit;
}

// 2. Check if the post's language matches the requested language
if ($post['lang_code'] !== $lang) {
    // Mismatch! We have the "English" (or source) slug, but want "ID" lang.

    // A. Check if a translation already exists in the same group
    $group_id = $post['translation_group'];

    // Ensure the current post has a group ID (Data repair)
    if (empty($group_id)) {
        $group_id = uniqid('group_', true);
        $pdo->prepare("UPDATE blog_posts SET translation_group = ? WHERE id = ?")->execute([$group_id, $post['id']]);
        $post['translation_group'] = $group_id;
    }

    $stmt = $pdo->prepare("SELECT slug FROM blog_posts WHERE translation_group = ? AND lang_code = ?");
    $stmt->execute([$group_id, $lang]);
    $existing_translation = $stmt->fetch();

    if ($existing_translation) {
        // Found! Redirect to the correct slug for this language
        header("Location: post.php?slug=" . $existing_translation['slug'] . "&lang=" . $lang);
        exit;
    } else {
        // B. Not found? AUTO-TRANSLATE and SAVE (The Magic Step)

        // Only translate if we have a source post (which we do, $post)
        // AND if the source is English (to keep quality high) OR we just translate from whatever we have.
        // Let's assume we translate from the current $post content.

        $new_title = Translator::translate($post['title'], $lang);
        $new_content = Translator::translate($post['content'], $lang);
        $new_meta_title = Translator::translate($post['meta_title'] ?? $post['title'], $lang);
        $new_meta_desc = Translator::translate($post['meta_description'] ?? '', $lang);

        // Generate new slug
        $new_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_title)));
        if (empty($new_slug))
            $new_slug = $post['slug'] . '-' . $lang;

        // Ensure unique slug
        $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
        $checkSlug->execute([$new_slug]);
        if ($checkSlug->fetchColumn() > 0) {
            $new_slug .= '-' . $lang . '-' . time();
        }

        try {
            // Insert the new translated post
            $stmtInsert = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, thumbnail, lang_code, meta_title, meta_description, translation_group, author_id, excerpt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $new_title,
                $new_slug,
                $new_content,
                $post['thumbnail'],
                $lang,
                $new_meta_title,
                $new_meta_desc,
                $group_id,
                $post['author_id'] ?? 1, // Default to admin if null
                Translator::translate($post['excerpt'] ?? '', $lang)
            ]);

            // Redirect to the new baby
            header("Location: post.php?slug=" . $new_slug . "&lang=" . $lang);
            exit;

        } catch (\Exception $e) {
            // If save fails, just show the auto-translated content on the fly (Fallback)
            // But don't redirect, just render below
            $post['title'] = $new_title;
            $post['content'] = $new_content;
            $post['lang_code'] = $lang;
        }
    }
}

$settings = getSiteSettings($pdo);

// Helper function for auto-translation
if (!function_exists('__')) {
    function __($text, $lang)
    {
        return Translator::translate($text, $lang);
    }
}

$t = [
    'back' => __('Back to Insights', $lang),
];

$headerItems = getMenuTree($pdo, 'header', $lang);
$footerItems = getMenuTree($pdo, 'footer', $lang);

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
    <?php echo $seoHelper->getHreflangTags(); ?>
    <?php if (!empty($settings['favicon_path'])): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($settings['favicon_path']); ?>">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #ffffff;
            color: #1a1a1a;
            line-height: 1.8;
        }

        .glass-header {
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .logo-text {
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .article-hero {
            padding: 140px 0 80px;
            background: #f8fafc;
        }

        .article-content h2 {
            font-size: 2.25rem;
            font-weight: 900;
            margin-top: 4rem;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
            color: #1e293b;
        }

        .article-content p {
            margin-bottom: 2rem;
            font-size: 1.125rem;
            color: #475569;
        }

        .article-content img {
            border-radius: 2rem;
            margin: 3rem 0;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }

        .footer-brand {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .footer-logo-text {
            font-size: 2.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-logo-icon {
            color: #a855f7;
            width: 36px;
            height: 36px;
        }

        .footer-links-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 12px;
        }

        .footer-link {
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 700;
            transition: color 0.2s;
            text-decoration: none;
        }

        .footer-link:hover {
            color: #3b82f6;
        }

        .footer-divider {
            width: 100%;
            height: 1px;
            background: #e2e8f0;
            margin: 40px 0;
        }

        .copyright-text {
            text-align: center;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .social-label {
            text-align: center;
            color: #1e293b;
            font-size: 0.875rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 0.05em;
        }

        .back-link {
            color: #64748b;
            font-weight: 700;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #7c3aed;
        }
    </style>
    <?php echo $settings['header_code'] ?? ''; ?>
</head>

<body class="flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 py-4 glass-header">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
            <a href="index.php?lang=<?php echo $lang; ?>" class="logo-text">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-8 w-auto" alt="Logo">
                <?php else: ?>
                    <i data-lucide="layers" class="w-8 h-8 text-purple-600"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?>
            </a>

            <div class="flex items-center gap-6 text-white font-bold text-sm uppercase tracking-wider">
                <nav class="hidden lg:flex items-center gap-6">
                    <a href="video.php?lang=<?php echo $lang; ?>"
                        class="text-white hover:text-[#ec4899] transition-colors">Video</a>
                    <a href="photo.php?lang=<?php echo $lang; ?>"
                        class="text-white hover:text-[#ec4899] transition-colors">Photo</a>
                    <a href="reels.php?lang=<?php echo $lang; ?>"
                        class="text-white hover:text-[#ec4899] transition-colors">Reels</a>
                    <a href="igtv.php?lang=<?php echo $lang; ?>"
                        class="text-white hover:text-[#ec4899] transition-colors">IGTV</a>
                    <a href="carousel.php?lang=<?php echo $lang; ?>"
                        class="text-white hover:text-[#ec4899] transition-colors">Carousel</a>
                    <div class="w-px h-4 bg-white/20 mx-2"></div>
                    <?php foreach ($headerItems as $item): ?>
                        <a href="<?php echo htmlspecialchars($item['final_url']); ?>"
                            class="text-white hover:text-[#ec4899] transition-colors">
                            <?php echo htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="relative group cursor-pointer">
                    <div class="flex items-center gap-1 text-white hover:text-[#ec4899] transition-colors uppercase">
                        <?php echo $lang; ?> <i data-lucide="chevron-down" class="w-4 h-4 text-white/50"></i>
                    </div>
                    <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-50">
                        <div class="w-32 bg-slate-900 shadow-2xl rounded-xl p-2 border border-slate-800">
                            <?php foreach (['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'] as $code => $label): ?>
                                <a href="?lang=<?php echo $code; ?>"
                                    class="block px-4 py-2 text-xs hover:bg-slate-800 rounded-lg <?php echo $lang === $code ? 'text-[#ec4899] font-bold' : 'text-slate-200'; ?>">
                                    <?php echo $label; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <article class="flex-1">
        <header class="article-hero">
            <div class="max-w-4xl mx-auto px-6">
                <a href="blog.php?lang=<?php echo $lang; ?>" class="back-link">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <?php echo $t['back']; ?>
                </a>

                <div
                    class="flex items-center gap-4 mb-6 text-xs font-black uppercase tracking-widest text-purple-600 bg-purple-50 px-4 py-2 rounded-xl w-fit">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
                </div>

                <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-[1.1] mb-12">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>

                <?php if ($post['thumbnail']): ?>
                    <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>"
                        class="w-full h-auto rounded-[3rem] shadow-2xl"
                        alt="<?php echo htmlspecialchars($post['title']); ?>">
                <?php endif; ?>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-6 py-20">
            <div class="article-content prose max-w-none">
                <?php echo $post['content']; ?>
            </div>
        </div>
    </article>

    <!-- Footer -->
    <footer class="py-16 bg-white border-t border-slate-100 mt-auto">
        <div class="max-w-4xl mx-auto px-6">
            <div class="footer-brand">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-10 w-auto" alt="Logo">
                <?php else: ?>
                    <i data-lucide="layers" class="footer-logo-icon"></i>
                <?php endif; ?>
                <span
                    class="footer-logo-text"><?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?></span>
            </div>

            <?php foreach ($footerItems as $group): ?>
                <div class="footer-links-group">
                    <?php if (isset($group['children']) && !empty($group['children'])): ?>
                        <?php foreach ($group['children'] as $index => $item): ?>
                            <a href="<?php echo htmlspecialchars($item['final_url']); ?>" class="footer-link">
                                <?php echo htmlspecialchars($item['label']); ?>
                            </a>
                            <?php if ($index < count($group['children']) - 1): ?>
                                <span class="text-slate-200 px-1">|</span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="footer-divider"></div>

            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <span class="social-label mb-0">follow us:</span>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="instagram" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="facebook" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="youtube" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="twitter" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="music-2" class="w-5 h-5"></i></a>
                    </div>
                </div>
                <p class="copyright-text">© 2020-2026
                    <?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?>. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>