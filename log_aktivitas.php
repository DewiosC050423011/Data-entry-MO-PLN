<?php
$page_title = "Log Aktivitas Sistem - Super Admin";
include 'includes/header.php';
requireSuperAdmin();
?>

<div class="mb-3">
    <a href="index.php" class="text-decoration-none text-secondary fw-semibold small"><i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard</a>
</div>

<div class="card-pln overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-person-check-fill me-2"></i>STATUS TERAKHIR LOGIN PENGGUNA</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-pln">
            <thead>
                <tr>
                    <th>USERNAME</th>
                    <th>NAMA LENGKAP</th>
                    <th>ROLE</th>
                    <th>TERAKHIR LOGIN</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_users = mysqli_query($koneksi, "SELECT username, nama_lengkap, role, last_login FROM users ORDER BY last_login DESC");
                while ($u = mysqli_fetch_assoc($q_users)) {
                    $last = $u['last_login'] ? date('d M Y, H:i:s', strtotime($u['last_login'])) . ' WITA' : '<span class="text-muted">Belum pernah login</span>';
                ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($u['username']); ?></td>
                    <td><?= htmlspecialchars($u['nama_lengkap']); ?></td>
                    <td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($u['role']); ?></span></td>
                    <td class="font-monospace small"><?= $last; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card-pln overflow-hidden">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-journal-text me-2 text-primary"></i>RIWAYAT LOG AKTIVITAS SISTEM</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-pln">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="18%">WAKTU (WITA)</th>
                    <th width="14%">USERNAME</th>
                    <th width="13%">ROLE</th>
                    <th width="16%">JENIS AKSI</th>
                    <th width="34%">DETAIL KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $q_logs = mysqli_query($koneksi, "SELECT * FROM log_aktivitas ORDER BY id DESC LIMIT 100");
                if (mysqli_num_rows($q_logs) > 0) {
                    while ($log = mysqli_fetch_assoc($q_logs)) {
                        $badge = 'bg-info-subtle text-dark';
                        if (strpos($log['aksi'], 'HAPUS') !== false) $badge = 'bg-danger-subtle text-danger';
                        if (strpos($log['aksi'], 'INPUT') !== false || strpos($log['aksi'], 'TAMBAH') !== false) $badge = 'bg-success-subtle text-success';
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td class="font-monospace small"><?= date('d/m/Y H:i:s', strtotime($log['waktu'])); ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($log['username']); ?></td>
                    <td><span class="badge bg-light text-dark border text-uppercase"><?= htmlspecialchars($log['role']); ?></span></td>
                    <td><span class="badge <?= $badge; ?> border"><?= htmlspecialchars($log['aksi']); ?></span></td>
                    <td><?= htmlspecialchars($log['keterangan']); ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada catatan aktivitas.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>