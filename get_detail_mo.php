<?php
include 'config/koneksi.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM master_mo WHERE id='$id'");
    $data = mysqli_fetch_assoc($q);
    echo json_encode($data);
} else {
    echo json_encode(null);
}
?>