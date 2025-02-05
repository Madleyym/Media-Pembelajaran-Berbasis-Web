<?php
// Di awal setiap file view
defined('ALLOWED_ACCESS') or die('Direct access not permitted');

// Cek session dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit();
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

// Ambil progress belajar
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT m.id) as total_materi,
        COUNT(DISTINCT pm.materi_id) as materi_selesai,
        COUNT(DISTINCT k.id) as total_kuis,
        COUNT(DISTINCT nk.id_kuis) as kuis_selesai
    FROM materi m
    LEFT JOIN progress_materi pm ON m.id = pm.materi_id AND pm.siswa_id = ?
    LEFT JOIN kuis k ON m.id_mapel = k.id_mapel
    LEFT JOIN nilai_kuis nk ON k.id = nk.id_kuis AND nk.id_siswa = ?
    WHERE m.id_mapel = ?
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $siswa['id_kelas']]);
$progress = $stmt->fetch();

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
    <title>Dashboard Siswa - <?= $siswa['nama_lengkap'] ?></title>
    <style>
        .dashboard-container {
            padding: 2rem;
        }

        .welcome-section {
            background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .progress-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .recent-activities {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s ease;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .action-button {
            background: #f8f9fa;
            border: none;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .action-button:hover {
            background: #e9ecef;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .welcome-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Selamat datang, <?= htmlspecialchars($siswa['nama_lengkap']) ?> 👋</h1>
                    <p class="mb-0">Kelas <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                </div>
                <div class="col-md-4 text-end">
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
        <div class="stats-container">
            <!-- Progress Materi -->
            <div class="stat-card">
                <h5 class="card-title">Progress Materi</h5>
                <div class="progress mb-3" style="height: 10px;">
                    <?php
                    $materi_progress = $progress['total_materi'] > 0
                        ? ($progress['materi_selesai'] / $progress['total_materi']) * 100
                        : 0;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar"
                        style="width: <?= $materi_progress ?>%"></div>
                </div>
                <p class="mb-0">
                    <?= $progress['materi_selesai'] ?> dari <?= $progress['total_materi'] ?> materi selesai
                </p>
            </div>

            <!-- Progress Kuis -->
            <div class="stat-card">
                <h5 class="card-title">Progress Kuis</h5>
                <div class="progress mb-3" style="height: 10px;">
                    <?php
                    $kuis_progress = $progress['total_kuis'] > 0
                        ? ($progress['kuis_selesai'] / $progress['total_kuis']) * 100
                        : 0;
                    ?>
                    <div class="progress-bar bg-primary" role="progressbar"
                        style="width: <?= $kuis_progress ?>%"></div>
                </div>
                <p class="mb-0">
                    <?= $progress['kuis_selesai'] ?> dari <?= $progress['total_kuis'] ?> kuis selesai
                </p>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="recent-activities">
            <h4>Nilai Terbaru</h4>
            <?php if (empty($nilai_terbaru)): ?>
                <p class="text-muted">Belum ada nilai kuis.</p>
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
                                    <td><?= htmlspecialchars($nilai['judul_kuis']) ?></td>
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

    <!-- Custom Scripts -->
    <script>
        // Add any custom JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>

</html>