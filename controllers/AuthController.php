<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/koneksi.php';

class AuthController
{
    // Constants definition
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const ALLOWED_ROLES = ['admin', 'guru', 'siswa'];
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_TIMEOUT = 900; // 15 minutes
    private const SESSION_LIFETIME = 7200; // 2 hours

    private PDO $conn;
    private string $upload_dir;
    private array $errors = [];

    public function __construct(PDO $db)
    {
        if (!$db) {
            throw new Exception("Database connection required");
        }
        $this->conn = $db;
        $this->upload_dir = dirname(__DIR__) . '/uploads/profile/';
        $this->initializeSession();
        $this->ensureUploadDirectory();
    }

    private function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set secure session parameters
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
        ini_set('session.gc_maxlifetime', (string)self::SESSION_LIFETIME);
    }

    private function ensureUploadDirectory(): void
    {
        if (!file_exists($this->upload_dir)) {
            if (!mkdir($this->upload_dir, 0755, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }
    }

    public function login(string $username, string $password): void
    {
        try {
            if (empty($username) || empty($password)) {
                throw new Exception("Username dan password harus diisi!");
            }

            // Check login attempts
            $this->checkLoginAttempts($username);

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([filter_var($username, FILTER_SANITIZE_STRING)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Record successful login
                $this->recordLoginAttempt($username, true);

                // Update last login time
                $updateStmt = $this->conn->prepare("UPDATE users SET last_login = :current_time WHERE id = :id");
                $updateStmt->execute([
                    ':current_time' => date('Y-m-d H:i:s'),
                    ':id' => $user['id']
                ]);

                // Set session data
                $this->initializeUserSession($user);

                // Redirect based on role
                $this->redirectBasedOnRole($user['role']);
            } else {
                // Record failed attempt
                $this->recordLoginAttempt($username, false);
                throw new Exception("Username atau Password salah!");
            }
        } catch (Exception $e) {
            error_log("Login error for user {$username}: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?page=login");
            exit();
        }
    }

    private function initializeUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['foto_profile'] = $user['foto_profile'] ?? 'default.jpg';
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['success'] = "Selamat datang kembali, {$user['nama_lengkap']}!";
    }

    private function checkLoginAttempts(string $username): void
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE username = ? 
            AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            AND success = 0
        ");
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            throw new Exception("Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.");
        }
    }

    private function recordLoginAttempt(string $username, bool $success): void
    {
        $stmt = $this->conn->prepare("
            INSERT INTO login_attempts (username, attempt_time, success) 
            VALUES (?, NOW(), ?)
        ");
        $stmt->execute([$username, $success ? 1 : 0]);

        if ($success) {
            // Clear failed attempts
            $stmt = $this->conn->prepare("
                DELETE FROM login_attempts 
                WHERE username = ? AND success = 0
            ");
            $stmt->execute([$username]);
        }
    }

    private function redirectBasedOnRole(string $role): void
    {
        $baseUrl = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $baseUrl .= $_SERVER['HTTP_HOST'];
        $baseUrl .= dirname($_SERVER['PHP_SELF']);

        switch ($role) {
            case 'admin':
                header("Location: {$baseUrl}/index.php?page=admin/dashboard");
                break;
            case 'guru':
                header("Location: {$baseUrl}/index.php?page=guru/dashboard");
                break;
            case 'siswa':
                header("Location: {$baseUrl}/index.php?page=siswa/dashboard");
                break;
            default:
                throw new Exception("Role tidak valid!");
        }
        exit();
    }

    public function logout(): void
    {
        // Clear all session data
        $_SESSION = array();

        // Delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        // Destroy the session
        session_destroy();

        // Redirect to login page
        header("Location: index.php?page=login");
        exit();
    }

    // Helper method to set flash messages
    protected function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ];
    }
}
