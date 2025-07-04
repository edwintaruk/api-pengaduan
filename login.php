<?php
try {
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");

    require 'db.php'; // koneksi ke database

    $data = json_decode(file_get_contents("php://input"));

    $username = $data->username ?? '';
    $password = $data->password ?? '';

    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            'status' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user['id_user'],
                'nama' => $user['nama'],
                'username' => $user['username'],
                'email' => $user['email'],
                'no_hp' => $user['no_hp'],
                'alamat' => $user['alamat'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Username atau password salah']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => true, 'message' => $e->getMessage()]);
}
