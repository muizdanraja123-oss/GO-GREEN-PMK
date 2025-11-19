<?php
include 'config/koneksi.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $nama = $_POST['nama'];
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];
    $role = 'user';

    function validatePasswordPolicy($password) {
        $errors = [];
        if (strlen($password) < 8) $errors[] = "Password harus minimal 8 karakter.";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password harus mengandung setidaknya satu huruf besar.";
        if (!preg_match('/[a-z]/', $password)) $errors[] = "Password harus mengandung setidaknya satu huruf kecil.";
        if (!preg_match('/[0-9]/', $password)) $errors[] = "Password harus mengandung setidaknya satu angka.";
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) $errors[] = "Password harus mengandung setidaknya satu simbol.";
        $commonPasswords = ['password', '123456', 'qwerty', 'admin', 'welcome'];
        if (in_array(strtolower($password), $commonPasswords)) $errors[] = "Password terlalu umum. Gunakan kombinasi yang lebih unik.";
        return $errors;
    }

    if (preg_match('/[\'"^%*()}{><;|=+\[\]~`]/', $username) || preg_match('/[\'"^%*()}{><;|=+\[\]~`]/', $nama)) {
        $error = "Gagal mendaftar, username dan nama tidak boleh mengandung karakter khusus seperti kutip, tanda persen, atau simbol lainnya.";
    } else {
        $stmt = $conn->prepare("SELECT id_user FROM User WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $cek = $stmt->get_result();

        if ($cek->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } elseif ($password != $konfirmasi) {
            $error = "Password dan konfirmasi tidak cocok!";
        } else {
            $policyErrors = validatePasswordPolicy($password);
            if (!empty($policyErrors)) {
                $error = implode("<br>", $policyErrors);
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO User (username, password, nama, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $hash, $nama, $role);
                if ($stmt->execute()) {
                    header("Location: login.php?register=success");
                    exit;
                } else {
                    $error = "Gagal mendaftar. Coba lagi.";
                }
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - GO GREEN PMK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Daftar Akun</h3>
                        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Minimal 8 karakter, kombinasi huruf besar, kecil, angka, dan simbol.</small>
                            </div>
                            <div class="mb-3">
                                <label>Konfirmasi Password</label>
                                <input type="password" name="konfirmasi" class="form-control" required>
                            </div>
                            <button type="submit" name="register" class="btn btn-success w-100">Daftar</button>
                        </form>
                        <div class="text-center mt-3">
                            <small>Sudah punya akun? <a href="login.php">Login di sini</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>