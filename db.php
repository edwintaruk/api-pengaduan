<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pengaduan_masyarakat"; // Ganti dengan nama database kamu

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['status' => false, 'message' => 'Koneksi gagal']));
}
?>
