<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        Data Kecamatan
        <span class="badge bg-secondary rounded-pill"><?= $total_data; ?></span>
    </h4>
    <div class="d-flex align-items-center">

        <?php
            $query_params = $this->input->get();
            unset($query_params['page'], $query_params['sort'], $query_params['order']);
            $query_string = http_build_query($query_params);
            $query_string = $query_string ? '&' . $query_string : '';
            $base_url = base_url(uri_string());
            
            $q_param = $this->input->get('q') ? htmlspecialchars($this->input->get('q')) : '';
            $order_param = $this->input->get('order') ? htmlspecialchars($this->input->get('order')) : 'DESC';
            $prov_param = $this->input->get('kode_provinsi') ? htmlspecialchars($this->input->get('kode_provinsi')) : '';
            $kab_param = $this->input->get('kode_kabupaten') ? htmlspecialchars($this->input->get('kode_kabupaten')) : '';
        ?>

        <form method="get" id="provFilterForm" class="me-2">
            <input type="hidden" name="q" value="<?= $q_param; ?>">
            <input type="hidden" name="order" value="<?= $order_param; ?>">
            <input type="hidden" name="sort" value="<?= $sort; ?>">
            <select name="kode_provinsi" class="form-select form-select-sm" onchange="this.form.submit()" title="Filter berdasarkan provinsi">
                <option value="">-- Semua Provinsi --</option>
                <?php foreach($provinsi_list as $p): ?>
                    <option value="<?= $p['kode']; ?>" <?= ($p['kode'] == $selected_provinsi) ? 'selected' : ''; ?>>
                        <?= $p['nama']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        
        <form method="get" id="kabFilterForm" class="me-2">
            <input type="hidden" name="q" value="<?= $q_param; ?>">
            <input type="hidden" name="order" value="<?= $order_param; ?>">
            <input type="hidden" name="sort" value="<?= $sort; ?>">
            <input type="hidden" name="kode_provinsi" value="<?= $prov_param; ?>">
            <select name="kode_kabupaten" class="form-select form-select-sm" onchange="this.form.submit()" title="Filter berdasarkan kabupaten" <?= (empty($selected_provinsi)) ? 'disabled' : ''; ?>>
                <option value="">-- Semua Kabupaten --</option>
                <?php foreach($kabupaten_list as $k): ?>
                    <option value="<?= $k['kode']; ?>" <?= ($k['kode'] == $selected_kabupaten) ? 'selected' : ''; ?>>
                        <?= $k['nama']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="get" class="me-2">
            <input type="hidden" name="order" value="<?= $order_param; ?>">
            <input type="hidden" name="sort" value="<?= $sort; ?>">
            <input type="hidden" name="kode_provinsi" value="<?= $prov_param; ?>">
            <input type="hidden" name="kode_kabupaten" value="<?= $kab_param; ?>">
            
            <div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control" placeholder="Cari kecamatan"
                    value="<?= htmlspecialchars($q_param); ?>" style="width: 150px;">
                <button class="btn btn-secondary" type="submit" title="Cari">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <a href="<?= base_url('kecamatan/add'); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Data
        </a>
    </div>
</div>

<div class="card shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>
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
                        Nama Kecamatan
                        <?php
                        $col = 'nama';
                        $new_order = ($sort == $col && $order == 'ASC') ? 'DESC' : 'ASC';
                        $icon = ($sort == $col) ? ($order == 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                        ?>
                        <a href="<?= "{$base_url}?sort={$col}&order={$new_order}{$query_string}"; ?>" class="text-white text-decoration-none ms-1">
                            <i class="fas <?= $icon; ?>"></i>
                        </a>
                    </th>
                    <th>
                        Nama Kabupaten
                        <?php
                        $col = 'kabupaten_nama';
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
                        $col = 'provinsi_nama';
                        $new_order = ($sort == $col && $order == 'ASC') ? 'DESC' : 'ASC';
                        $icon = ($sort == $col) ? ($order == 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                        ?>
                        <a href="<?= "{$base_url}?sort={$col}&order={$new_order}{$query_string}"; ?>" class="text-white text-decoration-none ms-1">
                            <i class="fas <?= $icon; ?>"></i>
                        </a>
                    </th>
                    <th style="width:150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kecamatan)): ?>
                    <tr><td colspan="5" class="text-center">Tidak ada data kecamatan</td></tr>
                <?php else: ?>
                    <?php foreach($kecamatan as $k): ?>
                        <tr>
                            <td><?= htmlspecialchars($k['kode']); ?></td>
                            <td><?= htmlspecialchars($k['nama']); ?></td>
                            <td><?= htmlspecialchars($k['kabupaten_nama']); ?></td>
                            <td><?= htmlspecialchars($k['provinsi_nama']); ?></td>
                            <td>
                                <a href="<?= base_url('kecamatan/edit/'.$k['kode']); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-pencil"></i>
                                </a>
                                <a href="<?= base_url('kecamatan/delete/'.$k['kode']); ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-sm btn-danger" title="Hapus">
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
    <?= isset($links) ? $links : ''; ?>
</div>