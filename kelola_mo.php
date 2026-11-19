<?php
$page_title = "Kelola Master MO - PT PLN";
include 'includes/header.php';
requireAdmin();

if (isset($_POST['tambah_mo'])) {
    $nama_mo      = mysqli_real_escape_string($koneksi, trim($_POST['nama_mo']));
    $up3_pengirim = mysqli_real_escape_string($koneksi, trim($_POST['up3_pengirim']));
    $ulp_pengirim = mysqli_real_escape_string($koneksi, trim($_POST['ulp_pengirim']));
    $up3_penerima = mysqli_real_escape_string($koneksi, trim($_POST['up3_penerima']));
    $ulp_penerima = mysqli_real_escape_string($koneksi, trim($_POST['ulp_penerima']));

    $q_in = "INSERT INTO master_mo (nama_mo, up3_pengirim, ulp_pengirim, up3_penerima, ulp_penerima) 
             VALUES ('$nama_mo', '$up3_pengirim', '$ulp_pengirim', '$up3_penerima', '$ulp_penerima')";
    if (mysqli_query($koneksi, $q_in)) {
        catatLog($koneksi, 'TAMBAH MO', "Menambahkan Master MO baru: $nama_mo ($up3_pengirim - $up3_penerima).");
        echo "<script>alert('Master MO berhasil ditambahkan!'); window.location='kelola_mo.php';</script>";
        exit();
    }
}

if (isset($_GET['hapus_id'])) {
    $hapus_id = intval($_GET['hapus_id']);
    $cek_mo = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_mo FROM master_mo WHERE id='$hapus_id'"));
    if ($cek_mo) {
        mysqli_query($koneksi, "DELETE FROM master_mo WHERE id='$hapus_id'");
        catatLog($koneksi, 'HAPUS MO', "Menghapus Master MO: {$cek_mo['nama_mo']}.");
        echo "<script>alert('Master MO berhasil dihapus!'); window.location='kelola_mo.php';</script>";
        exit();
    }
}
?>

<div class="mb-3">
    <a href="index.php" class="text-decoration-none text-secondary fw-semibold small"><i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard</a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card-pln p-4">
            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-plus-circle me-1"></i> Tambah Metering Outlet (MO)</h6>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama MO</label>
                    <input type="text" name="nama_mo" class="form-control form-control-sm" placeholder="Contoh: MO GI TRISAKTI" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Kode UP3 Pengirim</label>
                        <input type="text" name="up3_pengirim" class="form-control form-control-sm" placeholder="Contoh: BJM" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">ULP Pengirim</label>
                        <input type="text" name="ulp_pengirim" class="form-control form-control-sm" placeholder="Nama ULP" required>
                    </div>
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Kode UP3 Penerima</label>
                        <input type="text" name="up3_penerima" class="form-control form-control-sm" placeholder="Contoh: BRB" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">ULP Penerima</label>
                        <input type="text" name="ulp_penerima" class="form-control form-control-sm" placeholder="Nama ULP" required>
                    </div>
                </div>
                <button type="submit" name="tambah_mo" class="btn btn-pln-primary btn-sm w-100 py-2">Simpan Master MO</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-pln overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-secondary">DAFTAR MASTER METERING OUTLET (MO)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-pln">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NAMA MO</th>
                            <th>PENGIRIM (UP3 / ULP)</th>
                            <th>PENERIMA (UP3 / ULP)</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $q_all = mysqli_query($koneksi, "SELECT * FROM master_mo ORDER BY id ASC");
                        while ($m = mysqli_fetch_assoc($q_all)) {
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($m['nama_mo']); ?></td>
                            <td><?= htmlspecialchars($m['up3_pengirim'] . ' - ' . $m['ulp_pengirim']); ?></td>
                            <td><?= htmlspecialchars($m['up3_penerima'] . ' - ' . $m['ulp_penerima']); ?></td>
                            <td class="text-center">
                                <a href="kelola_mo.php?hapus_id=<?= $m['id']; ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Hapus MO <?= htmlspecialchars($m['nama_mo']); ?>?');">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>