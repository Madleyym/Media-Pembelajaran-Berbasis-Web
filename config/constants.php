<?php
// constants.php

// Deteksi environment
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

// Set konfigurasi berdasarkan environment
switch (ENVIRONMENT) {
    case 'development':
        define('DEBUG_MODE', true);
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('BASE_URL', 'http://localhost/media');
        break;

    case 'production':
        define('DEBUG_MODE', false);
        define('DB_HOST', 'production_host');
        define('DB_USER', 'production_user');
        define('DB_PASS', 'production_pass');
        define('BASE_URL', 'https://your-domain.com');
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Environment tidak valid.';
        exit(1);
}

// Database Configuration
define('DB_NAME', 'db_media_pembelajaran');

// Application Configuration
define('APP_NAME', 'Media Pembelajaran SD');
define('APP_VERSION', '1.0.0');

// Directory Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Upload Directories
define('UPLOAD_MATERI', UPLOAD_PATH . '/materi');
define('UPLOAD_PROFILE', UPLOAD_PATH . '/profile');
define('UPLOAD_TUGAS', UPLOAD_PATH . '/tugas');

// File Upload Limits (in bytes)
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_TYPES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'video/mp4',
    'audio/mpeg'
]);

// Session Configuration
define('SESSION_TIME', 7200); // 2 hours in seconds

// Timezone
define('TIMEZONE', 'Asia/Jakarta');

// Security
define('HASH_COST', 10); // For password hashing

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Default Values
define('DEFAULT_ROLE', 'siswa');
define('ITEMS_PER_PAGE', 10);

// Email Configuration
define('SMTP_HOST', ENVIRONMENT === 'production' ? 'smtp.gmail.com' : 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', ENVIRONMENT === 'production' ? 'your-email@gmail.com' : '');
define('SMTP_PASS', ENVIRONMENT === 'production' ? 'your-app-password' : '');

// Asset Paths
define('DEFAULT_AVATAR', 'assets/images/default-avatar.png');
define('CSS_PATH', BASE_URL . '/assets/css');
define('JS_PATH', BASE_URL . '/assets/js');
define('IMG_PATH', BASE_URL . '/assets/images');

// Custom Messages
define('ERROR_MESSAGES', [
    'login_failed' => 'Username atau Password salah! 😢',
    'db_error' => 'Ups! Ada masalah dengan database. Coba lagi ya! 😊',
    'upload_error' => 'Maaf, file tidak bisa diupload. Coba lagi ya! 📁',
    'not_found' => 'Halaman yang kamu cari tidak ada. 🔍',
    'access_denied' => 'Kamu tidak boleh mengakses halaman ini. 🚫'
]);


// SSL Check for production
if (ENVIRONMENT === 'production') {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}
