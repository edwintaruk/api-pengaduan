<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Hanya terima request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Ambil data JSON dari request body
$input = json_decode(file_get_contents('php://input'), true);

// Validasi data yang diperlukan
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validasi field yang diperlukan
$required_fields = ['judul', 'kategori', 'deskripsi', 'lokasi', 'tanggal_kejadian', 'waktu_kejadian', 'user_id'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Konfigurasi database
$host = 'localhost';
$dbname = 'pengaduan_db';
$username = 'root';
$password = '';

try {
    // Koneksi ke database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gabungkan tanggal dan waktu kejadian
    $tanggal_waktu = $input['tanggal_kejadian'] . ' ' . $input['waktu_kejadian'];

    // Query untuk insert data pengaduan
    $sql = "INSERT INTO pengaduan (
        judul, 
        kategori, 
        deskripsi, 
        lokasi, 
        tanggal_kejadian, 
        waktu_kejadian,
        tanggal_waktu_kejadian,
        user_id, 
        status, 
        tanggal_pengaduan,
        is_anonymous
    ) VALUES (
        :judul, 
        :kategori, 
        :deskripsi, 
        :lokasi, 
        :tanggal_kejadian, 
        :waktu_kejadian,
        :tanggal_waktu_kejadian,
        :user_id, 
        'Belum Diproses', 
        NOW(),
        :is_anonymous
    )";

    $stmt = $pdo->prepare($sql);

    // Bind parameter
    $stmt->bindParam(':judul', $input['judul']);
    $stmt->bindParam(':kategori', $input['kategori']);
    $stmt->bindParam(':deskripsi', $input['deskripsi']);
    $stmt->bindParam(':lokasi', $input['lokasi']);
    $stmt->bindParam(':tanggal_kejadian', $input['tanggal_kejadian']);
    $stmt->bindParam(':waktu_kejadian', $input['waktu_kejadian']);
    $stmt->bindParam(':tanggal_waktu_kejadian', $tanggal_waktu);
    $stmt->bindParam(':user_id', $input['user_id']);
    $is_anonymous = $input['is_anonymous'] ?? 0;
    $stmt->bindParam(':is_anonymous', $is_anonymous);

    // Eksekusi query
    $stmt->execute();

    // Ambil ID pengaduan yang baru dibuat
    $pengaduan_id = $pdo->lastInsertId();

    // Response sukses
    echo json_encode([
        'success' => true,
        'message' => 'Pengaduan berhasil disimpan',
        'pengaduan_id' => $pengaduan_id,
        'data' => [
            'judul' => $input['judul'],
            'kategori' => $input['kategori'],
            'status' => 'Belum Diproses',
            'tanggal_pengaduan' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
