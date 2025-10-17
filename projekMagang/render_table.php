<?php
include __DIR__ . '/database/db.php';

// ==================== Helper ==================== //
function buildQuery($params = []) {
    $q = array_merge($_GET, $params);
    foreach ($q as $k => $v) {
        if ($v === '' || $v === null) unset($q[$k]);
    }
    return http_build_query($q);
}

// ==================== Render Table ==================== //
function renderCustomTable($title, $whereClause, $suffix = "") {
    global $conn;

    // Parameter GET khusus tabel ini
    $sortKey     = "sort" . $suffix;
    $sortNamaKey = "sortNama" . $suffix;
    $pageKey     = "page" . $suffix;
    $searchKey   = "search" . $suffix;

    $sort     = $_GET[$sortKey] ?? 'desc';
    $sortNama = $_GET[$sortNamaKey] ?? '';
    $search   = trim($_GET[$searchKey] ?? '');

    // Map suffix ke tabel spesifik
    $tableMap = [
        "_provinsi"  => "provinsi",
        "_kabupaten" => "kabupaten",
        "_kecamatan" => "kecamatan",
        "_kelurahan" => "kelurahan"
    ];
    $table = $tableMap[$suffix] ?? "wilayah";

    // WHERE dasar + search
   // WHERE dasar + search
$where = $whereClause;
if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);

    // Sesuaikan kolom nama berdasarkan tabel
    $nameField = match ($table) {
        'provinsi'  => 'nama_prov',
        'kabupaten' => 'nama_kab',
        'kecamatan' => 'nama_kec',
        'kelurahan' => 'nama_kel',
        default     => 'nama'
    };

    $where .= " AND {$nameField} LIKE '%$searchEsc%'";
}


    // ORDER BY
if ($sortNama === 'az' || $sortNama === 'za') {
    switch ($table) {
        case 'provinsi':
            $orderBy = "ORDER BY nama_prov " . ($sortNama === 'az' ? 'ASC' : 'DESC');
            break;
        case 'kabupaten':
            $orderBy = "ORDER BY nama_kab " . ($sortNama === 'az' ? 'ASC' : 'DESC');
            break;
        case 'kecamatan':
            $orderBy = "ORDER BY nama_kec " . ($sortNama === 'az' ? 'ASC' : 'DESC');
            break;
        case 'kelurahan':
            $orderBy = "ORDER BY nama_kel " . ($sortNama === 'az' ? 'ASC' : 'DESC');
            break;
        default:
            $orderBy = "ORDER BY nama " . ($sortNama === 'az' ? 'ASC' : 'DESC');
    }
} else {
    switch ($table) {
        case 'provinsi':
            $orderBy = ($sort === 'asc') ? "ORDER BY kode_prov ASC" : "ORDER BY kode_prov DESC";
            break;
        case 'kabupaten':
            $orderBy = ($sort === 'asc') ? "ORDER BY kode_kab ASC" : "ORDER BY kode_kab DESC";
            break;
        case 'kecamatan':
            $orderBy = ($sort === 'asc') ? "ORDER BY kode_kec ASC" : "ORDER BY kode_kec DESC";
            break;
        case 'kelurahan':
            $orderBy = ($sort === 'asc') ? "ORDER BY kode_kel ASC" : "ORDER BY kode_kel DESC";
            break;
        default:
            $orderBy = ($sort === 'asc') ? "ORDER BY kode ASC" : "ORDER BY kode DESC";
    }
}

    // Pagination
    $limit  = 10;
    $page   = isset($_GET[$pageKey]) ? max(1, (int)$_GET[$pageKey]) : 1;
    $offset = ($page - 1) * $limit;

    // Hitung total
    $totalQuery = $conn->query("SELECT COUNT(*) AS total FROM $table WHERE ($where)");
    $totalData  = $totalQuery->fetch_assoc()['total'] ?? 0;
    $totalPages = max(1, ceil($totalData / $limit));

    $sql = "SELECT * FROM $table WHERE ($where) $orderBy LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

    // Tentukan halaman tambah dan edit
    $addPages = [
        "_provinsi"  => "../add_data/add_provinsi.php",
        "_kabupaten" => "../add_data/add_kabupaten.php",
        "_kecamatan" => "../add_data/add_kecamatan.php",
        "_kelurahan" => "../add_data/add_kelurahan.php"
    ];
    $addPage = $addPages[$suffix] ?? "../crud/create.php";

    // Tentukan halaman edit sesuai file aktif
    $editPageMap = [
      "provinsi.php"  => "../edit_data/edit_provinsi.php",
      "kabupaten.php" => "../edit_data/edit_kabupaten.php",
      "kecamatan.php" => "../edit_data/edit_kecamatan.php",
      "kelurahan.php" => "../edit_data/edit_kelurahan.php",
    ];
    $pageName = basename($_SERVER['PHP_SELF']);
    $editPage = $editPageMap[$pageName] ?? "../crud/edit.php";

    // ================= HEADER & SEARCH ================= //
    echo '<form method="get" class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-grow-1">
              <input type="text" name="'.$searchKey.'" class="form-control"
                placeholder="Cari wilayah" value="'.htmlspecialchars($search).'">
              <button type="submit" class="btn btn-secondary ms-2">
                <i class="bi bi-search"></i>
              </button>
            </div>
            <a href="'.$addPage.'?redirect='.urlencode($pageName).'" class="btn btn-primary ms-2">
              <i class="bi bi-plus-circle"></i> Tambah Data
            </a>
          </form>';

    // ==================== TABLE ==================== //
    echo '<div class="card mb-4"><div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped align-middle">
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
                          <li><a class="dropdown-item '.($sort==='asc'?'active-filter':'').'" href="?'.buildQuery([$sortKey=>'asc',$pageKey=>$page]).'">Ascending</a></li>
                          <li><a class="dropdown-item '.($sort==='desc'?'active-filter':'').'" href="?'.buildQuery([$sortKey=>'desc',$pageKey=>$page]).'">Descending</a></li>
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
                          <li><a class="dropdown-item '.($sortNama==='az'?'active-filter':'').'" href="?'.buildQuery([$sortNamaKey=>'az',$pageKey=>$page]).'">A → Z</a></li>
                          <li><a class="dropdown-item '.($sortNama==='za'?'active-filter':'').'" href="?'.buildQuery([$sortNamaKey=>'za',$pageKey=>$page]).'">Z → A</a></li>
                          <li><a class="dropdown-item '.($sortNama===''?'active-filter':'').'" href="?'.buildQuery([$sortNamaKey=>'', $pageKey=>$page]).'">Default</a></li>
                        </ul>
                      </div>
                    </div>
                  </th>';

    if ($table === 'kabupaten') echo '<th>Provinsi</th>';
    if ($table === 'kecamatan') echo '<th>Provinsi</th><th>Kabupaten</th>';
    if ($table === 'kelurahan') echo '<th>Provinsi</th><th>Kabupaten</th><th>Kecamatan</th>';

    echo '<th style="width:120px;">Aksi</th></tr></thead><tbody>';

    // ==================== LOOP DATA ==================== //
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        switch ($table) {
            case 'provinsi':
                $kode = $row['kode_prov'];
                $nama = htmlspecialchars($row['nama_prov']);
                break;
            case 'kabupaten':
                $kode = $row['kode_kab'];
                $nama = htmlspecialchars($row['nama_kab']);
                break;
            case 'kecamatan':
                $kode = $row['kode_kec'];
                $nama = htmlspecialchars($row['nama_kec']);
                break;
            case 'kelurahan':
                $kode = $row['kode_kel'];
                $nama = htmlspecialchars($row['nama_kel']);
                break;
            default:
                $kode = $row['kode'] ?? '-';
                $nama = htmlspecialchars($row['nama'] ?? '-');
                break;
        }

        $prov = $kab = $kec = '-';
        if ($table === 'kabupaten') {
            $kodeProv = explode('.', $kode)[0];
            $prov = $conn->query("SELECT nama_prov FROM provinsi WHERE kode_prov='$kodeProv'")->fetch_assoc()['nama_prov'] ?? '-';
        }
        if ($table === 'kecamatan') {
            $p = explode('.', $kode);
            $kodeProv = $p[0];
            $kodeKab  = $p[0].'.'.$p[1];
            $prov = $conn->query("SELECT nama_prov FROM provinsi WHERE kode_prov='$kodeProv'")->fetch_assoc()['nama_prov'] ?? '-';
            $kab  = $conn->query("SELECT nama_kab FROM kabupaten WHERE kode_kab='$kodeKab'")->fetch_assoc()['nama_kab'] ?? '-';
        }
        if ($table === 'kelurahan') {
            $p = explode('.', $kode);
            $kodeProv = $p[0];
            $kodeKab  = $p[0].'.'.$p[1];
            $kodeKec  = $p[0].'.'.$p[1].'.'.$p[2];
            $prov = $conn->query("SELECT nama_prov FROM provinsi WHERE kode_prov='$kodeProv'")->fetch_assoc()['nama_prov'] ?? '-';
            $kab  = $conn->query("SELECT nama_kab FROM kabupaten WHERE kode_kab='$kodeKab'")->fetch_assoc()['nama_kab'] ?? '-';
            $kec  = $conn->query("SELECT nama_kec FROM kecamatan WHERE kode_kec='$kodeKec'")->fetch_assoc()['nama_kec'] ?? '-';
        }

        echo "<tr>
                <td>$kode</td>
                <td>$nama</td>";

        if ($table === 'kabupaten') echo "<td>$prov</td>";
        if ($table === 'kecamatan') echo "<td>$prov</td><td>$kab</td>";
        if ($table === 'kelurahan') echo "<td>$prov</td><td>$kab</td><td>$kec</td>";

        echo "<td>
          <a href='{$editPage}?kode=" . urlencode($kode) . "&redirect=" . urlencode($_SERVER['REQUEST_URI']) . "' 
            class='btn btn-sm btn-warning'>
            <i class='bi bi-pencil'></i>
          </a>
          <a href='../crud/delete.php?kode=" . urlencode($kode) . "&redirect=" . urlencode($_SERVER['REQUEST_URI']) . "' 
            class='btn btn-sm btn-danger' 
            onclick=\"return confirm('Yakin ingin hapus data ini?');\">
            <i class='bi bi-trash'></i>
          </a>
        </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center text-muted'>Tidak ada data</td></tr>";
}


    echo "</tbody></table>";

    // PAGINATION
    echo '<nav class="mt-3"><ul class="pagination justify-content-center">';
    if ($page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?'.buildQuery([$pageKey=>1]).'">« First</a></li>';
        echo '<li class="page-item"><a class="page-link" href="?'.buildQuery([$pageKey=>$page-1]).'">‹ Prev</a></li>';
    }

    $start = max(1, $page - 3);
    $end   = min($totalPages, $start + 6);

    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $page) ? ' active' : '';
        echo '<li class="page-item'.$active.'"><a class="page-link" href="?'.buildQuery([$pageKey=>$i]).'">'.$i.'</a></li>';
    }

    if ($page < $totalPages) {
        echo '<li class="page-item"><a class="page-link" href="?'.buildQuery([$pageKey=>$page+1]).'">Next ›</a></li>';
        echo '<li class="page-item"><a class="page-link" href="?'.buildQuery([$pageKey=>$totalPages]).'">Last »</a></li>';
    }

    echo '</ul></nav></div></div></div>';
}
?>