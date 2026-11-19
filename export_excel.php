<?php
session_start();
include 'config/koneksi.php';
include 'includes/auth_helper.php';

checkLogin();

$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$lihat_selisih = (isAdmin() || isSuperAdmin());

catatLog($koneksi, 'EXPORT EXCEL', "Mengunduh laporan Excel periode $bulan_pilihan.");

// Auto deteksi URL Server
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Rekap_KWH_EXIM_PLN_Periode_".$bulan_pilihan.".xls");
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

    <div class="title">REKAP ENERGI KIRIM-TERIMA (kWh EXIM) ANTAR UP3</div>
    <div class="subtitle">PT PLN (PERSERO) UID KALSELTENG &bull; PERIODE BULAN: <?= date('F Y', strtotime($bulan_pilihan)); ?></div>

    <table>
        <thead>
            <tr height="30">
                <th>Kirim dari - Ke</th>
                <th>Versi Pengirim (kWh)</th>
                <th>Versi Penerima (kWh)</th>
                <?php if ($lihat_selisih): ?>
                    <th>SELISIH (kWh)</th>
                <?php endif; ?>
                <th>NAMA MO</th>
                <th>BUKTI FOTO METER (GEOTAG)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($koneksi, "
                SELECT master_mo.*, 
                       transaksi_kwh.kwh_hasil AS kwh_pengirim,
                       transaksi_kwh.kwh_penerima,
                       transaksi_kwh.foto_meter,
                       transaksi_kwh.foto_penerima
                FROM master_mo 
                LEFT JOIN transaksi_kwh ON master_mo.id = transaksi_kwh.mo_id AND transaksi_kwh.bulan_tahun='$bulan_pilihan'
                ORDER BY master_mo.id ASC
            ");

            while ($row = mysqli_fetch_assoc($query)) {
                $val_pengirim = $row['kwh_pengirim'];
                $val_penerima = $row['kwh_penerima'];

                $str_pengirim = ($val_pengirim !== null) ? number_format($val_pengirim, 2, ',', '.') : '-';
                $str_penerima = ($val_penerima !== null) ? number_format($val_penerima, 2, ',', '.') : '-';
                
                if ($val_pengirim !== null && $val_penerima !== null) {
                    $str_selisih = number_format(abs($val_pengirim - $val_penerima), 2, ',', '.');
                } else {
                    $str_selisih = "-";
                }

                $foto_kirim  = $row['foto_meter'];
                $foto_terima = $row['foto_penerima'];
                ?>
                <tr height="35">
                    <td><b><?= htmlspecialchars($row['up3_pengirim'] . ' - ' . $row['up3_penerima']); ?></b></td>
                    <td><?= $str_pengirim; ?></td>
                    <td class="bg-penerima"><?= $str_penerima; ?></td>
                    <?php if ($lihat_selisih): ?>
                        <td><b><?= $str_selisih; ?></b></td>
                    <?php endif; ?>
                    <td style="text-align: left; padding-left: 10px;"><?= htmlspecialchars($row['nama_mo']); ?></td>
                    <td>
                        <?php 
                        $ada_foto = false;
                        if (!empty($foto_kirim) && file_exists('uploads/foto_meter/' . $foto_kirim)): 
                            $ada_foto = true;
                        ?>
                            <a href="<?= $base_url . 'uploads/foto_meter/' . $foto_kirim; ?>" target="_blank" style="color: #0d6efd; font-weight: bold;">
                                📷 Foto Pengirim
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($foto_terima) && file_exists('uploads/foto_meter/' . $foto_terima)): 
                            if ($ada_foto) echo " | ";
                            $ada_foto = true;
                        ?>
                            <a href="<?= $base_url . 'uploads/foto_meter/' . $foto_terima; ?>" target="_blank" style="color: #198754; font-weight: bold;">
                                📷 Foto Penerima
                            </a>
                        <?php endif; ?>

                        <?php if (!$ada_foto): ?>
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