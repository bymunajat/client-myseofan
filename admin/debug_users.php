<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<div style='background:#f3f4f6; padding:40px; font-family:sans-serif;'>";
echo "<h1 style='color:#10b981;'>DEBUG USERS PAGE</h1>";

// 0. Check Environment
echo "<h3>Environment Check:</h3>";
if (class_exists('PDO')) {
    echo "<p style='color:green'>✅ PDO Class Exists</p>";
    echo "<p>Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
} else {
    echo "<p style='color:red'>❌ PDO Class MISSING!</p>";
}

if (function_exists('session_start')) {
    echo "<p style='color:green'>✅ session_start() exists</p>";
} else {
    echo "<p style='color:red'>❌ sessions NOT enabled!</p>";
}

// 1. Check for Config
$configPath = __DIR__ . '/../config.php';
if (file_exists($configPath)) {
    echo "<p style='color:green'>✅ config.php exists</p>";
    require_once $configPath;
    echo "<p>DB_PATH: " . DB_PATH . "</p>";
    $dir = dirname(DB_PATH);
    if (is_dir($dir)) {
        echo "<p style='color:green'>✅ Database Dir exists: $dir</p>";
        if (is_writable($dir)) {
            echo "<p style='color:green'>✅ Database Dir is WRITABLE</p>";
        } else {
            echo "<p style='color:red'>❌ Database Dir is NOT WRITABLE</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Database Dir MISSING: $dir</p>";
    }
} else {
    echo "<p style='color:red'>❌ config.php MISSING at $configPath</p>";
}

// 2. Check for db.php content manually or via include
$dbPath = __DIR__ . '/../includes/db.php';
if (file_exists($dbPath)) {
    echo "<p style='color:green'>✅ includes/db.php exists</p>";

    // Attempt include with error suppression and catch
    try {
        include $dbPath;
        echo "<p style='color:green'>✅ includes/db.php included successfully</p>";
    } catch (\Throwable $t) {
        echo "<p style='color:red'>❌ CRASH during include: " . $t->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ includes/db.php MISSING at $dbPath</p>";
}

// 2. Check Session
echo "<h3>Session Info:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 3. Try Raw Query
try {
    $c = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    echo "<p style='color:blue'>📊 Admins in Table: $c</p>";

    $raw = $pdo->query("SELECT * FROM admins")->fetchAll();
    echo "<h3>Raw User Data:</h3>";
    echo "<pre>";
    print_r($raw);
    echo "</pre>";
} catch (\Exception $e) {
    echo "<p style='color:red'>❌ Query Failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p>If you see this text, PHP is executing correctly on your server.</p>";
echo "</div>";
?>