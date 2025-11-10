<div class="card shadow-sm p-4">
    <h4 class="mb-4"><?= $title; ?></h4>
    <form method="post">
        
        <div class="mb-3">
            <label class="form-label">Pilih Provinsi</label>
            <select name="kode_provinsi" id="kode_provinsi" class="form-select" required>
                <option value="">-- Pilih Provinsi --</option>
                <?php foreach($provinsi as $p): ?>
                <option value="<?= $p['kode']; ?>"
                    <?= isset($selected_provinsi) && $selected_provinsi == $p['kode'] ? 'selected' : ''; ?>>
                    <?= $p['nama']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Pilih Kabupaten</label>
            <select name="kode_kabupaten" id="kode_kabupaten" class="form-select" required
                <?php 
                if ($mode == 'add' || !isset($selected_provinsi)) {
                    echo 'disabled';
                }
                ?>>
                <option value="">-- Pilih Kabupaten --</option>
                <?php foreach($kabupaten as $k): ?>
                    <?php 
                    if (isset($selected_provinsi) && $selected_provinsi == $k['kode_provinsi']): 
                    ?>
                    <option value="<?= $k['kode']; ?>"
                        <?= isset($selected_kabupaten) && $selected_kabupaten == $k['kode'] ? 'selected' : ''; ?>>
                        <?= $k['nama']; ?>
                    </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Kecamatan</label>
            <input type="text" name="nama" class="form-control" required
                value="<?= isset($item['nama']) ? $item['nama'] : ''; ?>"
                placeholder="Masukkan nama kecamatan">
        </div>

        <div class="d-flex justify-content-start mt-4">
            <button type="submit" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="<?= base_url('kecamatan'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#kode_provinsi').on('change', function() {
        var kode_provinsi = $(this).val();
        var kabSelect = $('#kode_kabupaten');

        kabSelect.empty().append('<option value="">-- Memuat... --</option>').prop('disabled', true);

        if (kode_provinsi) {
            $.ajax({
                url: "<?= base_url('kelurahan/get_kabupaten_by_provinsi'); ?>", 
                type: "POST",
                data: { kode_provinsi: kode_provinsi },
                dataType: "json",
                success: function(data) {
                    if(data && data.length > 0) {
                        kabSelect.empty().append('<option value="">-- Pilih Kabupaten --</option>');
                        kabSelect.prop('disabled', false);
                        $.each(data, function(key, value) {
                            kabSelect.append('<option value="' + value.kode + '">' + value.nama + '</option>');
                        });
                    } else {
                        kabSelect.empty().append('<option value="">-- Tidak ada kabupaten --</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    kabSelect.empty().append('<option value="">-- Gagal memuat --</option>').prop('disabled', true);
                }
            });
        } else {
            kabSelect.empty().append('<option value="">-- Pilih Kabupaten --</option>').prop('disabled', true);
        }
    });
});
</script>