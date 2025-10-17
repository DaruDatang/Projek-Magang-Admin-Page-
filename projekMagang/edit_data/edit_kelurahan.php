<?php
include __DIR__ . '/../database/db.php';

$kode = $_GET['kode'] ?? '';
if (empty($kode)) die("Error: Kode kelurahan/desa tidak valid.");

$stmt = $conn->prepare("SELECT * FROM kelurahan WHERE kode_kel = ?");
$stmt->bind_param("s", $kode);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$data) die("Data kelurahan/desa tidak ditemukan!");

$parts    = explode('.', $data['kode_kel']);
$provAwal = $parts[0] ?? '';
$kabAwal  = "{$parts[0]}.{$parts[1]}" ?? '';
$kecAwal  = "{$parts[0]}.{$parts[1]}.{$parts[2]}" ?? '';

$provinsi = [];
$res_prov = $conn->query("SELECT kode_prov AS kode, nama_prov AS nama FROM provinsi ORDER BY nama_prov ASC");
while ($row = $res_prov->fetch_assoc()) $provinsi[] = $row;

$kabupaten = [];
$stmt_kab = $conn->prepare("SELECT kode_kab AS kode, nama_kab AS nama FROM kabupaten WHERE kode_prov = ? ORDER BY nama_kab");
$stmt_kab->bind_param("s", $provAwal);
$stmt_kab->execute();
$res_kab = $stmt_kab->get_result();
while ($row = $res_kab->fetch_assoc()) $kabupaten[] = $row;

$kecamatan = [];
$stmt_kec = $conn->prepare("SELECT kode_kec AS kode, nama_kec AS nama FROM kecamatan WHERE kode_kab = ? ORDER BY nama_kec");
$stmt_kec->bind_param("s", $kabAwal);
$stmt_kec->execute();
$res_kec = $stmt_kec->get_result();
while ($row = $res_kec->fetch_assoc()) $kecamatan[] = $row;

$redirect = '/projekMagang/pages/kelurahan.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $response = ["success" => false, "message" => ""];
    $namaBaru = trim($_POST['nama'] ?? '');
    $kecBaru  = $_POST['kecamatan'] ?? '';
    $kodeLama = $_POST['kode_lama'] ?? '';
    $kecLama  = $_POST['kec_lama'] ?? '';
    
    if (empty($namaBaru) || empty($kecBaru)) {
        $response['message'] = 'Semua field tidak boleh kosong!';
        echo json_encode($response);
        exit;
    }

    $kodeBaru = $kodeLama;
    if ($kecBaru != $kecLama) {
        $stmt_new = $conn->prepare("SELECT kode_kel FROM kelurahan WHERE kode_kec = ? ORDER BY CAST(SUBSTRING_INDEX(kode_kel, '.', -1) AS UNSIGNED) DESC LIMIT 1");
        $stmt_new->bind_param("s", $kecBaru);
        $stmt_new->execute();
        $res = $stmt_new->get_result();
        if ($row = $res->fetch_assoc()) {
            $lastPart = (int)explode('.', $row['kode_kel'])[3];
            $kodeBaru = $kecBaru . '.' . str_pad($lastPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $kodeBaru = $kecBaru . '.0001';
        }
    }
    
    $conn->begin_transaction();
    try {
        $stmt_kel = $conn->prepare("UPDATE kelurahan SET kode_kel = ?, nama_kel = ?, kode_kec = ? WHERE kode_kel = ?");
        $stmt_kel->bind_param("ssss", $kodeBaru, $namaBaru, $kecBaru, $kodeLama);
        $stmt_kel->execute();

        $stmt_wil = $conn->prepare("UPDATE wilayah SET kode = ?, nama = ? WHERE kode = ?");
        $stmt_wil->bind_param("sss", $kodeBaru, $namaBaru, $kodeLama);
        $stmt_wil->execute();
        
        $conn->commit();
        $response["success"] = true;
        $response["message"] = "Data berhasil diperbarui!";
        $response["redirect"] = $redirect;
    } catch(Exception $e) {
        $conn->rollback();
        $response["message"] = "Gagal memperbarui data: " . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Kelurahan/Desa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        main { flex: 1; display: flex; }
        .sidebar { width: 220px; background-color: #f8f9fa; padding: 1rem; border-right: 1px solid #ddd; }
        .content { flex: 1; padding: 2rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid"><a class="navbar-brand" href="#">Kode Wilayah Indonesia</a></div>
</nav>
<main>
    <div class="sidebar">
        <h6 class="text-muted">Menu</h6>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="/projekMagang/pages/index.php" class="nav-link"><i class="bi bi-geo-alt"></i> Semua Data</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/provinsi.php" class="nav-link"><i class="bi bi-building"></i> Provinsi</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/kabupaten.php" class="nav-link"><i class="bi bi-signpost"></i> Kabupaten/Kota</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/kecamatan.php" class="nav-link"><i class="bi bi-signpost-split"></i> Kecamatan</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/kelurahan.php" class="nav-link"><i class="bi bi-house-door"></i> Kelurahan/Desa</a></li>
        </ul>
    </div>

    <div class="content">
        <h3>Edit Kelurahan/Desa: <?= htmlspecialchars($data['nama_kel']) ?></h3>
        <form method="post" id="formEditKelurahan" style="max-width: 600px;">
            <input type="hidden" name="kode_lama" value="<?= htmlspecialchars($data['kode_kel']) ?>">
            <input type="hidden" name="kec_lama" value="<?= htmlspecialchars($kecAwal) ?>">
            <div class="mb-3">
                <label for="provinsi" class="form-label">Provinsi</label>
                <select id="provinsi" class="form-select" required>
                    <?php foreach ($provinsi as $p): ?>
                        <option value="<?= htmlspecialchars($p['kode']) ?>" <?= ($p['kode'] == $provAwal ? 'selected' : '') ?>>
                            <?= htmlspecialchars($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="kabupaten" class="form-label">Kabupaten/Kota</label>
                <select id="kabupaten" class="form-select" required>
                    <?php foreach ($kabupaten as $k): ?>
                        <option value="<?= htmlspecialchars($k['kode']) ?>" <?= ($k['kode'] == $kabAwal ? 'selected' : '') ?>>
                            <?= htmlspecialchars($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="kecamatan" class="form-label">Kecamatan</label>
                <select name="kecamatan" id="kecamatan" class="form-select" required>
                    <?php foreach ($kecamatan as $kec): ?>
                        <option value="<?= htmlspecialchars($kec['kode']) ?>" <?= ($kec['kode'] == $kecAwal ? 'selected' : '') ?>>
                            <?= htmlspecialchars($kec['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Kelurahan/Desa</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($data['nama_kel']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="<?= htmlspecialchars($redirect) ?>" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</main>
<footer class="bg-dark text-light text-center py-2"><small>© 2025 Admin</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function loadWilayah(parentKode, targetId, placeholder) {
    const targetSelect = document.getElementById(targetId);
    targetSelect.innerHTML = `<option value="">Memuat...</option>`;
    targetSelect.disabled = true;
    try {
        const response = await fetch(`../database/get_wilayah.php?parent=${encodeURIComponent(parentKode)}`);
        const data = await response.json();
        targetSelect.innerHTML = `<option value="">-- ${placeholder} --</option>`;
        if (data.length > 0) {
            data.forEach(d => { const option = new Option(d.nama, d.kode); targetSelect.add(option); });
            targetSelect.disabled = false;
        } else {
            targetSelect.innerHTML = `<option value="">-- Tidak ada data --</option>`;
        }
    } catch (error) {
        targetSelect.innerHTML = `<option value="">Gagal memuat data</option>`;
    }
}
document.getElementById('provinsi').addEventListener('change', function() {
    const kabSelect = document.getElementById('kabupaten');
    const kecSelect = document.getElementById('kecamatan');
    kabSelect.innerHTML = '<option>-- Pilih Kabupaten --</option>'; kabSelect.disabled = true;
    kecSelect.innerHTML = '<option>-- Pilih Kecamatan --</option>'; kecSelect.disabled = true;
    if (this.value) loadWilayah(this.value, 'kabupaten', 'Pilih Kabupaten/Kota');
});
document.getElementById('kabupaten').addEventListener('change', function() {
    if (this.value) loadWilayah(this.value, 'kecamatan', 'Pilih Kecamatan');
});
document.getElementById('formEditKelurahan').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const data = await response.json();
        alert(data.message);
        if (data.success) {
            window.location.href = data.redirect;
        }
    } catch (err) {
        alert("Terjadi kesalahan saat berkomunikasi dengan server.");
    }
});
</script>
</body>
</html>