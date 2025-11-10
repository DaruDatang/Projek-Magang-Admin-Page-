<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <?= $title; ?>
        <span class="badge bg-secondary rounded-pill"><?= $total_data; ?></span>
    </h4>
    <div class="d-flex align-items-center">
        <form method="get" class="me-2">
            <input type="hidden" name="order" value="<?= $order; ?>">
            <input type="hidden" name="sort" value="<?= $sort; ?>">
            <div class="input-group input-group-sm">
                <input type="text" name="q" value="<?= $this->input->get('q'); ?>" class="form-control" placeholder="Cari Provinsi" style="width: 180px;">
                <button type="submit" class="btn btn-secondary" title="Cari">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        <a href="<?= base_url('provinsi/add'); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Data
        </a>
    </div>
</div>

<div class="card shadow-sm p-4">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <?php
            $query_params = $this->input->get();
            unset($query_params['page'], $query_params['sort'], $query_params['order']);
            $query_string = http_build_query($query_params);
            $query_string = $query_string ? '&' . $query_string : '';
            $base_url = base_url(uri_string());
            ?>
            <tr>
                <th width="100">
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
                    Nama Provinsi
                    <?php
                    $col = 'nama';
                    $new_order = ($sort == $col && $order == 'ASC') ? 'DESC' : 'ASC';
                    $icon = ($sort == $col) ? ($order == 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                    ?>
                    <a href="<?= "{$base_url}?sort={$col}&order={$new_order}{$query_string}"; ?>" class="text-white text-decoration-none ms-1">
                        <i class="fas <?= $icon; ?>"></i>
                    </a>
                </th>
                <th width="150" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($provinsi as $p): ?>
            <tr>
                <td><?= $p['kode']; ?></td>
                <td><?= $p['nama']; ?></td>
                <td class="text-center">
                    <a href="<?= base_url('provinsi/edit/'.$p['kode']); ?>" class="btn btn-warning btn-sm" title="Edit">
                        <i class="fas fa-pencil"></i>
                    </a>
                    <a href="<?= base_url('provinsi/delete/'.$p['kode']); ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin hapus data ini?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-3">
    <?= $links; ?>
</div>