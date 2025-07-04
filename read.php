<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

$koneksi = new mysqli('localhost', 'root', '', 'pengaduan_masyarakat');
$query = mysqli_query($koneksi, "SELECT * FROM user");
$data = mysqli_fetch_all($query, MYSQLI_ASSOC);
echo json_encode($data);

