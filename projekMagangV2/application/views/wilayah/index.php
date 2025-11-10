<div class="d-flex justify-content-between align-items-center mb-3">
    
    <h4 class="mb-0">
        Data Wilayah Indonesia 
        <span class="badge bg-secondary rounded-pill"><?= $total_data; ?></span>
    </h4>

    <div class="d-flex align-items-center">
        
        <form method="get" class="me-2">
            <div class="input-group input-group-sm">
                <input type="text" name="q" value="<?= $this->input->get('q'); ?>" class="form-control" placeholder="Cari Wilayah" style="width: 180px;">
                <button type="submit" class="btn btn-secondary" title="Cari">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <a href="<?= base_url('wilayah/form'); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Data
        </a>
    </div>
</div>

<div class="card shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <?php
                $query_params = $this->input->get();
                unset($query_params['page'], $query_params['sort'], $query_params['order']);
                $query_string = http_build_query($query_params);
                $query_string = $query_string ? '&' . $query_string : '';
                $base_url = base_url(uri_string());
                ?>
                <tr>
                    <th style="width:80px">
                        Kode
                        <?php
                        $col = 'kode';
                        $new_order = ($sort == $col && $order == 'ASC') ? 'DESC' : 'ASC';
                        $icon = ($sort == $col) ? ($order == 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                        ?>
                        <a href="<?= "{$base_url}?sort={$col}&order={$new_order}{$query_string}"; ?>" class="text-white text-decoration-none ms-1">
                            <i class="fas <?= $icon; ?>"></i>
                        </a>
                    </th>
                    <th>
                        Nama Wilayah
                        <?php
                        $col = 'nama';
                        $new_order = ($sort == $col && $order == 'ASC') ? 'DESC' : 'ASC';
                        $icon = ($sort == $col) ? ($order == 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                        ?>
                        <a href="<?= "{$base_url}?sort={$col}&order={$new_order}{$query_string}"; ?>" class="text-white text-decoration-none ms-1">
                            <i class="fas <?= $icon; ?>"></i>
                        </a>
                    </th>
                    <th style="width:120px">
                        Level
                        <?php
                        $col = 'level';
                        $new_order = ($sort == $col && $order == 'ASC') ? 'DESC' : 'ASC';
                        $icon = ($sort == $col) ? ($order == 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                        ?>
                        <a href="<?= "{$base_url}?sort={$col}&order={$new_order}{$query_string}"; ?>" class="text-white text-decoration-none ms-1">
                            <i class="fas <?= $icon; ?>"></i>
                        </a>
                    </th>
                    <th style="width:130px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($wilayah)): ?>
                    <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
                <?php else: ?>
                    <?php foreach($wilayah as $w): ?>
                        <?php
                        $controller_level = strtolower($w['level']);
                        ?>
                        <tr>
                            <td><?= $w['kode']; ?></td>
                            <td><?= $w['nama']; ?></td>
                            <td><?= $w['level']; ?></td>
                            <td>
                                <a href="<?= base_url($controller_level . '/edit/' . $w['kode']); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-pencil"></i>
                                </a>
                                <a href="<?= base_url($controller_level . '/delete/' . $w['kode']); ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Anda yakin ingin menghapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <?= $links; ?>
</div>