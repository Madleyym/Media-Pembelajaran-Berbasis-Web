<?php
define('ALLOWED_ACCESS', true);
define('BASE_PATH', __DIR__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    error_log("Session started.");
} else {
    error_log("Session already started.");
}

// Flash message functions
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); 
        return $flash;
    }
    return false;
}

// Load configurations and controllers
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/controllers/AuthController.php';
error_log("Configurations and AuthController loaded.");

// Sisa kode tetap sama...

// Initialize AuthController
$auth = new AuthController($conn);
error_log("AuthController initialized.");

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log POST data (kecuali password)
        $logData = $_POST;
        if (isset($logData['password'])) {
            $logData['password'] = '***';
        }
        if (isset($logData['confirm_password'])) {
            $logData['confirm_password'] = '***';
        }
        error_log("POST Data: " . print_r($logData, true));

        // Handle register
        if (isset($_POST['action']) && $_POST['action'] === 'register') {
            error_log("Processing registration request...");

            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
                throw new Exception(ERROR_MESSAGES['general']['csrf']);
            }

            // Process registration
            $auth->register($_POST);
            exit();
        }

        // Handle login
        if (isset($_POST['action']) && $_POST['action'] === 'login') {
            error_log("Processing login request...");

            // Validate required fields
            if (empty($_POST['username']) || empty($_POST['password'])) {
                throw new Exception(ERROR_MESSAGES['login']['failed']);
            }

            $auth->login($_POST['username'], $_POST['password']);
            exit();
        }
    } catch (Exception $e) {
        error_log("Error in form processing: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();

        // Redirect back based on action
        $redirect = BASE_URL . "/index.php?page=" . (isset($_POST['action']) ? $_POST['action'] : 'login');
        header("Location: " . $redirect);
        exit();
    }
}

// Function to get base URL
function getBaseURL()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $folder = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $folder;
}

// Set base URL if not defined
if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseURL());
    error_log("BASE_URL defined as: " . BASE_URL);
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    error_log("CSRF token generated.");
}

// Get page from URL and sanitize
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_URL) : 'login';
error_log("Current Page: " . $page);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Script Name: " . $_SERVER['SCRIPT_NAME']);
error_log("Base URL: " . BASE_URL);

// List of public pages
$public_pages = ['login', 'register'];

// Authentication check
if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    $_SESSION['error'] = 'Access denied';
    header("Location: " . BASE_URL . "/index.php?page=login");
    error_log("Access denied. Redirecting to login page.");
    exit();
}

// Content loader
function loadContent($page)
{
    global $conn;
    $file = '';
    $data = [];

    switch ($page) {
        case 'login':
            if (isset($_SESSION['user_id'])) {
                // Redirect ke dashboard sesuai role jika sudah login
                switch ($_SESSION['role']) {
                    case 'admin':
                        header("Location: " . BASE_URL . "/index.php?page=admin/dashboard");
                        error_log("Redirecting to admin dashboard.");
                        break;
                    case 'guru':
                        header("Location: " . BASE_URL . "/index.php?page=guru/dashboard");
                        error_log("Redirecting to guru dashboard.");
                        break;
                    case 'siswa':
                        header("Location: " . BASE_URL . "/index.php?page=siswa/dashboard");
                        error_log("Redirecting to siswa dashboard.");
                        break;
                }
                exit();
            }
            $file = 'views/siswa/auth/login.php'; // Updated path
            break;

        case 'register':
            if (isset($_SESSION['user_id'])) {
                header("Location: " . BASE_URL . "/index.php?page=dashboard");
                error_log("User already logged in. Redirecting to dashboard.");
                exit();
            }
            // Get kelas data for dropdown
            $stmt = $conn->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
            $data['kelas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data['tahun_ajaran'] = CURRENT_ACADEMIC_YEAR;
            $file = 'views/siswa/auth/register.php'; // Updated path
            break;

        case 'admin/dashboard':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['error'] = 'Access denied';
                header("Location: " . BASE_URL . "/index.php?page=login");
                error_log("Access denied for admin dashboard. Redirecting to login page.");
                exit();
            }
            $file = 'views/admin/dashboard.php';
            break;

        case 'guru/dashboard':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
                $_SESSION['error'] = 'Access denied';
                header("Location: " . BASE_URL . "/index.php?page=login");
                error_log("Access denied for guru dashboard. Redirecting to login page.");
                exit();
            }
            $file = 'views/guru/dashboard.php';
            break;

        case 'siswa/dashboard':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
                $_SESSION['error'] = 'Access denied';
                header("Location: " . BASE_URL . "/index.php?page=login");
                error_log("Access denied for siswa dashboard. Redirecting to login page.");
                exit();
            }
            $file = 'views/siswa/dashboard.php';
            break;

        case 'logout':
            global $auth;
            $auth->logout();
            error_log("User logged out.");
            break;

        default:
            $file = 'views/templates/404.php';
    }

    if (file_exists($file)) {
        // Extract data untuk digunakan di view
        if (!empty($data)) {
            extract($data);
        }
        error_log("Loading content file: " . $file);
        return $file;
    }
    error_log("Content file not found. Loading 404 template.");
    return 'views/templates/404.php';
}

// Load appropriate header based on user role
function loadHeader()
{
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'admin':
                include 'views/templates/header.php';
                break;
            case 'guru':
                include 'views/templates/header.php';
                break;
            case 'siswa':
                include 'views/templates/header.php';
                break;
            default:
                include 'views/templates/header.php';
        }
    } else {
        include 'views/templates/header.php';
    }
    error_log("Header loaded for role: " . ($_SESSION['role'] ?? 'guest'));
}

// Load footer
function loadFooter()
{
    include 'views/templates/footer.php';
    error_log("Footer loaded.");
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
    <title><?= $page == 'login' ? 'Login' : ($page == 'register' ? 'Register' : 'App Name') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <?php if (in_array($page, ['login', 'register'])): ?>
        <link href="<?= BASE_URL ?>/assets/css/<?= $page ?>-style.css" rel="stylesheet">
    <?php endif; ?>
</head>

<body>
    <?php
    // Display Flash Messages if any
    $flashMessage = getFlashMessage();
    if ($flashMessage): ?>
        <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
            <?= $flashMessage['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <?php if (in_array($page, ['login', 'register'])): ?>
        <script src="<?= BASE_URL ?>/assets/js/<?= $page ?>.js"></script>
    <?php endif; ?>

    <!-- Auto-hide alerts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>

</html>