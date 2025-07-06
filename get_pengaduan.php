<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require 'db.php';

$data = json_decode(file_get_contents("php://input"));
$role = $data->role ?? '';

$response = [];

try {
    if ($role === 'AdminSuper') {
        $sql = "SELECT * FROM pengaduan ORDER BY id_pengaduan DESC";
        $stmt = $conn->prepare($sql);
    } else {
        // Mapping role ke kategori
        $kategoriMap = [
            'AdminInfrastruktur' => 'Infrastruktur',
            'AdminPelayanan'     => 'Pelayanan Publik',
            'AdminKeamanan'      => 'Keamanan',
            'AdminLingkungan'    => 'Lingkungan',
        ];

        $kategori = $kategoriMap[$role] ?? '';

        if ($kategori === '') {
            echo json_encode(['status' => false, 'message' => 'Role tidak dikenal']);
            exit;
        }

        $sql = "SELECT * FROM pengaduan WHERE kategori = ? ORDER BY id_pengaduan DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $kategori);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Berhasil mengambil data pengaduan',
        'data' => $response
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
