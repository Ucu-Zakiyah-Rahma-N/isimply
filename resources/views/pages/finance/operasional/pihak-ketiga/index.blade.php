@extends('app.template')

@section('content')


<div class="card shadow-sm">

    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Data Pihak Ketiga</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPihakKetiga">
                <i class="bi bi-plus-circle"></i> Buat Pihak Ketiga
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. Pengajuan</th>
                        <th>Tanggal</th>
                        <th>Penerima</th>
                        <th>Metode</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Urgent</th>
                        <!-- <th width="160">Aksi</th> -->
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>

@include('pages.finance.operasional.pihak-ketiga.modal-tambah-pihak-ketiga')
@endsection