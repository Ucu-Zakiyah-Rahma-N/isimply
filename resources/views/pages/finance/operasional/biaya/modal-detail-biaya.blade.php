<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border-radius: 14px !important;
        border: 1px solid #e5e7eb !important;
        background: #fff;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        padding: 0 14px;
    }

    /* Perfect vertical alignment */
    .select2-container--default .select2-selection__rendered {
        padding-left: 0 !important;
        line-height: 42px !important;
    }

    .select2-container--default .select2-selection__arrow {
        height: 42px !important;
        right: 12px;
    }

    /* Focus */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
    }

    /* Dropdown */
    .select2-dropdown {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    /* ===== GROUP MODE ===== */

    .select2-group {
        display: flex;
        align-items: stretch;
    }

    .select2-group .select2-selection--single {
        border-radius: 14px 0 0 14px !important;
        border-right: none !important;
    }

    .select2-group .btn-saas-add {
        height: 42px;
        width: 42px;
        border-radius: 0 14px 14px 0;
        border: 1px solid #e5e7eb;
        border-left: none;
        background: #f1f3f5;
    }

    /* ============================= */
    /* ====== FORM STYLE =========== */
    /* ============================= */

    .label-saas {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 6px;
    }

    .input-saas {
        height: 42px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        padding: 0 14px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .input-saas:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    /* ============================= */
    /* ====== CARD & MODAL ========= */
    /* ============================= */

    .card {
        background: #ffffff;
    }

    .total-card {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border: 1px solid #f1f3f5;
    }

    .total-amount {
        font-size: 1.8rem;
        letter-spacing: 0.5px;
        color: #111827;
    }

    .urgent-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #dc3545;
    }

    .modal-content {
        border-radius: 24px;
        border: none;
    }
</style>

<!-- MODAL PENGAJUAN BIAYA -->
<div class="modal fade" id="modalDetailPengajuan">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Pengajuan Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- ================= HEADER ================= -->
                <div class="row g-4 mb-4 align-items-stretch">

                    <!-- LEFT SIDE -->
                    <div class="col-lg-9">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">

                            <div class="row g-4">

                                <!-- Jenis Pengajuan -->
                                <div class="col-md-4">
                                    <label class="form-label label-saas">Jenis Pengajuan</label>
                                    <select class="form-select input-saas" name="jenis_pengajuan">
                                        <option value="biaya">Biaya</option>
                                        <option value="pengeluaran">Pengeluaran</option>
                                    </select>
                                </div>

                                <!-- Penerima -->
                                <div class="col-md-4">
                                    <label class="form-label label-saas">Penerima</label>

                                    <div class="select2-group">
                                        <select id="kontakSelect"
                                            name="kontak_id">
                                        </select>

                                        <button type="button"
                                            id="btnOpenKontak"
                                            class="btn btn-saas-add">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Metode Pembayaran -->
                                <div class="col-md-4">
                                    <label class="form-label label-saas">Metode Pembayaran</label>
                                    <select class="form-select input-saas" name="metode_pembayaran">
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label label-saas">Tanggal Pengajuan</label>
                                    <input type="date"
                                        class="form-control input-saas"
                                        name="tanggal_pengajuan"
                                        value="{{ date('Y-m-d') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label label-saas">Project</label>
                                    <select class="input-saas" id="projectSelect" name="project_id"></select>
                                    <input type="text" name="jenis_project" id="jenis_project">
                                </div>

                            </div>

                        </div>
                    </div>

                    <!-- RIGHT SIDE TOTAL -->
                    <div class="col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 p-4 total-card h-100 d-flex flex-column justify-content-between">

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="urgent-label">Urgent</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_urgent">
                                </div>
                            </div>

                            <div class="mt-4">
                                <small class="text-muted">Grand Total</small>
                                <h2 class="fw-bold total-amount mb-0">
                                    <span id="grandTotal">0</span>
                                </h2>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- ================= ITEM ================= -->
                <div class="border rounded p-3 mb-4">

                    <div class="row fw-semibold text-muted mb-2">
                        <div class="col-md-3">Deskripsi</div>
                        <div class="col-md-1">Qty</div>
                        <div class="col-md-2">Harga</div>
                        <div class="col-md-2">Diskon</div>
                        <div class="col-md-2">Pajak</div>
                        <div class="col-md-2 text-end">Jumlah</div>
                    </div>

                    <div id="itemContainer">
                        <div class="row g-2 align-items-center mb-2 item-row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="deskripsi[]">
                            </div>
                            <div class="col-md-1">
                                <input type="number" class="form-control qty" name="qty[]" value="1" min="1">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control harga" name="harga[]" min="0">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control diskon" name="diskon[]" value="0">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select pajak" name="pajak_id[]">
                                    <option value="0">Non Pajak</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <input type="text" class="form-control jumlah text-end" readonly>
                                <button type="button" class="btn btn-outline-danger btnRemove">−</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnTambahItem">
                        + Tambah Item
                    </button>
                </div>

                <!-- ================= FOOTER ================= -->
                <div class="row mt-4">

                    <div class="col-md-6">
                        <label class="form-label">Lampiran</label>
                        <input type="file" class="form-control">
                    </div>

                    <div class="col-md-6">

                        <div class="d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span>Rp <span id="subtotal">0</span></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Diskon</span>
                            <span class="text-danger">Rp <span id="totalDiskon">0</span></span>
                        </div>

                        <div id="pajakSummary"></div>

                        <hr>

                        <div class="d-flex justify-content-between fw-bold fs-4">
                            <span>Total</span>
                            <span>Rp <span id="summaryTotal">0</span></span>
                        </div>

                        <div class="text-end mt-3">
                            <button class="btn btn-success px-5">
                                Buat
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>