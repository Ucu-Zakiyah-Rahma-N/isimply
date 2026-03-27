<style>
    .custom-modal {
        border-radius: 18px;
        padding: 10px 15px;
        border: none;
    }

    .modal-content {
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
    }

    .form-label {
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 6px;
        color: #444;
    }

    .custom-input {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 10px 12px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .custom-input:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.15);
    }

    .btn-primary {
        background-color: #4a90e2;
        border: none;
    }

    .btn-primary:hover {
        background-color: #3b7bd1;
    }

    .btn-light {
        background-color: #f8f9fa;
    }

    .input-group .btn {
        border-radius: 10px;
    }
</style>
<!-- MODAL -->
<div class="modal fade" id="modalPihakKetiga" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content custom-modal">

            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">Tambah Pihak Ketiga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <form id="formPihakKetiga">

                    <!-- Nama Perusahaan -->
                    <div class="mb-3">
                        <label class="form-label">Nama Perusahaan</label>
                        <input type="text" class="form-control custom-input"
                            placeholder="Contoh: PT Maju Jaya Abadi">
                    </div>

                    <!-- Attn -->
                    <div class="mb-3">
                        <label class="form-label">Attn (PIC)</label>
                        <input type="text" class="form-control custom-input"
                            placeholder="Nama kontak / penanggung jawab">
                    </div>

                    <!-- Alamat -->
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control custom-input"
                            placeholder="Masukkan alamat lengkap"
                            rows="2"></textarea>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>

                        <div id="emailContainer">
                            <div class="input-group mb-2">
                                <input type="email" class="form-control custom-input"
                                    placeholder="contoh@email.com">
                                <button type="button"
                                    class="btn btn-light border btn-add-email">
                                    +
                                </button>
                            </div>
                        </div>

                        <small class="text-muted">Bisa menambahkan lebih dari satu email</small>
                    </div>

                    <!-- No Tlp -->
                    <div class="mb-4">
                        <label class="form-label">No Telepon</label>
                        <input type="text" class="form-control custom-input"
                            placeholder="Contoh: 08123456789">
                    </div>

                    <!-- Action -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button"
                            class="btn btn-light border rounded-pill px-4"
                            data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit"
                            class="btn btn-primary rounded-pill px-4">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</div>

<script>
    $(document).on('click', '.btn-add-email', function() {
        let html = `
    <div class="input-group mb-2">
        <input type="email" class="form-control custom-input"
            placeholder="email tambahan">
        <button type="button"
            class="btn btn-light border btn-remove-email">
            x
        </button>
    </div>
    `;
        $('#emailContainer').append(html);
    });

    // hapus email
    $(document).on('click', '.btn-remove-email', function() {
        $(this).closest('.input-group').remove();
    });
</script>