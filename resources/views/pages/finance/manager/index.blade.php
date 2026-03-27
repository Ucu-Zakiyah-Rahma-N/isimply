@extends('app.template')

@section('content')

<style>
    .card-saas {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
    }

    /* HEADER */
    .page-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }

    /* NAVBAR FILTER */
    .custom-tabs {
        border-bottom: 1px solid #e5e7eb;
    }

    .custom-tabs .nav-link {
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        border: none;
        padding: 12px 18px;
    }

    .custom-tabs .nav-link.active {
        color: #111827;
        border-bottom: 3px solid #2563eb;
        background: transparent;
    }

    .custom-tabs .nav-link:hover {
        color: #111827;
    }

    /* TABLE */
    .table thead th {
        font-size: 12px;
        font-weight: 600;
        background: #f9fafb;
        color: #6b7280;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .table td {
        font-size: 13px;
        vertical-align: middle;
        border-color: #f1f5f9;
    }

    .table small {
        font-size: 11px;
        color: #9ca3af;
    }

    /* STATUS */
    .status-badge {
        padding: 4px 12px;
        font-size: 11px;
        border-radius: 20px;
        font-weight: 500;
    }

    .badge-diajukan {
        background: #fff7ed;
        color: #c2410c;
    }

    .badge-approved {
        background: #ecfdf5;
        color: #047857;
    }

    .badge-reject {
        background: #fef2f2;
        color: #b91c1c;
    }

    .badge-pending {
        background: #fffbeb;
        color: #b45309;
    }

    /* ACTION BUTTON */
    .action-btn {
        font-size: 11px;
        padding: 5px 10px;
        margin-bottom: 4px;
        border-radius: 6px;
    }

    .btn-pdf {
        font-size: 12px;
        padding: 6px 14px;
        border-radius: 8px;
    }
</style>

<div class="card card-saas shadow-sm">

    {{-- HEADER ROW --}}
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="page-title">
                Daftar Pengajuan
            </div>

            <button class="btn btn-primary btn-sm btn-pdf">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
        </div>
    </div>

    {{-- NAVBAR FILTER ROW --}}
    <div class="px-4">
        <ul class="nav custom-tabs">

            <!-- DIJADWALKAN (DEFAULT) -->
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'dijadwalkan' ? 'active' : '' }}"
                    href="{{ route('finance.manager_index', ['tab' => 'dijadwalkan']) }}">
                    Dijadwalkan
                    @if($countScheduled > 0)
                    <span class="badge bg-primary ms-1">{{ $countScheduled }}</span>
                    @endif
                </a>
            </li>

            <!-- HARI INI -->
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'hari_ini' ? 'active' : '' }}"
                    href="{{ route('finance.manager_index', ['tab' => 'hari_ini']) }}">
                    Hari Ini
                    @if($countToday > 0)
                    <span class="badge bg-success ms-1">{{ $countToday }}</span>
                    @endif
                </a>
            </li>

            <!-- PENDING -->
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'dipending' ? 'active' : '' }}"
                    href="{{ route('finance.manager_index', ['tab' => 'dipending']) }}">
                    Pending
                    @if($countPending > 0)
                    <span class="badge bg-warning ms-1">{{ $countPending }}</span>
                    @endif
                </a>
            </li>

            <!-- HISTORY -->
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'history' ? 'active' : '' }}"
                    href="{{ route('finance.manager_index', ['tab' => 'history']) }}">
                    History
                    @if($countHistory > 0)
                    <span class="badge bg-secondary ms-1">{{ $countHistory }}</span>
                    @endif
                </a>
            </li>

        </ul>
    </div>

    {{-- TABLE --}}
    <div class="card-body pt-3 px-4 pb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Tgl Bayar</th>
                        <th>Kategori</th>
                        <th>Sumber Transaksi</th>
                        <th>Tgl Pengajuan</th>
                        <th>No Pengajuan</th>
                        <th>Penerima</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $i => $row)
                    <tr>

                        <td>{{ $i + 1 }}</td>

                        <td>
                            {{ optional($row->scheduling)->tgl_pembayaran 
                            ? \Carbon\Carbon::parse($row->scheduling->tgl_pembayaran)->format('d-m-Y') 
                            : '-' }}
                        </td>

                        <td>{{ $row->kategori ?? '-' }}</td>

                        <td>{{ $row->sumber_transaksi ?? '-' }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($row->tgl_pengajuan)->format('d-m-Y') }}
                        </td>

                        <td>
                            <a href="javascript:void(0)"
                                class="text-decoration-none text-primary btnDetailPengajuan d-block"
                                data-id="{{ $row->id }}">

                                <div class="fw-semibold">
                                    {{ $row->nomor_pengajuan }}
                                </div>

                                <small class="text-muted">
                                    @foreach($row->items as $item)
                                    <div>{{ $item->deskripsi }}</div>
                                    @endforeach
                                </small>

                            </a>
                        </td>

                        <td>{{ $row->penerima ?? '-' }}</td>

                        <td>
                            @if($row->status == 'ditolak')
                            <span class="status-badge badge-reject">Ditolak</span>
                            @elseif($row->status == 'dipending')
                            <span class="status-badge badge-pending">Dipending</span>
                            @elseif(optional($row->scheduling)->tgl_pembayaran == \Carbon\Carbon::today()->toDateString())
                            <span class="status-badge badge-approved">Disetujui</span>

                            @else
                            <span class="status-badge badge-diajukan">Dijadwalkan</span>
                            @endif
                        </td>

                        <td class="text-end fw-semibold">
                            Rp {{ number_format($row->grand_total, 0, ',', '.') }}
                        </td>

                        <td>
                            <div class="d-flex flex-column gap-1">

                                {{-- Jika sudah ditolak --}}
                                @if($row->status == 'ditolak')
                                <button class="btn btn-danger action-btn" disabled>
                                    Ditolak
                                </button>

                                {{-- Jika sudah disetujui --}}
                                @elseif($row->status == 'disetujui')
                                <button class="btn btn-success action-btn" disabled>
                                    Disetujui
                                </button>

                                {{-- Jika pending --}}
                                @elseif($row->status == 'dipending')
                                <button class="btn btn-warning action-btn" disabled>
                                    Pending
                                </button>

                                {{-- Jika masih bisa diproses --}}
                                @else

                                {{-- APPROVE --}}
                                <form id="form-approve-{{ $row->id }}"
                                    action="{{ route('finance.manager.approve', $row->id) }}"
                                    method="POST">
                                    @csrf
                                    <button type="button"
                                        class="btn btn-success action-btn btn-approve"
                                        data-id="{{ $row->id }}">
                                        Disetujui
                                    </button>
                                </form>

                                {{-- PENDING --}}
                                <button class="btn btn-warning action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPending"
                                    data-id="{{ $row->id }}">
                                    Pending
                                </button>

                                {{-- REJECT --}}
                                <button class="btn btn-danger action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalTolak"
                                    data-id="{{ $row->id }}">
                                    Ditolak
                                </button>

                                @endif

                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            Tidak ada data pengajuan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@include('pages.finance.manager.modal-tolak')
@include('pages.finance.manager.modal-pending')
@include('pages.finance.manager.modal-detail-biaya')
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.btn-approve').forEach(function(button) {

            button.addEventListener('click', function() {

                let id = this.dataset.id;
                let form = document.getElementById('form-approve-' + id);

                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Pembayaran akan disetujui dan tidak dapat dibatalkan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

            });

        });

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modal = document.getElementById('modalTolak');

        modal.addEventListener('show.bs.modal', function(event) {

            const button = event.relatedTarget;

            document.getElementById('modalFormTolak').action =
                `/finance/manager/${button.getAttribute('data-id')}/tolak`;

        });

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modal = document.getElementById('modalPending');

        modal.addEventListener('show.bs.modal', function(event) {

            const button = event.relatedTarget;

            document.getElementById('modalFormPending').action =
                `/finance/manager/${button.getAttribute('data-id')}/pending`;

        });

    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on('click', '.btnDetailPengajuan', function() {
        const id = $(this).attr('data-id');

        if (typeof loadDetailPengajuanOnManager === 'function') {
            loadDetailPengajuanOnManager(id);
        } else {
            console.error('Function loadDetailPengajuanOnManager tidak ditemukan');
        }
    });
</script>