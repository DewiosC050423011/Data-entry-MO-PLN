<?php
include 'config/koneksi.php';

header('Content-Type: application/json');

$mo_id = isset($_GET['mo_id']) ? intval($_GET['mo_id']) : 0;
$stand_awal = 0;

if ($mo_id > 0) {
    // Ambil Stand Akhir dari transaksi paling akhir MO tersebut
    $query = mysqli_query($koneksi, "SELECT stand_akhir FROM transaksi_kwh WHERE mo_id='$mo_id' ORDER BY id DESC LIMIT 1");
    if ($row = mysqli_fetch_assoc($query)) {
        $stand_awal = floatval($row['stand_akhir']);
    }
}

echo json_encode(['stand_awal' => $stand_awal]);
?>