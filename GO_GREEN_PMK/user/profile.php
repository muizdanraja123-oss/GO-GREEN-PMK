<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$pesan = "";

$stmt_select = mysqli_prepare($conn, "SELECT * FROM User WHERE id_user = ?");
mysqli_stmt_bind_param($stmt_select, "i", $id_user);
mysqli_stmt_execute($stmt_select);
$q = mysqli_stmt_get_result($stmt_select);
$user = mysqli_fetch_assoc($q);
mysqli_stmt_close($stmt_select);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_baru = $_POST['nama'];
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];
    $username = $user['username'];

    if(preg_match('/[\'"^%*()}{><;|=+\[\]~`]/', $username) || preg_match('/[\'"^%*()}{><;|=+\[\]~`]/', $nama_baru)) {
        $pesan = "<div class='alert alert-warning'>Gagal memperbarui, nama tidak boleh mengandung karakter khusus.</div>";
    } else {

        if (!password_verify($password_lama, $user['password'])) {
            $pesan = "<div class='alert alert-danger'>Password lama salah!</div>";
        } else {
            $update_nama_berhasil = false;
            $update_pass_berhasil = false;

            $stmt_nama = mysqli_prepare($conn, "UPDATE User SET nama=? WHERE id_user=?");
            mysqli_stmt_bind_param($stmt_nama, "si", $nama_baru, $id_user);
            if (mysqli_stmt_execute($stmt_nama)) {
                $update_nama_berhasil = true;
            } else {
                 $pesan = "<div class='alert alert-danger'>Gagal memperbarui nama: " . htmlspecialchars(mysqli_stmt_error($stmt_nama)) . "</div>";
            }
            mysqli_stmt_close($stmt_nama);

            if (!empty($password_baru)) {
                if ($password_baru === $konfirmasi) {
                    $hash = password_hash($password_baru, PASSWORD_DEFAULT);
                    $stmt_pass = mysqli_prepare($conn, "UPDATE User SET password=? WHERE id_user=?");
                    mysqli_stmt_bind_param($stmt_pass, "si", $hash, $id_user);
                    if (mysqli_stmt_execute($stmt_pass)) {
                        $update_pass_berhasil = true;
                    } else {
                        if (empty($pesan)) {
                             $pesan = "<div class='alert alert-danger'>Gagal memperbarui password: " . htmlspecialchars(mysqli_stmt_error($stmt_pass)) . "</div>";
                        }
                    }
                    mysqli_stmt_close($stmt_pass);

                    if ($update_nama_berhasil && $update_pass_berhasil) {
                        $pesan = "<div class='alert alert-success'>Nama dan password berhasil diperbarui!</div>";
                    }
                } else {
                     if (empty($pesan)) {
                        $pesan = "<div class='alert alert-warning'>Konfirmasi password baru tidak cocok!</div>";
                     }
                }
            } else {
                if ($update_nama_berhasil && empty($pesan)) {
                    $pesan = "<div class='alert alert-success'>Nama berhasil diperbarui!</div>";
                }
            }

            if ($update_nama_berhasil) {
                $_SESSION['nama'] = $nama_baru;
                $stmt_select = mysqli_prepare($conn, "SELECT * FROM User WHERE id_user = ?");
                mysqli_stmt_bind_param($stmt_select, "i", $id_user);
                mysqli_stmt_execute($stmt_select);
                $q = mysqli_stmt_get_result($stmt_select);
                $user = mysqli_fetch_assoc($q);
                mysqli_stmt_close($stmt_select);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profile <?= ($_SESSION['role'] == 'admin' ? 'Admin' : 'User'); ?> - GO GREEN PMK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="bi bi-recycle me-1"></i> GO GREEN PMK - <?= ($_SESSION['role'] == 'admin' ? 'Admin' : 'User'); ?>
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
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'user.php') echo 'active'; ?>" href="user.php">
            <i class="bi bi-people"></i> User
          </a>
        </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i>
            <?php echo htmlspecialchars($_SESSION['nama']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
             <li><h6 class="dropdown-header"><?= ($_SESSION['role'] == 'admin' ? 'Admin' : 'User'); ?> Panel</h6></li>
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


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white text-center">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-1"></i> Profil <?= ($_SESSION['role'] == 'admin' ? 'Admin' : 'User'); ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pesan)) echo $pesan; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']); ?>" required>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password baru (Kosongkan jika tidak ingin mengubah password)">
                        </div>

                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Masukan Password Lama <span class="text-danger">*</span></label>
                            <input type="password" name="password_lama" class="form-control" required>
                        </div>


                        <div class="text-center">
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <footer class="text-center mt-4 mb-3 text-muted">
                <small>&copy; <?= date('Y'); ?> GO GREEN PMK</small>
            </footer>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>