<!-- MODAL TAMBAH PENERIMA -->
<div class="modal fade"
    id="modalTambahKontak"
    tabindex="-1"
    data-bs-backdrop="static"
    data-bs-keyboard="false">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Penerima</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formTambahKontak">
                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Nama -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Nama Supplier / PIC <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="nama"
                                class="form-control"
                                placeholder="Contoh: PT Sumber Makmur / Andi"
                                required>
                        </div>

                        <!-- Tipe Kontak -->
                        <div class="col-md-6">
                            <label class="form-label">Tipe Kontak</label>
                            <select name="tipe_kontak" class="form-select">
                                <option value="">Pilih Tipe Kontak</option>
                                <option value="supplier">Supplier</option>
                                <option value="karyawan">Karyawan</option>
                                <option value="vendor">Vendor</option>
                            </select>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email"
                                name="email"
                                class="form-control"
                                placeholder="email@contoh.com">
                        </div>

                        <!-- No HP -->
                        <div class="col-md-6">
                            <label class="form-label">No HP</label>
                            <input type="text"
                                name="no_hp"
                                class="form-control"
                                placeholder="08xxxxxxxxxx">
                        </div>

                        <!-- No Rekening -->
                        <div class="col-md-6">
                            <label class="form-label">No Rekening</label>
                            <input type="text"
                                name="no_rekening"
                                class="form-control"
                                placeholder="1234567890">
                        </div>

                        <!-- Alamat -->
                        <div class="col-md-6">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat"
                                class="form-control"
                                rows="2"
                                placeholder="Alamat singkat penerima"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn btn-success">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    document.getElementById("formTambahKontak")
        .addEventListener("submit", function(e) {

            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            Swal.fire({
                title: 'Menyimpan...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch("/finance/kontak/store", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw data;
                    return data;
                })
                .then(data => {

                    if (data.success) {

                        // ✅ reload dropdown penerima + auto select
                        loadKontak(data.id);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Penerima berhasil ditambahkan',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // tutup modal
                        bootstrap.Modal
                            .getInstance(document.getElementById('modalTambahKontak'))
                            .hide();

                        form.reset();
                    }
                })
                .catch(err => {

                    let message = 'Terjadi kesalahan';

                    if (err.errors) {
                        message = Object.values(err.errors).join('<br>');
                    } else if (err.message) {
                        message = err.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: message
                    });
                });

        });
</script>