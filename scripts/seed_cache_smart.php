<?php
// scripts/seed_cache_smart.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/Translator.php';

// Disable time limit for long-running translation
set_time_limit(0);

echo "Starting Smart Cache Seeder...\n";

// 1. Define Master Strings (from index.php & global UI)
$strings = [
    // General
    'Home',
    'Blog',
    'Features',
    'FAQ',
    'Contact',
    'Language',
    'Follow Us',
    'All rights reserved',
    'Download',
    'Paste',
    'Back to Home',
    'Read More',
    'Read Story',
    'Latest Articles',
    'View All Articles',
    'No articles found yet',
    'New articles are being prepared. Stay tuned!',
    'Instagram Insights',
    'Expert tips, tricks, and updates on Instagram media preservation and creative archiving.',

    // Index.php Content
    'Instagram Downloader - Download Videos, Photos, Reels & IGTV',
    'Instagram Downloader',
    'Download Instagram Videos, Photos, Reels, IGTV & carousel',
    'Insert instagram link here',
    'Instagram Videos and Photos Download',
    'MySeoFan is an online web tool that helps you download Instagram Videos, Photos, Reels, and IGTV. MySeoFan.app is designed to be easy to use on any device, such as a mobile phone, tablet, or computer.',
    'How to download from Instagram?',
    'You must follow these three easy steps to download video, reels, and photo from Instagram (IG, Insta). Follow the simple steps below.',
    'Copy the URL',
    'Open the Instagram application or website, copy the URL of the photo, video, reels, carousel, IGTV.',
    'Paste the link',
    'Return to the MySeoFan website, paste the link into the input field and click the "Download" button.',
    'Quickly you will get the results with several quality options. Download what fits your needs.',
    'Choose MySeoFan.app for download from Instagram',
    'Downloading videos from Instagram in just two clicks is possible without compromising on quality. Avoid using unreliable applications and appreciate the videos, even if they are of lower quality.',
    'Fast download',
    'Our servers are optimized to provide you with the fastest download speeds.',
    'Support for all devices',
    "Whether you're on a mobile, tablet, or desktop, MySeoFan has got you covered.",
    'High quality',
    'Download Instagram content in its original quality without any loss.',
    'Security',
    'We prioritize your privacy. No login required and all downloads are processed securely.',
    'Fetching media content...',

    // Tools
    'Video Downloader',
    'Photos Downloader',
    'Reels Downloader',
    'IGTV Downloader',
    'Carousel / Album Downloader',

    // Feature Descriptions
    'MySeoFan.app supports Instagram video download for singular videos and multiple videos from carousel. MySeoFan is created to enable you to download IG videos from your personal page.',
    'Instagram photo download provided by MySeoFan.app is a great tool for saving images from Instagram posts. With MySeoFan, you can download a single post image and multiple Instagram photos (carousel).',
    'Reels is a new video format that clones the principle of TikTok. Instagram Reels download with the help of MySeoFan. Our Instagram Reels downloader can help you to save your favorite Reels videos.',
    "IGTV is a long video type. If you can't watch it now, you can download IGTV videos to your device to be sure that you can return to watching later, without the need to be online or in case the IGTV can be deleted.",
    'Carousel, also known as Album or Gallery posts type with multiple photos, videos, or mixed content. If you need to download multiple photos from Instagram, the MySeoFan.app is the best to download gallery.',

    // FAQ
    'Frequently asked questions (FAQ)',
    'What exactly is MySeoFan.app?',
    'MySeoFan.app is a browser-based helper that lets you save public Instagram content - videos, photos, Reels, Stories, IGTV and carousels - to your own device for offline viewing. It works straight from the website; no software install is required.',
    'Is downloading from Instagram legal?',
    'Saving public posts for personal use is generally allowed, but copyright always belongs to the creator. Please keep the files private unless you have the owner\'s permission to share or reuse them.',
    'Do I need to log in or create an account?',
    'No. Just paste the Instagram link - there\'s no registration, no Instagram credentials, and no cookies that track you across the web.',
    'Can I grab content from private accounts?',
    'Sorry, no. We respect user privacy, so only public posts are accessible. If you can\'t view the post in a logged-out browser tab, MySeoFan.app can\'t fetch it either.'
];

// 2. Target Languages
$langs = ['id', 'es', 'fr', 'de', 'ja'];

$total = count($strings) * count($langs);
$current = 0;

echo "Found " . count($strings) . " strings to translate into " . count($langs) . " languages.\n";
echo "Total operations: $total\n\n";

foreach ($langs as $lang) {
    echo "Processing [$lang]... ";
    $count = 0;
    foreach ($strings as $text) {
        $current++;
        // Check if exists first to avoid API hit
        $hash = md5($text . $lang);
        $stmt = $pdo->prepare("SELECT 1 FROM translation_cache WHERE hash = ?");
        $stmt->execute([$hash]);

        if (!$stmt->fetch()) {
            // Translate
            Translator::translate($text, $lang);
            // Small delay to be polite to the API
            usleep(200000); // 200ms
            $count++;
            echo ".";
        }
    }
    echo " Done! ($count new)\n";
}

echo "\nAll translations seeded successfully! Frontend load should now be instant.\n";
?>