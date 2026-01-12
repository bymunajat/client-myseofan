<?php
// Script to update all input/textarea text colors to black with bold font

$files = [
    'blog.php',
    'pages.php',
    'translations.php',
    'users.php',
    'profile.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "❌ Not found: $file\n";
        continue;
    }

    $content = file_get_contents($file);
    $original = $content;

    // Replace patterns for input and textarea fields
    // Pattern 1: text-gray-XXX to text-black with font-bold/font-semibold
    $content = preg_replace(
        '/class="([^"]*)\btext-gray-[0-9]+\b([^"]*)"/i',
        'class="$1text-black font-semibold$2"',
        $content
    );

    // Pattern 2: Add font-bold to inputs that don't have font styling
    $content = preg_replace(
        '/<input([^>]*class="[^"]*(?!font-)[^"]*")([^>]*)>/i',
        '<input$1 font-semibold$2>',
        $content
    );

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "✅ Updated: $file\n";
    } else {
        echo "⚠️  No changes: $file\n";
    }
}

echo "\n✅ Font update completed!\n";
?>