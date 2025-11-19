<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
$q_user = mysqli_query($conn, "SELECT COUNT(*) AS total_user FROM User WHERE role='user'");
$total_user = mysqli_fetch_assoc($q_user)['total_user'];

$q_setor = mysqli_query($conn, "SELECT COUNT(*) AS total_setor FROM Setor");
$total_setor = mysqli_fetch_assoc($q_setor)['total_setor'];

$q_poin = mysqli_query($conn, "SELECT SUM(poin) AS total_poin FROM Setor");
$total_poin = mysqli_fetch_assoc($q_poin)['total_poin'] ?? 0;

$q_tukar = mysqli_query($conn, "SELECT COUNT(*) AS total_penukaran FROM Penukaran");
$total_tukar = mysqli_fetch_assoc($q_tukar)['total_penukaran'];

$data_setor = mysqli_query($conn, "
    SELECT s.*, u.nama 
    FROM Setor s 
    JOIN User u ON s.id_user = u.id_user 
    ORDER BY s.tanggal DESC 
    LIMIT 5
");

$data_tukar = mysqli_query($conn, "
    SELECT p.*, u.nama 
    FROM Penukaran p 
    JOIN User u ON p.id_user = u.id_user 
    ORDER BY p.tanggal DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - GO GREEN PMK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="../index.php">
      <i class="bi bi-recycle me-1"></i> GO GREEN PMK - Admin
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
        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'user.php') echo 'active'; ?>" href="user.php">
            <i class="bi bi-people"></i> User
          </a>
        </li>
      </ul>

      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i>
            <?php echo htmlspecialchars($_SESSION['nama']); ?> </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header">Admin Panel</h6></li>
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
    <h3 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard Admin</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-success text-center">
                <div class="card-body">
                    <h5 class="text-success">Total User</h5>
                    <h2><?= htmlspecialchars($total_user); ?></h2> <p class="text-muted mb-0">User aktif di sistem</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary text-center">
                <div class="card-body">
                    <h5 class="text-primary">Total Setoran</h5>
                    <h2><?= htmlspecialchars($total_setor); ?></h2> <p class="text-muted mb-0">Data setoran sampah</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning text-center">
                <div class="card-body">
                    <h5 class="text-warning">Total Poin</h5>
                    <h2><?= htmlspecialchars($total_poin); ?></h2> <p class="text-muted mb-0">Dihasilkan dari setoran</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger text-center">
                <div class="card-body">
                    <h5 class="text-danger">Total Penukaran</h5>
                    <h2><?= htmlspecialchars($total_tukar); ?></h2> <p class="text-muted mb-0">Transaksi penukaran poin</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <strong>Setoran Terbaru</strong>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-success">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nama User</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Berat (kg)</th>
                                <th>Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($data_setor) > 0):
                            while ($row = mysqli_fetch_assoc($data_setor)):
                                // PERBAIKAN: Menggunakan htmlspecialchars() pada setiap output data
                                echo "<tr>
                                        <td class='text-center'>" . htmlspecialchars($no) . "</td>
                                        <td>" . htmlspecialchars($row['nama']) . "</td>
                                        <td>" . htmlspecialchars($row['tanggal']) . "</td>
                                        <td>" . htmlspecialchars($row['jenis_sampah']) . "</td>
                                        <td class='text-center'>" . htmlspecialchars($row['berat']) . "</td>
                                        <td class='text-center'>" . htmlspecialchars($row['poin']) . "</td>
                                    </tr>";
                                $no++;
                            endwhile;
                        else:
                            echo "<tr><td colspan='6' class='text-center text-muted'>Belum ada data setoran.</td></tr>";
                        endif;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <strong>Penukaran Terbaru</strong>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-success">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nama User</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Poin Ditukar</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($data_tukar) > 0):
                            while ($row = mysqli_fetch_assoc($data_tukar)):
                                // PERBAIKAN: Menggunakan htmlspecialchars() pada setiap output data
                                echo "<tr>
                                        <td class='text-center'>" . htmlspecialchars($no) . "</td>
                                        <td>" . htmlspecialchars($row['nama']) . "</td>
                                        <td>" . htmlspecialchars($row['tanggal']) . "</td>
                                        <td>" . htmlspecialchars($row['item']) . "</td>
                                        <td class='text-center'>" . htmlspecialchars($row['poin_ditukar']) . "</td>
                                    </tr>";
                                $no++;
                            endwhile;
                        else:
                            echo "<tr><td colspan='5' class='text-center text-muted'>Belum ada data penukaran.</td></tr>";
                        endif;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center mt-5 mb-3 text-muted">
    <small>&copy; <?php echo date('Y'); ?> GO GREEN PMK</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 