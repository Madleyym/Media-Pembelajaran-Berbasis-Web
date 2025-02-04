<?php
// Di awal setiap file view
defined('ALLOWED_ACCESS') or die('Direct access not permitted');

// Cek session dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit();
}


// Ambil data materi
$stmt = $conn->prepare("
    SELECT m.*, k.nama_kelas, mp.nama_mapel,
           CASE WHEN pm.id IS NOT NULL THEN 1 ELSE 0 END as is_completed
    FROM materi m
    JOIN kelas k ON m.id_kelas = k.id
    JOIN mata_pelajaran mp ON m.id_mapel = mp.id
    LEFT JOIN progress_materi pm ON m.id = pm.id_materi AND pm.id_siswa = ?
    WHERE m.id_kelas = (
        SELECT id_kelas 
        FROM siswa_kelas 
        WHERE id_siswa = ? AND status = 'active'
    )
    ORDER BY m.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$materi_list = $stmt->fetchAll();

// Group materi by mata pelajaran
$materi_by_mapel = [];
foreach ($materi_list as $materi) {
    $materi_by_mapel[$materi['nama_mapel']][] = $materi;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Materi Pembelajaran</title>
    <style>
        .materi-container {
            padding: 2rem;
        }

        .materi-header {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
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

        .materi-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .materi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .materi-card.completed {
            border-left: 4px solid #28a745;
        }

        .progress-pill {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .search-box {
            position: relative;
            margin-bottom: 2rem;
        }

        .search-box input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: #FF6B6B;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }

        @media (max-width: 768px) {
            .materi-container {
                padding: 1rem;
            }

            .materi-header {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="materi-container">
        <!-- Header Section -->
        <div class="materi-header">
            <h1>Materi Pembelajaran</h1>
            <p class="mb-0">Pelajari materi sesuai dengan mata pelajaran yang tersedia</p>
        </div>

        <!-- Search Box -->
        <div class="search-box">
            <input type="text" id="searchMateri"
                placeholder="Cari materi pembelajaran..."
                class="form-control">
        </div>

        <!-- Materi Content -->
        <?php foreach ($materi_by_mapel as $mapel => $materi_list): ?>
            <div class="mapel-section">
                <h3 class="mb-4"><?= htmlspecialchars($mapel) ?></h3>

                <div class="row">
                    <?php foreach ($materi_list as $materi): ?>
                        <div class="col-md-6 col-lg-4 materi-item">
                            <div class="materi-card <?= $materi['is_completed'] ? 'completed' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5><?= htmlspecialchars($materi['judul']) ?></h5>
                                        <p class="text-muted mb-2">
                                            <?= substr(strip_tags($materi['deskripsi']), 0, 100) ?>...
                                        </p>
                                    </div>
                                    <?php if ($materi['is_completed']): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-3">
                                    <a href="?page=siswa/detail_materi&id=<?= $materi['id'] ?>"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-book-reader me-1"></i><i class="fas fa-book-reader me-1"></i>
                                        Pelajari Materi
                                    </a>
                                    <?php if ($materi['file_path']): ?>
                                        <a href="<?= BASE_URL ?>/uploads/materi/<?= $materi['file_path'] ?>"
                                            class="btn btn-outline-primary btn-sm"
                                            target="_blank">
                                            <i class="fas fa-download me-1"></i>
                                            Unduh
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Empty State -->
        <?php if (empty($materi_by_mapel)): ?>
            <div class="text-center py-5">
                <img src="<?= BASE_URL ?>/assets/images/empty-state.svg"
                    alt="Tidak ada materi"
                    style="max-width: 300px;">
                <h3 class="mt-4">Belum Ada Materi</h3>
                <p class="text-muted">Materi pembelajaran belum tersedia untuk kelasmu.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchMateri');
            const materiItems = document.querySelectorAll('.materi-item');

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();

                materiItems.forEach(item => {
                    const title = item.querySelector('h5').textContent.toLowerCase();
                    const description = item.querySelector('p').textContent.toLowerCase();

                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // Animate cards on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            materiItems.forEach(item => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'all 0.5s ease';
                observer.observe(item);
            });
        });
    </script>
</body>

</html>