<div class="card shadow-sm p-4">
    <h4 class="mb-4"><?= $title; ?></h4>

    <form action="" method="post">
        <?php if ($mode == 'edit' && isset($item)): ?>
            <input type="hidden" name="kode" value="<?= htmlspecialchars($item['kode']); ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="kode_provinsi" class="form-label">Pilih Provinsi</label>
            <select class="form-select" id="kode_provinsi" name="kode_provinsi" required>
                <option value="">-- Pilih Provinsi --</option>
                <?php foreach ($provinsi as $p): ?>
                    <option value="<?= htmlspecialchars($p['kode']); ?>"
                        <?= (isset($selected_provinsi) && $selected_provinsi == $p['kode']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($p['nama']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Kabupaten</label>
            <input type="text" class="form-control" id="nama" name="nama"
                value="<?= isset($item['nama']) ? htmlspecialchars($item['nama']) : ''; ?>"
                placeholder="Masukkan nama kabupaten" required>
        </div>

        <div class="d-flex justify-content-start mt-4">
            <button type="submit" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="<?= base_url('kabupaten'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
});
</script>