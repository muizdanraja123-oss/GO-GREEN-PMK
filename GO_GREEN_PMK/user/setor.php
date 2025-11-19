<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$data = mysqli_query($conn, "SELECT * FROM Setor WHERE id_user='$id_user' ORDER BY tanggal DESC");

// Array tips setoran
$tips = [
    "Pastikan sampah kering sebelum disetor untuk poin maksimal.",
    "Pisahkan sampah berdasarkan jenis agar lebih mudah diproses.",
    "Setor sampah secara rutin untuk akumulasi poin yang lebih besar.",
    "Gunakan aplikasi untuk melacak poin setoran Anda."
];

// Array quote acak
$quotes = [
    "“Setiap setoran sampah adalah langkah menuju bumi yang lebih bersih.”",
    "“Daur ulang dimulai dari setoran Anda.”",
    "“Poin hari ini, masa depan yang lebih hijau.”"
];
$random_quote = $quotes[array_rand($quotes)];

// Data untuk grafik (poin per setoran terbaru, max 5 untuk sederhana)
$chart_labels = [];
$chart_data = [];
$chart_query = mysqli_query($conn, "SELECT tanggal, poin FROM Setor WHERE id_user='$id_user' ORDER BY tanggal DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($chart_query)) {
    $chart_labels[] = htmlspecialchars($row['tanggal']);
    $chart_data[] = (int)$row['poin'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Riwayat Setoran Sampah - GO GREEN PMK">
    <title>Setor Sampah - GO GREEN PMK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(90deg, #198754, #198754);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        .table tbody tr:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }
        .quote-container {
            margin: 40px 0;
            text-align: center;
            animation: fadeIn 1.5s ease-in-out;
        }
        .fancy-quote {
            font-size: 2.3rem;
            color: #2e7d32;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, color 0.3s ease;
            display: inline-block;
        }
        .fancy-quote:hover {
            transform: scale(1.05);
            color: #388e3c;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chart-container {
            max-width: 600px;
            margin: 20px auto;
        }
        .tips-carousel .carousel-item {
            text-align: center;
            padding: 20px;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff5722;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f8f9fa;
            position: fixed;
            width: 100%;
            z-index: 9999;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Loading Spinner -->
    <div id="loading" class="loading">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="bi bi-recycle me-1"></i> GO GREEN PMK - User
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarAdmin">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo 'active'; ?>" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'setor.php') echo 'active'; ?>" href="setor.php">
                            <i class="bi bi-box"></i> Setoran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'penukaran.php') echo 'active'; ?>" href="penukaran.php">
                            <i class="bi bi-gift"></i> Penukaran
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item dropdown position-relative">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['nama']); ?>
                            <!-- Notifikasi Badge -->
                            <span class="notification-badge">2</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">User Panel</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-success" href="profile.php">
                                    <i class="bi bi-person-circle me-1"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="logoutModalLabel"><i class="bi bi-box-arrow-right"></i> Konfirmasi Logout</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="fs-5">Yakin ingin keluar dari akun ini?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="../proses/logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <h3 class="mb-3"><i class="bi bi-box"></i> Setor Sampah</h3>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <strong><i class="bi bi-list-ul"></i> Riwayat Setoran Kamu</strong>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-success">
                        <tr class="text-center">
                            <th>No</th>
                            <th><i class="bi bi-calendar"></i> Tanggal</th>
                            <th><i class="bi bi-trash"></i> Jenis Sampah</th>
                            <th><i class="bi bi-weight"></i> Berat (kg)</th>
                            <th><i class="bi bi-star"></i> Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $total_poin = 0;
                        if (mysqli_num_rows($data) > 0):
                            while ($row = mysqli_fetch_assoc($data)):
                                echo "<tr>
                                        <td class='text-center'>" . htmlspecialchars($no) . "</td>
                                        <td>" . htmlspecialchars($row['tanggal']) . "</td>
                                        <td>" . htmlspecialchars($row['jenis_sampah']) . "</td>
                                        <td class='text-center'>" . htmlspecialchars($row['berat']) . "</td>
                                        <td class='text-center'>" . htmlspecialchars($row['poin']) . "</td>
                                    </tr>";
                                $total_poin += $row['poin'];
                                $no++;
                            endwhile;
                        else:
                            echo "<tr><td colspan='5' class='text-center text-muted'>Belum ada setoran.</td></tr>";
                        endif;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="4" class="text-end">Total Poin</th>
                            <th class="text-center"><?php echo htmlspecialchars($total_poin); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Grafik Riwayat Setoran -->
        <div class="chart-container mt-4">
            <h5 class="text-center text-success"><i class="bi bi-bar-chart"></i> Statistik Poin Setoran (5 Terbaru)</h5>
            <canvas id="setoranChart"></canvas>
        </div>

        <!-- Tips Setoran -->
        <div class="mt-5">
            <h4 class="text-center text-success mb-3"><i class="bi bi-lightbulb"></i> Tips Setoran Hari Ini</h4>
            <div id="tipsCarousel" class="carousel slide tips-carousel" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($tips as $index => $tip): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <p class="fs-5 text-muted"><?php echo htmlspecialchars($tip); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#tipsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#tipsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>

    <div class="quote-container">
        <p class="fancy-quote"><?php echo htmlspecialchars($random_quote); ?></p>
    </div>

    <footer class="text-center mt-5 mb-4 text-muted">
        <small>&copy; <?php echo date('Y'); ?> GO GREEN PMK
        </small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hide loading spinner after page load
        window.addEventListener('load', function() {
            document.getElementById('loading').style.display = 'none';
        });

        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

        // Chart.js for Setoran
        const ctx = document.getElementById('setoranChart').getContext('2d');
        const setoranChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Poin per Setoran',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: 'rgba(76, 175, 80, 0.6)',
                    borderColor: 'rgba(76, 175, 80, 1)',
                    borderWidth: 2,
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Poin: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuad'
                }
            }
        });
    </script>
</body>
</html>
