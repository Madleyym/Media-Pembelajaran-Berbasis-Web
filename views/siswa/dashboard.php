<?php
// Pastikan session tidak double start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Di awal setiap file view
defined('ALLOWED_ACCESS') or die('Direct access not permitted');

// Cek session dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit();
}

// Pastikan koneksi database tersedia
if (!isset($conn)) {
    die("Koneksi database tidak ditemukan.");
}

// Ambil data siswa
$stmt = $conn->prepare("
    SELECT u.*, sk.id_kelas, k.nama_kelas 
    FROM users u
    JOIN siswa_kelas sk ON u.id = sk.id_siswa
    JOIN kelas k ON sk.id_kelas = k.id
    WHERE u.id = ? AND sk.status = 'active'
");
$stmt->execute([$_SESSION['user_id']]);
$siswa = $stmt->fetch();

// Pastikan data siswa ada
if (!$siswa) {
    die("Data siswa tidak ditemukan atau belum terdaftar di kelas.");
}

// Ambil progress belajar
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT m.id) as total_materi,
        COALESCE(COUNT(DISTINCT pm.materi_id), 0) as materi_selesai,
        COUNT(DISTINCT k.id) as total_kuis,
        COALESCE(COUNT(DISTINCT nk.id_kuis), 0) as kuis_selesai
    FROM materi m
    LEFT JOIN progress_materi pm ON m.id = pm.materi_id AND pm.siswa_id = ?
    LEFT JOIN kuis k ON m.id_kelas = k.id_kelas AND m.id_mapel = k.id_mapel
    LEFT JOIN nilai_kuis nk ON k.id = nk.id_kuis AND nk.id_siswa = ?
    WHERE m.id_kelas = ?
");

$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $siswa['id_kelas']]);
$progress = $stmt->fetch();

// Ambil materi untuk kelas siswa
$stmt = $conn->prepare("
    SELECT * 
    FROM materi 
    WHERE id_kelas = ?
");
$stmt->execute([$siswa['id_kelas']]);
$materi = $stmt->fetchAll();

// Ambil kuis untuk kelas siswa
$stmt = $conn->prepare("
    SELECT * 
    FROM kuis 
    WHERE id_kelas = ?
");
$stmt->execute([$siswa['id_kelas']]);
$kuis = $stmt->fetchAll();

// Ambil nilai terbaru
$stmt = $conn->prepare("
    SELECT k.judul, nk.nilai, nk.created_at
    FROM nilai_kuis nk
    JOIN kuis k ON nk.id_kuis = k.id
    WHERE nk.id_siswa = ?
    ORDER BY nk.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$nilai_terbaru = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - <?= htmlspecialchars($siswa['nama_lengkap']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #50C878;
            --accent-color: #FF6B6B;
            --background-color: #F4F7F6;
            --text-primary: #2C3E50;
            --text-secondary: rgb(255, 255, 255);
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard-container {
            flex: 1;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .welcome-section:hover {
            transform: translateY(-5px);
        }

        .quick-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .action-button {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            color: var(--white);
            text-decoration: none;
            transition: background 0.3s ease, transform 0.2s;
        }

        .action-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .progress {
            height: 15px;
            border-radius: 10px;
            overflow: hidden;
        }

        .recent-activities {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .footer {
            background-color: var(--white);
            padding: 15px 0;
            margin-top: auto;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .quick-actions {
                justify-content: center;
                flex-direction: column;
                align-items: center;
            }

            .action-button {
                width: 100%;
                max-width: 200px;
                margin-bottom: 10px;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section fade-in">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Selamat datang, <?= htmlspecialchars($siswa['nama_lengkap']) ?> 👋</h1>
                    <p class="mb-0">Kelas <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                </div>
                <div class="col-md-4">
                    <div class="quick-actions">
                        <a href="?page=siswa/materi" class="action-button">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <div>Materi</div>
                        </a>
                        <a href="?page=siswa/kuis" class="action-button">
                            <i class="fas fa-question-circle fa-2x mb-2"></i>
                            <div>Kuis</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container fade-in">
            <!-- Progress Materi -->
            <div class="stat-card">
                <h5 class="card-title mb-3">Progress Materi</h5>
                <div class="progress mb-3">
                    <?php
                    $materi_progress = $progress['total_materi'] > 0
                        ? ($progress['materi_selesai'] / $progress['total_materi']) * 100
                        : 0;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $materi_progress ?>%"></div>
                </div>
                <p class="mb-0 text-secondary">
                    <?= $progress['materi_selesai'] ?> dari <?= $progress['total_materi'] ?> materi selesai
                </p>
            </div>

            <!-- Progress Kuis -->
            <div class="stat-card">
                <h5 class="card-title mb-3">Progress Kuis</h5>
                <div class="progress mb-3">
                    <?php
                    $kuis_progress = $progress['total_kuis'] > 0
                        ? ($progress['kuis_selesai'] / $progress['total_kuis']) * 100
                        : 0;
                    ?>
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $kuis_progress ?>%"></div>
                </div>
                <p class="mb-0 text-secondary">
                    <?= $progress['kuis_selesai'] ?> dari <?= $progress['total_kuis'] ?> kuis selesai
                </p>
            </div>
        </div>

        <!-- Materi Section -->
        <div class="stats-container fade-in">
            <div class="stat-card">
                <h5 class="card-title mb-3">Materi Kelas 6</h5>
                <ul>
                    <?php foreach ($materi as $m): ?>
                        <li>
                            <strong><?= htmlspecialchars($m['judul']) ?></strong><br>
                            <?= htmlspecialchars($m['deskripsi']) ?><br>
                            <a href="path/to/materi/<?= htmlspecialchars($m['file_materi']) ?>">Download Materi</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Kuis Section -->
            <div class="stat-card">
                <h5 class="card-title mb-3">Kuis Kelas 6</h5>
                <ul>
                    <?php foreach ($kuis as $k): ?>
                        <li>
                            <strong><?= htmlspecialchars($k['judul']) ?></strong><br>
                            <?= htmlspecialchars($k['deskripsi']) ?><br>
                            <span>Waktu: <?= htmlspecialchars($k['waktu_pengerjaan']) ?> menit</span><br>
                            <span>Tanggal Mulai: <?= htmlspecialchars($k['tanggal_mulai']) ?></span><br>
                            <span>Tanggal Selesai: <?= htmlspecialchars($k['tanggal_selesai']) ?></span><br>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="recent-activities fade-in">
            <h4 class="mb-4">Nilai Terbaru</h4>
            <?php if (empty($nilai_terbaru)): ?>
                <p class="text-secondary text-center">Belum ada nilai kuis.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kuis</th>
                                <th>Nilai</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nilai_terbaru as $nilai): ?>
                                <tr>
                                    <td><?= htmlspecialchars($nilai['judul']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $nilai['nilai'] >= 70 ? 'success' : 'danger' ?>">
                                            <?= $nilai['nilai'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($nilai['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container text-center">
            <span class="text-muted">© <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>

</html>