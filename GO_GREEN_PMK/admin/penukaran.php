<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$alert = '';

function hitungSisaPoin($conn, $id_user, $exclude_id_penukaran = 0) {
    $stmt_setor = mysqli_prepare($conn, "SELECT SUM(poin) AS total FROM Setor WHERE id_user=?");
    mysqli_stmt_bind_param($stmt_setor, "i", $id_user);
    mysqli_stmt_execute($stmt_setor);
    $result_setor = mysqli_stmt_get_result($stmt_setor);
    $total_setor = mysqli_fetch_assoc($result_setor)['total'] ?? 0;
    mysqli_stmt_close($stmt_setor);

    $query_tukar = "SELECT SUM(poin_ditukar) AS total FROM Penukaran WHERE id_user=?";
    
    if ($exclude_id_penukaran > 0) {
        $query_tukar .= " AND id_penukaran != ?";
    }
    
    $stmt_tukar = mysqli_prepare($conn, $query_tukar);

    if ($exclude_id_penukaran > 0) {
        mysqli_stmt_bind_param($stmt_tukar, "ii", $id_user, $exclude_id_penukaran);
    } else {
        mysqli_stmt_bind_param($stmt_tukar, "i", $id_user);
    }

    mysqli_stmt_execute($stmt_tukar);
    $result_tukar = mysqli_stmt_get_result($stmt_tukar);
    $total_tukar = mysqli_fetch_assoc($result_tukar)['total'] ?? 0;
    mysqli_stmt_close($stmt_tukar);

    return $total_setor - $total_tukar;
}

if (isset($_POST['tambah'])) {
    $id_user = intval($_POST['id_user']);
    $tanggal = $_POST['tanggal'];
    $item = $_POST['item'];
    $poin_ditukar = intval($_POST['poin_ditukar']);

    $sisa_poin = hitungSisaPoin($conn, $id_user);

    if ($sisa_poin >= $poin_ditukar) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Penukaran (id_user, tanggal, item, poin_ditukar) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issi", $id_user, $tanggal, $item, $poin_ditukar); 
            
            if (mysqli_stmt_execute($stmt)) {
                $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                              <i class="bi bi-check-circle"></i> Penukaran berhasil disimpan!
                              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
            } else {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                              <i class="bi bi-x-circle"></i> Gagal menyimpan data! Error: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '
                              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                      <i class="bi bi-x-circle"></i> Gagal! Poin user tidak mencukupi. (Sisa: ' . htmlspecialchars($sisa_poin) . ' poin)
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
    }
}

if (isset($_POST['edit'])) {
    $id_penukaran = intval($_POST['id_penukaran']);
    $tanggal = $_POST['tanggal'];
    $item = $_POST['item'];
    $poin_ditukar = intval($_POST['poin_ditukar']);

    $q_user = mysqli_query($conn, "SELECT id_user FROM Penukaran WHERE id_penukaran='$id_penukaran'");
    $id_user = mysqli_fetch_assoc($q_user)['id_user'];
    $sisa_poin = hitungSisaPoin($conn, $id_user, $id_penukaran);

    if ($sisa_poin >= $poin_ditukar) {
        $stmt = mysqli_prepare($conn, "UPDATE Penukaran SET tanggal=?, item=?, poin_ditukar=? WHERE id_penukaran=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssii", $tanggal, $item, $poin_ditukar, $id_penukaran); 
            
            if (mysqli_stmt_execute($stmt)) {
                $alert = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                              <i class="bi bi-info-circle"></i> Data penukaran berhasil diperbarui.
                              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
            } else {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                              <i class="bi bi-x-circle"></i> Gagal memperbarui data! Error: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '
                              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                      <i class="bi bi-x-circle"></i> Gagal memperbarui! Poin user tidak mencukupi. (Sisa: ' . htmlspecialchars($sisa_poin) . ' poin)
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
    }
}

if (isset($_POST['hapus'])) {
    $id_penukaran = intval($_POST['id_penukaran']);
    $stmt = mysqli_prepare($conn, "DELETE FROM Penukaran WHERE id_penukaran=?");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_penukaran);

        if (mysqli_stmt_execute($stmt)) {
            $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                          🗑️ Data penukaran berhasil dihapus.
                          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
        } else {
            $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <i class="bi bi-x-circle"></i> Gagal menghapus data. Error: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '
                          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
        }
        mysqli_stmt_close($stmt);
    }
}

$data_penukaran = mysqli_query($conn, "
    SELECT p.*, u.nama 
    FROM Penukaran p
    JOIN User u ON p.id_user = u.id_user
    ORDER BY p.tanggal DESC
");
$user = mysqli_query($conn, "SELECT id_user, nama FROM User WHERE role='user'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Penukaran - Admin</title>
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
            <?php echo htmlspecialchars($_SESSION['nama']); ?>
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
    <h3 class="mb-4"><i class="bi bi-gift"></i> Kelola Data Penukaran</h3>

    <?= $alert; ?> <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#tambahModal">
        <i class="bi bi-plus-circle"></i> Tambah Penukaran
    </button>

    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <strong>Data Penukaran Poin</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-success text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama User</th>
                        <th>Tanggal</th>
                        <th>Item</th>
                        <th>Poin Ditukar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while ($row = mysqli_fetch_assoc($data_penukaran)): ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars($no++); ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= htmlspecialchars($row['tanggal']); ?></td>
                        <td><?= htmlspecialchars($row['item']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['poin_ditukar']); ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= htmlspecialchars($row['id_penukaran']); ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#hapusModal<?= htmlspecialchars($row['id_penukaran']); ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal<?= htmlspecialchars($row['id_penukaran']); ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST">
                            <div class="modal-header bg-primary text-white">
                              <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Penukaran</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="id_penukaran" value="<?= htmlspecialchars($row['id_penukaran']); ?>">
                              <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($row['tanggal']); ?>" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Item</label>
                                <input type="text" name="item" class="form-control" value="<?= htmlspecialchars($row['item']); ?>" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Poin Ditukar</label>
                                <input type="number" name="poin_ditukar" class="form-control" value="<?= htmlspecialchars($row['poin_ditukar']); ?>" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <div class="modal fade" id="hapusModal<?= htmlspecialchars($row['id_penukaran']); ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <form method="POST">
                            <div class="modal-header bg-danger text-white">
                              <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus</h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                              <p>Yakin ingin menghapus penukaran 
                                <strong><?= htmlspecialchars($row['item']); ?></strong> milik 
                                <strong><?= htmlspecialchars($row['nama']); ?></strong>?</p>
                              <input type="hidden" name="id_penukaran" value="<?= htmlspecialchars($row['id_penukaran']); ?>">
                            </div>
                            <div class="modal-footer justify-content-center">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" name="hapus" class="btn btn-danger">Hapus</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Penukaran</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Pilih User</label>
            <select name="id_user" class="form-select" required>
              <option value="">-- Pilih User --</option>
              <?php 
                mysqli_data_seek($user, 0); 
                while ($u = mysqli_fetch_assoc($user)): 
              ?>
                <option value="<?= htmlspecialchars($u['id_user']); ?>"><?= htmlspecialchars($u['nama']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Item</label>
            <input type="text" name="item" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Poin Ditukar</label>
            <input type="number" name="poin_ditukar" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer class="text-center mt-5 mb-3 text-muted">
    <small>&copy; <?= date('Y'); ?> GO GREEN PMK</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>