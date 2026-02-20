<div class="modal fade" id="modalEditKontak" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <form id="formEditKontak" method="POST">
                @csrf
                @method('PUT')

                <!-- hidden id -->
                <input type="hidden" name="id" id="edit_id">

                <!-- HEADER -->
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        Edit Kontak
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-2">

                    <div class="row g-3">

                        <!-- INFORMASI UMUM -->
                        <div class="col-md-7">
                            <div class="p-3 rounded-3 bg-white border">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">
                                        Nama
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" name="nama"
                                            class="form-control"
                                            id="edit_nama"
                                            required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">
                                        Tipe Kontak
                                    </label>
                                    <select name="tipe_kontak"
                                        class="form-select form-select-sm"
                                        id="edit_tipe"
                                        required>
                                        <option value="">-- Pilih Tipe --</option>
                                        <option value="customer">Customer</option>
                                        <option value="supplier">Supplier</option>
                                        <option value="karyawan">Karyawan</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">
                                            Email
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">
                                                <i class="bi bi-envelope"></i>
                                            </span>
                                            <input type="email" name="email"
                                                class="form-control"
                                                id="edit_email">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">
                                            No HP
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">
                                                <i class="bi bi-telephone"></i>
                                            </span>
                                            <input type="text" name="no_hp"
                                                class="form-control"
                                                id="edit_no_hp">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label small fw-semibold text-muted">
                                        Alamat
                                    </label>
                                    <textarea name="alamat"
                                        class="form-control form-control-sm"
                                        rows="2"
                                        id="edit_alamat"></textarea>
                                </div>

                            </div>
                        </div>

                        <!-- INFORMASI BANK -->
                        <div class="col-md-5">
                            <div class="p-3 rounded-3 border">

                                <h6 class="fw-bold small mb-3">
                                    Informasi Bank
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">
                                        Nama Bank
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="bi bi-bank"></i>
                                        </span>
                                        <input type="text" name="nama_bank"
                                            class="form-control"
                                            id="edit_nama_bank">
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-semibold text-muted">
                                        No Rekening
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="bi bi-credit-card"></i>
                                        </span>
                                        <input type="text" name="no_rek"
                                            class="form-control"
                                            id="edit_no_rek">
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm"
                        data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        Update
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>