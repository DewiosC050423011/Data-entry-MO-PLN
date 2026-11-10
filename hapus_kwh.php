
<?php
session_start();
include 'config/koneksi.php';
include 'includes/auth_helper.php';

// KUNCI KEAMANAN: Hanya Admin yang bisa eksekusi file ini!
requireAdmin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

    $get_foto = mysqli_query($koneksi, "SELECT foto_meter FROM transaksi_kwh WHERE id='$id'");
    $data = mysqli_fetch_assoc($get_foto);

    if ($data) {
        $foto = $data['foto_meter'];
        $path_foto = 'uploads/foto_meter/' . $foto;

        if (!empty($foto) && file_exists($path_foto)) {
            unlink($path_foto);
        }

        $delete = mysqli_query($koneksi, "DELETE FROM transaksi_kwh WHERE id='$id'");

        if ($delete) {
            echo "<script>alert('Data stand meter dan foto geotag berhasil dihapus.'); window.location='index.php?bulan=".$bulan."';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data: " . mysqli_error($koneksi) . "'); window.location='index.php?bulan=".$bulan."';</script>";
        }
    } else {
        header("Location: index.php?bulan=" . $bulan);
    }
} else {
    header("Location: index.php");
}
?>