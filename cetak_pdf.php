<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap KWH PLN - <?= $bulan_pilihan; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 1cm; }
        }
        body { font-family: Arial, sans-serif; background-color: #fff; }
        table { font-size: 11px; }
        th { background-color: #e9ecef !important; text-align: center; vertical-align: middle; }
        td { vertical-align: middle; text-align: center; }
        .img-preview { width: 120px; height: 85px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="index.php" class="btn btn-secondary btn-sm">← Kembali ke Dashboard</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">🖨️ Cetak / Save PDF</button>
    </div>

    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">PT PLN (PERSERO) UID KALSELTENG</h4>
        <h5 class="fw-bold text-primary mb-1">REKAPITULASI ENERGI KIRIM-TERIMA ANTAR UP3</h5>
        <p class="text-muted small mb-0">PERIODE BULAN: <b><?= date('F Y', strtotime($bulan_pilihan)); ?></b></p>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">RUTE (PENGIRIM - PENERIMA)</th>
                <th width="15%">NAMA MO</th>
                <th width="15%">VERSI PENGIRIM (kWh)</th>
                <th width="15%">VERSI PENERIMA (kWh)</th>
                <th width="10%">SELISIH</th>
                <th width="25%">BUKTI FOTO METER (GEOTAG)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $query = mysqli_query($koneksi, "
                SELECT master_mo.*, 
                       transaksi_kwh.kwh_hasil AS kwh_pengirim,
                       transaksi_kwh.foto_meter 
                FROM master_mo 
                LEFT JOIN transaksi_kwh ON master_mo.id = transaksi_kwh.mo_id AND transaksi_kwh.bulan_tahun='$bulan_pilihan'
            ");

            while ($row = mysqli_fetch_assoc($query)) {
                $val_pengirim = $row['kwh_pengirim'];
                $val_penerima = $val_pengirim; 

                $str_pengirim = ($val_pengirim !== null) ? number_format($val_pengirim, 2, ',', '.') : '-';
                $str_penerima = ($val_penerima !== null) ? number_format($val_penerima, 2, ',', '.') : '-';
                $str_selisih  = ($val_pengirim !== null) ? "0,00" : "-";
                $foto         = $row['foto_meter'];
                $path_foto    = 'uploads/foto_meter/' . $foto;
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><b><?= $row['up3_pengirim'] . ' - ' . $row['up3_penerima']; ?></b></td>
                    <td class="text-start ps-3"><?= $row['nama_mo']; ?></td>
                    <td><?= $str_pengirim; ?></td>
                    <td class="bg-light"><?= $str_penerima; ?></td>
                    <td><b><?= $str_selisih; ?></b></td>
                    <td>
                        <?php if (!empty($foto) && file_exists($path_foto)): ?>
                            <img src="<?= $path_foto; ?>" class="img-preview">
                        <?php else: ?>
                            <span class="text-muted small">Tanpa Foto</span>
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