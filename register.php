<?php
try {
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
    require 'db.php';

    $data = json_decode(file_get_contents("php://input"));

    $nama = $data->nama ?? '';
    $email = $data->email ?? '';
    $no_hp = $data->no_hp ?? '';
    $alamat = $data->alamat ?? '';
    $username = $data->username ?? '';
    $password = $data->password ?? '';
    $role = $data->role ?? '';

    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO user (nama, email, no_hp, alamat, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nama, $email, $no_hp, $alamat, $username, $hashed_password, $role);

    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Registrasi berhasil']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Registrasi gagal']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => true, 'message' => $e->getMessage()]);
}
