<?php
$page_title = "Dashboard Rekap Energi - PT PLN";
include 'includes/header.php';

$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

$q_admin = mysqli_query($koneksi, "SELECT no_wa FROM users WHERE role='admin' AND no_wa != '' LIMIT 1");
$d_admin = mysqli_fetch_assoc($q_admin);
$wa_admin = !empty($d_admin['no_wa']) ? $d_admin['no_wa'] : '6281234567890';
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
            <?php if (isPetugas()): ?>
                <button type="button" class="btn btn-sm btn-outline-info fw-semibold" onclick="alert('Template Berita Acara sedang dalam tahap penyusunan.');">
                    <i class="bi bi-file-earmark-text me-1"></i> Berita Acara (Segera Hadir)
                </button>
            <?php endif; ?>

            <a href="cetak_pdf.php?bulan=<?= $bulan_pilihan; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Cetak PDF
            </a>
            <a href="export_excel.php?bulan=<?= $bulan_pilihan; ?>" class="btn btn-sm btn-pln-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>

            <?php if (isPetugas() || isAdmin()): ?>
                <a href="entry_kwh.php" class="btn btn-sm btn-pln-primary">
                    <i class="bi bi-camera me-1"></i> Input Stand Meter & Geotag
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabel Rekap Data -->
<div class="card-pln overflow-hidden">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-secondary">
            <i class="bi bi-table me-2 text-primary"></i>REKAPITULASI ENERGI KIRIM-TERIMA (kWh EXIM) ANTAR UP3
        </h6>
        <span class="badge bg-secondary-subtle text-secondary border fs-7">Periode: <?= date('F Y', strtotime($bulan_pilihan)); ?></span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-pln align-middle">
            <thead>
                <tr>
                    <th width="4%" class="text-center">NO</th>
                    <th width="15%">RUTE (PENGIRIM - PENERIMA)</th>
                    <th width="18%">NAMA MO</th>
                    <th width="12%" class="text-end">PENGIRIM (kWh)</th>
                    <th width="12%" class="text-end">PENERIMA (kWh)</th>
                    
                    <?php if (isAdmin() || isSuperAdmin()): ?>
                        <th width="11%" class="text-center">SELISIH (kWh)</th>
                    <?php endif; ?>

                    <th width="14%" class="text-center">FOTO METER (GEOTAG)</th>
                    
                    <?php if (isAdmin()): ?>
                        <th width="10%" class="text-center">AKSI ADMIN</th>
                    <?php elseif (isPetugas()): ?>
                        <th width="12%" class="text-center">KENDALA DATA</th>
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
                           transaksi_kwh.kwh_penerima,
                           transaksi_kwh.foto_meter,
                           transaksi_kwh.foto_penerima
                    FROM master_mo 
                    LEFT JOIN transaksi_kwh ON master_mo.id = transaksi_kwh.mo_id AND transaksi_kwh.bulan_tahun='$bulan_pilihan'
                    ORDER BY master_mo.id ASC
                ");

                if (mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        $val_pengirim = $row['kwh_pengirim'];
                        $val_penerima = $row['kwh_penerima'];

                        $str_pengirim = ($val_pengirim !== null) ? number_format($val_pengirim, 2, ',', '.') : '-';
                        $str_penerima = ($val_penerima !== null) ? number_format($val_penerima, 2, ',', '.') : '-';
                        
                        if ($val_pengirim !== null && $val_penerima !== null) {
                            $selisih_num = abs($val_pengirim - $val_penerima);
                            $str_selisih = number_format($selisih_num, 2, ',', '.');
                            $badge_class = ($selisih_num == 0) ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                        } else {
                            $str_selisih = "Menunggu Lengkap";
                            $badge_class = "bg-warning-subtle text-dark border-warning-subtle";
                        }

                        $foto_kirim  = $row['foto_meter'];
                        $foto_terima = $row['foto_penerima'];
                        ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $no++; ?></td>
                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($row['up3_pengirim'] . ' - ' . $row['up3_penerima']); ?></span></td>
                            <td><span class="fw-medium"><?= htmlspecialchars($row['nama_mo']); ?></span></td>
                            <td class="text-end font-monospace fw-semibold text-primary"><?= $str_pengirim; ?></td>
                            <td class="text-end font-monospace fw-semibold text-success bg-light-subtle"><?= $str_penerima; ?></td>
                            
                            <?php if (isAdmin() || isSuperAdmin()): ?>
                                <td class="text-center">
                                    <span class="badge <?= $badge_class; ?> border px-2"><?= $str_selisih; ?></span>
                                </td>
                            <?php endif; ?>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <?php if (!empty($foto_kirim) && file_exists('uploads/foto_meter/' . $foto_kirim)): ?>
                                        <a href="uploads/foto_meter/<?= $foto_kirim; ?>" target="_blank" title="Foto Pengirim">
                                            <img src="uploads/foto_meter/<?= $foto_kirim; ?>" class="img-thumb-table">
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($foto_terima) && file_exists('uploads/foto_meter/' . $foto_terima)): ?>
                                        <a href="uploads/foto_meter/<?= $foto_terima; ?>" target="_blank" title="Foto Penerima">
                                            <img src="uploads/foto_meter/<?= $foto_terima; ?>" class="img-thumb-table border-success">
                                        </a>
                                    <?php endif; ?>
                                    <?php if (empty($foto_kirim) && empty($foto_terima)): ?>
                                        <span class="text-muted small"><i class="bi bi-camera-video-off me-1"></i>Belum Ada</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <?php if (isAdmin()): ?>
                                <td class="text-center">
                                    <?php if (!empty($row['tx_id'])): ?>
                                        <a href="hapus_kwh.php?id=<?= $row['tx_id']; ?>&bulan=<?= $bulan_pilihan; ?>" 
                                           class="btn btn-sm btn-outline-danger px-2 py-1" 
                                           onclick="return confirm('Hapus data entry <?= htmlspecialchars($row['nama_mo']); ?>?');">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                            <?php elseif (isPetugas()): ?>
                                <td class="text-center">
                                    <?php if (!empty($row['tx_id'])): 
                                        $pesan_wa = urlencode("Halo Admin PLN UID Kalselteng, saya petugas *{$user_nama}* ({$user_role}) ingin melaporkan kesalahan input data kWh EXIM:\n\n- *MO:* {$row['nama_mo']}\n- *Rute:* {$row['up3_pengirim']} - {$row['up3_penerima']}\n- *Periode:* {$bulan_pilihan}\n\nMohon bantuannya untuk penghapusan/koreksi data tersebut. Terima kasih.");
                                    ?>
                                        <a href="https://wa.me/<?= $wa_admin; ?>?text=<?= $pesan_wa; ?>" target="_blank" class="btn btn-sm btn-outline-success px-2 py-1" title="Laporkan salah input ke WA Admin">
                                            <i class="bi bi-whatsapp me-1"></i> Lapor Salah Input
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data master MO.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>