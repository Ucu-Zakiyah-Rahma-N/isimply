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

            {{-- WAITING --}}
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'waiting' ? 'active' : '' }}"
                    href="{{ route('finance.purchasing_index', ['tab' => 'waiting']) }}">
                    Waiting List
                    @if($countWaiting > 0)
                    <span class="badge bg-danger ms-1">{{ $countWaiting }}</span>
                    @endif
                </a>
            </li>

            {{-- HARI INI --}}
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'today' ? 'active' : '' }}"
                    href="{{ route('finance.purchasing_index', ['tab' => 'today']) }}">
                    Hari Ini
                    @if($countToday > 0)
                    <span class="badge bg-primary ms-1">{{ $countToday }}</span>
                    @endif
                </a>
            </li>

            {{-- RENCANA PEMBAYARAN --}}
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'dijadwalkan' ? 'active' : '' }}"
                    href="{{ route('finance.purchasing_index', ['tab' => 'dijadwalkan']) }}">
                    Rencana Pembayaran
                    @if($countScheduled > 0)
                    <span class="badge bg-success ms-1">{{ $countScheduled }}</span>
                    @endif
                </a>
            </li>

            {{-- PENDING --}}
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'pending' ? 'active' : '' }}"
                    href="{{ route('finance.purchasing_index', ['tab' => 'pending']) }}">
                    Pending
                    @if($countPending > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $countPending }}</span>
                    @endif
                </a>
            </li>

            {{-- DISETUJUI --}}
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'disetujui' ? 'active' : '' }}"
                    href="{{ route('finance.purchasing_index', ['tab' => 'disetujui']) }}">
                    Disetujui
                    @if($countApproved > 0)
                    <span class="badge bg-success ms-1">{{ $countApproved }}</span>
                    @endif
                </a>
            </li>

            {{-- REJECT --}}
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'ditolak' ? 'active' : '' }}"
                    href="{{ route('finance.purchasing_index', ['tab' => 'ditolak']) }}">
                    Reject
                    @if($countReject > 0)
                    <span class="badge bg-danger ms-1">{{ $countReject }}</span>
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
                            <div class="fw-semibold">
                                {{ $row->nomor_pengajuan }}
                            </div>
                            <small>
                                @foreach($row->items as $item)
                                <div>{{ $item->deskripsi }}</div>
                                @endforeach
                            </small>
                        </td>

                        <td>{{ $row->penerima ?? '-' }}</td>

                        <td>
                            @if($row->approved_at)
                            <span class="status-badge badge-approved">Disetujui</span>

                            @elseif($row->status == 'ditolak')
                            <span class="status-badge badge-reject">Ditolak</span>

                            @elseif($row->scheduling && \Carbon\Carbon::parse($row->scheduling->tgl_pembayaran)->lt(\Carbon\Carbon::today()))
                            <span class="status-badge badge-warning">Pending</span>

                            @elseif($row->scheduling)
                            <span class="status-badge badge-success">Dijadwalkan</span>

                            @else
                            <span class="status-badge badge-diajukan">Menunggu Jadwal</span>
                            @endif
                        </td>

                        <td class="text-end fw-semibold">
                            Rp {{ number_format($row->grand_total, 0, ',', '.') }}
                        </td>

                        <td>
                            <div class="d-flex flex-column">

                                @if($row->approved_at)
                                <button class="btn btn-success action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalBayarkan"
                                    data-id="{{ $row->id }}"
                                    data-no="{{ $row->nomor_pengajuan }}"
                                    data-total="{{ number_format($row->grand_total,0,',','.') }}"
                                    data-tgl="{{ \Carbon\Carbon::parse($row->tgl_pengajuan)->format('d/m/Y') }}">
                                    Bayarkan
                                </button>

                                @elseif($row->status == 'ditolak')
                                <button class="btn btn-danger action-btn" disabled>
                                    Ditolak
                                </button>

                                @elseif($row->scheduling)
                                <button class="btn btn-success action-btn">
                                    Dijadwalkan
                                </button>
                                <button class="btn btn-secondary action-btn" disabled>
                                    Edit
                                </button>

                                @else
                                <button class="btn btn-primary action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalJadwalkan"
                                    data-id="{{ $row->id }}"
                                    data-no="{{ $row->nomor_pengajuan }}"
                                    data-tgl="{{ \Carbon\Carbon::parse($row->tgl_pengajuan)->format('d/m/Y') }}"
                                    data-deskripsi="{{ $row->items->pluck('deskripsi')->implode(', ') }}"
                                    data-penerima="{{ $row->penerima }}"
                                    data-total="{{ number_format($row->grand_total,0,',','.') }}">
                                    Jadwalkan
                                </button>

                                <button class="btn btn-warning action-btn">
                                    Edit
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

@include('pages.finance.purchasing.modal-jadwalkan')
@include('pages.finance.purchasing.modal-bayarkan')
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modal = document.getElementById('modalJadwalkan');

        modal.addEventListener('show.bs.modal', function(event) {

            const button = event.relatedTarget;

            document.getElementById('modalTextPengajuan').innerText =
                button.getAttribute('data-no');

            document.getElementById('modalNoPengajuan').value =
                button.getAttribute('data-no');

            document.getElementById('modalTglPengajuan').innerText =
                button.getAttribute('data-tgl');

            document.getElementById('modalDeskripsi').innerText =
                button.getAttribute('data-deskripsi');

            document.getElementById('modalPenerima').innerText =
                button.getAttribute('data-penerima');

            document.getElementById('modalTotal').innerText =
                'Rp ' + button.getAttribute('data-total');

        });

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modal = document.getElementById('modalBayarkan');

        modal.addEventListener('show.bs.modal', function(event) {

            const button = event.relatedTarget;

            document.getElementById('modalTextPengajuanBayarkan').innerText =
                button.getAttribute('data-no');

            document.getElementById('modalNoPengajuanBayarkan').value =
                button.getAttribute('data-no');
            document.getElementById('modalTglPengajuanBayarkan').innerText =
                button.getAttribute('data-tgl');

            document.getElementById('modalTotalBayarkan').innerText =
                'Rp ' + button.getAttribute('data-total');
        });

    });
</script>