<?php
include __DIR__ . '/../database/db.php';

$redirect = '/projekMagang/pages/kecamatan.php';

// Ambil data provinsi untuk dropdown awal
$provinsi = [];
$res = $conn->query("SELECT kode_prov AS kode, nama_prov AS nama FROM provinsi ORDER BY nama_prov ASC");
while ($row = $res->fetch_assoc()) {
    $provinsi[] = $row;
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $response = ["success" => false, "message" => ""];

    $kab = $_POST['kabupaten'] ?? '';
    $nama = trim($_POST['nama'] ?? '');

    if (empty($kab) || empty($nama)) {
        $response["message"] = "Semua field harus diisi lengkap!";
        echo json_encode($response);
        exit;
    }

    // Cek duplikat
    $stmt = $conn->prepare("SELECT 1 FROM kecamatan WHERE nama_kec = ? AND kode_kab = ?");
    $stmt->bind_param("ss", $nama, $kab);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $response["message"] = "Nama kecamatan '$nama' sudah ada di kabupaten ini!";
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Generate kode baru
    $stmt = $conn->prepare("SELECT kode_kec FROM kecamatan WHERE kode_kab = ? ORDER BY CAST(SUBSTRING_INDEX(kode_kec, '.', -1) AS UNSIGNED) DESC LIMIT 1");
    $stmt->bind_param("s", $kab);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $lastPart = (int)explode('.', $row['kode_kec'])[2];
        $newKode = $kab . '.' . str_pad($lastPart + 1, 2, '0', STR_PAD_LEFT);
    } else {
        $newKode = $kab . '.01';
    }
    $stmt->close();

    // Simpan data
    $conn->begin_transaction();
    try {
        $stmt_kec = $conn->prepare("INSERT INTO kecamatan (kode_kec, nama_kec, kode_kab) VALUES (?, ?, ?)");
        $stmt_kec->bind_param("sss", $newKode, $nama, $kab);
        $stmt_kec->execute();

        $stmt_wil = $conn->prepare("INSERT INTO wilayah (kode, nama, level) VALUES (?, ?, 3)");
        $stmt_wil->bind_param("ss", $newKode, $nama);
        $stmt_wil->execute();
        
        $conn->commit();
        $response["success"] = true;
        $response["message"] = "Data kecamatan berhasil ditambahkan!";
        $response["redirect"] = $redirect;
    } catch(Exception $e) {
        $conn->rollback();
        $response["message"] = "Gagal menyimpan data: " . $e->getMessage();
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
    <title>Tambah Kecamatan</title>
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
        <h3>Tambah Kecamatan Baru</h3>
        <form method="post" id="formAddKecamatan" style="max-width: 600px;">
            <div class="mb-3">
                <label for="provinsi" class="form-label">Pilih Provinsi</label>
                <select id="provinsi" class="form-select" required>
                    <option value="">-- Pilih Provinsi --</option>
                    <?php foreach ($provinsi as $p): ?>
                        <option value="<?= htmlspecialchars($p['kode']) ?>"><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="kabupaten" class="form-label">Pilih Kabupaten/Kota</label>
                <select name="kabupaten" id="kabupaten" class="form-select" required disabled>
                    <option value="">-- Pilih Provinsi terlebih dahulu --</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Kecamatan</label>
                <input type="text" name="nama" id="nama" class="form-control" required placeholder="Contoh: Coblong">
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
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
    if (this.value) {
        loadWilayah(this.value, 'kabupaten', 'Pilih Kabupaten/Kota');
    } else {
        document.getElementById('kabupaten').innerHTML = '<option value="">-- Pilih Provinsi terlebih dahulu --</option>';
        document.getElementById('kabupaten').disabled = true;
    }
});
document.getElementById('formAddKecamatan').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const data = await response.json();
        alert(data.message);
        if (data.success) window.location.href = data.redirect;
    } catch (err) {
        alert("Terjadi kesalahan saat mengirim data.");
    }
});
</script>
</body>
</html>