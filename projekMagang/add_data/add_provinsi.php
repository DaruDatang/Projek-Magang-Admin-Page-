<?php
include __DIR__ . '/../database/db.php';
$redirect = '/projekMagang/pages/provinsi.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    $nama = trim($_POST['nama'] ?? '');
    if (empty($nama)) {
        $response['message'] = 'Nama provinsi tidak boleh kosong!';
        echo json_encode($response);
        exit;
    }
    $stmt_check = $conn->prepare("SELECT 1 FROM provinsi WHERE nama_prov = ?");
    $stmt_check->bind_param("s", $nama);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $response['message'] = "Nama provinsi '$nama' sudah ada!";
        echo json_encode($response);
        exit;
    }
    $res = $conn->query("SELECT kode_prov FROM provinsi ORDER BY CAST(kode_prov AS UNSIGNED) DESC LIMIT 1");
    $newKode = $res->num_rows > 0 ? str_pad((int)$res->fetch_assoc()['kode_prov'] + 1, 2, "0", STR_PAD_LEFT) : "01";
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO provinsi (kode_prov, nama_prov) VALUES (?, ?)");
        $stmt->bind_param("ss", $newKode, $nama);
        $stmt->execute();
        $stmt->close();
        $wilayahStmt = $conn->prepare("INSERT INTO wilayah (kode, nama, level) VALUES (?, ?, 1)");
        $wilayahStmt->bind_param("ss", $newKode, $nama);
        $wilayahStmt->execute();
        $wilayahStmt->close();
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Provinsi berhasil ditambahkan!';
        $response['redirect'] = $redirect;
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Gagal menyimpan data: ' . $e->getMessage();
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
    <title>Tambah Provinsi</title>
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
        <h3>Tambah Provinsi Baru</h3>
        <form method="post" id="formAddProvinsi" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Provinsi</label>
                <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh: Jawa Barat" required>
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="<?= htmlspecialchars($redirect) ?>" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</main>
<footer class="bg-dark text-light text-center py-2"><small>© 2025 Admin</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formAddProvinsi').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const data = await response.json();
        alert(data.message);
        if (data.success) {
            window.location.href = data.redirect;
        }
    } catch (error) {
        alert('Terjadi kesalahan saat mengirim data.');
    }
});
</script>
</body>
</html>