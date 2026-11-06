<?php
session_start();
include 'config/koneksi.php';

// Kalau udah login, langsung lempar ke index
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query  = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    $user   = mysqli_fetch_assoc($query);

    // Untuk simplifikasi prototype, password dicek langsung (atau pake password_verify jika di-hash)
    if ($user && ($password == $user['password'] || password_verify($password, $user['password']))) {
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['username']     = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role']         = $user['role'];
        $_SESSION['ulp_id']       = $user['ulp_id'];

        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem KWH PLN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <h4 class="card-title text-center fw-bold mb-1 text-primary">KWH MONITORING</h4>
                        <p class="text-center text-muted small mb-4">Sistem Energi Kirim-Terima PLN</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 small"><?= $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>