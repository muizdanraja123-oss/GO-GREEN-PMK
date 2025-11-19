<?php
session_start();
include 'config/koneksi.php';
if (isset($_SESSION['id_user'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}


if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
     if(preg_match('/[\'"^%*()}{><;|=+\[\]~`]/', $username)) {
        $error = "Gagal login, username tidak boleh mengandung karakter khusus seperti kutip, tanda persen, atau simbol lainnya.";
    }else{

   $stmt = $conn->prepare("SELECT id_user, username, password, role, nama FROM User WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama'] = $row['nama'];

            if ($row['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit;
        }
    }
    $error = "Username atau password salah!";
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - GO GREEN PMK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      align-items: stretch;
      background: #e8f5e9;
      font-family: 'Poppins', sans-serif;
    }

    .login-left {
      flex: 1;
      background-image: url('https://i.pinimg.com/736x/3b/80/d6/3b80d66f36e33be5167db1f635548bd4.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
    }

    .login-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #ffffff;
    }

    .login-box {
      width: 100%;
      max-width: 400px;
      padding: 40px;
    }

    .login-box h3 {
      color: #2e7d32;
      font-weight: 700;
      margin-bottom: 25px;
    }

    .btn-success {
      background-color: #2e7d32;
      border: none;
      transition: all 0.3s;
    }

    .btn-success:hover {
      background-color: #1b5e20;
      transform: scale(1.03);
    }

    .alert {
      animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 992px) {
      .login-left { display: none; }
      body { background: url('https://img.tempo.co/indonesiana/images/all/2023/06/29/f202306292302041.jpg') no-repeat center center/cover; }
      .login-right { background: rgba(255, 255, 255, 0.92); }
      .login-box { max-width: 90%; padding: 30px; }
    }
  </style>
</head>
<body>

  <div class="login-left"></div>
  <div class="login-right">
    <div class="login-box shadow rounded">
      <h3 class="text-center"><a class="navbar-brand fw-bold" href="../GO_GREEN_PMK/index.php">♻️ GO GREEN PMK</h3>
      <form method="POST" action="login.php">
        <div class="mb-3">
          <label class="form-label fw-semibold">Username</label>
          <input type="text" name="username" class="form-control form-control-lg" placeholder="Masukkan username" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <input type="password" name="password" class="form-control form-control-lg" placeholder="Masukkan password" required>
        </div>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger py-2 text-center"><?= $error; ?></div>
        <?php endif; ?>

         <?php if (isset($_GET['register'])): ?>
          <div class="alert alert-success py-2 text-center">Register berhasil! Silakan login menggunakan akun baru</div>
        <?php endif; ?>

        <button type="submit" name="login" class="btn btn-success w-100 mt-3 py-2 fs-5">Masuk</button>
      </form>

      <div class="text-center mt-4">
        <small>Belum punya akun? <a href="register.php" class="text-success fw-semibold">Daftar di sini</a></small>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
