<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bank_sampah";

$conn = mysqli_connect(hostname: $host, username: $user, password: $pass, database: $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
