<div class="modal fade" id="modalTambahKontak">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <form id="formTambahKontak">
                @csrf

                <!-- HEADER -->
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        Tambah Kontak
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
                                            placeholder="Nama lengkap"
                                            required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">
                                        Tipe Kontak
                                    </label>
                                    <select name="tipe_kontak"
                                        class="form-select form-select-sm"
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
                                                placeholder="email@example.com">
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
                                                placeholder="08xxxxxxxxxx">
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
                                        placeholder="Alamat lengkap"></textarea>
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
                                            placeholder="Contoh: BCA">
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
                                        <input type="text" name="no_rekening"
                                            class="form-control"
                                            placeholder="Nomor rekening">
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer border-0 pt-0">
                    <button type="button"
                        class="btn btn-light btn-sm"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                        class="btn btn-primary btn-sm px-4"
                        id="btnSimpanKontak">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>