<?php
require_once 'includes/db.php';

// Organize existing pages into sections
$org = [
    'about-us' => 'Navigation',
    'tentang-kami' => 'Navigation',
    'contact-us' => 'Navigation',
    'hubungi-kami' => 'Navigation',
    'privacy-policy' => 'Legal',
    'kebijakan-privasi' => 'Legal',
    'terms-of-service' => 'Legal',
    'syarat-dan-ketentuan' => 'Legal'
];

foreach ($org as $slug => $section) {
    $stmt = $pdo->prepare("UPDATE pages SET footer_section = ? WHERE slug = ?");
    $stmt->execute([$section, $slug]);
}

echo "Footer sections organized! Your footer should now have multiple columns: Downloader, Navigation, and Legal.";
unlink(__FILE__);
