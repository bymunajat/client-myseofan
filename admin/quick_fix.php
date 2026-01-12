<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>QUICK FIX - DIRECT DB TEST</h1>";

$db_path = __DIR__ . "/../database/myseofan.db";
echo "Attempting to connect to: " . $db_path . "<br>";

try {
    $pdo = new PDO("sqlite:" . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ CONNECTION SUCCESSFUL<br>";

    $stmt = $pdo->query("SELECT * FROM admins");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Users found: " . count($users) . "</h3>";
    foreach ($users as $u) {
        echo "User: " . $u['username'] . " | Role: " . $u['role'] . "<br>";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}

echo "<hr><p>If you see this, PHP/SQLite is working fine in standalone mode.</p>";
?>