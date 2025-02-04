<?php
// koneksi.php

// Mencegah akses langsung ke file
if (!defined('ROOT_PATH')) {
    die('Direct access not permitted');
}

// Set header security
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
if (ENVIRONMENT === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Load constants
require_once 'constants.php';

// Fungsi untuk mencatat log error database
function logDatabaseError($error, $query = '')
{
    $logFile = ROOT_PATH . '/logs/db_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$error}" . ($query ? " Query: {$query}" : "") . PHP_EOL;
    error_log($logMessage, 3, $logFile);
}

try {
    // Konfigurasi PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    // Membuat koneksi database
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );

    if (DEBUG_MODE) {
        // echo "Koneksi database berhasil!";
    }
} catch (PDOException $e) {
    logDatabaseError($e->getMessage());
    if (DEBUG_MODE) {
        die("Koneksi gagal: " . $e->getMessage());
    } else {
        die("Maaf, terjadi kesalahan pada sistem. 😢");
    }
}

// Set timezone
date_default_timezone_set(TIMEZONE);

// Session configuration
$session_options = [
    'cookie_httponly' => true,                 // Mencegah akses JavaScript ke cookie session
    'cookie_secure' => ENVIRONMENT === 'production', // Hanya HTTPS di production
    'cookie_samesite' => 'Lax',               // Proteksi CSRF
    'use_strict_mode' => true,                // Mencegah session fixation
    'use_only_cookies' => true,               // Hanya gunakan cookies untuk session
    'gc_maxlifetime' => SESSION_TIME,         // Waktu session
    'cookie_lifetime' => SESSION_TIME         // Waktu cookie
];

// Start session dengan konfigurasi yang aman
if (session_status() === PHP_SESSION_NONE) {
    session_start($session_options);
}

// Session security checks
if (!empty($_SESSION['user_id'])) {
    // Cek User Agent
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    } elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        header("Location: " . BASE_URL . "/index.php?page=login&msg=security");
        exit();
    }

    // Cek IP Address (opsional, uncomment jika diperlukan)
    /*
    if (!isset($_SESSION['ip_address'])) {
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    } elseif ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        header("Location: " . BASE_URL . "/index.php?page=login&msg=security");
        exit();
    }
    */

    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 menit
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    // Check session timeout
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    } elseif (time() - $_SESSION['last_activity'] > SESSION_TIME) {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "/index.php?page=login&msg=timeout");
        exit();
    }

    $_SESSION['last_activity'] = time();
}

// Cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fungsi untuk membersihkan koneksi
function closeConnection()
{
    global $conn;
    $conn = null;
}

// Register shutdown function
register_shutdown_function('closeConnection');
