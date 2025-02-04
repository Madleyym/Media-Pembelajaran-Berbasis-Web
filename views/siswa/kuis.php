<?php
// Di awal setiap file view
defined('ALLOWED_ACCESS') or die('Direct access not permitted');

// Cek session dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit();
}


// Ambil data kuis
$stmt = $conn->prepare("
    SELECT k.*, mp.nama_mapel,
           CASE WHEN nk.id IS NOT NULL THEN nk.nilai ELSE NULL END as nilai,
           CASE WHEN nk.id IS NOT NULL THEN 1 ELSE 0 END as is_completed
    FROM kuis k
    JOIN mata_pelajaran mp ON k.id_mapel = mp.id
    LEFT JOIN nilai_kuis nk ON k.id = nk.id_kuis AND nk.id_siswa = ?
    WHERE k.id_kelas = (
        SELECT id_kelas 
        FROM siswa_kelas 
        WHERE id_siswa = ? AND status = 'active'
    )
    ORDER BY k.tanggal_mulai DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$kuis_list = $stmt->fetchAll();

// Group kuis by mata pelajaran
$kuis_by_mapel = [];
foreach ($kuis_list as $kuis) {
    $kuis_by_mapel[$kuis['nama_mapel']][] = $kuis;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Kuis</title>
    <style>
        .kuis-container {
            padding: 2rem;
        }

        .kuis-header {
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .mapel-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .kuis-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .kuis-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .kuis-card.completed::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #28a745;
        }

        .nilai-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
        }

        .timer {
            background: rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .kuis-container {
                padding: 1rem;
            }

            .kuis-header {
                padding: 1.5rem;
            }

            .nilai-badge {
                position: static;
                margin-top: 1rem;
                display: inline-block;
            }
        }
    </style>
</head>

<body>
    <div class="kuis-container">
        <!-- Header Section -->
        <div class="kuis-header">
            <h1>Kuis</h1>
            <p class="mb-0">Uji pemahamanmu dengan mengerjakan kuis yang tersedia</p>
        </div>

        <!-- Kuis Content -->
        <?php foreach ($kuis_by_mapel as $mapel => $kuis_list): ?>
            <div class="mapel-section">
                <h3 class="mb-4"><?= htmlspecialchars($mapel) ?></h3>

                <div class="row">
                    <?php foreach ($kuis_list as $kuis): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="kuis-card <?= $kuis['is_completed'] ? 'completed' : '' ?>">
                                <?php if ($kuis['is_completed']): ?>
                                    <div class="nilai-badge bg-<?= $kuis['nilai'] >= 70 ? 'success' : 'danger' ?>">
                                        Nilai: <?= $kuis['nilai'] ?>
                                    </div>
                                <?php endif; ?>

                                <h5><?= htmlspecialchars($kuis['judul_kuis']) ?></h5>
                                <p class="text-muted mb-3">
                                    <?= substr(strip_tags($kuis['deskripsi']), 0, 100) ?>...
                                </p>

                                <div class="mb-3">
                                    <div class="timer">
                                        <i class="fas fa-clock"></i>
                                        <?= $kuis['durasi'] ?> menit
                                    </div>
                                </div>

                                <?php
                                $now = new DateTime();
                                $start = new DateTime($kuis['tanggal_mulai']);
                                $end = new DateTime($kuis['tanggal_selesai']);
                                ?>

                                <?php if ($now < $start): ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        Kuis belum dimulai
                                    </button>
                                <?php elseif ($now > $end): ?>
                                    <button class="btn btn-danger w-100" disabled>
                                        Kuis sudah berakhir
                                    </button>
                                <?php elseif ($kuis['is_completed']): ?>
                                    <a href="?page=siswa/hasil_kuis&id=<?= $kuis['id'] ?>"
                                        class="btn btn-success w-100">
                                        Lihat Hasil
                                    </a>
                                <?php else: ?>
                                    <a href="?page=siswa/mulai_kuis&id=<?= $kuis['id'] ?>"
                                        class="btn btn-primary w-100">
                                        Mulai Kuis
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Empty State -->
        <?php if (empty($kuis_by_mapel)): ?>
            <div class="text-center py-5">
                <img src="<?= BASE_URL ?>/assets/images/empty-state.svg"
                    alt="Tidak ada kuis"
                    style="max-width: 300px;">
                <h3 class="mt-4">Belum Ada Kuis</h3>
                <p class="text-muted">Kuis belum tersedia untuk kelasmu.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            const kuisCards = document.querySelectorAll('.kuis-card');
            kuisCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>

</html>