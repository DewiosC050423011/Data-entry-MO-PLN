<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/koneksi.php';
include_once __DIR__ . '/auth_helper.php';

checkLogin();

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
$user_nama = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Sistem Monitoring Energi kWh - PT PLN (Persero)'; ?></title>
    
    <!-- Google Fonts & Bootstrap 5 -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --pln-blue: #005B9C;
            --pln-blue-dark: #004070;
            --pln-cyan: #00A3E0;
            --pln-yellow: #FFC20E;
            --pln-bg: #F4F6F9;
            --pln-text: #2D3748;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--pln-bg);
            color: var(--pln-text);
        }

        .navbar-pln {
            background-color: var(--pln-blue);
            border-bottom: 4px solid var(--pln-yellow);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .brand-logo-img {
            height: 38px;
            width: auto;
            background: #ffffff;
            padding: 3px 6px;
            border-radius: 4px;
        }

        .brand-logo-text { font-weight: 700; letter-spacing: 0.5px; color: #ffffff; }
        .brand-subtext { font-size: 11px; color: var(--pln-cyan); font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }

        .card-pln {
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background: #ffffff;
        }

        .table-pln { font-size: 13.5px; margin-bottom: 0; }
        .table-pln thead th {
            background-color: #F8FAFC;
            color: #4A5568;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #E2E8F0;
            padding: 12px 14px;
            vertical-align: middle;
        }

        .table-pln tbody td {
            padding: 12px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #EDF2F7;
        }

        .btn-pln-primary { background-color: var(--pln-blue); color: #fff; font-weight: 600; border: none; }
        .btn-pln-primary:hover { background-color: var(--pln-blue-dark); color: #fff; }
        .btn-pln-success { background-color: #10B981; color: #fff; font-weight: 600; }

        .img-thumb-table {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #CBD5E1;
        }
    </style>
</head>
<body>

    <!-- Header Navbar PLN dengan Logo CDN -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-pln py-2 mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/97/Logo_PLN.png" alt="Logo PLN" class="brand-logo-img">
                <div>
                    <div class="brand-logo-text leading-none">PT PLN (PERSERO)</div>
                    <div class="brand-subtext">UID KALSELTENG &bull; Rekap Energi Interkoneksi</div>
                </div>
            </a>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end text-white d-none d-md-block">
                    <div class="fw-semibold small"><?= htmlspecialchars($user_nama); ?></div>
                    <?php if (isAdmin()): ?>
                        <span class="badge bg-danger text-uppercase" style="font-size: 10px; padding: 3px 6px;"><i class="bi bi-shield-lock-fill me-1"></i>ADMINISTRATOR</span>
                    <?php else: ?>
                        <span class="badge bg-info text-dark text-uppercase" style="font-size: 10px; padding: 3px 6px;"><i class="bi bi-person-badge me-1"></i>PETUGAS</span>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="btn btn-outline-light btn-sm px-3"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">