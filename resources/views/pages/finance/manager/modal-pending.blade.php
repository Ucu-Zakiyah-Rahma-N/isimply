<div class="modal fade" id="modalPending" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" id="modalFormPending">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Pending Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Alasan Pending</label>
                        <textarea
                            name="note"
                            class="form-control"
                            rows="4"
                            placeholder="Masukkan alasan pending..."
                            required></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-warning">
                        Ya, Pending
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>