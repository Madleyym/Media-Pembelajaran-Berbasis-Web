<?php
session_start();

// Validasi akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'config/database.php';

// Ambil data guru
$stmt = $conn->prepare("
    SELECT u.*, COUNT(DISTINCT gm.id_mapel) as total_mapel, 
           COUNT(DISTINCT gm.id_kelas) as total_kelas
    FROM users u
    LEFT JOIN guru_mapel gm ON u.id = gm.id_guru
    WHERE u.id = ? AND u.role = 'guru'
    GROUP BY u.id
");
$stmt->execute([$_SESSION['user_id']]);
$guru = $stmt->fetch();

// Ambil statistik materi
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_materi,
        COUNT(CASE WHEN status = 'published' THEN 1 END) as materi_published,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as materi_baru
    FROM materi
    WHERE id_guru = ?
");
$stmt->execute([$_SESSION['user_id']]);
$materi_stats = $stmt->fetch();

// Ambil statistik kuis
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_kuis,
        COUNT(CASE WHEN tanggal_mulai > NOW() THEN 1 END) as kuis_upcoming,
        COUNT(CASE WHEN tanggal_mulai <= NOW() AND tanggal_selesai >= NOW() THEN 1 END) as kuis_active
    FROM kuis
    WHERE id_guru = ?
");
$stmt->execute([$_SESSION['user_id']]);
$kuis_stats = $stmt->fetch();

// Ambil mata pelajaran yang diajar
$stmt = $conn->prepare("
    SELECT DISTINCT 
        mp.id, 
        mp.nama_mapel,
        k.nama_kelas,
        (SELECT COUNT(*) FROM materi m WHERE m.id_mapel = mp.id AND m.id_guru = ?) as total_materi,
        (SELECT COUNT(*) FROM kuis ku WHERE ku.id_mapel = mp.id AND ku.id_guru = ?) as total_kuis
    FROM guru_mapel gm
    JOIN mata_pelajaran mp ON gm.id_mapel = mp.id
    JOIN kelas k ON gm.id_kelas = k.id
    WHERE gm.id_guru = ? AND gm.status = 'active'
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$mapel_list = $stmt->fetchAll();

// Ambil aktivitas terbaru siswa
$stmt = $conn->prepare("
    SELECT 
        u.nama_lengkap as nama_siswa,
        k.nama_kelas,
        CASE 
            WHEN pm.id IS NOT NULL THEN CONCAT('Menyelesaikan materi ', m.judul)
            WHEN nk.id IS NOT NULL THEN CONCAT('Mengerjakan kuis ', ku.judul, ' dengan nilai ', nk.nilai)
        END as aktivitas,
        COALESCE(pm.created_at, nk.created_at) as waktu
    FROM users u
    JOIN siswa_kelas sk ON u.id = sk.id_siswa
    JOIN kelas k ON sk.id_kelas = k.id
    LEFT JOIN progress_materi pm ON u.id = pm.siswa_id
    LEFT JOIN materi m ON pm.materi_id = m.id
    LEFT JOIN nilai_kuis nk ON u.id = nk.id_siswa
    LEFT JOIN kuis ku ON nk.id_kuis = ku.id
    WHERE (m.id_guru = ? OR ku.id_guru = ?)
    AND COALESCE(pm.created_at, nk.created_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY waktu DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$aktivitas_siswa = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - <?= htmlspecialchars($guru['nama_lengkap']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4E7EFF;
            --secondary-color: #6C757D;
            --success-color: #28A745;
            --warning-color: #FFC107;
            --danger-color: #DC3545;
            --light-color: #F8F9FA;
            --dark-color: #343A40;
        }

        body {
            background-color: #F5F7FF;
            font-family: 'Inter', sans-serif;
        }

        .dashboard-container {
            padding: 2rem;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), #2B4BCC);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: 100%;
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-card:hover {
            background: var(--primary-color);
            color: white;
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .mapel-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .activity-list {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }

        .chart-container {
            min-height: 300px;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-3">Selamat datang, <?= htmlspecialchars($guru['nama_lengkap']) ?></h2>
                    <p class="mb-0">
                        <i class="fas fa-book-reader me-2"></i>Mengajar <?= $guru['total_mapel'] ?> mata pelajaran
                        <span class="mx-2">|</span>
                        <i class="fas fa-users me-2"></i>Di <?= $guru['total_kelas'] ?> kelas
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="fas fa-user-circle me-2"></i>Edit Profil
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="create-materi.php" class="action-card">
                <i class="fas fa-file-alt action-icon"></i>
                <h5>Buat Materi</h5>
                <p class="mb-0 text-muted">Upload materi pembelajaran baru</p>
            </a>
            <a href="create-quiz.php" class="action-card">
                <i class="fas fa-question-circle action-icon"></i>
                <h5>Buat Kuis</h5>
                <p class="mb-0 text-muted">Buat soal dan kuis baru</p>
            </a>
            <a href="student-progress.php" class="action-card">
                <i class="fas fa-chart-line action-icon"></i>
                <h5>Progress Siswa</h5>
                <p class="mb-0 text-muted">Pantau perkembangan siswa</p>
            </a>
        </div>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <h5 class="card-title">Statistik Materi</h5>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <h3 class="mb-0"><?= $materi_stats['total_materi'] ?></h3>
                            <p class="text-muted mb-0">Total Materi</p>
                        </div>
                        <div class="text-end">
                            <p class="mb-1">
                                <span class="badge bg-success"><?= $materi_stats['materi_published'] ?> Published</span>
                            </p>
                            <p class="mb-0">
                                <span class="badge bg-info"><?= $materi_stats['materi_baru'] ?> Minggu Ini</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <h5 class="card-title">Statistik Kuis</h5>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <h3 class="mb-0"><?= $kuis_stats['total_kuis'] ?></h3>
                            <p class="text-muted mb-0">Total Kuis</p>
                        </div>
                        <div class="text-end">
                            <p class="mb-1">
                                <span class="badge bg-warning"><?= $kuis_stats['kuis_upcoming'] ?> Akan Datang</span>
                            </p>
                            <p class="mb-0">
                                <span class="badge bg-success"><?= $kuis_stats['kuis_active'] ?> Aktif</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <h5 class="card-title">Rata-rata Nilai</h5>
                    <div id="avgScoreChart" class="chart-container"></div>
                </div>
            </div>
        </div>

        <!-- Mata Pelajaran dan Aktivitas -->
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="mapel-card">
                    <h5 class="mb-4">Mata Pelajaran yang Diampu</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Materi</th>
                                    <th>Kuis</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mapel_list as $mapel): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($mapel['nama_mapel']) ?></td>
                                        <td><?= htmlspecialchars($mapel['nama_kelas']) ?></td>
                                        <td><?= $mapel['total_materi'] ?></td>
                                        <td><?= $mapel['total_kuis'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="location.href='detail-mapel.php?id=<?= $mapel['id'] ?>'">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="activity-list">
                    <h5 class="mb-4">Aktivitas Terbaru Siswa</h5>
                    <?php if (empty($aktivitas_siswa)): ?>
                        <p class="text-center text-muted">Belum ada aktivitas</p>
                    <?php else: ?>
                        <?php foreach ($aktivitas_siswa as $aktivitas): ?>
                            <div class="activity-item">
                                <p class="mb-1 fw-bold"><?= htmlspecialchars($aktivitas['nama_siswa']) ?></p>
                                <p class="mb-1 text-muted"><?= htmlspecialchars($aktivitas['aktivitas']) ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= date('d M Y H:i', strtotime($aktivitas['waktu'])) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>