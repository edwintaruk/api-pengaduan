<?php
try {
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");

    require 'db.php'; // koneksi ke database

    // Ambil input JSON dari Flutter
    $data = json_decode(file_get_contents("php://input"));

    $id_pengaduan = $data->id_pengaduan ?? null;
    $status = $data->status ?? null;

    if (!$id_pengaduan || !$status) {
        echo json_encode(['status' => false, 'message' => 'ID atau status tidak boleh kosong']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE pengaduan SET status = ? WHERE id_pengaduan = ?");
    $stmt->bind_param("si", $status, $id_pengaduan);
    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['status' => true, 'message' => 'Status berhasil diperbarui']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Gagal memperbarui status']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}