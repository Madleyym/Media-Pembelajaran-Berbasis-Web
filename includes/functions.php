<?php
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function getUserRole()
{
    return $_SESSION['role'] ?? null;
}

function checkPermission($requiredRole)
{
    if (!isLoggedIn()) {
        redirect('login');
    }
    if (getUserRole() !== $requiredRole) {
        setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini.');
        redirect('dashboard');
    }
}

function redirect($page)
{
    header("Location: " . BASE_URL . "/index.php?page=" . $page);
    exit();
}

function setFlashMessage($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatDate($date)
{
    return date('d F Y', strtotime($date));
}

function sanitizeInput($data)
{
    return htmlspecialchars(trim($data));
}

function generateRandomString($length = 10)
{
    return bin2hex(random_bytes($length));
}
