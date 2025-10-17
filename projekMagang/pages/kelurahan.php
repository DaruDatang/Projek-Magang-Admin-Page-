<?php
include __DIR__ . '/../database/db.php';
include __DIR__ . '/../render_table.php';
$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Data Kelurahan/Desa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { min-height: 100vh; display: flex; flex-direction: column; }
    main { flex: 1; display: flex; }
    .sidebar { width: 220px; background-color: #f8f9fa; padding: 1rem; border-right: 1px solid #ddd; }
    .content { flex: 1; padding: 2rem; }
    .active { font-weight: bold; color: #0d6efd !important; }
  </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid"><a class="navbar-brand" href="#">Kode Wilayah Indonesia</a></div>
</nav>
<main>
  <div class="sidebar">
    <h6 class="text-muted">Menu</h6>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="index.php" class="nav-link <?= $current=='index.php'?'active':'' ?>"><i class="bi bi-geo-alt"></i> Semua Data</a></li>
      <li class="nav-item"><a href="provinsi.php" class="nav-link <?= $current=='provinsi.php'?'active':'' ?>"><i class="bi bi-building"></i> Provinsi</a></li>
      <li class="nav-item"><a href="kabupaten.php" class="nav-link <?= $current=='kabupaten.php'?'active':'' ?>"><i class="bi bi-signpost"></i> Kabupaten/Kota</a></li>
      <li class="nav-item"><a href="kecamatan.php" class="nav-link <?= $current=='kecamatan.php'?'active':'' ?>"><i class="bi bi-signpost-split"></i> Kecamatan</a></li>
      <li class="nav-item"><a href="kelurahan.php" class="nav-link <?= $current=='kelurahan.php'?'active':'' ?>"><i class="bi bi-house-door"></i> Kelurahan/Desa</a></li>
    </ul>
  </div>
  <div class="content">
    <h3>Data Kelurahan/Desa</h3>
    <?php renderCustomTable("Data Kelurahan/Desa", "1=1", "_kelurahan"); ?>
  </div>
</main>
<footer class="bg-dark text-light text-center py-2"><small>© 2025 Admin</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>