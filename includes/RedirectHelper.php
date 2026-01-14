<?php
// includes/RedirectHelper.php

function handle_custom_redirects($pdo)
{
    if (!$pdo)
        return;

    // Get current URI path
    $current_uri = $_SERVER['REQUEST_URI'];

    // Remove query string for matching (optional, depends on requirement, usually we match path)
    $path = parse_url($current_uri, PHP_URL_PATH);

    // 1. Exact Match Check
    $stmt = $pdo->prepare("SELECT target_url, redirect_type FROM redirects WHERE source_url = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$path]);
    $redirect = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. If not found, try with/without trailing slash
    if (!$redirect) {
        $alt_path = (substr($path, -1) === '/') ? rtrim($path, '/') : $path . '/';
        $stmt->execute([$alt_path]);
        $redirect = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($redirect) {
        $target = $redirect['target_url'];
        $code = (int) $redirect['redirect_type'];

        // Validate Status Code
        if (!in_array($code, [301, 302])) {
            $code = 301;
        }

        // Perform Redirect
        header("Location: $target", true, $code);
        exit;
    }
}
