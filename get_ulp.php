<?php
include 'config/koneksi.php';
header('Content-Type: application/json');

$up3_input = isset($_GET['up3']) ? trim($_GET['up3']) : '';
$data_ulp = [];

if (!empty($up3_input)) {
    // Mapping singkatan ke nama UP3 resmi di tabel up3
    $map_up3 = [
        'BJM' => 'BANJARMASIN',
        'BRB' => 'BARABAI',
        'KPS' => 'KUALA KAPUAS',
        'PRY' => 'PALANGKARAYA',
        'KTB' => 'TANAH BUMBU',
        'PBN' => 'PANGKALAN BUN'
    ];

    $nama_up3_search = isset($map_up3[strtoupper($up3_input)]) ? $map_up3[strtoupper($up3_input)] : $up3_input;

    $query = mysqli_query($koneksi, "
        SELECT ulp.* 
        FROM ulp 
        JOIN up3 ON ulp.up3_id = up3.id 
        WHERE up3.nama_up3 LIKE '%$nama_up3_search%' 
        ORDER BY ulp.nama_ulp ASC
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        $data_ulp[] = $row;
    }
}

echo json_encode($data_ulp);
?>