<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require 'db.php';

try {
    $data = json_decode(file_get_contents("php://input"));
    $id_user = $data->id_user ?? null;

    if (!$id_user) {
        echo json_encode(['status' => false, 'message' => 'ID user tidak ditemukan']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM pengaduan WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    $laporan = [];
    while ($row = $result->fetch_assoc()) {
        $laporan[] = $row;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Berhasil mengambil laporan',
        'data' => $laporan
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Kesalahan: ' . $e->getMessage()
    ]);
}
