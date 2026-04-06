<div class="modal fade" id="modalExportPdf" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="{{ route('finance.exportPdf') }}">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Export PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Export PDF
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>