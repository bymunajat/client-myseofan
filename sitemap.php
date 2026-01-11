<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/db.php';

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Main Pages -->
    <url>
        <loc>
            <?php echo $base_url; ?>/
        </loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc>
            <?php echo $base_url; ?>/blog.php
        </loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>

    <!-- Dynamic Blog Posts -->
    <?php
    $stmt = $pdo->query("SELECT slug, lang_code, created_at FROM blog_posts");
    while ($post = $stmt->fetch()):
        ?>
        <url>
            <loc><?php echo $base_url; ?>/post.php?slug=<?php echo $post['slug']; ?>&amp;lang=<?php echo $post['lang_code']; ?></loc>
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
            <loc><?php echo $base_url; ?>/page.php?slug=<?php echo $page['slug']; ?>&amp;lang=<?php echo $page['lang_code']; ?></loc>
            <priority>0.5</priority>
        </url>
    <?php endwhile; ?>
</urlset>