<?php
// Di awal setiap file view
defined('ALLOWED_ACCESS') or die('Direct access not permitted');

// Cek session dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit();
}

// ... rest of your code ...
// Ambil data nilai
$stmt = $conn->prepare("
    SELECT nk.*, k.judul_kuis, k.tanggal_mulai, mp.nama_mapel
    FROM nilai_kuis nk
    JOIN kuis k ON nk.id_kuis = k.id
    JOIN mata_pelajaran mp ON k.id_mapel = mp.id
    WHERE nk.id_siswa = ?
    ORDER BY nk.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$nilai_list = $stmt->fetchAll();

// Hitung statistik
$total_kuis = count($nilai_list);
$rata_rata = $total_kuis > 0 ? array_sum(array_column($nilai_list, 'nilai')) / $total_kuis : 0;
$nilai_tertinggi = $total_kuis > 0 ? max(array_column($nilai_list, 'nilai')) : 0;
$nilai_terendah = $total_kuis > 0 ? min(array_column($nilai_list, 'nilai')) : 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Nilai Kuis</title>
    <style>
        .nilai-container {
            padding: 2rem;
        }

        .nilai-header {
            background: linear-gradient(135deg, #2196F3 0%, #4CAF50 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .nilai-table {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .nilai-container {
                padding: 1rem;
            }

            .nilai-header {
                padding: 1.5rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="nilai-container">
        <!-- Header Section -->
        <div class="nilai-header">
            <h1>Nilai Kuis</h1>
            <p class="mb-0">Rekap nilai dari kuis yang telah dikerjakan</p>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?= $total_kuis ?></h3>
                <p class="text-muted mb-0">Total Kuis</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($rata_rata, 1) ?></h3>
                <p class="text-muted mb-0">Rata-rata Nilai</p>
            </div>
            <div class="stat-card">
                <h3><?= $nilai_tertinggi ?></h3>
                <p class="text-muted mb-<h3><?= $nilai_tertinggi ?></h3>
                <p class=" text-muted mb-0">Nilai Tertinggi</p>
            </div>
            <div class="stat-card">
                <h3><?= $nilai_terendah ?></h3>
                <p class="text-muted mb-0">Nilai Terendah</p>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <canvas id="nilaiChart"></canvas>
        </div>

        <!-- Nilai Table -->
        <div class="nilai-table">
            <h4 class="mb-4">Riwayat Nilai</h4>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Mata Pelajaran</th>
                            <th>Judul Kuis</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($nilai_list)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <img src="<?= BASE_URL ?>/assets/images/empty-state.svg"
                                        alt="Belum ada nilai"
                                        style="max-width: 200px;">
                                    <p class="mt-3 text-muted">Belum ada nilai kuis.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($nilai_list as $nilai): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($nilai['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($nilai['nama_mapel']) ?></td>
                                    <td><?= htmlspecialchars($nilai['judul_kuis']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $nilai['nilai'] >= 70 ? 'success' : 'danger' ?>">
                                            <?= $nilai['nilai'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($nilai['nilai'] >= 70): ?>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> Lulus
                                            </span>
                                        <?php else: ?>
                                            <span class="text-danger">
                                                <i class="fas fa-times-circle"></i> Tidak Lulus
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=siswa/detail_nilai&id=<?= $nilai['id'] ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare chart data
            const nilaiData = <?= json_encode(array_map(function ($n) {
                                    return [
                                        'tanggal' => date('d/m', strtotime($n['created_at'])),
                                        'nilai' => $n['nilai'],
                                        'mapel' => $n['nama_mapel']
                                    ];
                                }, array_reverse($nilai_list))) ?>;

            const ctx = document.getElementById('nilaiChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: nilaiData.map(n => n.tanggal),
                    datasets: [{
                        label: 'Nilai Kuis',
                        data: nilaiData.map(n => n.nilai),
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const nilai = context.raw;
                                    const mapel = nilaiData[context.dataIndex].mapel;
                                    return `${mapel}: ${nilai}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });

            // Animate elements on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            const elements = document.querySelectorAll('.stat-card, .chart-container, .nilai-table');
            elements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'all 0.5s ease';
                observer.observe(el);
            });
        });
    </script>
</body>

</html>