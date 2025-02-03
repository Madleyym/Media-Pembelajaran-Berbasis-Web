<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/koneksi.php';

class AuthController
{
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const ALLOWED_ROLES = ['admin', 'guru', 'siswa'];
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_TIMEOUT = 900; // 15 minutes
    private const SESSION_LIFETIME = 7200; // 2 hours
    private const TOKEN_EXPIRY = 24; // 24 hours for reset token

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
        $this->ensureUploadDirectory();
    }

    // Pindahkan inisialisasi session ke method terpisah yang dipanggil sebelum session_start()
    public static function initializeSessionSettings(): void
    {
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

            $stmt = $this->conn->prepare("
                SELECT * FROM users 
                WHERE username = ? 
                AND status = 'active'
            ");
            $stmt->execute([filter_var($username, FILTER_SANITIZE_STRING)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Record successful login
                $this->recordLoginAttempt($username, true);

                // Update last accessed time
                $updateStmt = $this->conn->prepare("
                    UPDATE users 
                    SET updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :id
                ");
                $updateStmt->execute([':id' => $user['id']]);

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

    public function register(array $data): void
    {
        try {
            $this->validateRegistrationData($data);
            $this->validateCSRFToken($data['csrf_token'] ?? '');

            $this->conn->beginTransaction();

            // Handle profile photo upload
            $fotoPath = $this->handleProfilePhotoUpload($_FILES['foto_profile']);

            // Generate activation token
            $activationToken = bin2hex(random_bytes(32));

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert user data
            $userId = $this->createNewUser($data, $hashedPassword, $fotoPath, $activationToken);

            // Insert siswa_kelas data
            $this->assignStudentToClass($userId, (int)$data['id_kelas'], $data['tahun_ajaran']);

            $this->conn->commit();

            // Send activation email here if needed
            // $this->sendActivationEmail($data['email'], $activationToken);

            $this->setFlashMessage('success', 'Registrasi berhasil! Silakan login.');
            header("Location: index.php?page=login");
            exit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->setFlashMessage('error', $e->getMessage());
            header("Location: index.php?page=register");
            exit();
        }
    }

    private function createNewUser(array $data, string $hashedPassword, string $fotoPath, string $activationToken): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO users (
                username, 
                password, 
                nama_lengkap, 
                email, 
                role, 
                foto_profile,
                status,
                activation_token
            ) VALUES (
                :username, 
                :password, 
                :nama_lengkap, 
                :email, 
                'siswa', 
                :foto_profile,
                'active',
                :activation_token
            )
        ");

        $stmt->execute([
            ':username' => $data['username'],
            ':password' => $hashedPassword,
            ':nama_lengkap' => $data['nama_lengkap'],
            ':email' => $data['email'],
            ':foto_profile' => $fotoPath,
            ':activation_token' => $activationToken
        ]);

        return (int)$this->conn->lastInsertId();
    }

    private function assignStudentToClass(int $userId, int $kelasId, string $tahunAjaran): void
    {
        $stmt = $this->conn->prepare("
            INSERT INTO siswa_kelas (
                id_siswa, 
                id_kelas, 
                tahun_ajaran,
                status
            ) VALUES (
                :id_siswa, 
                :id_kelas, 
                :tahun_ajaran,
                'active'
            )
        ");

        $stmt->execute([
            ':id_siswa' => $userId,
            ':id_kelas' => $kelasId,
            ':tahun_ajaran' => $tahunAjaran
        ]);
    }

    private function validateRegistrationData(array $data): void
    {
        $required = ['username', 'password', 'confirm_password', 'email', 'nama_lengkap', 'id_kelas'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field {$field} harus diisi!");
            }
        }

        // Username validation
        if (!preg_match('/^[a-zA-Z0-9_]{5,20}$/', $data['username'])) {
            throw new Exception("Username hanya boleh berisi huruf, angka, dan underscore (5-20 karakter)!");
        }

        // Password validation
        if (strlen($data['password']) < 8) {
            throw new Exception("Password minimal 8 karakter!");
        }

        if (!preg_match('/[A-Z]/', $data['password'])) {
            throw new Exception("Password harus mengandung minimal 1 huruf besar!");
        }

        if (!preg_match('/[0-9]/', $data['password'])) {
            throw new Exception("Password harus mengandung minimal 1 angka!");
        }

        if ($data['password'] !== $data['confirm_password']) {
            throw new Exception("Konfirmasi password tidak cocok!");
        }

        // Email validation
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format email tidak valid!");
        }

        // Check existing username and email
        $this->checkExistingCredentials($data['username'], $data['email']);

        // Validate kelas
        $this->validateKelas((int)$data['id_kelas']);
    }

    private function validateKelas(int $kelasId): void
    {
        $stmt = $this->conn->prepare("SELECT id FROM kelas WHERE id = ?");
        $stmt->execute([$kelasId]);
        if (!$stmt->fetch()) {
            throw new Exception("Kelas tidak valid!");
        }
    }

    private function checkExistingCredentials(string $username, string $email): void
    {
        $stmt = $this->conn->prepare("
            SELECT username, email FROM users 
            WHERE username = :username OR email = :email
        ");

        $stmt->execute([':username' => $username, ':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if ($result['username'] === $username) {
                throw new Exception("Username sudah digunakan!");
            }
            if ($result['email'] === $email) {
                throw new Exception("Email sudah terdaftar!");
            }
        }
    }

    private function handleProfilePhotoUpload(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error dalam upload file!");
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new Exception("Ukuran file terlalu besar! Maksimal 2MB.");
        }

        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, self::ALLOWED_EXTENSIONS)) {
            throw new Exception("Format file tidak diizinkan! Gunakan JPG, JPEG, atau PNG.");
        }

        $newFileName = uniqid('profile_') . '.' . $fileExt;
        $destination = $this->upload_dir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Gagal mengupload file!");
        }

        return $newFileName;
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

    private function validateCSRFToken(?string $token): void
    {
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            throw new Exception("Invalid CSRF token!");
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

    protected function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ];
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
}
