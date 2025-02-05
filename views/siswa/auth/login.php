<?php
if (!defined('ALLOWED_ACCESS')) {
    die('Direct access not permitted');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Dunia Belajar! 📚</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
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

    .login-container {
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
        min-height: 500px;
    }

    .mascot-container {
        position: relative;
        margin-bottom: 30px;
    }

    .mascot-image {
        width: 250px;
        height: 250px;
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

    .welcome-bubble:after {
        content: '';
        position: absolute;
        top: -15px;
        left: 50%;
        border: 15px solid transparent;
        border-bottom-color: white;
        transform: translateX(-50%);
    }

    .right-side {
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 10px;
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

    .btn-login {
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

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 159, 67, 0.4);
        background: var(--primary-color);
    }

    .help-text {
        text-align: center;
        margin-top: 25px;
        font-size: 16px;
        color: var(--text-secondary);
    }

    .floating-items {
        position: absolute;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .floating-item {
        position: absolute;
        animation: float 6s infinite;
        opacity: 0.6;
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

    @keyframes float {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg);
        }

        50% {
            transform: translate(15px, -15px) rotate(5deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 991px) {
        .left-side {
            min-height: auto;
            padding: 30px;
        }

        .mascot-image {
            width: 200px;
            height: 200px;
        }

        .welcome-bubble {
            margin-bottom: 20px;
        }
    }

    @media (max-width: 767px) {
        .login-container {
            margin: 15px;
        }

        .left-side,
        .right-side {
            padding: 20px;
        }

        .mascot-image {
            width: 150px;
            height: 150px;
        }
    }

    /* Animasi untuk Alert */
    .alert {
        animation: slideDown 0.5s ease-out;
        border-radius: 15px;
        font-size: 16px;
        padding: 15px 20px;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<body>
    <div class="login-container">
        <div class="row g-0">
            <!-- Sisi Kiri - Visual -->
            <div class="col-lg-6 left-side">
                <div class="mascot-container">
                    <img src="assets/images/mascot.png" alt="Maskot Belajar" class="mascot-image">
                </div>
                <div class="welcome-bubble">
                    <h2 class="text-center mb-0">Ayo Belajar Bersama! 🌟</h2>
                </div>
            </div>

            <!-- Sisi Kanan - Form Login -->
            <div class="col-lg-6 right-side">
                <div class="login-content">
                    <h1 class="text-center mb-4" style="font-size: 28px; font-weight: 700; color: var(--text-primary)">
                        Selamat Datang di Kelas Digital! 📚
                    </h1>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/index.php" method="POST" class="login-form">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user me-2"></i>Nama Pengguna
                            </label>
                            <input type="text" name="username" class="form-control form-control-lg"
                                placeholder="Ketik nama pengguna kamu" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-lock me-2"></i>Kata Sandi
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control form-control-lg"
                                    id="password" placeholder="Ketik kata sandi kamu" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-login w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Ayo Mulai Belajar!
                        </button>

                        <div class="help-text text-center mt-2">
                            <p>Belum punya akun? <a href="<?= BASE_URL ?>/index.php?page=register" class="text-primary">Daftar di sini</a></p>
                        </div>

                        <div class="help-text">
                            <p>Butuh bantuan? Tanya Ibu/Bapak Guru ya! 😊</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Password Visibility
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            if (togglePassword && password) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }

            // Form validation dengan pesan yang ramah anak
            const loginForm = document.querySelector('.login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const username = this.querySelector('input[name="username"]').value;
                    const password = this.querySelector('input[name="password"]').value;

                    if (!username || !password) {
                        e.preventDefault();
                        showError('Ups! Jangan lupa isi nama pengguna dan kata sandi ya! 😊');
                    }
                });
            }

            function showError(message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                const form = document.querySelector('.login-form');
                form.insertBefore(alertDiv, form.firstChild);
            }
        });
    </script>
</body>

</html>