<?php
include __DIR__ . '/../database/db.php';
$kode = $_GET['kode'] ?? '';
if (empty($kode)) die("Error: Kode provinsi tidak ditemukan.");
$stmt = $conn->prepare("SELECT * FROM provinsi WHERE kode_prov = ?");
$stmt->bind_param("s", $kode);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$data) die("Data provinsi tidak ditemukan!");
$redirect = '/projekMagang/pages/provinsi.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $response = ["success" => false, "message" => ""];
    $nama = trim($_POST['nama'] ?? '');
    $kodeLama = $_POST['kode'] ?? '';
    if (empty($nama)) {
        $response['message'] = 'Nama provinsi tidak boleh kosong!';
        echo json_encode($response);
        exit;
    }
    $stmt_check = $conn->prepare("SELECT 1 FROM provinsi WHERE nama_prov = ? AND kode_prov != ?");
    $stmt_check->bind_param("ss", $nama, $kodeLama);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $response['message'] = "Nama provinsi '$nama' sudah digunakan!";
        echo json_encode($response);
        exit;
    }
    $conn->begin_transaction();
    try {
        $stmt_prov = $conn->prepare("UPDATE provinsi SET nama_prov = ? WHERE kode_prov = ?");
        $stmt_prov->bind_param("ss", $nama, $kodeLama);
        $stmt_prov->execute();
        $stmt_wil = $conn->prepare("UPDATE wilayah SET nama = ? WHERE kode = ?");
        $stmt_wil->bind_param("ss", $nama, $kodeLama);
        $stmt_wil->execute();
        $conn->commit();
        $response["success"] = true;
        $response["message"] = "Data berhasil diperbarui!";
        $response["redirect"] = $redirect;
    } catch (Exception $e) {
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
    <title>Edit Provinsi</title>
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
        <h3>Edit Provinsi: <?= htmlspecialchars($data['nama_prov']) ?></h3>
        <form method="post" id="formEditProvinsi" style="max-width: 600px;">
            <input type="hidden" name="kode" value="<?= htmlspecialchars($data['kode_prov']) ?>">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Provinsi</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($data['nama_prov']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="<?= htmlspecialchars($redirect) ?>" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</main>
<footer class="bg-dark text-light text-center py-2"><small>© 2025 Admin</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formEditProvinsi').addEventListener('submit', async function(e) {
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