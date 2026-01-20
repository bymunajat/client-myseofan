<?php
require_once __DIR__ . '/../includes/db.php';

$articles = [
    [
        'title' => "How to Download Instagram Reels for Free",
        'content' => "<p>Instagram Reels have become one of the most popular ways to consume short-form video content. Often, you might find a Reel so inspiring or funny that you want to save it to your device to watch later or share with friends who aren't on Instagram.</p>
                      <h3>Why Download Reels?</h3>
                      <ul>
                        <li>Offline viewing: Watch your favorite content anywhere.</li>
                        <li>Video editing: Use snippets for your own creative projects (with respect to copyright).</li>
                        <li>Sharing: Send videos directly via messaging apps.</li>
                      </ul>
                      <p>Using MySeoFan, downloading is simple. Just copy the link, paste it into our tool, and click download. It's fast, free, and keeps the original quality.</p>",
        'category' => "Tutorial",
        'tags' => "instagram, reels, downloader, guide",
        'excerpt' => "Learn the easiest and fastest way to download Instagram Reels to your mobile or desktop for free."
    ],
    [
        'title' => "The Best Instagram Video Downloader Apps in 2024",
        'content' => "<p>Finding a reliable Instagram video downloader can be tricky with so many options available online. In 2024, the best tools are those that offer speed, high quality, and privacy.</p>
                      <p>Tools like MySeoFan stand out because they don't require you to login to your Instagram account, ensuring your data stays safe while you get the content you need.</p>
                      <h3>Top Features to Look For:</h3>
                      <ol>
                        <li>No Login Required.</li>
                        <li>High Resolution (HD/4K) downloads.</li>
                        <li>Compatibility with all devices.</li>
                      </ol>",
        'category' => "News",
        'tags' => "apps, 2024, instagram, video",
        'excerpt' => "A review of the top-performing Instagram video downloader tools and why MySeoFan is a leading choice."
    ],
    [
        'title' => "How to Save High-Quality Photos from Instagram",
        'content' => "<p>Sometimes a single image is worth a thousand words. Instagram is home to professional photographers and artists sharing breathtaking work. Saving these photos in high resolution can be a challenge since Instagram doesn't provide a direct \"Save Image\" option.</p>
                      <p>Our Photo Downloader tool allows you to grab the original file size from any public Instagram post. Simply copy the post URL and let our system do the rest.</p>",
        'category' => "Tutorial",
        'tags' => "photos, instagram, hd, save",
        'excerpt' => "Stop taking screenshots! Discover how to download high-resolution photos from Instagram without losing quality."
    ],
    [
        'title' => "The Ultimate Guide to Downloading Instagram Stories",
        'content' => "<p>Stories are ephemeral, lasting only 24 hours. But what if you want to keep a memory or a piece of useful information shared in a story? Downloading them is the answer.</p>
                      <p>MySeoFan's Story Downloader lets you watch and save stories anonymously. This means you can keep the content without the uploader knowing you've viewed or saved it.</p>",
        'category' => "Tips",
        'tags' => "stories, instagram, guide, tips",
        'excerpt' => "Never miss a story again. Learn how to download and save Instagram Stories anonymously and quickly."
    ],
    [
        'title' => "Why You Should Use MySeoFan for Instagram Media",
        'content' => "<p>MySeoFan is designed with the user in mind. We prioritize speed, simplicity, and most importantly, security. Unlike many other tools, we don't track your downloads or ask for your social media credentials.</p>
                      <p>Whether it's a Video, Reel, Photo, or IGTV, our multi-functional downloader handles it all. Plus, it's optimized for SEO, making sure you get the fastest experience possible.</p>",
        'category' => "General",
        'tags' => "myseofan, features, safety, downloader",
        'excerpt' => "Discover the unique benefits of using MySeoFan for all your Instagram media downloading needs."
    ],
    [
        'title' => "Tips for Growing Your Instagram Following in 2024",
        'content' => "<p>Growing on Instagram in 2024 requires a mix of high-quality content, consistent posting, and smart engagement strategies. Reels are currently the best way to get discovered by new audiences.</p>
                      <p>Collaborations and using trending audio can also boost your reach significantly. Don't forget to engage with your followers to build a loyal community.</p>",
        'category' => "Tips",
        'tags' => "growth, 2024, followers, engagement",
        'excerpt' => "Master the latest strategies to grow your Instagram audience and increase your engagement in 2024."
    ],
    [
        'title' => "How to Backup Your Instagram Profile Data",
        'content' => "<p>Your Instagram profile is a digital diary. Losing access to it can be devastating. Regularly backing up your data is a essential habit for every social media user.</p>
                      <p>While Instagram offers a built-in download tool, using third-party saves for specific high-value posts can ensure you have multiple copies of your most important content.</p>",
        'category' => "Tutorial",
        'tags' => "backup, security, data, instagram",
        'excerpt' => "Step-by-step guide on how to protect your digital legacy by backing up your Instagram account data."
    ],
    [
        'title' => "Understanding Instagram's Copyright Rules for Creators",
        'content' => "<p>As a creator, understanding copyright is vital. Downloading content is often fine for personal use, but re-uploading someone else's work without permission can lead to account bans or legal issues.</p>
                      <p>Always give credit to the original creators and, whenever possible, ask for permission before using their content for commercial purposes.</p>",
        'category' => "General",
        'tags' => "copyright, legal, creators, instagram",
        'excerpt' => "A comprehensive look at Instagram's copyright policies and how to stay safe while sharing and downloading."
    ],
    [
        'title' => "How to Download Carousels from Instagram",
        'content' => "<p>Carousel posts are great for storytelling, but they can be harder to download since they contain multiple images or videos. MySeoFan simplifies this by detecting all media in a carousel post.</p>
                      <p>You can choose to download all items at once or pick only the ones you like. It's the most flexible way to manage multi-item posts.</p>",
        'category' => "Tutorial",
        'tags' => "carousel, multi-post, downloader, guide",
        'excerpt' => "Learn how to easily download all images and videos from an Instagram carousel post in one go."
    ],
    [
        'title' => "Managing Your Instagram Reach and Engagement",
        'content' => "<p>Reach is the number of unique eyes on your content, while engagement is how people interact with it. Both are crucial for success on the platform.</p>
                      <p>Analyzing your insights can help you understand what works and what doesn't. Focus on quality over quantity to keep your audience coming back for more.</p>",
        'category' => "Tips",
        'tags' => "reach, engagement, metrics, marketing",
        'excerpt' => "A deep dive into Instagram metrics and how to improve your content strategy for better results."
    ]
];

echo "Starting blog seeding...\n";

foreach ($articles as $art) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $art['title'])));

    // Check if already exists
    $check = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetch()) {
        echo "Skipping: " . $art['title'] . " (Already exists)\n";
        continue;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, lang_code, meta_title, meta_description, category, status, tags, translation_group, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $group = uniqid('group_', true);
        $stmt->execute([
            $art['title'],
            $slug,
            $art['content'],
            $art['excerpt'],
            'en',
            $art['title'] . " - MySeoFan Blog",
            $art['excerpt'],
            $art['category'],
            'published',
            $art['tags'],
            $group,
            1 // Admin
        ]);

        echo "Seeded: " . $art['title'] . "\n";
    } catch (Exception $e) {
        echo "Error seeding " . $art['title'] . ": " . $e->getMessage() . "\n";
    }
}

echo "Blog seeding complete!\n";
