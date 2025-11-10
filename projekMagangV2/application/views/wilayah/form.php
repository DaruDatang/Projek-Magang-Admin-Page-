<div class="card shadow-sm p-4">
    <h4 class="mb-4"><?= $title; ?></h4>

    <form action="<?= base_url('wilayah/form'); ?>" method="post">

        <div class="mb-3">
            <label for="level" class="form-label">Pilih Level Wilayah</label>
            <select class="form-select" id="level" name="level" required>
                <option value="">-- Pilih Level --</option>
                <option value="provinsi">Provinsi</option>
                <option value="kabupaten">Kabupaten</option>
                <option value="kecamatan">Kecamatan</option>
                <option value="kelurahan">Kelurahan</option>
            </select>
        </div>

        <div id="dynamic_fields">

            <div class="mb-3 form-group" id="group_nama_provinsi" style="display:none;">
                <label for="nama_provinsi" class="form-label">Nama Provinsi</label>
                <input type="text" class="form-control" id="nama_provinsi" name="nama_provinsi" placeholder="Masukkan nama provinsi">
            </div>

            <div class="mb-3 form-group" id="group_provinsi" style="display:none;">
                <label for="kode_provinsi" class="form-label">Pilih Provinsi</label>
                <select class="form-select" id="kode_provinsi" name="kode_provinsi">
                    <option value="">-- Pilih Provinsi --</option>
                    <?php foreach($wilayah['provinsi'] as $p): ?>
                        <option value="<?= $p['kode']; ?>"><?= $p['nama']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 form-group" id="group_nama_kabupaten" style="display:none;">
                <label for="nama_kabupaten" class="form-label">Nama Kabupaten</label>
                <input type="text" class="form-control" id="nama_kabupaten" name="nama_kabupaten" placeholder="Masukkan nama kabupaten">
            </div>

            <div class="mb-3 form-group" id="group_kabupaten" style="display:none;">
                <label for="kode_kabupaten" class="form-label">Pilih Kabupaten</label>
                <select class="form-select" id="kode_kabupaten" name="kode_kabupaten_kec" disabled>
                    <option value="">-- Pilih Kabupaten --</option>
                </select>
            </div>

            <div class="mb-3 form-group" id="group_nama_kecamatan" style="display:none;">
                <label for="nama_kecamatan" class="form-label">Nama Kecamatan</label>
                <input type="text" class="form-control" id="nama_kecamatan" name="nama_kecamatan" placeholder="Masukkan nama kecamatan">
            </div>

            <div class="mb-3 form-group" id="group_kecamatan" style="display:none;">
                <label for="kode_kecamatan" class="form-label">Pilih Kecamatan</label>
                <select class="form-select" id="kode_kecamatan" name="kode_kecamatan_kel" disabled>
                    <option value="">-- Pilih Kecamatan --</option>
                </select>
            </div>

            <div class="mb-3 form-group" id="group_nama_kelurahan" style="display:none;">
                <label for="nama_kelurahan" class="form-label">Nama Kelurahan</label>
                <input type="text" class="form-control" id="nama_kelurahan" name="nama_kelurahan" placeholder="Masukkan nama kelurahan">
            </div>

        </div>

        <div class="d-flex justify-content-start mt-4">
            <button type="submit" class="btn btn-success me-2" id="btn-simpan" disabled><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="<?= base_url('wilayah'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    
    var levelSelect = $('#level');
    var dynamicFields = $('#dynamic_fields');
    var btnSimpan = $('#btn-simpan');
    
    var groupProv = $('#group_provinsi');
    var groupNamaProv = $('#group_nama_provinsi');
    var groupKab = $('#group_kabupaten');
    var groupNamaKab = $('#group_nama_kabupaten');
    var groupKec = $('#group_kecamatan');
    var groupNamaKec = $('#group_nama_kecamatan');
    var groupNamaKel = $('#group_nama_kelurahan');
    
    var selectProv = $('#kode_provinsi');
    var selectKab = $('#kode_kabupaten');
    var selectKec = $('#kode_kecamatan');

    function resetForm() {
        dynamicFields.find('.form-group').hide();
        dynamicFields.find('input, select').prop('disabled', true).prop('required', false);
        selectKab.empty().append('<option value="">-- Pilih Kabupaten --</option>');
        selectKec.empty().append('<option value="">-- Pilih Kecamatan --</option>');
        btnSimpan.prop('disabled', true);
    }

    levelSelect.on('change', function() {
        var level = $(this).val();
        resetForm();

        if (!level) return;

        if (level === 'provinsi') {
            groupNamaProv.show();
            groupNamaProv.find('input').prop('disabled', false).prop('required', true);
            btnSimpan.prop('disabled', false);
        } else if (level === 'kabupaten') {
            groupProv.show();
            groupProv.find('select').prop('disabled', false).prop('required', true);
            groupNamaKab.show();
            groupNamaKab.find('input').prop('disabled', false).prop('required', true);
            btnSimpan.prop('disabled', false); 
        } else if (level === 'kecamatan') {
            groupProv.show();
            groupProv.find('select').prop('disabled', false).prop('required', true);
            groupKab.show();
            groupKab.find('select').prop('disabled', true).prop('required', true);
            groupNamaKec.show();
            groupNamaKec.find('input').prop('disabled', false).prop('required', true);
            btnSimpan.prop('disabled', false);
        } else if (level === 'kelurahan') {
            groupProv.show();
            groupProv.find('select').prop('disabled', false).prop('required', true);
            groupKab.show();
            groupKab.find('select').prop('disabled', true).prop('required', true);
            groupKec.show();
            groupKec.find('select').prop('disabled', true).prop('required', true);
            groupNamaKel.show();
            groupNamaKel.find('input').prop('disabled', false).prop('required', true);
            btnSimpan.prop('disabled', false);
        }
    });

    selectProv.on('change', function() {
        var kode_provinsi = $(this).val();
        
        selectKab.empty().append('<option value="">-- Memuat... --</option>').prop('disabled', true);
        selectKec.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);

        if (kode_provinsi) {
            $.ajax({
                url: "<?= base_url('wilayah/api_kabupaten_by_provinsi'); ?>", 
                type: "POST",
                data: { kode_provinsi: kode_provinsi },
                dataType: "json",
                success: function(data) {
                    if(data && data.length > 0) {
                        selectKab.empty().append('<option value="">-- Pilih Kabupaten --</option>');
                        selectKab.prop('disabled', false);
                        $.each(data, function(key, value) {
                            selectKab.append('<option value="' + value.kode + '">' + value.nama + '</option>');
                        });
                    } else {
                        selectKab.empty().append('<option value="">-- Tidak ada kabupaten --</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    selectKab.empty().append('<option value="">-- Gagal memuat --</option>').prop('disabled', true);
                }
            });
        } else {
            selectKab.empty().append('<option value="">-- Pilih Kabupaten --</option>').prop('disabled', true);
        }
    });

    selectKab.on('change', function() {
        var kode_kabupaten = $(this).val();

        selectKec.empty().append('<option value="">-- Memuat... --</option>').prop('disabled', true);

        if (kode_kabupaten) {
            $.ajax({
                url: "<?= base_url('wilayah/api_kecamatan_by_kabupaten'); ?>",
                type: "POST",
                data: { kode_kabupaten: kode_kabupaten },
                dataType: "json",
                success: function(data) {
                    if(data && data.length > 0) {
                        selectKec.empty().append('<option value="">-- Pilih Kecamatan --</option>');
                        selectKec.prop('disabled', false);
                        $.each(data, function(key, value) {
                            selectKec.append('<option value="' + value.kode + '">' + value.nama + '</option>');
                        });
                    } else {
                        selectKec.empty().append('<option value="">-- Tidak ada kecamatan --</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    selectKec.empty().append('<option value="">-- Gagal memuat --</option>').prop('disabled', true);
                }
            });
        } else {
            selectKec.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
        }
    });

});
</script>