<div class="modal fade" id="modalTambahPajak" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah PPN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <label>Nama Akun</label>
                    <input type="text" id="nama" class="form-control" placeholder="contoh: PPN 11%">
                </div>

                <div class="mb-2">
                    <label>Nilai Pajak (%)</label>
                    <input type="number" id="nilai" class="form-control" placeholder="contoh: 11">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnSimpanPajak">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>