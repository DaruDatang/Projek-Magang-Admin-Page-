<?php
include __DIR__ . '/../database/db.php';
function esc($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
function generateNextKode($conn, $table, $kodeField, $parentPrefix = '') {
    $like = $parentPrefix === '' ? '%' : $parentPrefix . '.%';
    $sql = "SELECT $kodeField FROM $table WHERE $kodeField LIKE '" . $conn->real_escape_string($like) . "' ORDER BY $kodeField DESC LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $last = $row[$kodeField];
        $parts = explode('.', $last);
        $parts[count($parts)-1] = (int)$parts[count($parts)-1] + 1;
        return implode('.', $parts);
    } else {
        if ($parentPrefix === '') {
            $res2 = $conn->query("SELECT $kodeField FROM $table ORDER BY $kodeField DESC LIMIT 1");
            if ($res2 && $res2->num_rows > 0) {
                $r = $res2->fetch_assoc();
                return (string)((int)$r[$kodeField] + 1);
            }
            return '1';
        } else {
            return $parentPrefix . '.01';
        }
    }
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = (int)($_POST['level'] ?? 0);
    $kodeInput = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $parent_prov = $_POST['provinsi'] ?? '';
    $parent_kab  = $_POST['kabupaten'] ?? '';
    $parent_kec  = $_POST['kecamatan'] ?? '';

    if ($level < 1 || $level > 4) $errors[] = "Level tidak valid.";
    if ($nama === '') $errors[] = "Nama wajib diisi.";
    
    $table = $kodeField = $namaField = null;
    if ($level === 1) { $table = 'provinsi'; $kodeField = 'kode_prov'; $namaField = 'nama_prov'; }
    elseif ($level === 2) { $table = 'kabupaten'; $kodeField = 'kode_kab'; $namaField = 'nama_kab'; }
    elseif ($level === 3) { $table = 'kecamatan'; $kodeField = 'kode_kec'; $namaField = 'nama_kec'; }
    elseif ($level === 4) { $table = 'kelurahan'; $kodeField = 'kode_kel'; $namaField = 'nama_kel'; }

    $parentPrefix = '';
    if ($level === 2) { if (!$parent_prov) $errors[] = "Pilih Provinsi sebagai parent."; $parentPrefix = $parent_prov; }
    if ($level === 3) { if (!$parent_kab) $errors[] = "Pilih Kabupaten sebagai parent."; $parentPrefix = $parent_kab; }
    if ($level === 4) { if (!$parent_kec) $errors[] = "Pilih Kecamatan sebagai parent."; $parentPrefix = $parent_kec; }

    if (empty($errors)) {
        if ($kodeInput === '') {
            $newKode = generateNextKode($conn, $table, $kodeField, $parentPrefix);
        } else {
            $newKode = $kodeInput;
            $cek = $conn->query("SELECT 1 FROM $table WHERE $kodeField='" . $conn->real_escape_string($newKode) . "' LIMIT 1");
            if ($cek && $cek->num_rows > 0) $errors[] = "Kode sudah ada di database.";
        }
    }

    if (empty($errors)) {
        $kodeEsc = $conn->real_escape_string($newKode);
        $namaEsc = $conn->real_escape_string($nama);

        if ($level === 1) { $sql = "INSERT INTO provinsi ($kodeField, $namaField) VALUES ('$kodeEsc', '$namaEsc')"; }
        elseif ($level === 2) { $prov_for_kab = $conn->real_escape_string($parent_prov); $sql = "INSERT INTO kabupaten ($kodeField, $namaField, kode_prov) VALUES ('$kodeEsc', '$namaEsc', '$prov_for_kab')"; }
        elseif ($level === 3) { $kab_for_kec = $conn->real_escape_string($parent_kab); $sql = "INSERT INTO kecamatan ($kodeField, $namaField, kode_kab) VALUES ('$kodeEsc', '$namaEsc', '$kab_for_kec')"; }
        else { $kec_for_kel = $conn->real_escape_string($parent_kec); $sql = "INSERT INTO kelurahan ($kodeField, $namaField, kode_kec) VALUES ('$kodeEsc', '$namaEsc', '$kec_for_kel')"; }

        if (!$conn->query($sql)) {
            $errors[] = "Gagal menyimpan: " . $conn->error;
        } else {
            if ($conn->query("SHOW TABLES LIKE 'wilayah'")->num_rows > 0) {
                $conn->query("INSERT INTO wilayah (kode, nama, level) VALUES ('$kodeEsc', '$namaEsc', $level)");
            }
            $redirectPage = [1 => '../pages/provinsi.php', 2 => '../pages/kabupaten.php', 3 => '../pages/kecamatan.php', 4 => '../pages/kelurahan.php'][$level];
            header("Location: $redirectPage");
            exit;
        }
    }
}
$provList = $conn->query("SELECT kode_prov as kode, nama_prov as nama FROM provinsi ORDER BY nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Data Wilayah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        main { flex: 1; display: flex; }
        .sidebar { width: 220px; background-color: #f8f9fa; padding: 1rem; border-right: 1px solid #ddd; }
        .content { flex: 1; padding: 2rem; }
        .small-note{ font-size:.9rem; color:#6c757d; }
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
            <li class="nav-item"><a href="../pages/index.php" class="nav-link <?= $current=='index.php'?'active':'' ?>"><i class="bi bi-geo-alt"></i> Semua Data</a></li>
            <li class="nav-item"><a href="../pages/provinsi.php" class="nav-link <?= $current=='provinsi.php'?'active':'' ?>"><i class="bi bi-building"></i> Provinsi</a></li>
            <li class="nav-item"><a href="../pages/kabupaten.php" class="nav-link <?= $current=='kabupaten.php'?'active':'' ?>"><i class="bi bi-signpost"></i> Kabupaten/Kota</a></li>
            <li class="nav-item"><a href="../pages/kecamatan.php" class="nav-link <?= $current=='kecamatan.php'?'active':'' ?>"><i class="bi bi-signpost-split"></i> Kecamatan</a></li>
            <li class="nav-item"><a href="../pages/kelurahan.php" class="nav-link <?= $current=='kelurahan.php'?'active':'' ?>"><i class="bi bi-house-door"></i> Kelurahan/Desa</a></li>
        </ul>
    </div>

    <div class="content">
        <h3>Tambah Data Wilayah</h3>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($errors as $e) echo "<li>".esc($e)."</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width: 600px;">
            <div class="mb-3">
                <label class="form-label">Level</label>
                <select name="level" id="level" class="form-select" required>
                    <option value="1">Provinsi</option>
                    <option value="2">Kabupaten/Kota</option>
                    <option value="3">Kecamatan</option>
                    <option value="4">Kelurahan/Desa</option>
                </select>
                <div class="small-note">Pilih level data yang ingin ditambahkan.</div>
            </div>

            <div class="mb-3 parent parent-2 parent-3 parent-4" style="display:none">
                <label class="form-label">Provinsi</label>
                <select name="provinsi" id="provinsi" class="form-select">
                    <option value="">-- Pilih Provinsi --</option>
                    <?php
                    $provList->data_seek(0); 
                    while ($p = $provList->fetch_assoc()): ?>
                        <option value="<?= esc($p['kode']) ?>"><?= esc($p['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3 parent parent-3 parent-4" style="display:none">
                <label class="form-label">Kabupaten</label>
                <select name="kabupaten" id="kabupaten" class="form-select" disabled>
                    <option value="">-- Pilih Provinsi terlebih dahulu --</option>
                </select>
            </div>

            <div class="mb-3 parent parent-4" style="display:none">
                <label class="form-label">Kecamatan</label>
                <select name="kecamatan" id="kecamatan" class="form-select" disabled>
                    <option value="">-- Pilih Kabupaten terlebih dahulu --</option>
                </select>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">Kode</label>
                <input type="text" name="kode" id="kode" class="form-control" placeholder="Contoh: 33 atau 33.02.04">
                <div class="small-note">Jika dibiarkan kosong, sistem akan membuat kode selanjutnya secara otomatis.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '../pages/index.php') ?>">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="../pages/index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</main>
<footer class="bg-dark text-light text-center py-2"><small>© 2025 Admin</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    async function loadChildren(parentKode, targetId, placeholder) {
        const targetSelect = document.getElementById(targetId);
        targetSelect.innerHTML = `<option value="">Memuat...</option>`;
        targetSelect.disabled = true;
        try {
            const response = await fetch(`../database/get_wilayah.php?parent=${encodeURIComponent(parentKode)}`);
            const data = await response.json();
            targetSelect.innerHTML = `<option value="">-- ${placeholder} --</option>`;
            if (data.length > 0) {
                data.forEach(d => {
                    const option = new Option(`${d.nama} (${d.kode})`, d.kode);
                    targetSelect.add(option);
                });
                targetSelect.disabled = false;
            } else {
                targetSelect.innerHTML = `<option value="">-- Tidak ada data --</option>`;
            }
        } catch (error) {
            console.error('Error:', error);
            targetSelect.innerHTML = `<option value="">Gagal memuat data</option>`;
        }
    }
    document.getElementById('level').addEventListener('change', function(){
        const lvl = +this.value;
        document.querySelectorAll('.parent').forEach(el => el.style.display = 'none');
        if (lvl >= 2) document.querySelectorAll('.parent-2').forEach(el => el.style.display = 'block');
        if (lvl >= 3) document.querySelectorAll('.parent-3').forEach(el => el.style.display = 'block');
        if (lvl >= 4) document.querySelectorAll('.parent-4').forEach(el => el.style.display = 'block');
    });
    document.getElementById('provinsi').addEventListener('change', function(){
        const provKode = this.value;
        const kabSelect = document.getElementById('kabupaten');
        const kecSelect = document.getElementById('kecamatan');
        kabSelect.innerHTML = '<option value="">-- Pilih Provinsi terlebih dahulu --</option>';
        kabSelect.disabled = true;
        kecSelect.innerHTML = '<option value="">-- Pilih Kabupaten terlebih dahulu --</option>';
        kecSelect.disabled = true;
        if (provKode) {
            loadChildren(provKode, 'kabupaten', 'Pilih Kabupaten/Kota');
        }
    });
    document.getElementById('kabupaten').addEventListener('change', function(){
        const kabKode = this.value;
        const kecSelect = document.getElementById('kecamatan');
        kecSelect.innerHTML = '<option value="">-- Pilih Kabupaten terlebih dahulu --</option>';
        kecSelect.disabled = true;
        if (kabKode) {
            loadChildren(kabKode, 'kecamatan', 'Pilih Kecamatan');
        }
    });
    document.getElementById('level').dispatchEvent(new Event('change'));
</script>
</body>
</html>