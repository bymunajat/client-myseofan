<?php
require_once 'includes/db.php';
$sql = $pdo->query("SELECT sql FROM sqlite_master WHERE name='menu_items'")->fetchColumn();
echo $sql;
