<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$q_poin = mysqli_query($conn, "SELECT SUM(poin) AS total_poin FROM Setor WHERE id_user='$id_user'");
$data_poin = mysqli_fetch_assoc($q_poin);
$total_poin = $data_poin['total_poin'] ?? 0;

$q_setor = mysqli_query($conn, "SELECT COUNT(*) AS total_setor FROM Setor WHERE id_user='$id_user'");
$data_setor = mysqli_fetch_assoc($q_setor);
$total_setor = $data_setor['total_setor'] ?? 0;

$q_tukar = mysqli_query($conn, "SELECT SUM(poin_ditukar) AS poin_tukar FROM Penukaran WHERE id_user='$id_user'");
$data_tukar = mysqli_fetch_assoc($q_tukar);
$poin_tukar = $data_tukar['poin_tukar'] ?? 0;

$poin_tersedia = $total_poin - $poin_tukar;

// Array tips daur ulang (bisa diambil dari database nanti)
$tips = [
    "Pisahkan sampah organik dan anorganik untuk memudahkan daur ulang.",
    "Gunakan tas belanja kain untuk mengurangi plastik sekali pakai.",
    "Daur ulang kertas dan kardus untuk menghemat pohon.",
    "Kurangi penggunaan botol plastik dengan botol reusable."
];

// Array quote acak
$quotes = [
    "“Sampahmu hari ini bisa jadi harapan bumi esok hari.”",
    "“Daur ulang adalah investasi untuk masa depan.”",
    "“Setiap tindakan kecil bisa membuat perubahan besar.”"
];
$random_quote = $quotes[array_rand($quotes)];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard GO GREEN PMK - Kelola setoran, poin, dan penukaran dengan mudah.">
    <title>Dashboard User - GO GREEN PMK</title>
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
        .recycle-logo {
            width: 120px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .recycle-logo:hover {
            transform: scale(1.1);
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
            max-width: 400px;
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
                            <!-- Notifikasi Badge (contoh: jumlah setoran baru) -->
                            <span class="notification-badge">3</span>
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
        <h3 class="mb-4 text-center">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?> <i class="bi bi-emoji-smile"></i></h3>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="bi bi-box-seam"></i> Total Setoran</h5>
                        <h2><?php echo $total_setor; ?></h2>
                        <p class="text-muted">Setoran yang sudah kamu lakukan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-star"></i> Total Poin</h5>
                        <h2><?php echo $total_poin; ?></h2>
                        <p class="text-muted">Akumulasi poin dari semua setoran</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="bi bi-trophy"></i> Poin Tersedia</h5>
                        <h2><?php echo $poin_tersedia; ?></h2>
                        <p class="text-muted">Bisa ditukar ke hadiah</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Poin -->
        <div class="chart-container mt-4">
            <canvas id="poinChart"></canvas>
        </div>

        <!-- Tips Daur Ulang -->
        <div class="mt-5">
            <h4 class="text-center text-success mb-3"><i class="bi bi-lightbulb"></i> Tips Daur Ulang Hari Ini</h4>
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

    <div class="text-center my-4">
        <img src="../img/pngwing.com.png" alt="Logo Daur Ulang" class="recycle-logo" data-bs-toggle="tooltip" data-bs-placement="top">
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

                // Chart.js for Poin
        const ctx = document.getElementById('poinChart').getContext('2d');
        const poinChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total Poin', 'Poin Tersedia'],
                datasets: [{
                    label: 'Jumlah Poin',
                    data: [<?php echo $total_poin; ?>, <?php echo $poin_tersedia; ?>],
                    backgroundColor: [
                        'rgba(76, 175, 80, 0.6)',  // Hijau untuk Total Poin
                        'rgba(255, 193, 7, 0.6)'   // Kuning untuk Poin Tersedia
                    ],
                    borderColor: [
                        'rgba(76, 175, 80, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
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
                                return context.label + ': ' + context.parsed.y + ' Poin';
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
        