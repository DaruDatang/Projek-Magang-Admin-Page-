<?php
include __DIR__ . '/../database/db.php';
function buildQuery($params = []) {
    $q = array_merge($_GET, $params);
    foreach ($q as $k => $v) {
        if ($v === '' || $v === null) unset($q[$k]);
    }
    return http_build_query($q);
}

$allowedSort = ['asc','desc','recent'];
$allowedSortNama = ['az','za',''];
$allowedLevel = [1,2,3,4,''];

$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : 'desc';
$sortNama = isset($_GET['sortNama']) && in_array($_GET['sortNama'], $allowedSortNama) ? $_GET['sortNama'] : '';
$levelFilter = isset($_GET['level']) && in_array($_GET['level'], $allowedLevel) ? $_GET['level'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Pagination ---
$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$unionQuery = "
    SELECT kode_prov AS kode, nama_prov AS nama, 1 AS level FROM provinsi
    UNION ALL
    SELECT kode_kab AS kode, nama_kab AS nama, 2 AS level FROM kabupaten
    UNION ALL
    SELECT kode_kec AS kode, nama_kec AS nama, 3 AS level FROM kecamatan
    UNION ALL
    SELECT kode_kel AS kode, nama_kel AS nama, 4 AS level FROM kelurahan
";

// --- WHERE (filter level + search) ---
$where = "1=1";
if ($levelFilter !== '') {
    $levelInt = (int)$levelFilter;
    $where .= " AND level = $levelInt";
}
if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);
    $where .= " AND nama LIKE '%$searchEsc%'";
}

// --- ORDER BY ---
if ($sortNama === 'az') {
    $orderBy = "ORDER BY nama COLLATE utf8mb4_unicode_ci ASC";
} elseif ($sortNama === 'za') {
    $orderBy = "ORDER BY nama COLLATE utf8mb4_unicode_ci DESC";
} else {
    if ($sort === 'recent' || $sort === 'desc') {
        $orderBy = "ORDER BY kode DESC";
    } else {
        $orderBy = "ORDER BY kode ASC";
    }
}

// --- Hitung total data ---
$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM ($unionQuery) AS wilayahGabung WHERE $where");
$totalData = $totalQuery->fetch_assoc()['total'] ?? 0;
$totalPages = max(1, ceil($totalData / $limit));

// --- Ambil data dengan pagination ---
$sql = "
    SELECT * FROM ($unionQuery) AS wilayahGabung
    WHERE $where
    $orderBy
    LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel - Data Wilayah</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { min-height: 100vh; display: flex; flex-direction: column; }
    main { flex: 1; display: flex; }
    .sidebar { width: 220px; background-color: #f8f9fa; padding: 1rem; border-right: 1px solid #ddd; }
    .content { flex: 1; padding: 2rem; }
    th .dropdown-toggle { padding: 0.15rem 0.4rem; }
    .active-filter { font-weight: bold; color: #0d6efd; }
    .card { margin-bottom: 20px;}
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
      <li class="nav-item"><a href="index.php" class="nav-link <?= $current=='index.php'?'active':'' ?>"><i class="bi bi-geo-alt"></i> Semua Data</a></li>
      <li class="nav-item"><a href="provinsi.php" class="nav-link <?= $current=='provinsi.php'?'active':'' ?>"><i class="bi bi-building"></i> Provinsi</a></li>
      <li class="nav-item"><a href="kabupaten.php" class="nav-link <?= $current=='kabupaten.php'?'active':'' ?>"><i class="bi bi-signpost"></i> Kabupaten/Kota</a></li>
      <li class="nav-item"><a href="kecamatan.php" class="nav-link <?= $current=='kecamatan.php'?'active':'' ?>"><i class="bi bi-signpost-split"></i> Kecamatan</a></li>
      <li class="nav-item"><a href="kelurahan.php" class="nav-link <?= $current=='kelurahan.php'?'active':'' ?>"><i class="bi bi-house-door"></i> Kelurahan/Desa</a></li>
  </ul>
</div>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Semua Data Wilayah</h3>
    <div class="d-flex gap-2">
      <!-- Search bar -->
      <form method="get" class="d-flex">
        <input type="text" name="search" class="form-control" placeholder="Cari wilayah" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-secondary ms-1"><i class="bi bi-search"></i></button>
        <?php
        foreach (['sort','sortNama','level','page'] as $p) {
          if (isset($_GET[$p]) && $_GET[$p] !== '') {
            echo '<input type="hidden" name="'.$p.'" value="'.htmlspecialchars($_GET[$p]).'">';
          }
        }
        ?>
      </form>
      <a href="../crud/create.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Data</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        
        <table class="table table-striped">
          <thead class="table-dark">
            <tr>
              <th>
                <div class="d-flex justify-content-between align-items-center">
                  Kode
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item <?= ($sort==='asc'?'active-filter':'') ?>" href="?<?= buildQuery(['sort'=>'asc','page'=>$page]) ?>">Ascending</a></li>
                      <li><a class="dropdown-item <?= ($sort==='desc'?'active-filter':'') ?>" href="?<?= buildQuery(['sort'=>'desc','page'=>$page]) ?>">Descending</a></li>
                      <li><a class="dropdown-item <?= ($sort==='recent'?'active-filter':'') ?>" href="?<?= buildQuery(['sort'=>'recent','page'=>$page]) ?>">Recently Added</a></li>
                    </ul>
                  </div>
                </div>
              </th>
              <th>
                <div class="d-flex justify-content-between align-items-center">
                  Nama Wilayah
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item <?= ($sortNama==='az'?'active-filter':'') ?>" href="?<?= buildQuery(['sortNama'=>'az','page'=>$page]) ?>">A → Z</a></li>
                      <li><a class="dropdown-item <?= ($sortNama==='za'?'active-filter':'') ?>" href="?<?= buildQuery(['sortNama'=>'za','page'=>$page]) ?>">Z → A</a></li>
                      <li><a class="dropdown-item <?= ($sortNama===''?'active-filter':'') ?>" href="?<?= buildQuery(['sortNama'=>'','page'=>$page]) ?>">Default</a></li>
                    </ul>
                  </div>
                </div>
              </th>
              <th>
                <div class="d-flex justify-content-between align-items-center">
                  Level
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item <?= ($levelFilter==='1'?'active-filter':'') ?>" href="?<?= buildQuery(['level'=>1,'page'=>$page]) ?>">Provinsi</a></li>
                      <li><a class="dropdown-item <?= ($levelFilter==='2'?'active-filter':'') ?>" href="?<?= buildQuery(['level'=>2,'page'=>$page]) ?>">Kabupaten/Kota</a></li>
                      <li><a class="dropdown-item <?= ($levelFilter==='3'?'active-filter':'') ?>" href="?<?= buildQuery(['level'=>3,'page'=>$page]) ?>">Kecamatan</a></li>
                      <li><a class="dropdown-item <?= ($levelFilter==='4'?'active-filter':'') ?>" href="?<?= buildQuery(['level'=>4,'page'=>$page]) ?>">Kelurahan/Desa</a></li>
                      <li><a class="dropdown-item <?= ($levelFilter===''?'active-filter':'') ?>" href="?<?= buildQuery(['level'=>'','page'=>$page]) ?>">Semua</a></li>
                    </ul>
                  </div>
                </div>
              </th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if ($result && $result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  $level = substr_count($row['kode'], '.') + 1;
                  $levelText = match($level) {
                      1 => 'Provinsi',
                      2 => 'Kabupaten/Kota',
                      3 => 'Kecamatan',
                      default => 'Kelurahan/Desa'
                  };
                  $kodeEsc = htmlspecialchars($row['kode'], ENT_QUOTES, 'UTF-8');
                  $namaEsc = htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8');

                  echo "<tr>
                          <td>{$kodeEsc}</td>
                          <td>{$namaEsc}</td>
                          <td>{$levelText}</td>
                          <td>
                            <a href='../crud/edit.php?kode=".urlencode($row['kode'])."' class='btn btn-sm btn-warning'><i class='bi bi-pencil'></i></a>
                            <a href='../crud/delete.php?kode=".urlencode($row['kode'])."' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin ingin hapus data ini?');\"><i class='bi bi-trash'></i></a>
                          </td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='4'>Tidak ada data</td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav class="mt-3">
        <ul class="pagination justify-content-center">
          <?php
          if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="?'.buildQuery(['page'=>1]).'">« First</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?'.buildQuery(['page'=>$page-1]).'">‹ Prev</a></li>';
          }

          $start = max(1, $page - 7);
          $end = min($totalPages, $start + 14);
          if ($end - $start < 14) $start = max(1, $end - 14);

          for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page) ? ' active' : '';
            echo '<li class="page-item'.$active.'"><a class="page-link" href="?'.buildQuery(['page'=>$i]).'">'.$i.'</a></li>';
          }

          if ($page < $totalPages) {
            echo '<li class="page-item"><a class="page-link" href="?'.buildQuery(['page'=>$page+1]).'">Next ›</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?'.buildQuery(['page'=>$totalPages]).'">Last »</a></li>';
          }
          ?>
        </ul>
      </nav>
    </div>
  </div>
</div>
</main>
<footer class="bg-dark text-light text-center py-2"><small>© 2025 Admin</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>