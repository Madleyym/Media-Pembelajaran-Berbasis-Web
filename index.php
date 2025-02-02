<?php
// Di bagian atas file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Logging untuk debug
error_log("Requested URI: " . $_SERVER['REQUEST_URI']);
error_log("Current working directory: " . getcwd());
// Start session di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Handler POST request di luar class
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController($conn);

    if (isset($_POST['login'])) {
        $auth->login($_POST['username'], $_POST['password']);
    } elseif (isset($_POST['register'])) {
        $auth->register($_POST, $_FILES);
    }
}
// Load configuration
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/koneksi.php';

// Fungsi untuk mendapatkan base URL
function getBaseURL()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $folder = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $folder;
}

// Set base URL jika belum didefinisikan
if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseURL());
}

// Get page from URL dan sanitasi
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_URL) : 'login';
error_log("Current Page: " . $page);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Script Name: " . $_SERVER['SCRIPT_NAME']);
error_log("Base URL: " . BASE_URL);
// Daftar halaman yang tidak memerlukan autentikasi
$public_pages = ['login', 'register'];

// Authentication check
if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit();
}

// Content loader
function loadContent($page)
{
    $file = '';

    switch ($page) {
        case 'login':
            if (isset($_SESSION['user_id'])) {
                // Redirect ke dashboard sesuai role jika sudah login
                switch ($_SESSION['role']) {
                    case 'admin':
                        header("Location: " . BASE_URL . "/index.php?page=admin/dashboard");
                        break;
                    case 'guru':
                        header("Location: " . BASE_URL . "/index.php?page=guru/dashboard");
                        break;
                    case 'siswa':
                        header("Location: " . BASE_URL . "/index.php?page=siswa/dashboard");
                        break;
                }
                exit();
            }
            $file = 'views/auth/login.php';
            break;

        case 'register':
            if (isset($_SESSION['user_id'])) {
                header("Location: " . BASE_URL . "/index.php?page=dashboard");
                exit();
            }
            $file = 'views/auth/register.php';
            break;

        case 'admin/dashboard':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                header("Location: " . BASE_URL . "/index.php?page=login");
                exit();
            }
            $file = 'views/admin/dashboard.php';
            break;

        case 'guru/dashboard':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
                header("Location: " . BASE_URL . "/index.php?page=login");
                exit();
            }
            $file = 'views/guru/dashboard.php';
            break;

        case 'siswa/dashboard':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
                header("Location: " . BASE_URL . "/index.php?page=login");
                exit();
            }
            $file = 'views/siswa/dashboard.php';
            break;

        case 'logout':
            session_unset();
            session_destroy();
            header("Location: " . BASE_URL . "/index.php?page=login");
            exit();
            break;

        default:
            $file = 'views/templates/404.php';
    }

    if (file_exists($file)) {
        return $file;
    }
    return 'views/templates/404.php';
}

// Load appropriate header based on user role
function loadHeader()
{
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'admin':
                include 'views/templates/admin_header.php';
                break;
            case 'guru':
                include 'views/templates/guru_header.php';
                break;
            case 'siswa':
                include 'views/templates/siswa_header.php';
                break;
            default:
                include 'views/templates/header.php';
        }
    } else {
        include 'views/templates/header.php';
    }
}

// Load footer
function loadFooter()
{
    include 'views/templates/footer.php';
}

// Handle static files
if (preg_match('/\.(css|js|png|jpg|jpeg|gif)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

// Start output
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page == 'login' ? 'Login' : ($page == 'register' ? 'Register' : APP_NAME) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <!-- <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet"> -->
    <?php if (in_array($page, ['login', 'register'])): ?>
        <!-- <link href="<?= BASE_URL ?>/assets/css/<?= $page ?>-style.css" rel="stylesheet"> -->
    <?php endif; ?>
</head>

<body>
    <?php
    // Load Header kecuali untuk halaman login dan register
    if (!in_array($page, ['login', 'register'])) {
        loadHeader();
    }

    // Load Content
    $content_file = loadContent($page);
    include $content_file;

    // Load Footer kecuali untuk halaman login dan register
    if (!in_array($page, ['login', 'register'])) {
        loadFooter();
    }
    ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom JS -->
    <!-- <script src="<?= BASE_URL ?>/assets/js/main.js"></script> -->
    <?php if (in_array($page, ['login', 'register'])): ?>
        <!-- <script src="<?= BASE_URL ?>/assets/js/<?= $page ?>.js"></script> -->
    <?php endif; ?>
</body>

</html>