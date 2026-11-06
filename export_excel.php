<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

// Header paksa browser download jadi Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap_KWH_PLN_Periode_".$bulan_pilihan.".xls");
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
    @page {
        size: A4 landscape;
        margin: 0.5in;
    }
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    th, td { border: 1px solid #000000; padding: 8px; text-align: center; font-size: 11pt; }
    th { background-color: #d9edf7; font-weight: bold; }
    .bg-penerima { background-color: #fcf8e3; }
    .title { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 5px; }
    .subtitle { font-size: 11pt; text-align: center; margin-bottom: 15px; }
</style>
</head>
<body>

    <div class="title">REKAP ENERGI KIRIM-TERIMA ANTAR UP3</div>
    <div class="subtitle">PERIODE BULAN: <?= date('F Y', strtotime($bulan_pilihan)); ?></div>

    <table>
        <thead>
            <tr>
                <th>Kirim dari - Ke</th>
                <th>Versi Pengirim (kWh)</th>
                <th>Versi Penerima (kWh)</th>
                <th>SELISIH</th>
                <th>NAMA MO</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($koneksi, "
                SELECT master_mo.*, 
                       transaksi_kwh.kwh_hasil 
                FROM master_mo 
                LEFT JOIN transaksi_kwh ON master_mo.id = transaksi_kwh.mo_id AND transaksi_kwh.bulan_tahun='$bulan_pilihan'
            ");

            while ($row = mysqli_fetch_assoc($query)) {
                $pengirim_val = ($row['kwh_hasil'] !== null) ? number_format($row['kwh_hasil'], 2, ',', '.') : '-';
                $penerima_val = ($row['kwh_hasil'] !== null) ? number_format($row['kwh_hasil'], 2, ',', '.') : '-';
                $selisih = ($row['kwh_hasil'] !== null) ? "0,00" : "-";
                ?>
                <tr>
                    <td><b><?= $row['up3_pengirim'] . ' - ' . $row['up3_penerima']; ?></b></td>
                    <td><?= $pengirim_val; ?></td>
                    <td class="bg-penerima"><?= $penerima_val; ?></td>
                    <td><b><?= $selisih; ?></b></td>
                    <td style="text-align: left; padding-left: 10px;"><?= $row['nama_mo']; ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

</body>
</html>