<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo" height="30">
            <?= APP_NAME ?>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <img src="<?= BASE_URL ?>/assets/images/avatar.png" alt="Profile"
                                class="rounded-circle" width="30">
                            <?= $_SESSION['nama_lengkap'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?page=profile">
                                    <i class="fas fa-user me-2"></i>Profil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?page=logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</nav>