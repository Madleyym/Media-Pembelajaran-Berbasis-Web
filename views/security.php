<?php
// security.php
defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__));

// Cek akses langsung ke file
if (!defined('ALLOWED_ACCESS')) {
    header("HTTP/1.0 403 Forbidden");
    echo "Direct access not permitted";
    exit();
}
