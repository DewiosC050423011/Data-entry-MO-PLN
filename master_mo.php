<?php
session_start();
include 'config/koneksi.php';

// Hanya Admin yang bisa akses halaman ini
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Proses Tambah MO
if (isset($_POST['tambah'])) {
    $nama_mo      = mysqli_real_escape_string($koneksi, $_POST['nama_mo']);
    $up3_pengirim = mysqli_real_escape_string($koneksi, $_POST['up3_pengirim']);
    $up3_penerima = mysqli_real_escape_string($koneksi, $_POST['up3_penerima']);

    mysqli_query($koneksi, "INSERT INTO master_mo (nama_mo, up3_pengirim, up3_penerima) VALUES ('$nama_mo', '$up3_pengirim', '$up3_penerima')");
    header("Location: master_mo.php");
    exit();
}

// Proses Hapus MO
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM master_mo WHERE id='$id'");
    header("Location: master_mo.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Master MO - PLN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">← Kembali ke Dashboard</a>
            <span class="text-white small">Kelola Data Master MO</span>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Form Tambah -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Tambah Master MO</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nama MO</label>
                                <input type="text" name="nama_mo" class="form-control" placeholder="Contoh: MO KURIPAN" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">UP3 Pengirim</label>
                                <input type="text" name="up3_pengirim" class="form-control" placeholder="Contoh: BRB" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">UP3 Penerima</label>
                                <input type="text" name="up3_penerima" class="form-control" placeholder="Contoh: BJM" required>
                            </div>
                            <button type="submit" name="tambah" class="btn btn-primary w-100 fw-bold">Simpan MO Baru</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar MO -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0 align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama MO</th>
                                    <th>Pengirim</th>
                                    <th>Penerima</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $q = mysqli_query($koneksi, "SELECT * FROM master_mo ORDER BY id DESC");
                                while ($r = mysqli_fetch_assoc($q)) {
                                    echo "<tr>
                                            <td>{$no}</td>
                                            <td class='text-start ps-3'>{$r['nama_mo']}</td>
                                            <td>{$r['up3_pengirim']}</td>
                                            <td>{$r['up3_penerima']}</td>
                                            <td>
                                                <a href='master_mo.php?hapus={$r['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin hapus MO ini?\")'>Hapus</a>
                                            </td>
                                          </tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>