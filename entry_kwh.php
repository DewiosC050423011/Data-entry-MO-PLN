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
    $foto_base64  = $_POST['foto_base64'];

    $kwh_hasil = ($stand_akhir - $stand_awal) * $faktor_kali;

    // Simpan foto jika ada
    $nama_foto_db = NULL;
    if (!empty($foto_base64)) {
        $dir_upload = 'uploads/foto_meter/';
        
        // Auto-create folder jika belum ada
        if (!is_dir($dir_upload)) {
            mkdir($dir_upload, 0777, true);
        }

        $imgData = str_replace('data:image/jpeg;base64,', '', $foto_base64);
        $imgData = str_replace(' ', '+', $imgData);
        $dataDecoded = base64_decode($imgData);
        
        $nama_foto_db = 'MO_' . $mo_id . '_' . time() . '.jpg';
        $upload_path = $dir_upload . $nama_foto_db;
        file_put_contents($upload_path, $dataDecoded);
    }

    $cek_existing = mysqli_query($koneksi, "SELECT id FROM transaksi_kwh WHERE mo_id='$mo_id' AND bulan_tahun='$bulan_tahun'");
    
    if (mysqli_num_rows($cek_existing) > 0) {
        $message = "<div class='alert alert-warning py-2 small'>Data MO ini untuk bulan tersebut sudah ada!</div>";
    } else {
        $query_insert = "INSERT INTO transaksi_kwh (mo_id, ulp_pengirim, ulp_penerima, bulan_tahun, stand_awal, stand_akhir, faktor_kali, kwh_hasil, foto_meter, status_verifikasi) 
                         VALUES ('$mo_id', '$ulp_pengirim', '$ulp_penerima', '$bulan_tahun', '$stand_awal', '$stand_akhir', '$faktor_kali', '$kwh_hasil', '$nama_foto_db', 'pending')";
        
        if (mysqli_query($koneksi, $query_insert)) {
            echo "<script>alert('Data Stand & Foto Geotag Berhasil Disimpan!'); window.location='index.php';</script>";
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
    <style>
        #webcam, #canvas-preview {
            width: 100%;
            max-height: 350px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #000;
        }
    </style>
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
                        <p class="text-muted small mb-4">Input data pembacaan meter & bukti foto Geotag lokasi</p>

                        <?= $message; ?>

                        <form method="POST" id="kwhForm">
                            
                            <!-- 1. PILIH MO -->
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

                            <!-- 2. DROPDOWN ULP & UP3 -->
                            <div class="p-3 bg-white rounded-3 border mb-3 d-none" id="section_ulp">
                                <div class="row g-3">
                                    <div class="col-md-6 border-end">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary">PENGIRIM</span>
                                            <small class="fw-bold text-primary" id="txt_up3_pengirim">UP3: -</small>
                                        </div>
                                        <label class="form-label extra-small fw-semibold">ULP Pengirim</label>
                                        <select name="ulp_pengirim" id="ulp_pengirim" class="form-select form-select-sm" required></select>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-success">PENERIMA</span>
                                            <small class="fw-bold text-success" id="txt_up3_penerima">UP3: -</small>
                                        </div>
                                        <label class="form-label extra-small fw-semibold">ULP Penerima</label>
                                        <select name="ulp_penerima" id="ulp_penerima" class="form-select form-select-sm" required></select>
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

                            <div class="alert alert-info py-2 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small fw-semibold">Estimasi Hasil kWh:</span>
                                    <h4 class="mb-0 fw-bold text-info-emphasis" id="preview_kwh">0.00</h4>
                                </div>
                            </div>

                            <!-- 5. KAMERA GEOTAG WATERMARK -->
                            <div class="p-3 bg-light rounded-3 border mb-4">
                                <label class="form-label small fw-semibold text-dark d-block">📷 Ambil Foto Bukti Meteran (Geotag Real-Time)</label>
                                
                                <video id="webcam" autoplay playsinline></video>
                                <canvas id="canvas-preview" class="d-none"></canvas>
                                
                                <input type="hidden" name="foto_base64" id="foto_base64">

                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold w-50" id="btnCapture" onclick="takeSnapshot()">📸 Buka Kamera / Ambil Foto</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary fw-bold w-50 d-none" id="btnRetake" onclick="resetCamera()">🔄 Foto Ulang</button>
                                </div>
                                <span class="form-text text-muted extra-small d-block mt-1" id="locationStatus">Mengambil data GPS...</span>
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary w-100 fw-bold py-2">Simpan Data Stand</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentLat = "-";
        let currentLong = "-";
        let videoStream = null;

        window.onload = function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    currentLat = position.coords.latitude.toFixed(6);
                    currentLong = position.coords.longitude.toFixed(6);
                    document.getElementById('locationStatus').innerText = `📍 GPS Aktif: ${currentLat}, ${currentLong}`;
                }, error => {
                    document.getElementById('locationStatus').innerText = "⚠️ Izinkan akses lokasi GPS browser Anda.";
                });
            }
        };

        function startWebcam() {
            const video = document.getElementById('webcam');
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(stream => {
                    videoStream = stream;
                    video.srcObject = stream;
                })
                .catch(err => {
                    alert("Gagal mengakses kamera: " + err);
                });
        }

        function takeSnapshot() {
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas-preview');
            const context = canvas.getContext('2d');

            if (!videoStream) {
                startWebcam();
                document.getElementById('btnCapture').innerText = "📸 Jepret Foto";
                return;
            }

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;

            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const now = new Date();
            const timeStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + now.toTimeString().split(' ')[0];

            context.fillStyle = "#00FF66";
            context.font = "bold 16px Arial";
            context.shadowColor = "black";
            context.shadowBlur = 4;

            const startY = canvas.height - 70;
            context.fillText(timeStr, canvas.width - 250, startY);
            context.fillText(`${currentLat} S  ${currentLong} E`, canvas.width - 250, startY + 22);
            context.fillText(`PLN KWH Geotag Monitoring`, canvas.width - 250, startY + 44);

            document.getElementById('foto_base64').value = canvas.toDataURL('image/jpeg');

            video.classList.add('d-none');
            canvas.classList.remove('d-none');
            document.getElementById('btnCapture').classList.add('d-none');
            document.getElementById('btnRetake').classList.remove('d-none');

            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }

        function resetCamera() {
            document.getElementById('webcam').classList.remove('d-none');
            document.getElementById('canvas-preview').classList.add('d-none');
            document.getElementById('btnCapture').classList.remove('d-none');
            document.getElementById('btnCapture').innerText = "📸 Jepret Foto";
            document.getElementById('btnRetake').classList.add('d-none');
            document.getElementById('foto_base64').value = "";
            startWebcam();
        }

        function onMoChange() {
            let moId = document.getElementById('mo_id').value;
            if (!moId) return;

            fetch('get_detail_mo.php?id=' + moId)
                .then(res => res.json())
                .then(data => {
                    if(data) {
                        document.getElementById('section_ulp').classList.remove('d-none');
                        document.getElementById('txt_up3_pengirim').innerText = 'UP3: ' + data.up3_pengirim;
                        document.getElementById('txt_up3_penerima').innerText = 'UP3: ' + data.up3_penerima;

                        fetchUlpOptions(data.up3_pengirim, 'ulp_pengirim', data.ulp_pengirim);
                        fetchUlpOptions(data.up3_penerima, 'ulp_penerima', data.ulp_penerima);
                    }
                });

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
                            opt.selected = true;
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