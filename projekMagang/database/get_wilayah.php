<?php
include __DIR__ . '/db.php'; 
header('Content-Type: application/json; charset=utf-8');

$parent = $_GET['parent'] ?? '';
$parent = trim($parent);

if ($parent === '') {
    echo json_encode([]);
    exit;
}

$level = substr_count($parent, '.') + 1;
$table = $kodeField = $namaField = $relField = null;

switch ($level) {
    case 1:
        $table = "kabupaten"; 
        $kodeField = "kode_kab"; 
        $namaField = "nama_kab"; 
        $relField  = "kode_prov";
        break;
    case 2: 
        $table = "kecamatan"; 
        $kodeField = "kode_kec"; 
        $namaField = "nama_kec"; 
        $relField  = "kode_kab";
        break;
    case 3:
        $table = "kelurahan"; 
        $kodeField = "kode_kel"; 
        $namaField = "nama_kel"; 
        $relField  = "kode_kec";
        break;
    default:
        echo json_encode([]);
        exit;
}

$sql = "SELECT {$kodeField} AS kode, {$namaField} AS nama 
        FROM {$table}
        WHERE {$relField} = ?
        ORDER BY {$namaField} ASC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode([
        'error' => 'Query Gagal Disiapkan',
        'mysql_error' => $conn->error,
        'query_gagal' => $sql
    ]);
    exit;
}

$stmt->bind_param("s", $parent);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($data);
?>