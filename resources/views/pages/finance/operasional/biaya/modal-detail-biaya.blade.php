<!-- ================= MODAL DETAIL PENGAJUAN BIAYA ================= -->
<div class="modal fade" id="modalDetailPengajuan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Pengajuan Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- ================= HEADER INFO ================= -->
                <div class="row mb-4 align-items-start">

                    <div class="col-md-8">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Metode Pembayaran</label>
                                <input type="text" class="form-control" id="d_metode" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Penerima</label>
                                <input type="text" class="form-control" id="d_kontak" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pengajuan</label>
                                <input type="text" class="form-control" id="d_tanggal" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nomor Pengajuan</label>
                                <input type="text" class="form-control" id="d_nomor" readonly>
                            </div>

                        </div>
                    </div>

                    <!-- TOTAL -->
                    <div class="col-md-4 text-end">
                        <div class="border rounded p-3">
                            <div class="mb-2 text-start">
                                <span id="d_urgent" class="badge bg-danger d-none">URGENT</span>
                            </div>
                            <h3 class="fw-bold mt-3">
                                Total : <br>
                                <span id="d_grand_total">Rp 0</span>
                            </h3>
                        </div>
                    </div>

                </div>

                <!-- ================= ITEMS ================= -->
                <div class="border rounded p-3 mb-4">

                    <div class="row fw-semibold text-muted mb-2">
                        <div class="col-md-3">Deskripsi</div>
                        <div class="col-md-1">Qty</div>
                        <div class="col-md-2">Harga</div>
                        <div class="col-md-2">Diskon</div>
                        <div class="col-md-2">Pajak</div>
                        <div class="col-md-2 text-end">Jumlah</div>
                    </div>

                    <div id="detailItemContainer">
                        <div class="text-center text-muted">
                            Memuat data...
                        </div>
                    </div>

                </div>

                <!-- ================= FOOTER INFO ================= -->
                <div class="row align-items-end">

                    <div class="col-md-6" id="lampiranWrapper" style="display:none;">
                        <label class="form-label">Lampiran</label><br>
                        <a href="#" target="_blank" id="d_lampiran" class="btn btn-outline-primary btn-sm">
                            Lihat Lampiran
                        </a>
                    </div>

                    <div class="col-md-6 ms-auto">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">Rp <span id="d_subtotal">0</span></td>
                            </tr>
                            <tr>
                                <td>Diskon</td>
                                <td class="text-end">Rp <span id="d_diskon">0</span></td>
                            </tr>
                            <tr>
                                <td>PPN</td>
                                <td class="text-end">Rp <span id="d_ppn">0</span></td>
                            </tr>
                            <tr class="fw-bold fs-5">
                                <td>Total</td>
                                <td class="text-end">Rp <span id="d_total">0</span></td>
                            </tr>
                        </table>
                    </div>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button"
                    class="btn btn-warning"
                    id="btnEditPengajuan"
                    data-nomor="">
                    Edit
                </button>
            </div>

        </div>

    </div>
</div>
</div>