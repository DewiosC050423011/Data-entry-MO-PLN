<?php
session_start();
include 'config/koneksi.php';
include 'includes/auth_helper.php';

if (isset($_SESSION['username'])) {
    catatLog($koneksi, 'LOGOUT', "User {$_SESSION['username']} keluar dari sistem.");
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>