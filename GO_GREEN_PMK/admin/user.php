<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data User - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
            <?php echo $_SESSION['nama']; ?>
          </a>
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
    <h3 class="mb-4"><i class="bi bi-people"></i> Data User & Sisa Poin</h3>

    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <strong>Daftar User</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Total Setoran (Poin)</th>
                        <th>Total Penukaran (Poin)</th>
                        <th>Sisa Poin</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $q = mysqli_query($conn, "SELECT * FROM User WHERE role='user' ORDER BY id_user DESC");
                while ($u = mysqli_fetch_assoc($q)):
                    $id_user = $u['id_user'];

                    $setor = mysqli_query($conn, "SELECT SUM(poin) AS total FROM Setor WHERE id_user='$id_user'");
                    $total_setor = mysqli_fetch_assoc($setor)['total'] ?? 0;

                    $tukar = mysqli_query($conn, "SELECT SUM(poin_ditukar) AS total FROM Penukaran WHERE id_user='$id_user'");
                    $total_tukar = mysqli_fetch_assoc($tukar)['total'] ?? 0;

                    $sisa = $total_setor - $total_tukar;
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($u['nama']); ?></td>
                        <td><?= htmlspecialchars($u['username']); ?></td>
                        <td>
                            <?php if ($u['role'] == 'admin'): ?>
                                <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-success">User</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $total_setor; ?></td>
                        <td><?= $total_tukar; ?></td>
                        <td>
                            <?php if ($sisa < 0): ?>
                                <span class="text-danger fw-bold"><?= $sisa; ?></span>
                            <?php else: ?>
                                <span class="text-primary fw-bold"><?= $sisa; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="text-center mt-4 mb-3 text-muted">
    <small>&copy; <?= date('Y'); ?> GO GREEN PMK</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
