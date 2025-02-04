<?php
// config/init.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Base constants
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Base URL
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $folder = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', rtrim($protocol . '://' . $host . $folder, '/'));
}

// App constants
define('APP_NAME', 'Media Pembelajaran SD');
define('APP_VERSION', '1.0.0');

// Database constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_media_pembelajaran');
define('DB_USER', 'root');
define('DB_PASS', '');

// Debug log
error_log("BASE_URL: " . BASE_URL);
error_log("ROOT_PATH: " . ROOT_PATH);
