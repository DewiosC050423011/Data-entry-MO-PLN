<?php
session_start();
include 'config/koneksi.php';

// Kalau sudah login, langsung ke index.php
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        
        if ($query && mysqli_num_rows($query) === 1) {
            $user = mysqli_fetch_assoc($query);
            
            // Cek Password (support password_verify, md5, atau plain text)
            $is_valid = false;
            if (password_verify($password, $user['password'])) {
                $is_valid = true;
            } elseif (md5($password) === $user['password']) {
                $is_valid = true;
            } elseif ($password === $user['password']) {
                $is_valid = true;
            }

            if ($is_valid) {
                $_SESSION['username']     = $user['username'];
                $_SESSION['nama_lengkap'] = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : $user['username'];
                $_SESSION['role']         = !empty($user['role']) ? strtolower($user['role']) : 'user';

                // Lempar ke index.php
                header("Location: index.php");
                exit();
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username '$username' tidak ditemukan di database!";
        }
    } else {
        $error = "Username dan Password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Monitoring kWh Meter EXIM PT PLN (Persero)</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --pln-blue: #005B9C;
            --pln-blue-dark: #004070;
            --pln-cyan: #00A3E0;
            --pln-yellow: #FFC20E;
            --pln-bg: #EDF2F7;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #E2E8F0 0%, #EDF2F7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 91, 156, 0.1);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .login-header {
            background-color: #ffffff;
            padding: 30px 25px 20px 25px;
            text-align: center;
            border-bottom: 3px solid var(--pln-yellow);
        }

        .logo-pln {
            height: 60px;
            width: auto;
            margin-bottom: 12px;
        }

        .login-title {
            color: var(--pln-blue);
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .login-subtitle {
            color: #718096;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .form-control:focus {
            border-color: var(--pln-blue);
            box-shadow: 0 0 0 0.25rem rgba(0, 91, 156, 0.15);
        }

        .btn-pln {
            background-color: var(--pln-blue);
            color: #ffffff;
            font-weight: 600;
            padding: 10px;
            border: none;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-pln:hover {
            background-color: var(--pln-blue-dark);
            color: #ffffff;
        }

        .login-footer {
            font-size: 11px;
            color: #A0AEC0;
            text-align: center;
            padding-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <img src="https://upload.wikimedia.org/wikipedia/commons/9/97/Logo_PLN.png" alt="Logo PLN" class="logo-pln">
        <div class="login-title">PT PLN (PERSERO)</div>
        <div class="login-subtitle">UID KALSELTENG &bull; MONITORING KWH EXIM</div>
    </div>

    <div class="p-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                <div><?= $error; ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" name="submit_login" class="btn btn-pln w-100 mb-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
            </button>
        </form>
    </div>

    <div class="login-footer">
        &copy; <?= date('Y'); ?> PT PLN (Persero) UID Kalselteng
    </div>
</div>

</body>
</html>