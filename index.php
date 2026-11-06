<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$nama = $_SESSION['nama_lengkap'];

// Ambil bulan dari filter, jika tidak ada pakai bulan berjalan
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Rekap KWH PLN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PLN KWH MONITORING</a>
            <div class="d-flex align-items-center text-white">
                <?php if ($role == 'admin'): ?>
                    <a href="master_mo.php" class="btn btn-warning btn-sm me-3 fw-bold">⚙️ Kelola MO</a>
                <?php endif; ?>
                <span class="me-3">Halo, <strong><?= $nama; ?></strong> (<?= strtoupper($role); ?>)</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <!-- Filter & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">REKAP ENERGI KIRIM-TERIMA ANTAR UP3</h4>
                <p class="text-muted small mb-0">Periode Data: <strong><?= date('F Y', strtotime($bulan_filter)); ?></strong></p>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2 mt-3 mt-md-0">
                <!-- Form Filter Bulan -->
                <form method="GET" class="d-flex gap-2">
                    <input type="month" name="bulan" class="form-control form-control-sm" value="<?= $bulan_filter; ?>" onchange="this.form.submit()">
                </form>

                <?php if ($role == 'admin' || $role == 'pengirim'): ?>
                    <a href="entry_kwh.php" class="btn btn-success btn-sm fw-bold">+ Entry Stand</a>
                <?php endif; ?>
                
                <a href="export_excel.php?bulan=<?= $bulan_filter; ?>" class="btn btn-outline-success btn-sm fw-bold">Export Excel</a>
            </div>
        </div>

        <!-- Tabel Rekap -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0 align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Kirim dari - Ke</th>
                                <th>Versi Pengirim (kWh)</th>
                                <th>Versi Penerima (kWh)</th>
                                <th>SELISIH</th>
                                <th>NAMA MO</th>
                                <th>Status BA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($koneksi, "
                                SELECT master_mo.*, 
                                       transaksi_kwh.kwh_hasil, 
                                       transaksi_kwh.status_verifikasi 
                                FROM master_mo 
                                LEFT JOIN transaksi_kwh ON master_mo.id = transaksi_kwh.mo_id AND transaksi_kwh.bulan_tahun='$bulan_filter'
                            ");

                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $pengirim_val = ($row['kwh_hasil'] !== null) ? number_format($row['kwh_hasil'], 2, ',', '.') : '-';
                                    $penerima_val = ($row['kwh_hasil'] !== null) ? number_format($row['kwh_hasil'], 2, ',', '.') : '-';
                                    $selisih = ($row['kwh_hasil'] !== null) ? "0,00" : "-";
                                    $status = $row['status_verifikasi'] ?? 'Belum Isi';
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?= $row['up3_pengirim'] . ' - ' . $row['up3_penerima']; ?></td>
                                        <td class="bg-light"><?= $pengirim_val; ?></td>
                                        <td class="bg-warning-subtle"><?= $penerima_val; ?></td>
                                        <td class="fw-bold text-success"><?= $selisih; ?></td>
                                        <td class="text-start ps-3"><?= $row['nama_mo']; ?></td>
                                        <td>
                                            <?php if ($status == 'verified'): ?>
                                                <span class="badge bg-success">Verified</span>
                                            <?php elseif ($status == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending BA</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Belum Input</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-muted py-3'>Belum ada data MO.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
</html>