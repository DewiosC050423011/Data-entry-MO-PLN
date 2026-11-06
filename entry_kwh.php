<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'pengirim')) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_POST['submit'])) {
    $mo_id        = $_POST['mo_id'];
    $ulp_pengirim = $_POST['ulp_pengirim'];
    $ulp_penerima = $_POST['ulp_penerima'];
    $bulan_tahun  = $_POST['bulan_tahun'];
    $stand_awal   = floatval($_POST['stand_awal']);
    $stand_akhir  = floatval($_POST['stand_akhir']);
    $faktor_kali  = floatval($_POST['faktor_kali']);

    $kwh_hasil = ($stand_akhir - $stand_awal) * $faktor_kali;

    $cek_existing = mysqli_query($koneksi, "SELECT id FROM transaksi_kwh WHERE mo_id='$mo_id' AND bulan_tahun='$bulan_tahun'");
    
    if (mysqli_num_rows($cek_existing) > 0) {
        $message = "<div class='alert alert-warning py-2 small'>Data MO ini untuk bulan tersebut sudah pernah diinput!</div>";
    } else {
        $query_insert = "INSERT INTO transaksi_kwh (mo_id, ulp_pengirim, ulp_penerima, bulan_tahun, stand_awal, stand_akhir, faktor_kali, kwh_hasil, status_verifikasi) 
                         VALUES ('$mo_id', '$ulp_pengirim', '$ulp_penerima', '$bulan_tahun', '$stand_awal', '$stand_akhir', '$faktor_kali', '$kwh_hasil', 'pending')";
        
        if (mysqli_query($koneksi, $query_insert)) {
            echo "<script>alert('Data Stand KWH Berhasil Disimpan!'); window.location='index.php';</script>";
            exit();
        } else {
            $message = "<div class='alert alert-danger py-2 small'>Gagal menyimpan data: " . mysqli_error($koneksi) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry Stand KWH - PLN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">← Kembali ke Dashboard</a>
            <span class="text-white small">Entry Data Stand Meter</span>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-primary mb-1">Form Entry Stand KWH</h4>
                        <p class="text-muted small mb-4">Pilih MO untuk mengunci UP3, lalu pilih ULP Pengirim & Penerima</p>

                        <?= $message; ?>

                        <form method="POST">
                            
                            <!-- 1. PILIH MO (Memicu Auto Lock UP3) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Meter Outfit (MO)</label>
                                <select name="mo_id" id="mo_id" class="form-select" required onchange="onMoChange()">
                                    <option value="" disabled selected>-- Pilih MO --</option>
                                    <?php
                                    $get_mo = mysqli_query($koneksi, "SELECT * FROM master_mo ORDER BY nama_mo ASC");
                                    while ($mo = mysqli_fetch_assoc($get_mo)) {
                                        echo "<option value='".$mo['id']."'>".$mo['nama_mo']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- 2. DROPDOWN ULP & UP3 (Dinamis) -->
                            <div class="p-3 bg-white rounded-3 border mb-3 d-none" id="section_ulp">
                                <div class="row g-3">
                                    <!-- Pengirim -->
                                    <div class="col-md-6 border-end">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary">PENGIRIM</span>
                                            <small class="fw-bold text-primary" id="txt_up3_pengirim">UP3: -</small>
                                        </div>
                                        <label class="form-label extra-small fw-semibold">Pilih ULP Pengirim</label>
                                        <select name="ulp_pengirim" id="ulp_pengirim" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih ULP Pengirim --</option>
                                        </select>
                                    </div>

                                    <!-- Penerima -->
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-success">PENERIMA</span>
                                            <small class="fw-bold text-success" id="txt_up3_penerima">UP3: -</small>
                                        </div>
                                        <label class="form-label extra-small fw-semibold">Pilih ULP Penerima</label>
                                        <select name="ulp_penerima" id="ulp_penerima" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih ULP Penerima --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. PERIODE -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Periode Bulan & Tahun</label>
                                <input type="month" name="bulan_tahun" class="form-control" required value="<?= date('Y-m'); ?>">
                            </div>

                            <hr class="my-3 text-muted">

                            <!-- 4. STAND AWAL & AKHIR -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-danger">Stand Awal (Terkunci Otomatis)</label>
                                <input type="number" step="0.01" id="display_stand_awal" class="form-control bg-light" value="0" disabled>
                                <input type="hidden" name="stand_awal" id="input_stand_awal" value="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Stand Akhir</label>
                                <input type="number" step="0.01" name="stand_akhir" id="stand_akhir" class="form-control" placeholder="0.00" required oninput="hitungKwh()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Faktor Kali (FK)</label>
                                <input type="number" step="0.01" name="faktor_kali" id="faktor_kali" class="form-control" value="1.00" required oninput="hitungKwh()">
                            </div>

                            <div class="alert alert-info py-2 mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small fw-semibold">Estimasi Hasil kWh:</span>
                                    <h4 class="mb-0 fw-bold text-info-emphasis" id="preview_kwh">0.00</h4>
                                </div>
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary w-100 fw-bold py-2">Simpan Data Stand</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function onMoChange() {
            let moId = document.getElementById('mo_id').value;
            if (!moId) return;

            // Load Detail MO & Populasi Dropdown ULP
            fetch('get_detail_mo.php?id=' + moId)
                .then(res => res.json())
                .then(data => {
                    if(data) {
                        document.getElementById('section_ulp').classList.remove('d-none');
                        document.getElementById('txt_up3_pengirim').innerText = 'UP3: ' + data.up3_pengirim;
                        document.getElementById('txt_up3_penerima').innerText = 'UP3: ' + data.up3_penerima;

                        // Fetch Options ULP Pengirim
                        fetchUlpOptions(data.up3_pengirim, 'ulp_pengirim', data.ulp_pengirim);
                        // Fetch Options ULP Penerima
                        fetchUlpOptions(data.up3_penerima, 'ulp_penerima', data.ulp_penerima);
                    }
                });

            // Load Stand Awal
            fetch('get_stand_awal.php?mo_id=' + moId)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('display_stand_awal').value = data.stand_awal;
                    document.getElementById('input_stand_awal').value = data.stand_awal;
                    hitungKwh();
                });
        }

        function fetchUlpOptions(up3Code, elementId, defaultUlp) {
            let select = document.getElementById(elementId);
            select.innerHTML = '<option value="">-- Pilih ULP --</option>';

            fetch('get_ulp.php?up3=' + up3Code)
                .then(res => res.json())
                .then(data => {
                    data.forEach(item => {
                        let opt = document.createElement('option');
                        opt.value = item.nama_ulp;
                        opt.textContent = item.nama_ulp;
                        if (item.nama_ulp.toLowerCase() === defaultUlp.toLowerCase()) {
                            opt.selected = true; // Auto-select ULP default dari catatan
                        }
                        select.appendChild(opt);
                    });
                });
        }

        function hitungKwh() {
            let stAwal  = parseFloat(document.getElementById('input_stand_awal').value) || 0;
            let stAkhir = parseFloat(document.getElementById('stand_akhir').value) || 0;
            let fk      = parseFloat(document.getElementById('faktor_kali').value) || 1;

            let hasil = (stAkhir - stAwal) * fk;
            if (hasil < 0) hasil = 0;

            document.getElementById('preview_kwh').innerText = hasil.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    </script>
</body>
</html>