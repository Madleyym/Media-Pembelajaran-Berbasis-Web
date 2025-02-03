<?php
// Di bagian paling atas file
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Media Pembelajaran');
}
// Pastikan BASE_URL didefinisikan
if (!defined('BASE_URL')) {
    // Definisikan BASE_URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $folder = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . $host . $folder);
}

// Redirect if already logged in
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
    <title>Daftar Akun Baru | <?= defined('APP_NAME') ? APP_NAME : 'Media Pembelajaran' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap');

    :root {
        --primary-color: #FF9F43;
        --secondary-color: #54A0FF;
        --accent-color: #FF6B6B;
        --background-color: #F8F9FA;
        --success-color: #26DE81;
        --warning-color: #FED330;
        --text-primary: #2D3436;
        --text-secondary: #636E72;
        --border-color: #E0E0E0;
        --input-bg: var(--background-color);
        --shadow-sm: 0 5px 15px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 15px 40px rgba(0, 0, 0, 0.2);
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
        padding: 2rem;
    }

    .input-group .btn-outline-secondary {
        border-color: var(--border-color);
        background: var(--input-bg);
        transition: all 0.3s ease;
    }

    .input-group .btn-outline-secondary:hover {
        background: var(--border-color);
    }

    /* Perbaikan untuk file input */
    .custom-file {
        position: relative;
        display: inline-block;
        width: 100%;
        height: calc(1.5em + 1.5rem + 2px);
        margin-bottom: 0;
    }

    .custom-file-input {
        position: relative;
        z-index: 2;
        width: 100%;
        height: calc(1.5em + 1.5rem + 2px);
        margin: 0;
        opacity: 0;
    }

    .custom-file-label {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1;
        height: calc(1.5em + 1.5rem + 2px);
        padding: 0.75rem 1.25rem;
        font-weight: 400;
        line-height: 1.5;
        color: var(--text-secondary);
        background-color: var(--input-bg);
        border: 3px solid var(--border-color);
        border-radius: 15px;
    }

    .register-container {
        background: white;
        border-radius: 30px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
        width: 100%;
        max-width: 1200px;
        padding: 0;
        position: relative;
    }

    .left-side {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 3rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        min-height: 100%;
    }

    .welcome-bubble {
        background: white;
        border-radius: 20px;
        padding: 1.5rem 2rem;
        margin-top: 1.5rem;
        color: var(--text-primary);
        box-shadow: var(--shadow-sm);
        width: 100%;
        max-width: 300px;
    }

    .right-side {
        padding: 3rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-control {
        border: 3px solid var(--border-color);
        border-radius: 15px;
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: var(--input-bg);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(255, 159, 67, 0.1);
    }

    /* Password Strength Meter Custom Styles */
    .password-strength-meter {
        height: 5px;
        border-radius: 2.5px;
        margin-top: 0.5rem;
    }

    .strength-weak {
        background-color: var(--accent-color);
    }

    .strength-medium {
        background-color: var(--warning-color);
    }

    .strength-strong {
        background-color: var(--success-color);
    }

    .btn-register {
        background: var(--primary-color);
        border: none;
        border-radius: 15px;
        padding: 0.75rem 1.875rem;
        font-size: 1.125rem;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(255, 159, 67, 0.3);
    }

    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: var(--primary-color);
    }

    /* Responsive Design */
    @media (max-width: 991px) {
        .left-side {
            padding: 2rem;
        }

        .right-side {
            padding: 2rem;
        }

        .mascot-image {
            width: 150px;
            height: 150px;
        }

        .welcome-bubble {
            padding: 1.25rem;
            margin-top: 1.25rem;
        }
    }

    @media (max-width: 767px) {
        body {
            padding: 1rem;
        }

        .register-container {
            margin: 0;
            border-radius: 20px;
        }

        .left-side,
        .right-side {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }
    }
</style>

<body>
    <div class="register-container">
        <div class="row g-0">
            <!-- Sisi Kiri - Visual -->
            <div class="col-lg-4 left-side">
                <div class="mascot-container">
                    <img src="<?= BASE_URL ?>/assets/images/mascot-toga.png" alt="Maskot Belajar" class="mascot-image">
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

                    <!-- Perbaikan: Tambahkan form opening tag dengan action dan method -->
                    <form action="<?= BASE_URL ?>/index.php" method="POST" class="register-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user me-2"></i>Nama Pengguna
                                    </label>
                                    <input type="text"
                                        name="username"
                                        class="form-control"
                                        placeholder="Masukkan nama pengguna"
                                        required
                                        oninvalid="this.setCustomValidity('Mohon isi nama pengguna')"
                                        oninput="this.setCustomValidity('')">
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
                                        <input type="password"
                                            name="password"
                                            class="form-control"
                                            id="password"
                                            placeholder="Buat kata sandi"
                                            required
                                            oninvalid="this.setCustomValidity('Mohon isi kata sandi')"
                                            oninput="this.setCustomValidity('')">
                                        <button class="btn btn-outline-secondary d-flex align-items-center"
                                            type="button"
                                            id="togglePassword">
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
                                        <input type="password"
                                            name="confirm_password"
                                            class="form-control"
                                            id="confirm_password"
                                            placeholder="Ulangi kata sandi"
                                            required>
                                        <button class="btn btn-outline-secondary d-flex align-items-center"
                                            type="button"
                                            id="toggleConfirmPassword">
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
                            <div class="custom-file">
                                <input type="file"
                                    name="foto_profile"
                                    class="form-control"
                                    id="customFile"
                                    accept="image/jpeg,image/png,image/jpg"
                                    data-browse="Pilih File"
                                    required
                                    oninvalid="this.setCustomValidity('Mohon pilih foto profil')"
                                    oninput="this.setCustomValidity('')">
                                <label class="custom-file-label" for="customFile">Pilih file</label>
                            </div>
                            <small class="text-muted">Format: JPG, JPEG, PNG (Maks. 2MB)</small>
                            <button type="submit" class="btn btn-register w-100 mt-3">
                                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                            </button>
                        </div>


                        <div class="login-link">
                            <p>Sudah punya akun? <a href="<?= BASE_URL ?>/index.php?page=login">Masuk di sini</a></p>
                        </div>
                    </form> <!-- Perbaikan: Pindahkan closing form tag ke sini -->
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Register JS loaded");

            // Toggle Password Visibility
            function setupPasswordToggle(inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);

                if (toggle && input) {
                    toggle.addEventListener("click", function() {
                        const type =
                            input.getAttribute("type") === "password" ? "text" : "password";
                        input.setAttribute("type", type);
                        this.querySelector("i").classList.toggle("fa-eye");
                        this.querySelector("i").classList.toggle("fa-eye-slash");
                    });
                }
            }

            setupPasswordToggle("password", "togglePassword");
            setupPasswordToggle("confirm_password", "toggleConfirmPassword");

            // Password Strength Meter
            function checkPasswordStrength(password) {
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[$@#&!]+/)) strength++;

                const strengthBar = document.createElement('div');
                strengthBar.className = 'progress mt-2';
                strengthBar.style.height = '5px';

                const progressBar = document.createElement('div');
                progressBar.className = 'progress-bar';
                progressBar.style.width = (strength * 20) + '%';

                switch (strength) {
                    case 0:
                    case 1:
                        progressBar.className += ' bg-danger';
                        progressBar.textContent = 'Lemah';
                        break;
                    case 2:
                    case 3:
                        progressBar.className += ' bg-warning';
                        progressBar.textContent = 'Sedang';
                        break;
                    case 4:
                    case 5:
                        progressBar.className += ' bg-success';
                        progressBar.textContent = 'Kuat';
                        break;
                }

                strengthBar.appendChild(progressBar);
                return strengthBar;
            }

            // Add password strength meter
            const passwordInput = document.getElementById('password');
            const strengthContainer = document.createElement('div');
            strengthContainer.id = 'password-strength';
            passwordInput.parentNode.appendChild(strengthContainer);

            passwordInput.addEventListener('input', function() {
                const oldStrength = document.querySelector('#password-strength .progress');
                if (oldStrength) oldStrength.remove();

                if (this.value) {
                    const strengthBar = checkPasswordStrength(this.value);
                    document.getElementById('password-strength').appendChild(strengthBar);
                }
            });

            // Preview foto profil
            const fileInput = document.querySelector('input[name="foto_profile"]');
            const previewContainer = document.createElement('div');
            previewContainer.className = 'mt-2';
            fileInput.parentNode.appendChild(previewContainer);

            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-1" 
                                    aria-label="Close" id="removeImage"></button>
                        </div>
                    `;

                        document.getElementById('removeImage').addEventListener('click', function() {
                            fileInput.value = '';
                            previewContainer.innerHTML = '';
                        });
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Form validation
            const registerForm = document.querySelector(".register-form");
            if (registerForm) {
                registerForm.addEventListener("submit", function(e) {
                    const password = document.getElementById("password").value;
                    const confirmPassword = document.getElementById("confirm_password").value;
                    const fileInput = document.querySelector('input[type="file"]');
                    const submitBtn = this.querySelector('button[type="submit"]');

                    // Password match validation
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        showError("Ups! Kata sandi tidak cocok. Coba periksa lagi ya! 😊");
                        return;
                    }

                    // File size validation
                    if (fileInput.files.length > 0) {
                        const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                        if (fileSize > 2) {
                            e.preventDefault();
                            showError("Ukuran file terlalu besar! Maksimal 2MB ya! 📁");
                            return;
                        }
                    }

                    // Add loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mendaftar...';
                });
            }

            function showError(message) {
                const existingAlert = document.querySelector(".alert");
                if (existingAlert) {
                    existingAlert.remove();
                }

                const alertDiv = document.createElement("div");
                alertDiv.className = "alert alert-danger alert-dismissible fade show";
                alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
                const form = document.querySelector(".register-form");
                form.insertBefore(alertDiv, form.firstChild);
            }
        });
    </script>
</body>

</html>