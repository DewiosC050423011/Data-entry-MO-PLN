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

function isAdmin() {
    return isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
}

function isUser() {
    return isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'user';
}

function requireAdmin() {
    checkLogin();
    if (!isAdmin()) {
        echo "<script>
                alert('Akses Ditolak! Fitur ini khusus untuk Administrator.');
                window.location='index.php';
              </script>";
        exit();
    }
}
?>