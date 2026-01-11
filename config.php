<?php
/**
 * Global Configuration for Instagram Downloader
 */

// Database Settings (SQLite)
define('DB_PATH', __DIR__ . '/database/myseofan.db');

// API Settings
define('COBALT_API_URL', 'http://localhost:9000'); // Ensure Cobalt server is running

// Site Settings
define('SITE_VERSION', '1.0.0');

// Error Reporting (Turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
