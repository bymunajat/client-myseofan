<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>SESSION TEST</h1>";
echo "Attempting to start session...<br>";

try {
    session_start();
    echo "✅ SESSION STARTED SUCCESSFULLY<br>";
    $_SESSION['test_time'] = time();
    echo "Session ID: " . session_id() . "<br>";
    echo "Stored Time: " . $_SESSION['test_time'] . "<br>";
} catch (Exception $e) {
    echo "❌ SESSION START FAILED: " . $e->getMessage();
}

echo "<hr><p>If you see this, sessions are working.</p>";
?>