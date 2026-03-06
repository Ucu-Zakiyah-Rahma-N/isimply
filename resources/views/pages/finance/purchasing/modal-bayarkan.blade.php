<div class="modal fade" id="modalBayarkan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <!-- Header -->
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    Jadwalkan Transaksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formBayarkan">
                @csrf

                <div class="modal-body">

                    <div class="p-4 rounded-3 mb-4" style="background:#f8fafc">

                        <div class="row align-items-center">
                            <div class="col-md-7">

                                <div class="small text-muted mb-1">
                                    Nomor Pengajuan
                                </div>

                                <div class="fw-semibold fs-6 mb-3"
                                    id="modalTextPengajuanBayarkan">
                                    -
                                </div>

                                <div class="small text-muted mb-1">
                                    Tanggal Pengajuan
                                </div>

                                <div class="fw-semibold"
                                    id="modalTglPengajuanBayarkan">
                                    -
                                </div>

                                <input type="hidden"
                                    name="nomor_pengajuan"
                                    id="modalNoPengajuanBayarkan">

                            </div>

                            <!-- Right Amount -->
                            <div class="col-md-5 text-end">

                                <div class="small text-muted">
                                    Total Pengajuan
                                </div>

                                <div class="fw-bold text-success"
                                    style="font-size:28px"
                                    id="modalTotalBayarkan">
                                    Rp 0
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- FORM PAYMENT -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">

                            <h6 class="fw-bold mb-3">
                                Detail Pembayaran
                            </h6>

                            <div class="row g-3">

                                <!-- Tanggal Pembayaran -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        Tanggal Pembayaran
                                    </label>

                                    <input type="date"
                                        name="tgl_pembayaran"
                                        class="form-control">
                                </div>

                            </div>

                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer border-top">

                    <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                        class="btn btn-success px-4">
                        Bayarkan
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>