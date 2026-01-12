<?php
// Script to apply soft green theme to all admin pages

$files = [
    'blog.php',
    'pages.php',
    'menus.php',
    'translations.php',
    'users.php',
    'profile.php',
    'media.php'
];

$oldStyle = "    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
        }

        .sidebar {
            height: 100vh;
            background: #111827;
            color: white;
        }

        .nav-active {
            background: #374151;
            border-left: 4px solid #10b981;
        }
    </style>";

$newStyle = "    <style>
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
    </style>";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace($oldStyle, $newStyle, $content);
        file_put_contents($file, $content);
        echo "✅ Updated: $file\n";
    } else {
        echo "❌ Not found: $file\n";
    }
}

echo "\n✅ Theme applied to all admin pages!\n";
?>