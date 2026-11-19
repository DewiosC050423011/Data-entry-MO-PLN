<?php
session_start();
include 'config/koneksi.php';
include 'includes/auth_helper.php';

requireAdmin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

    $q = mysqli_query($koneksi, "SELECT transaksi_kwh.*, master_mo.nama_mo FROM transaksi_kwh LEFT JOIN master_mo ON transaksi_kwh.mo_id = master_mo.id WHERE transaksi_kwh.id='$id'");
    $data = mysqli_fetch_assoc($q);

    if ($data) {
        foreach (['foto_meter', 'foto_penerima'] as $kolom) {
            if (!empty($data[$kolom]) && file_exists('uploads/foto_meter/' . $data[$kolom])) {
                unlink('uploads/foto_meter/' . $data[$kolom]);
            }
        }

        if (mysqli_query($koneksi, "DELETE FROM transaksi_kwh WHERE id='$id'")) {
            catatLog($koneksi, 'HAPUS ENTRY', "Menghapus data transaksi MO {$data['nama_mo']} periode {$data['bulan_tahun']}.");
            echo "<script>alert('Data entry & foto geotag berhasil dihapus.'); window.location='index.php?bulan=".$bulan."';</script>";
        }
    } else {
        header("Location: index.php?bulan=" . $bulan);
    }
} else {
    header("Location: index.php");
}
?>