<?php
include __DIR__ . '/../database/db.php';

// Ambil kode dari URL
$kode = $_GET['kode'] ?? '';
if (empty($kode)) {
    header("Location: /projekMagang/pages/index.php?status=error&msg=" . urlencode("Kode tidak valid!"));
    exit;
}

$dotCount = substr_count($kode, '.');
switch ($dotCount) {
    case 0: 
        $redirect_url = '/projekMagang/pages/provinsi.php';
        break;
    case 1: 
        $redirect_url = '/projekMagang/pages/kabupaten.php';
        break;
    case 2: 
        $redirect_url = '/projekMagang/pages/kecamatan.php';
        break;
    case 3: 
        $redirect_url = '/projekMagang/pages/kelurahan.php';
        break;
    default: 
        $redirect_url = '/projekMagang/pages/index.php';
}

$conn->begin_transaction();

try {
    $stmt_wilayah = $conn->prepare("DELETE FROM wilayah WHERE kode = ? OR kode LIKE ?");
    $like_kode = $kode . '.%';
    $stmt_wilayah->bind_param("ss", $kode, $like_kode);
    $stmt_wilayah->execute();

    if ($dotCount <= 3) { 
        $stmt_kel = $conn->prepare("DELETE FROM kelurahan WHERE kode_kel LIKE ?");
        $stmt_kel->bind_param("s", $like_kode);
        $stmt_kel->execute();
    }
    if ($dotCount <= 2) {
        $stmt_kec = $conn->prepare("DELETE FROM kecamatan WHERE kode_kec LIKE ?");
        $stmt_kec->bind_param("s", $like_kode);
        $stmt_kec->execute();
    }
    if ($dotCount <= 1) { 
        $stmt_kab = $conn->prepare("DELETE FROM kabupaten WHERE kode_kab LIKE ?");
        $stmt_kab->bind_param("s", $like_kode);
        $stmt_kab->execute();
    }
    if ($dotCount == 0) { 
        $stmt_prov = $conn->prepare("DELETE FROM provinsi WHERE kode_prov = ?");
        $stmt_prov->bind_param("s", $kode);
        $stmt_prov->execute();
    }
    
    $conn->commit();
    
    header("Location: " . $redirect_url . "?status=success&msg=" . urlencode("Data berhasil dihapus."));
    exit;

} catch (mysqli_sql_exception $exception) {
    $conn->rollback();

    $error_message = "Gagal menghapus data: " . $exception->getMessage();
    header("Location: " . $redirect_url . "?status=error&msg=" . urlencode($error_message));
    exit;
}
?>