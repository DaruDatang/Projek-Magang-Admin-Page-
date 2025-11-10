$(function() {
    /**
     * @param {string} url
     * @param {object} params
     * @param {function} cb
     */
    function ajaxGet(url, params, cb) {
        $.get(url, params)
            .done(function(res) {
                cb(res);
            })
            .fail(function(xhr, status, err) {
                console.error('AJAX error:', url, status, err);
            });
    }

    function updatePreviewCode(parent_code, level) {
        if (!parent_code) {
            $('#preview_kode, #preview_kode_cascade').val('');
            return;
        }
        ajaxGet(base_url + 'api/preview_generate_code', { parent_code, level }, function(res) {
            if (res.kode) {
                $('#preview_kode').val(res.kode);
                $('#preview_kode_cascade').val(res.kode);
            }
        });
    }

    // ==============================
    // PROVINSI → KABUPATEN
    // ==============================
    $('#kode_provinsi, #kode_provinsi_kec, #kode_provinsi_kel, #cascade_provinsi').on('change', function() {
        const kode = $(this).val();
        const isCascade = $(this).attr('id').includes('cascade');
        const targetKab = isCascade ? '#cascade_kabupaten' : $(this).attr('id').includes('_kel') ? '#kode_kabupaten_kel' : '#kode_kabupaten_kec';

        if (!kode) {
            $(targetKab).html('<option value="">-- Pilih Kabupaten --</option>');
            return;
        }

        ajaxGet(base_url + 'api/kabupaten_by_provinsi', { kode_provinsi: kode }, function(list) {
            let html = '<option value="">-- Pilih Kabupaten --</option>';
            list.forEach(function(it) {
                html += `<option value="${it.kode}">${it.nama}</option>`;
            });
            $(targetKab).html(html);
        });

        if (!isCascade)
            updatePreviewCode(kode, 'kabupaten');
    });

    // ==============================
    // KABUPATEN → KECAMATAN
    // ==============================
    $('#kode_kabupaten_kec, #kode_kabupaten_kel, #cascade_kabupaten').on('change', function() {
        const kode = $(this).val();
        const isCascade = $(this).attr('id').includes('cascade');
        const targetKec = isCascade ? '#cascade_kecamatan' : '#kode_kecamatan_kel';

        if (!kode) {
            $(targetKec).html('<option value="">-- Pilih Kecamatan --</option>');
            return;
        }

        ajaxGet(base_url + 'api/kecamatan_by_kabupaten', { kode_kabupaten: kode }, function(list) {
            let html = '<option value="">-- Pilih Kecamatan --</option>';
            list.forEach(function(it) {
                html += `<option value="${it.kode}">${it.nama}</option>`;
            });
            $(targetKec).html(html);
        });

        if (!isCascade)
            updatePreviewCode(kode, 'kecamatan');
    });

    // ==============================
    // KECAMATAN → KELURAHAN
    // ==============================
    $('#kode_kecamatan_kel, #cascade_kecamatan').on('change', function() {
        const kode = $(this).val();
        const isCascade = $(this).attr('id').includes('cascade');

        if (!kode) {
            if (!isCascade) $('#preview_kode').val('');
            return;
        }

        if (!isCascade)
            updatePreviewCode(kode, 'kelurahan');
        else
            ajaxGet(base_url + 'api/kelurahan_by_kecamatan', { kode_kecamatan: kode }, function(list) {
                let html = '<option value="">-- Pilih Kelurahan --</option>';
                list.forEach(function(it) {
                    html += `<option value="${it.kode}">${it.nama}</option>`;
                });
                $('#cascade_kelurahan').html(html);
            });
    });

    $('#level_select').on('change', function() {
        const val = $(this).val();
        if (val === 'provinsi') {
            updatePreviewCode(null, 'provinsi');
        }
    });
});