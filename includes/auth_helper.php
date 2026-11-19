<?php
// includes/auth_helper.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
}

function getRole() {
    return isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';
}

function isSuperAdmin() {
    return getRole() === 'super_admin';
}

function isAdmin() {
    return getRole() === 'admin';
}

function isPengirim() {
    return getRole() === 'pengirim';
}

function isPenerima() {
    return getRole() === 'penerima';
}

function isPetugas() {
    return isPengirim() || isPenerima();
}

function requireAdmin() {
    checkLogin();
    if (!isAdmin() && !isSuperAdmin()) {
        echo "<script>alert('Akses Ditolak! Fitur ini khusus Admin.'); window.location='index.php';</script>";
        exit();
    }
}

function requireSuperAdmin() {
    checkLogin();
    if (!isSuperAdmin()) {
        echo "<script>alert('Akses Ditolak! Halaman ini khusus Super Admin.'); window.location='index.php';</script>";
        exit();
    }
}

function catatLog($koneksi, $aksi,$keterangan) {
    if (isset($_SESSION['username'])) {$user = mysqli_real_escape_string($koneksi,$_SESSION['username']);
        $role = mysqli_real_escape_string($koneksi, getRole());
        $aksi_clean = mysqli_real_escape_string($koneksi, $aksi);$ket_clean  = mysqli_real_escape_string($koneksi,$keterangan);
        
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (username, role, aksi, keterangan, waktu) VALUES ('$user', '$role', '$aksi_clean', '$ket_clean', NOW())");
    }
}
?>