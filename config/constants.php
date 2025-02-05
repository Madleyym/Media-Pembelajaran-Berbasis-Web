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
// db_media_pembelajaran'

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

// Profile Image Settings
define('PROFILE_ALLOWED_TYPES', [
    'image/jpeg',
    'image/png',
    'image/jpg'
]);
define('PROFILE_MAX_SIZE', 2 * 1024 * 1024); // 2MB khusus untuk foto profile

// File Upload General Settings
define('MAX_FILE_SIZE', 10485760); // 10MB untuk file umum
define('ALLOWED_TYPES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'video/mp4',
    'audio/mpeg'
]);

// Session & Security Configuration
define('SESSION_TIME', 7200); // 2 hours in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes in seconds
define('LOGIN_ATTEMPT_WINDOW', 15); // 15 minutes window for attempts
define('CSRF_EXPIRE', 7200); // 2 hours

// Password Requirements
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIREMENTS', [
    'uppercase' => true,  // Memerlukan huruf besar
    'number' => true,     // Memerlukan angka
    'special' => false    // Tidak memerlukan karakter khusus
]);

// Timezone
define('TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set(TIMEZONE);

// Security
define('HASH_COST', 10); // For password hashing

// User Roles
define('USER_ROLES', ['admin', 'guru', 'siswa']);
define('DEFAULT_ROLE', 'siswa');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Academic Settings
define('CURRENT_ACADEMIC_YEAR', date('Y') . '/' . (date('Y') + 1));
define('SCHOOL_LEVELS', [
    1 => 'Kelas 1',
    2 => 'Kelas 2',
    3 => 'Kelas 3',
    4 => 'Kelas 4',
    5 => 'Kelas 5',
    6 => 'Kelas 6'
]);
// define('CURRENT_ACADEMIC_YEAR', '2024/2025'); // Update this accordingly
// define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT_MINUTES', 30);
// Asset Paths
define('CSS_PATH', BASE_URL . '/assets/css');
define('JS_PATH', BASE_URL . '/assets/js');
define('IMG_PATH', BASE_URL . '/assets/images');
define('DEFAULT_AVATAR', 'assets/images/default-avatar.png');

// Custom Messages
define('ERROR_MESSAGES', [
    'login' => [
        'failed' => 'Username atau Password salah! 😢',
        'attempts' => 'Terlalu banyak percobaan login. Silakan tunggu 15 menit. 🕒',
        'inactive' => 'Akun Anda tidak aktif. Hubungi admin untuk informasi lebih lanjut. ⚠️'
    ],
    'register' => [
        'username_exists' => 'Username sudah digunakan! 😅',
        'email_exists' => 'Email sudah terdaftar! 📧',
        'password_mismatch' => 'Password tidak cocok! 🔐',
        'invalid_class' => 'Kelas tidak valid! 🏫'
    ],
    'upload' => [
        'size' => 'Ukuran file terlalu besar! Maksimal {size}. 📁',
        'type' => 'Format file tidak diizinkan! 📎',
        'failed' => 'Gagal mengupload file! Coba lagi ya. ⚠️'
    ],
    'general' => [
        'db_error' => 'Ups! Ada masalah dengan database. Coba lagi ya! 🔧',
        'not_found' => 'Halaman yang kamu cari tidak ada. 🔍',
        'access_denied' => 'Kamu tidak boleh mengakses halaman ini. 🚫',
        'csrf' => 'Token keamanan tidak valid! Silakan refresh halaman. 🔒'
    ]
]);

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// SSL Check for production
if (ENVIRONMENT === 'production' && !isset($_SERVER['HTTPS'])) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
