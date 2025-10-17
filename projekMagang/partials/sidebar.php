<?php
?>
<style>
    .sidebar-custom {
        background-color: #f8f9fa; 
        width: 250px;
        height: 100vh; 
        padding: 1.5rem;
    }
    .sidebar-custom .nav-link {
        color: #0d6efd;
        font-size: 1rem;
        font-weight: 500;
        padding: 0.5rem 0; 
        display: flex;
        align-items: center;
    }
    .sidebar-custom .nav-link .bi {
        font-size: 1.1rem;
        margin-right: 0.75rem; 
    }
    .sidebar-custom .nav-link:hover {
        text-decoration: underline;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<div class="sidebar-custom border-end">
    <h5 class="text-muted mb-3">Menu</h5>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="/projekMagang/pages/index.php" class="nav-link">
                <i class="bi bi-collection"></i>Semua Data
            </a>
        </li>
        <li class="nav-item">
            <a href="/projekMagang/pages/provinsi.php" class="nav-link">
                <i class="bi bi-building"></i>Provinsi
            </a>
        </li>
        <li class="nav-item">
            <a href="/projekMagang/pages/kabupaten.php" class="nav-link">
                <i class="bi bi-signpost-2"></i>Kabupaten/Kota
            </a>
        </li>
        <li class="nav-item">
            <a href="/projekMagang/pages/kecamatan.php" class="nav-link">
                <i class="bi bi-signpost-split"></i>Kecamatan
            </a>
        </li>
        <li class="nav-item">
            <a href="/projekMagang/pages/kelurahan.php" class="nav-link">
                <i class="bi bi-house-door"></i>Kelurahan/Desa
            </a>
        </li>
    </ul>
</div>