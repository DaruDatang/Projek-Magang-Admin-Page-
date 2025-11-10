<div class="card shadow-sm p-4">
    <h4 class="mb-4"><?= $mode == 'add' ? 'Tambah Kelurahan' : 'Edit Kelurahan'; ?></h4>
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
            <label class="form-label">Pilih Kecamatan</label>
            <select name="kode_kecamatan" id="kode_kecamatan" class="form-select" required
                <?php 
                if ($mode == 'add' || !isset($selected_kabupaten)) {
                    echo 'disabled';
                }
                ?>>
                <option value="">-- Pilih Kecamatan --</option>
                <?php foreach($kecamatan as $kc): ?>
                    <?php 
                    if (isset($selected_kabupaten) && $selected_kabupaten == $kc['kode_kabupaten']): 
                    ?>
                    <option value="<?= $kc['kode']; ?>"
                        <?= isset($selected_kecamatan) && $selected_kecamatan == $kc['kode'] ? 'selected' : ''; ?>>
                        <?= $kc['nama']; ?>
                    </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Kelurahan</label>
            <input type="text" name="nama" class="form-control" required
                value="<?= isset($item['nama']) ? $item['nama'] : ''; ?>"
                placeholder="Masukkan nama kelurahan">
        </div>

        <div class="d-flex justify-content-start mt-4">
            <button type="submit" class="btn btn-success me-2"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="<?= base_url('kelurahan'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {

    $('#kode_provinsi').on('change', function() {
        var kode_provinsi = $(this).val();
        var kabSelect = $('#kode_kabupaten');
        var kecSelect = $('#kode_kecamatan');

        kabSelect.empty().append('<option value="">-- Memuat... --</option>').prop('disabled', true);
        kecSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);

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

    $('#kode_kabupaten').on('change', function() {
        var kode_kabupaten = $(this).val();
        var kecSelect = $('#kode_kecamatan');

        kecSelect.empty().append('<option value="">-- Memuat... --</option>').prop('disabled', true);

        if (kode_kabupaten) {
            $.ajax({
                url: "<?= base_url('kelurahan/get_kecamatan_by_kabupaten'); ?>", 
                type: "POST",
                data: { kode_kabupaten: kode_kabupaten },
                dataType: "json",
                success: function(data) {
                    if(data && data.length > 0) {
                        kecSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>');
                        kecSelect.prop('disabled', false);
                        $.each(data, function(key, value) {
                            kecSelect.append('<option value="' + value.kode + '">' + value.nama + '</option>');
                        });
                    } else {
                        kecSelect.empty().append('<option value="">-- Tidak ada kecamatan --</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    kecSelect.empty().append('<option value="">-- Gagal memuat --</option>').prop('disabled', true);
                }
            });
        } else {
            kecSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
        }
    });
});
</script>