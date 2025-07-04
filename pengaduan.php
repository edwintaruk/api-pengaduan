<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require 'db.php';

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data form
    $id_user = $_POST['id_user'] ?? null;
    $judul_pengaduan = $_POST['judul_pengaduan'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $jam = $_POST['jam'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $is_anonim = $_POST['is_anonim'] ?? '1'; // default tidak anonim

    $lampiran = '';
    $foto = '';

    // Upload lampiran
    if (isset($_FILES['lampiran'])) {
        $lampiran_tmp = $_FILES['lampiran']['tmp_name'];
        $lampiran_name = basename($_FILES['lampiran']['name']);
        $lampiran_path = 'uploads/lampiran/' . $lampiran_name;
        move_uploaded_file($lampiran_tmp, $lampiran_path);
        $lampiran = $lampiran_name;
    }else{
        $lampiran = null;
    }

    // Upload foto
    if (isset($_FILES['foto'])) {
        $foto_tmp = $_FILES['foto']['tmp_name'];
        $foto_name = basename($_FILES['foto']['name']);
        $foto_path = 'uploads/foto/' . $foto_name;
        move_uploaded_file($foto_tmp, $foto_path);
        $foto = $foto_name;
    }else{
        $foto = null;
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO pengaduan (id_user, judul_pengaduan, kategori, deskripsi, latitude, longitude, alamat, jam, tanggal, lampiran, foto, is_anonim) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss",$id_user, $judul_pengaduan, $kategori, $deskripsi, $latitude, $longitude, $alamat, $jam, $tanggal, $lampiran, $foto, $is_anonim);

    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Pengaduan berhasil dikirim']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Gagal menyimpan pengaduan']);
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Metode tidak diizinkan']);
}
