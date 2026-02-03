@extends('app.template')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit PO / SPK</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('po.update', $po->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- No PO & Tanggal --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>No PO <span class="text-danger">*</span></label>
                    <input type="text" name="no_po" class="form-control"
                           value="{{ old('no_po', $po->no_po) }}">
                </div>
                <div class="col-md-6">
                    <label>Tanggal PO <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_po" class="form-control"
                           value="{{ old('tgl_po', $po->tgl_po) }}" required>
                </div>
            </div>

            {{-- File PO --}}
            <div class="mb-3">
                <label>File PO (PDF)</label>
                <input type="file" name="file" accept="application/pdf" class="form-control mt-1">

                @if ($po->file_path)
                    <p>
                        <a href="javascript:void(0);" 
                           onclick="openPDFModal('{{ route('files.view', $po->file_path) }}')">
                            {{ basename($po->file_path) }}
                        </a>
                    </p>
                    <!--<button type="button" class="btn btn-sm btn-danger mt-2"-->
                    <!--    onclick="openPDFModal('{{ route('files.view', $po->file_path) }}')">-->
                    <!--    {{ basename($po->file_path) }}-->
                    <!--</button>-->
                @else
                    <span class="text-danger">Belum ada file</span>
                @endif
            </div>

            {{-- Customer & SPH --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Nama Perusahaan <span class="text-danger">*</span></label>
                    <select id="customer-select" name="customer_id" class="form-select">
                        <option value="">-- Pilih Perusahaan --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ $customer->id == $po->customer_id ? 'selected' : '' }}>
                                {{ $customer->nama_perusahaan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Referensi SPH <span class="text-danger">*</span></label>
                    <select id="sph-select" name="quotation_id" class="form-select">
                        <option value="">-- Pilih SPH --</option>
                        @foreach($quotations as $q)
                            <option value="{{ $q->id }}"
                                {{ $q->id == $po->quotation_id ? 'selected' : '' }}>
                                {{ $q->no_sph }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- PIC --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>PIC Keuangan</label>
                    <input type="text" name="nama_pic_keuangan"
                           value="{{ old('nama_pic_keuangan', $po->nama_pic_keuangan) }}"
                           class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Kontak</label>
                    <input type="text" name="kontak_pic_keuangan"
                           value="{{ old('kontak_pic_keuangan', $po->kontak_pic_keuangan) }}"
                           class="form-control">
                </div>
            </div>

            <button class="btn btn-primary mt-3">Update</button>
            <a href="{{ route('PO.index') }}" class="btn btn-secondary mt-3">Batal</a>
        </form>
    </div>
</div>

{{-- Modal PDF --}}
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-pdf text-danger"></i> Preview File PO
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="pdfViewer" src="" width="100%" height="600" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT --}}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    // Select2
    $('#customer-select, #sph-select').select2({ width: '100%' });

    // Load SPH by customer
    $('#customer-select').on('change', function () {
        let customerId = $(this).val();
        let sphSelect = $('#sph-select');

        sphSelect.empty().append('<option>Loading...</option>').prop('disabled', true);

        if (!customerId) return;

        $.get('{{ url("quotation/by-customer") }}/' + customerId, function (data) {
            sphSelect.empty().append('<option value="">-- Pilih SPH --</option>');
            data.forEach(item => {
                sphSelect.append(`<option value="${item.id}">${item.no_sph}</option>`);
            });
            sphSelect.prop('disabled', false);
        });
    });
});

// Fungsi buka modal PDF
function openPDFModal(fileUrl) {
    document.getElementById('pdfViewer').src = fileUrl;
    var pdfModal = new bootstrap.Modal(document.getElementById('pdfModal'));
    pdfModal.show();
}
</script>

@endsection
