<div class="modal fade" id="modalTolak" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" id="modalFormTolak">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan</label>
                        <textarea
                            name="note"
                            class="form-control"
                            rows="4"
                            placeholder="Masukkan alasan penolakan..."
                            required></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-danger">
                        Ya, Tolak
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>