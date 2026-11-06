<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

// Auto deteksi URL Server
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Rekap_KWH_PLN_Periode_".$bulan_pilihan.".xls");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" 
      xmlns:x="urn:schemas-microsoft-com:office:excel" 
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    @page { size: A4 landscape; margin: 0.5in; }
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    th, td { border: 1px solid #000000; padding: 8px; text-align: center; font-size: 10pt; vertical-align: middle; }
    th { background-color: #d9edf7; font-weight: bold; }
    .bg-penerima { background-color: #fcf8e3; }
    .title { font-size: 14pt; font-weight: bold; text-align: center; }
    .subtitle { font-size: 11pt; text-align: center; margin-bottom: 15px; }
</style>
</head>
<body>

    <div class="title">REKAP ENERGI KIRIM-TERIMA ANTAR UP3</div>
    <div class="subtitle">PERIODE BULAN: <?= date('F Y', strtotime($bulan_pilihan)); ?></div>

    <table>
        <thead>
            <tr height="30">
                <th>Kirim dari - Ke</th>
                <th>Versi Pengirim (kWh)</th>
                <th>Versi Penerima (kWh)</th>
                <th>SELISIH</th>
                <th>NAMA MO</th>
                <th>BUKTI FOTO METER (GEOTAG)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($koneksi, "
                SELECT master_mo.*, 
                       transaksi_kwh.kwh_hasil AS kwh_pengirim,
                       transaksi_kwh.foto_meter 
                FROM master_mo 
                LEFT JOIN transaksi_kwh ON master_mo.id = transaksi_kwh.mo_id AND transaksi_kwh.bulan_tahun='$bulan_pilihan'
            ");

            while ($row = mysqli_fetch_assoc($query)) {
                $val_pengirim = $row['kwh_pengirim'];
                $val_penerima = $val_pengirim; // Disamakan sementara

                $str_pengirim = ($val_pengirim !== null) ? number_format($val_pengirim, 2, ',', '.') : '-';
                $str_penerima = ($val_penerima !== null) ? number_format($val_penerima, 2, ',', '.') : '-';
                $str_selisih  = ($val_pengirim !== null) ? "0,00" : "-";
                $foto         = $row['foto_meter'];
                ?>
                <tr height="35">
                    <td><b><?= $row['up3_pengirim'] . ' - ' . $row['up3_penerima']; ?></b></td>
                    <td><?= $str_pengirim; ?></td>
                    <td class="bg-penerima"><?= $str_penerima; ?></td>
                    <td><b><?= $str_selisih; ?></b></td>
                    <td style="text-align: left; padding-left: 10px;"><?= $row['nama_mo']; ?></td>
                    <td>
                        <?php 
                        $path_foto = 'uploads/foto_meter/' . $foto;
                        if (!empty($foto) && file_exists($path_foto)): 
                            $img_url = $base_url . $path_foto;
                        ?>
                            <a href="<?= $img_url; ?>" target="_blank" style="color: #0d6efd; font-weight: bold; text-decoration: underline;">
                                📷 Lihat Foto Geotag
                            </a>
                        <?php else: ?>
                            <span style="color: #888;">Tidak Ada Foto</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

</body>
</html>