<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/db.php';

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Main Pages -->
    <url>
        <loc><?php echo $base_url . getUrl('/', 'en'); ?></loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc><?php echo $base_url . getUrl('blog', 'en'); ?></loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>

    <!-- Language Specific Home & Blog -->
    <?php foreach (['id', 'es', 'fr', 'de', 'ja'] as $l): ?>
        <url>
            <loc><?php echo $base_url . getUrl('/', $l); ?></loc>
            <priority>0.9</priority>
        </url>
        <url>
            <loc><?php echo $base_url . getUrl('blog', $l); ?></loc>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>

    <!-- Dynamic Blog Posts -->
    <?php
    $stmt = $pdo->query("SELECT slug, lang_code, created_at FROM blog_posts WHERE status = 'published'");
    while ($post = $stmt->fetch()):
        ?>
        <url>
            <loc><?php echo $base_url . getUrl($post['slug'], $post['lang_code'], 'blog'); ?></loc>
            <lastmod><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></lastmod>
            <priority>0.6</priority>
        </url>
    <?php endwhile; ?>

    <!-- Dynamic Static Pages -->
    <?php
    $stmt = $pdo->query("SELECT slug, lang_code FROM pages");
    while ($page = $stmt->fetch()):
        ?>
        <url>
            <loc><?php echo $base_url . getUrl($page['slug'], $page['lang_code'], 'page'); ?></loc>
            <priority>0.5</priority>
        </url>
    <?php endwhile; ?>
</urlset>