<?php
// Selalu pastikan output adalah JSON
header('Content-Type: application/json');

include 'read.php';

// Ambil parameter periode dari URL, default ke 'weekly'
$period = $_GET['period'] ?? 'weekly';

// Siapkan struktur respons default
$response = [
    'success' => true,
    'totalPengaduan' => 0,
    'belumDiproses' => 0,
    'sedangDiproses' => 0,
    'selesai' => 0,
    'chartData' => [],
    'chartLabels' => [],
    'maxY' => 10.0,
    'yInterval' => 2.0
];

$date_condition = "";
if ($period == 'weekly') {
    $date_condition = "WHERE tgl_pengaduan >= CURDATE() - INTERVAL 6 DAY";
} elseif ($period == 'monthly') {
    $date_condition = "WHERE YEAR(tgl_pengaduan) = YEAR(CURDATE()) AND MONTH(tgl_pengaduan) = MONTH(CURDATE())";
} elseif ($period == 'yearly') {
    $date_condition = "WHERE YEAR(tgl_pengaduan) = YEAR(CURDATE())";
}

// 1. Query untuk statistik kartu (Total, Belum, Proses, Selesai)
// Ganti nilai status jika berbeda, misal: 'Belum Diproses', 'Sedang Diproses'
$sql_stats = "
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) as belum,
        SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
    FROM pengaduan $date_condition
";

$result_stats = $conn->query($sql_stats);
if ($row = $result_stats->fetch_assoc()) {
    $response['totalPengaduan'] = (int)($row['total'] ?? 0);
    $response['belumDiproses'] = (int)($row['belum'] ?? 0);
    $response['sedangDiproses'] = (int)($row['proses'] ?? 0);
    $response['selesai'] = (int)($row['selesai'] ?? 0);
}

// 2. Query untuk data grafik
$max_val = 0; // Untuk menghitung nilai Y maksimum pada grafik

if ($period == 'weekly') {
    $days = [];
    $labels = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $days[$date] = 0;
        $labels[] = date('D', strtotime($date)); // Output: Mon, Tue, Wed
    }

    $sql_chart = "SELECT DATE(tgl_pengaduan) as tanggal, COUNT(*) as jumlah FROM pengaduan WHERE tgl_pengaduan >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(tgl_pengaduan)";
    $result_chart = $conn->query($sql_chart);
    while ($row = $result_chart->fetch_assoc()) {
        $days[$row['tanggal']] = (int)$row['jumlah'];
    }

    $x_axis = 0;
    foreach ($days as $count) {
        $response['chartData'][] = ['x' => (float)$x_axis, 'y' => (float)$count];
        if ($count > $max_val) $max_val = $count;
        $x_axis++;
    }
    $response['chartLabels'] = $labels;
} elseif ($period == 'monthly') {
    // Implementasi untuk bulanan (contoh: 4 minggu terakhir)
    $weeks = [];
    for ($i = 3; $i >= 0; $i--) {
        $week_num = date('W', strtotime("-$i week"));
        $weeks[$week_num] = 0;
    }

    $sql_chart = "SELECT WEEK(tgl_pengaduan) as minggu, COUNT(*) as jumlah FROM pengaduan WHERE tgl_pengaduan >= CURDATE() - INTERVAL 4 WEEK GROUP BY minggu";
    $result_chart = $conn->query($sql_chart);
    while ($row = $result_chart->fetch_assoc()) {
        $weeks[$row['minggu']] = (int)$row['jumlah'];
    }

    $x_axis = 0;
    foreach ($weeks as $week => $count) {
        $response['chartData'][] = ['x' => (float)$x_axis, 'y' => (float)$count];
        $response['chartLabels'][] = "W" . $week; // Output: W34, W35
        if ($count > $max_val) $max_val = $count;
        $x_axis++;
    }
} elseif ($period == 'yearly') {
    $months = [];
    for ($m = 1; $m <= 12; $m++) {
        $months[$m] = 0;
        $response['chartLabels'][] = date('M', mktime(0, 0, 0, $m, 1)); // Output: Jan, Feb
    }

    $sql_chart = "SELECT MONTH(tgl_pengaduan) as bulan, COUNT(*) as jumlah FROM pengaduan WHERE YEAR(tgl_pengaduan) = YEAR(CURDATE()) GROUP BY MONTH(tgl_pengaduan)";
    $result_chart = $conn->query($sql_chart);
    while ($row = $result_chart->fetch_assoc()) {
        $months[(int)$row['bulan']] = (int)$row['jumlah'];
    }

    $x_axis = 0;
    foreach ($months as $count) {
        $response['chartData'][] = ['x' => (float)$x_axis, 'y' => (float)$count];
        if ($count > $max_val) $max_val = $count;
        $x_axis++;
    }
}

// Atur skala Y grafik secara dinamis
$response['maxY'] = $max_val == 0 ? 10.0 : ceil($max_val * 1.25);
$response['yInterval'] = $response['maxY'] / 5;
if ($response['yInterval'] < 1) $response['yInterval'] = 1;


echo json_encode($response);
$conn->close();
