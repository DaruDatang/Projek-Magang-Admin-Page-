<?php $active_menu = isset($active_menu) ? $active_menu : ''; ?>

<div class="sidebar p-3">
    
    <div>
        <div class="text-center mb-3">
            <img src="<?= base_url('assets/img/logo2.png'); ?>" alt="Logo" style="width: 90px; height: auto;">
        </div>

        <h6 class="text-white-50 small text-uppercase" style="padding-left: .5rem;">Menu</h6>
        <nav class="nav flex-column">
            <a href="<?= base_url('wilayah'); ?>" class="nav-link <?= $active_menu == 'wilayah' ? 'active' : ''; ?>">
                <i class="fa-solid fa-layer-group fa-fw"></i> Semua Wilayah
            </a>
            <a href="<?= base_url('provinsi'); ?>" class="nav-link <?= $active_menu == 'provinsi' ? 'active' : ''; ?>">
                <i class="fa-solid fa-map fa-fw"></i> Provinsi
            </a>
            <a href="<?= base_url('kabupaten'); ?>" class="nav-link <?= $active_menu == 'kabupaten' ? 'active' : ''; ?>">
                <i class="fa-solid fa-map-location-dot fa-fw"></i> Kabupaten
            </a>
            <a href="<?= base_url('kecamatan'); ?>" class="nav-link <?= $active_menu == 'kecamatan' ? 'active' : ''; ?>">
                <i class="fa-solid fa-location-dot fa-fw"></i> Kecamatan
            </a>
            <a href="<?= base_url('kelurahan'); ?>" class="nav-link <?= $active_menu == 'kelurahan' ? 'active' : ''; ?>">
                <i class="fa-solid fa-house-chimney fa-fw"></i> Kelurahan
            </a>
        </nav>
    </div>

    <div class="sidebar-footer text-center"> 
        <small class="text-white-50" style="font-size: 0.75rem;">
            &copy; 2025 Central Asesmen Asia
        </small>
    </div>
</div>