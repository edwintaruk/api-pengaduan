<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require 'db.php';

$sql = "SELECT * FROM pengaduan ORDER BY id_pengaduan DESC";
$result = $conn->query($sql);

$response = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
}

echo json_encode($response);
