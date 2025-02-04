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

    .register-container {
        background: white;
        border-radius: 30px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
        width: 100%;
        max-width: 1200px;
        position: relative;
    }

    .register-content {
        max-width: 800px;
        margin: 0 auto;
    }

    /* Left Side Styles */
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

    .mascot-container {
        text-align: center;
        margin-bottom: 2rem;
    }

    .mascot-image {
        max-width: 200px;
        height: auto;
        margin-bottom: 1.5rem;
    }

    .welcome-bubble {
        background: white;
        border-radius: 20px;
        padding: 1.5rem 2rem;
        color: var(--text-primary);
        box-shadow: var(--shadow-sm);
        width: 100%;
        max-width: 300px;
        text-align: center;
    }

    /* Right Side Styles */
    .right-side {
        padding: 3rem;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        font-weight: 600;
        display: block;
    }

    .form-control {
        border: 3px solid var(--border-color);
        border-radius: 15px;
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: var(--input-bg);
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(255, 159, 67, 0.1);
        outline: none;
    }

    .form-select {
        border: 3px solid var(--border-color);
        border-radius: 15px;
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: var(--input-bg);
        width: 100%;
        cursor: pointer;
    }

    /* Input Group Styles */
    .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
    }

    .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .btn-outline-secondary {
        border-color: var(--border-color);
        background: var(--input-bg);
        transition: all 0.3s ease;
        padding: 0.75rem 1.25rem;
    }

    /* File Input Styles */
    .custom-file {
        position: relative;
        display: inline-block;
        width: 100%;
        margin-bottom: 0.5rem;
    }

    .preview-container {
        max-width: 150px;
        margin: 10px 0;
    }

    .preview-image-wrapper {
        position: relative;
        width: 100%;
        height: auto;
        margin-bottom: 1rem;
    }

    .preview-image {
        width: 100%;
        height: auto;
        object-fit: cover;
        border-radius: 8px;
    }

    /* Button Styles */
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
        width: 100%;
        margin-top: 1.5rem;
    }

    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: var(--primary-color);
    }

    /* Password Strength Meter */
    .password-strength-meter {
        height: 5px;
        border-radius: 2.5px;
        margin-top: 0.5rem;
    }

    #password-strength-container {
        margin-top: 0.5rem;
        margin-bottom: 1rem;
    }

    /* Alert Styles */
    .alert {
        border-radius: 15px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    /* Login Link */
    .login-link {
        text-align: center;
        margin-top: 2rem;
    }

    .login-link a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 700;
    }

    .login-link a:hover {
        text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 991px) {
        .register-container {
            margin: 1rem;
        }

        .left-side {
            padding: 2rem;
        }

        .right-side {
            padding: 2rem;
        }

        .mascot-image {
            max-width: 150px;
        }

        .welcome-bubble {
            padding: 1.25rem;
        }
    }

    @media (max-width: 767px) {
        body {
            padding: 1rem;
        }

        .register-container {
            border-radius: 20px;
        }

        .left-side,
        .right-side {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .row {
            margin: 0 -0.5rem;
        }

        .col-md-6 {
            padding: 0 0.5rem;
        }

        .btn-register {
            margin-top: 1rem;
        }
    }
</style>

<body>
    <div class="register-container">
        <div class="row g-0">
            <!-- Sisi Kiri - Visual -->
            <div class="col-lg-4 left-side">
                <div class="mascot-container">
                    <img src="<?= BASE_URL ?>/assets/images/toga.png" alt="Maskot Belajar" class="mascot-image">
                </div>
                <div class="welcome-bubble">
                    <h2 class="text-center mb-0">Ayo Bergabung! 🌟</h2>
                    <p class="text-center mb-0">Buat akun baru dan mulai belajar!</p>
                </div>
            </div>

            <!-- Sisi Kanan - Form Register -->
            <div class="col-lg-8 right-side">
                <div class="register-content">
                    <h1 class="text-center mb-4">Daftar Akun Siswa 📚</h1>

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
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="role" value="siswa">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user me-2"></i>Nama Pengguna
                                    </label>
                                    <input type="text" name="username" class="form-control"
                                        placeholder="Masukkan nama pengguna" required
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
                                        <i class="fas fa-school me-2"></i>Kelas
                                    </label>
                                    <select name="id_kelas" class="form-select" required>
                                        <option value="">Pilih Kelasmu</option>
                                        <option value="1">Kelas 1</option>
                                        <option value="2">Kelas 2</option>
                                        <option value="3">Kelas 3</option>
                                        <option value="4">Kelas 4</option>
                                        <option value="5">Kelas 5</option>
                                        <option value="6">Kelas 6</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar me-2"></i>Tahun Ajaran
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="tahun_ajaran" class="form-control" readonly
                                            value="<?= date('Y') . '/' . (date('Y') + 1) ?>"
                                            style="background-color: var(--input-bg);">
                                        <span class="input-group-text text-muted small">
                                            <i class="fas fa-info-circle me-1"></i> Ajaran Saat Ini
                                        </span>
                                    </div>
                                </div>
                            </div>

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
                                            oninvalid="this.setCustomValidity('Mohon isi kata sandi')"
                                            oninput="this.setCustomValidity('')">
                                        <button class="btn btn-outline-secondary d-flex align-items-center"
                                            type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-strength-container" style="height: 20px; margin-top: 0.5rem;">
                                        <div id="password-strength"></div>
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
                                        <button class="btn btn-outline-secondary d-flex align-items-center"
                                            type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-image me-2"></i>Foto Profil
                            </label>
                            <div class="custom-file">
                                <input type="file" name="foto_profile" class="form-control"
                                    id="customFile" accept="image/jpeg,image/png,image/jpg"
                                    data-browse="Pilih File" required
                                    oninvalid="this.setCustomValidity('Mohon pilih foto profil')"
                                    oninput="this.setCustomValidity('')">
                            </div>
                            <small class="text-muted d-block mb-3">Format: JPG, JPEG, PNG (Maks. 2MB)</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-register w-100">
                                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                            </button>
                        </div>

                        <div class="login-link text-center mt-3">
                            <p class="mb-0">Sudah punya akun? <a href="<?= BASE_URL ?>/index.php?page=login">Masuk di sini</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Register Siswa JS loaded");

            // Utility functions
            const createElement = (tag, className = '', attributes = {}) => {
                const element = document.createElement(tag);
                if (className) element.className = className;
                Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));
                return element;
            };

            // Password Toggle Visibility
            function setupPasswordToggle(inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);

                if (toggle && input) {
                    toggle.addEventListener("click", function() {
                        const type = input.getAttribute("type") === "password" ? "text" : "password";
                        input.setAttribute("type", type);
                        const icon = this.querySelector("i");
                        icon.classList.toggle("fa-eye");
                        icon.classList.toggle("fa-eye-slash");
                    });
                }
            }

            setupPasswordToggle("password", "togglePassword");
            setupPasswordToggle("confirm_password", "toggleConfirmPassword");

            // Password Strength Meter
            function checkPasswordStrength(password) {
                const criteria = {
                    length: password.length >= 8,
                    lowercase: /[a-z]/.test(password),
                    uppercase: /[A-Z]/.test(password),
                    numbers: /[0-9]/.test(password),
                    special: /[$@#&!]/.test(password)
                };

                const strength = Object.values(criteria).filter(Boolean).length;
                const strengthText = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'][strength - 1] || '';

                const container = createElement('div');
                const progressBar = createElement('div', 'progress', {
                    style: 'height: 5px;'
                });
                const progress = createElement('div', `progress-bar ${
            ['bg-danger', 'bg-danger', 'bg-warning', 'bg-info', 'bg-success'][strength - 1]
        }`, {
                    style: `width: ${strength * 20}%`,
                    'aria-valuenow': strength * 20,
                    'aria-valuemin': '0',
                    'aria-valuemax': '100'
                });

                progressBar.appendChild(progress);
                container.appendChild(progressBar);

                const textIndicator = createElement('small', 'text-muted mt-1 d-block');
                textIndicator.textContent = strengthText;
                container.appendChild(textIndicator);

                return container;
            }

            // Setup Password Strength Meter
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                const strengthContainer = document.getElementById('password-strength-container');
                passwordInput.addEventListener('input', function() {
                    strengthContainer.innerHTML = '';
                    if (this.value) {
                        strengthContainer.appendChild(checkPasswordStrength(this.value));
                    }
                });
            }

            // File Upload Preview
            const fileInput = document.querySelector('input[name="foto_profile"]');
            if (fileInput) {
                const previewContainer = createElement('div', 'preview-container mt-3');
                fileInput.parentNode.appendChild(previewContainer);

                fileInput.addEventListener('change', function(e) {
                    const file = this.files[0];
                    if (!file) return;

                    // Validate file
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    if (!validTypes.includes(file.type)) {
                        showError('Hanya file JPG, JPEG, dan PNG yang diperbolehkan! 🖼️');
                        this.value = '';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        showError('Ukuran file terlalu besar! Maksimal 2MB ya! 📁');
                        this.value = '';
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `
                    <div class="preview-image-wrapper position-relative">
                        <img src="${e.target.result}" class="preview-image" alt="Preview">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-1" 
                                aria-label="Close" id="removeImage"></button>
                    </div>
                `;

                        document.getElementById('removeImage').addEventListener('click', function() {
                            fileInput.value = '';
                            previewContainer.innerHTML = '';
                        });
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Form Validation
            const registerForm = document.querySelector(".register-form");
            if (registerForm) {
                registerForm.addEventListener("submit", function(e) {
                    e.preventDefault();

                    const password = document.getElementById("password").value;
                    const confirmPassword = document.getElementById("confirm_password").value;
                    const kelas = document.querySelector('select[name="id_kelas"]').value;
                    const fileInput = document.querySelector('input[name="foto_profile"]');
                    const submitBtn = this.querySelector('button[type="submit"]');

                    // Validation checks
                    const validations = [{
                            condition: !kelas,
                            message: "Mohon pilih kelas kamu ya! 🏫"
                        },
                        {
                            condition: password !== confirmPassword,
                            message: "Ups! Kata sandi tidak cocok. Coba periksa lagi ya! 😊"
                        },
                        {
                            condition: password.length < 8,
                            message: "Kata sandi minimal 8 karakter ya! 🔐"
                        },
                        {
                            condition: !(/[A-Z]/.test(password)),
                            message: "Kata sandi harus memiliki minimal 1 huruf besar! 🔠"
                        },
                        {
                            condition: !(/[0-9]/.test(password)),
                            message: "Kata sandi harus memiliki minimal 1 angka! 🔢"
                        },
                        {
                            condition: fileInput.files.length > 0 && fileInput.files[0].size > 2 * 1024 * 1024,
                            message: "Ukuran file terlalu besar! Maksimal 2MB ya! 📁"
                        }
                    ];

                    const failedValidation = validations.find(v => v.condition);
                    if (failedValidation) {
                        showError(failedValidation.message);
                        return;
                    }

                    // Submit form with loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>
                                 Mendaftarkan Siswa Kelas ${kelas}...`;

                    try {
                        this.submit();
                    } catch (error) {
                        showError("Terjadi kesalahan. Silakan coba lagi! 😔");
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Daftar Sekarang';
                    }
                });
            }

            // Error Display
            function showError(message) {
                const existingAlert = document.querySelector(".alert");
                if (existingAlert) {
                    existingAlert.remove();
                }

                const alertDiv = createElement("div", "alert alert-danger alert-dismissible fade show");
                alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

                const form = document.querySelector(".register-form");
                form.insertBefore(alertDiv, form.firstChild);

                // Auto-dismiss alert
                setTimeout(() => {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 150);
                }, 5000);
            }
        });
    </script>
</body>

</html>