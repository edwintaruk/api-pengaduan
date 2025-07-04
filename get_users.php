<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require 'db.php';

try {
    $result = $conn->query("SELECT nama, email FROM user where role = 'Pengguna'");

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Berhasil mengambil data pengguna',
        'data' => $users
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal mengambil data: ' . $e->getMessage()
    ]);
}