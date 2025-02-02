<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
echo "Script started<br>";
var_dump($_SESSION);
var_dump(defined('BASE_URL') ? BASE_URL : 'BASE_URL not defined');

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php?page=dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/register-style.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap');

        :root {
            --primary-color: #FF9F43;
            /* Orange hangat */
            --secondary-color: #54A0FF;
            /* Biru cerah */
            --accent-color: #FF6B6B;
            /* Merah muda ceria */
            --background-color: #F8F9FA;
            /* Putih lembut */
            --success-color: #26DE81;
            /* Hijau cerah */
            --warning-color: #FED330;
            /* Kuning cerah */
            --text-primary: #2D3436;
            /* Hitam lembut */
            --text-secondary: #636E72;
            /* Abu-abu gelap */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Comic Neue', cursive;
        }

        body {
            background: var(--background-color);
            background-image:
                radial-gradient(circle at 10% 20%, rgba(255, 159, 67, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(84, 160, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 50% 50%, rgba(255, 107, 107, 0.1) 0%, transparent 30%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 1200px;
            padding: 0;
            position: relative;
        }

        .row {
            margin: 0;
        }

        .left-side {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            min-height: 100%;
        }

        .mascot-container {
            position: relative;
            margin-bottom: 30px;
        }

        .mascot-image {
            width: 200px;
            height: 200px;
            object-fit: contain;
            animation: bounce 3s ease-in-out infinite;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.2));
        }

        .welcome-bubble {
            background: white;
            border-radius: 20px;
            padding: 15px 25px;
            position: relative;
            margin-top: 20px;
            color: var(--text-primary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .right-side {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-control {
            border: 3px solid #E0E0E0;
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #F8F9FA;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(255, 159, 67, 0.1);
        }

        .btn-register {
            background: var(--primary-color);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-size: 18px;
            font-weight: 700;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 159, 67, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 159, 67, 0.4);
            background: var(--primary-color);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: var(--text-secondary);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        /* Responsive Design */
        @media (max-width: 991px) {
            .left-side {
                padding: 30px;
            }

            .mascot-image {
                width: 150px;
                height: 150px;
            }
        }

        @media (max-width: 767px) {
            .register-container {
                margin: 15px;
            }

            .left-side,
            .right-side {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="row g-0">
            <!-- Sisi Kiri - Visual -->
            <div class="col-lg-4 left-side">
                <div class="mascot-container">
                    <img src="<?= BASE_URL ?>/assets/images/mascot.png" alt="Maskot Belajar" class="mascot-image">
                </div>
                <div class="welcome-bubble">
                    <h2 class="text-center mb-0">Ayo Bergabung! 🌟</h2>
                    <p class="text-center mb-0">Buat akun baru dan mulai belajar!</p>
                </div>
            </div>

            <!-- Sisi Kanan - Form Register -->
            <div class="col-lg-8 right-side">
                <div class="register-content">
                    <h1 class="text-center mb-4">Daftar Akun Baru 📝</h1>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/index.php" method="POST" class="register-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="register">
                        <!-- Rest of your form -->
                    </form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user me-2"></i>Nama Pengguna
                                </label>
                                <input type="text" name="username" class="form-control"
                                    placeholder="Contoh: johndoe123" required
                                    pattern="[a-zA-Z0-9_]{4,20}"
                                    title="Username harus 4-20 karakter (huruf, angka, dan underscore)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user-circle me-2"></i>Nama Lengkap
                                </label>
                                <input type="text" name="nama_lengkap" class="form-control"
                                    placeholder="Masukkan nama lengkapmu" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email
                        </label>
                        <input type="email" name="email" class="form-control"
                            placeholder="Masukkan email aktif" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock me-2"></i>Kata Sandi
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control"
                                        id="password" placeholder="Buat kata sandi" required
                                        pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                                        title="Minimal 8 karakter, harus mengandung huruf dan angka">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock me-2"></i>Ulangi Kata Sandi
                                </label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" class="form-control"
                                        id="confirm_password" placeholder="Ulangi kata sandi" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user-tag me-2"></i>Peran
                        </label>
                        <select name="role" class="form-select" required>
                            <option value="">Pilih peranmu</option>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-image me-2"></i>Foto Profil
                        </label>
                        <input type="file" name="foto_profile" class="form-control"
                            accept="image/jpeg,image/png,image/jpg"
                            max-size="2048">
                        <small class="text-muted">Format: JPG, JPEG, PNG (Max. 2MB)</small>
                    </div>

                    <button type="submit" class="btn btn-register w-100 mt-3">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </button>

                    <div class="login-link">
                        <p>Sudah punya akun? <a href="<?= BASE_URL ?>/index.php?page=login">Masuk di sini</a></p>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="<?= BASE_URL ?>/assets/js/register.js"></script> -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Password Visibility
            function setupPasswordToggle(inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);

                if (toggle && input) {
                    toggle.addEventListener('click', function() {
                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                        input.setAttribute('type', type);
                        this.querySelector('i').classList.toggle('fa-eye');
                        this.querySelector('i').classList.toggle('fa-eye-slash');
                    });
                }
            }

            setupPasswordToggle('password', 'togglePassword');
            setupPasswordToggle('confirm_password', 'toggleConfirmPassword');

            // Form validation
            const registerForm = document.querySelector('.register-form');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    const fileInput = document.querySelector('input[type="file"]');

                    // Password match validation
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        showError('Ups! Kata sandi tidak cocok. Coba periksa lagi ya! 😊');
                        return;
                    }

                    // File size validation
                    if (fileInput.files.length > 0) {
                        const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                        if (fileSize > 2) {
                            e.preventDefault();
                            showError('Ukuran file terlalu besar! Maksimal 2MB ya! 📁');
                            return;
                        }
                    }
                });
            }

            function showError(message) {
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }

                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
                const form = document.querySelector('.register-form');
                form.insertBefore(alertDiv, form.firstChild);
            }
        });
    </script>
</body>

</html>