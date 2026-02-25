<div class="modal fade" id="modalJadwalkan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Jadwalkan Pembayaran</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="formJadwalkan">
                    @csrf
                    <!-- Informasi Pengajuan -->
                    <div class="border rounded p-3 mb-4 bg-light">
                        <div class="row small">
                            <div class="col-md-2"><strong>No Pengajuan</strong></div>
                            <div class="col-md-2"><strong>Tgl Pengajuan</strong></div>
                            <div class="col-md-3"><strong>Deskripsi</strong></div>
                            <div class="col-md-2"><strong>Penerima</strong></div>
                            <div class="col-md-3"><strong>Total</strong></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-2" id="modalTextPengajuan">-</div>
                            <input type="hidden" name="nomor_pengajuan" id="modalNoPengajuan">
                            <div class="col-md-2" id="modalTglPengajuan">-</div>
                            <div class="col-md-3" id="modalDeskripsi">-</div>
                            <div class="col-md-2" id="modalPenerima">-</div>
                            <div class="col-md-3 fw-bold text-primary" id="modalTotal">-</div>
                        </div>
                    </div>

                    <!-- Form Jadwal -->
                    <div class="row g-3 align-items-end">

                        <!-- COA -->
                        <div class="col-md-3">
                            <label class="form-label">COA</label>
                            <select name="coa_lawan_id" id="selectAkunCoa" class="form-select">
                                <option value="">Pilih Coa</option>
                            </select>
                        </div>

                        <!-- Tanggal Pembayaran -->
                        <div class="col-md-3">
                            <label class="form-label">Tgl Pembayaran</label>
                            <input type="date" name="tgl_pembayaran" class="form-control">
                        </div>

                        <!-- Sumber Bank -->
                        <div class="col-md-4">
                            <label class="form-label">Bayar dari (Sumber Bank)</label>
                            <select name="coa_bank_id" id="selectCoa" class="form-select">
                                <option value="">Pilih Bank</option>
                            </select>
                        </div>

                        <!-- Akomodasi -->
                        <div class="col-md-2">
                            <label class="form-label d-block">Akomodasi</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" name="is_akomodasi"
                                    type="checkbox" value="1">
                            </div>
                        </div>

                    </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Batal
                </button>

                <button type="submit"
                    class="btn btn-success">
                    Jadwalkan
                </button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        $('#selectCoa').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalJadwalkan'),
            width: '100%',
            placeholder: 'Pilih Bank',
            allowClear: true,
            ajax: {
                url: "{{ route('finance.getCoaKasBank') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        $('#selectAkunCoa').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalJadwalkan'),
            width: '100%',
            placeholder: 'Pilih Coa',
            allowClear: true,
            ajax: {
                url: "{{ route('finance.getAkunCoa') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const form = document.getElementById('formJadwalkan');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            fetch("{{ route('finance.scheduling.store') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    if (data.status === 'success') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            location.reload();
                        });

                    } else {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message
                        });

                    }

                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem'
                    });
                });

        });

    });
</script>