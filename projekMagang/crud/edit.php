<?php
include __DIR__ . '/../database/db.php';

$kode = $_GET['kode'] ?? '';
if (empty($kode)) die("Error: Kode wilayah tidak valid.");
$stmt = $conn->prepare("SELECT kode, nama, CHAR_LENGTH(kode) - CHAR_LENGTH(REPLACE(kode, '.', '')) + 1 AS level FROM wilayah WHERE kode = ?");
$stmt->bind_param("s", $kode);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    die("Data wilayah dengan kode '$kode' tidak ditemukan!");
}
$level = $data['level'];
$parts = explode('.', $data['kode']);
$prov_kode = $parts[0] ?? null;
$kab_kode  = ($level >= 2) ? "{$parts[0]}.{$parts[1]}" : null;
$kec_kode  = ($level >= 3) ? "{$parts[0]}.{$parts[1]}.{$parts[2]}" : null;
$provinsiList = [];
$res_prov = $conn->query("SELECT kode_prov AS kode, nama_prov AS nama FROM provinsi ORDER BY nama_prov ASC");
while ($p = $res_prov->fetch_assoc()) $provinsiList[] = $p;
$kabupatenList = [];
if ($prov_kode) {
    $stmt = $conn->prepare("SELECT kode_kab AS kode, nama_kab AS nama FROM kabupaten WHERE kode_prov = ? ORDER BY nama_kab");
    $stmt->bind_param("s", $prov_kode);
    $stmt->execute();
    $res_kab = $stmt->get_result();
    while ($k = $res_kab->fetch_assoc()) $kabupatenList[] = $k;
    $stmt->close();
}
$kecamatanList = [];
if ($kab_kode) {
    $stmt = $conn->prepare("SELECT kode_kec AS kode, nama_kec AS nama FROM kecamatan WHERE kode_kab = ? ORDER BY nama_kec");
    $stmt->bind_param("s", $kab_kode);
    $stmt->execute();
    $res_kec = $stmt->get_result();
    while ($kc = $res_kec->fetch_assoc()) $kecamatanList[] = $kc;
    $stmt->close();
}
$redirect = $_SERVER['REQUEST_URI'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $response = ["success" => false, "message" => ""];
    try {
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Data berhasil diperbarui!';
        $response['redirect'] = $redirect . (strpos($redirect, '?') === false ? '?' : '&') . 'status=updated';
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $response['message'] = 'Gagal memperbarui data: ' . $exception->getMessage();
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
    <title>Edit Data Wilayah</title>
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
        <?php $current = basename($_SERVER['PHP_SELF']); ?>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="/projekMagang/pages/index.php" class="nav-link"><i class="bi bi-geo-alt"></i> Semua Data</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/provinsi.php" class="nav-link"><i class="bi bi-building"></i> Provinsi</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/kabupaten.php" class="nav-link"><i class="bi bi-signpost"></i> Kabupaten/Kota</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/kecamatan.php" class="nav-link"><i class="bi bi-signpost-split"></i> Kecamatan</a></li>
            <li class="nav-item"><a href="/projekMagang/pages/kelurahan.php" class="nav-link"><i class="bi bi-house-door"></i> Kelurahan/Desa</a></li>
        </ul>
    </div>

    <div class="content">
        <h3>Edit Data: <?= htmlspecialchars($data['nama']) ?></h3>
        <?php if(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <form method="post" id="formEditWilayah" style="max-width: 600px;">
            <input type="hidden" name="kode_lama" value="<?= htmlspecialchars($data['kode']) ?>">
            <input type="hidden" name="level" value="<?= htmlspecialchars($data['level']) ?>">
            
            <div class="mb-3">
                <label for="provinsi" class="form-label">Provinsi</label>
                <select name="provinsi" id="provinsi" class="form-select" <?= $level <= 1 ? 'disabled' : '' ?>>
                    <option value="">-- Pilih Provinsi --</option>
                    <?php foreach ($provinsiList as $p): ?>
                        <option value="<?= htmlspecialchars($p['kode']) ?>" <?= ($p['kode'] == $prov_kode ? 'selected' : '') ?>><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="kabupaten" class="form-label">Kabupaten/Kota</label>
                <select name="kabupaten" id="kabupaten" class="form-select" <?= $level <= 2 ? 'disabled' : '' ?>>
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                    <?php foreach ($kabupatenList as $k): ?>
                        <option value="<?= htmlspecialchars($k['kode']) ?>" <?= ($k['kode'] == $kab_kode ? 'selected' : '') ?>><?= htmlspecialchars($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="kecamatan" class="form-label">Kecamatan</label>
                <select name="kecamatan" id="kecamatan" class="form-select" <?= $level <= 3 ? 'disabled' : '' ?>>
                    <option value="">-- Pilih Kecamatan --</option>
                    <?php foreach ($kecamatanList as $kc): ?>
                        <option value="<?= htmlspecialchars($kc['kode']) ?>" <?= ($kc['kode'] == $kec_kode ? 'selected' : '') ?>><?= htmlspecialchars($kc['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <hr>
            
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Wilayah</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</main>
<footer class="bg-dark text-light text-center py-2"><small>© 2025 Admin</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    async function loadWilayah(parentKode, targetId, placeholder) {
    }
    document.getElementById('provinsi').addEventListener('change', function() {
    });
    document.getElementById('kabupaten').addEventListener('change', function() {
    });
    document.getElementById('formEditWilayah').addEventListener('submit', async function(e) {
    });
</script>
</body>
</html>