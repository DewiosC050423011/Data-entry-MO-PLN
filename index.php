<?php
$page_title = "Dashboard Rekap Energi - PT PLN";
include 'includes/header.php';

$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
?>

<!-- Top Action Bar -->
<div class="card-pln p-3 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-4">
            <form method="GET" action="index.php" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 small fw-semibold text-muted text-nowrap">Periode Bulan:</label>
                <input type="month" name="bulan" class="form-control form-control-sm" value="<?= $bulan_pilihan; ?>" onchange="this.form.submit()">
            </form>
        </div>
        <div class="col-md-8 text-md-end d-flex justify-content-md-end gap-2 flex-wrap">
            <a href="cetak_pdf.php?bulan=<?= $bulan_pilihan; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Cetak Laporan (PDF)
            </a>
            <a href="export_excel.php?bulan=<?= $bulan_pilihan; ?>" class="btn btn-sm btn-pln-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (.xls)
            </a>
            <a href="entry_kwh.php" class="btn btn-sm btn-pln-primary">
                <i class="bi bi-camera me-1"></i> Input Stand Meter & Geotag
            </a>
        </div>
    </div>
</div>

<!-- Tabel Rekap Data -->
<div class="card-pln overflow-hidden">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-secondary">
            <i class="bi bi-table me-2 text-primary"></i>REKAPITULASI ENERGI KIRIM-TERIMA ANTAR UP3
        </h6>
        <span class="badge bg-secondary-subtle text-secondary border fs-7">Periode: <?= date('F Y', strtotime($bulan_pilihan)); ?></span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-pln align-middle">
            <thead>
                <tr>
                    <th width="4%" class="text-center">NO</th>
                    <th width="16%">RUTE (PENGIRIM - PENERIMA)</th>
                    <th width="18%">NAMA MO</th>
                    <th width="13%" class="text-end">PENGIRIM (kWh)</th>
                    <th width="13%" class="text-end">PENERIMA (kWh)</th>
                    <th width="10%" class="text-center">SELISIH</th>
                    <th width="14%" class="text-center">FOTO METER (GEOTAG)</th>
                    
                    <?php if (isAdmin()): ?>
                        <th width="12%" class="text-center">AKSI ADMIN</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $query = mysqli_query($koneksi, "
                    SELECT master_mo.*, 
                           transaksi_kwh.id AS tx_id,
                           transaksi_kwh.kwh_hasil AS kwh_pengirim,
                           transaksi_kwh.foto_meter 
                    FROM master_mo 
                    LEFT JOIN transaksi_kwh ON master_mo.id = transaksi_kwh.mo_id AND transaksi_kwh.bulan_tahun='$bulan_pilihan'
                    ORDER BY master_mo.id ASC
                ");

                if (mysqli_num_rows($query) > 0) {
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
                            <td class="text-center text-muted fw-semibold"><?= $no++; ?></td>
                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($row['up3_pengirim'] . ' - ' . $row['up3_penerima']); ?></span></td>
                            <td><span class="fw-medium"><?= htmlspecialchars($row['nama_mo']); ?></span></td>
                            <td class="text-end font-monospace fw-semibold"><?= $str_pengirim; ?></td>
                            <td class="text-end font-monospace text-muted bg-light-subtle"><?= $str_penerima; ?></td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success border border-success-subtle px-2"><?= $str_selisih; ?></span></td>
                            <td class="text-center">
                                <?php if (!empty($foto) && file_exists($path_foto)): ?>
                                    <a href="<?= $path_foto; ?>" target="_blank" title="Klik untuk memperbesar">
                                        <img src="<?= $path_foto; ?>" class="img-thumb-table">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic"><i class="bi bi-camera-video-off me-1"></i>Belum Ada</span>
                                <?php endif; ?>
                            </td>
                            
                            <?php if (isAdmin()): ?>
                                <td class="text-center">
                                    <?php if (!empty($row['tx_id'])): ?>
                                        <a href="hapus_kwh.php?id=<?= $row['tx_id']; ?>&bulan=<?= $bulan_pilihan; ?>" 
                                           class="btn btn-sm btn-outline-danger px-2 py-1 fs-7" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data transaksi <?= htmlspecialchars($row['nama_mo']); ?>? Foto geotag akan terhapus permanen.');">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fs-7">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>

                        </tr>
                        <?php
                    }
                } else {
                    $colspan = isAdmin() ? 8 : 7;
                    echo '<tr><td colspan="'.$colspan.'" class="text-center py-4 text-muted">Belum ada data tersimpan.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>