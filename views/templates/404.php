<?php
if (!defined('ALLOWED_ACCESS')) {
    die('Direct access not permitted');
}
?>

<style>
    /* Menggunakan font yang sama dengan login page */
    @import url('https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap');

    .error-container {
        background: white;
        border-radius: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
        text-align: center;
        max-width: 800px;
        margin: 20px auto;
    }

    .error-number {
        font-size: 120px;
        font-weight: bold;
        background: linear-gradient(135deg, #FF9F43, #54A0FF);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        font-family: 'Comic Neue', cursive;
        animation: bounce 3s ease-in-out infinite;
    }

    .error-mascot {
        width: 200px;
        height: 200px;
        margin: 20px auto;
        animation: float 6s infinite;
    }

    .error-title {
        font-size: 28px;
        font-weight: 700;
        color: #2D3436;
        margin: 20px 0;
        font-family: 'Comic Neue', cursive;
    }

    .error-description {
        color: #636E72;
        font-size: 18px;
        line-height: 1.6;
        margin-bottom: 30px;
        font-family: 'Comic Neue', cursive;
    }

    .error-btn {
        background: #FF9F43;
        border: none;
        border-radius: 15px;
        padding: 12px 30px;
        font-size: 18px;
        font-weight: 700;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(255, 159, 67, 0.3);
        display: inline-flex;
        align-items: center;
        font-family: 'Comic Neue', cursive;
    }

    .error-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 159, 67, 0.4);
        color: white;
    }

    .error-btn i {
        margin-right: 8px;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translate(0, 0) rotate(0deg);
        }
        50% {
            transform: translate(15px, -15px) rotate(5deg);
        }
    }
</style>

<div class="error-container">
    <h1 class="error-number">404</h1>
    <!-- Gunakan mascot yang sama dengan login page -->
    <img src="<?= BASE_URL ?>/assets/images/mascot.png" alt="Maskot Sedih" class="error-mascot">
    <h2 class="error-title">Ups! Halaman Tidak Ditemukan 😢</h2>
    <p class="error-description">
        Maaf ya adik-adik, sepertinya halaman yang kamu cari sedang bermain petak umpet! 
        Ayo kita kembali ke halaman utama dan mulai belajar lagi! 🌟
    </p>
    <a href="<?= BASE_URL ?>" class="error-btn">
        <i class="fas fa-home"></i>
        Ayo Kembali Belajar!
    </a>
</div>