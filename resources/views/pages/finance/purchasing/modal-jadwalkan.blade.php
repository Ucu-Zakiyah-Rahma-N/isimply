<div class="modal fade" id="modalJadwalkan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Jadwalkan Pembayaran</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

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
                        <div class="col-md-2">PB-001</div>
                        <div class="col-md-2">24/02/2026</div>
                        <div class="col-md-3">Pembelian Laptop</div>
                        <div class="col-md-2">Shopee</div>
                        <div class="col-md-3 fw-bold text-primary">Rp 5.000.000</div>
                    </div>
                </div>

                <!-- Form Jadwal -->
                <div class="row g-3 align-items-end">

                    <!-- COA -->
                    <div class="col-md-3">
                        <label class="form-label">COA</label>
                        <select class="form-select">
                            <option value="">Pilih COA</option>
                            <option>Cash</option>
                            <option>Transfer</option>
                        </select>
                    </div>

                    <!-- Tanggal Pembayaran -->
                    <div class="col-md-3">
                        <label class="form-label">Tgl Pembayaran</label>
                        <input type="date" class="form-control">
                    </div>

                    <!-- Sumber Bank -->
                    <div class="col-md-4">
                        <label class="form-label">Bayar dari (Sumber Bank)</label>
                        <select class="form-select">
                            <option value="">Pilih Bank</option>
                            <option>Saldo Mandiri</option>
                            <option>Saldo BCA</option>
                            <option>Saldo Muamalat</option>
                            <option>Saldo BJB</option>
                            <option>Saldo Petty Cash</option>
                            <option>Saldo MyBank</option>
                        </select>
                    </div>

                    <!-- Akomodasi -->
                    <div class="col-md-2">
                        <label class="form-label d-block">Akomodasi</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                type="checkbox">
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

        </div>
    </div>
</div>