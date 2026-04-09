    @extends('app.template')

    @section('content')


    <div class="card shadow-sm">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Total Biaya Bulan Ini</small>
                        <h5 class="fw-bold mb-0">
                            Rp {{ number_format($totalBulanIni ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Biaya 30 Hari Ini</small>
                        <h5 class="fw-bold mb-0">
                            Rp {{ number_format($total30Hari ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Biaya Belum Dibayar</small>
                        <h5 class="fw-bold mb-0 text-danger">
                            Rp {{ number_format($totalBelumBayar ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-header bg-white border-0 pb-2">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Data Biaya</h5>

                <button class="btn btn-primary btn-sm px-3"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPengajuanBiaya">
                    <i class="bi bi-plus-circle"></i> Buat Biaya
                </button>
            </div>

            <!-- FILTER -->
            <form method="GET" action="{{ route('finance.biaya_index') }}">
                <div class="row g-2 align-items-end">

                    <!-- STATUS -->
                    <div class="col-auto">
                        <small class="text-muted d-block mb-1">Status</small>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="dipurchasing" {{ request('status') == 'dipurchasing' ? 'selected' : '' }}>Dipurchasing</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <!-- METODE -->
                    <div class="col-auto">
                        <small class="text-muted d-block mb-1">Metode</small>
                        <select name="metode" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="cash" {{ request('metode') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ request('metode') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>

                    <!-- PERIODE -->
                    <div class="col-auto">
                        <small class="text-muted d-block mb-1">Periode</small>
                        <div class="d-flex align-items-center gap-2">
                            <input type="date" name="tanggal_dari"
                                value="{{ request('tanggal_dari') }}"
                                class="form-control form-control-sm">

                            <span class="text-muted">—</span>

                            <input type="date" name="tanggal_sampai"
                                value="{{ request('tanggal_sampai') }}"
                                class="form-control form-control-sm">
                        </div>
                    </div>

                    <!-- BUTTON -->
                    <div class="col-auto d-flex gap-2 ms-2">
                        <button type="submit" class="btn btn-sm btn-dark px-3">
                            <i class="bi bi-funnel"></i> Filter
                        </button>

                        <a href="{{ route('finance.biaya_index') }}"
                            class="btn btn-sm btn-light border px-3">
                            Reset
                        </a>
                    </div>

                </div>
            </form>

        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Pengaju / Dept</th>
                            <th>Item Pengajuan</th>
                            <th class="text-end">Total</th>
                            <th>Metode</th>
                            <th>No. Pengajuan</th>
                            <th>Status</th>
                            <th>Urgent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <!-- Tanggal -->
                            <td>{{ $row->tgl_pengajuan->format('d/m/Y') }}</td>

                            <!-- Pengaju + Departemen -->
                            <td>
                                <div class="fw-bold">
                                    {{ optional($row->user)->username ?? '-' }}
                                </div>
                                <small class="text-muted">
                                    {{ optional($row->user)->role ?? '-' }}
                                </small>
                            </td>

                            <!-- Item Pengajuan (ringkas) -->
                            <td>
                                {{ $row->items->pluck('deskripsi')->take(2)->implode(', ') }}
                                @if($row->items->count() > 2)
                                <span class="text-muted">+{{ $row->items->count() - 2 }} lainnya</span>
                                @endif
                            </td>

                            <!-- Total -->
                            <td class="text-end fw-bold">
                                Rp {{ number_format($row->grand_total, 0, ',', '.') }}
                            </td>

                            <!-- Metode -->
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst($row->metode_pembayaran) }}
                                </span>
                            </td>

                            <!-- No Pengajuan -->
                            <td class="fw-bold">
                                <a href="javascript:void(0)"
                                    class="text-decoration-none text-primary btnDetailPengajuan"
                                    data-id="{{ $row->id }}">
                                    {{ $row->nomor_pengajuan }}
                                </a>
                            </td>

                            @php
                            $statusClass = [
                            'dipurchasing' => 'bg-primary',
                            'dijadwalkan' => 'bg-warning',
                            'disetujui' => 'bg-success',
                            'ditolak' => 'bg-danger'
                            ];
                            @endphp

                            <!-- Status -->
                            <td>
                                <span class="badge {{ $statusClass[$row->status] ?? 'bg-secondary' }}">
                                    {{ $row->status ?? 'dipurchasing' }}
                                </span>
                            </td>

                            <!-- Urgent -->
                            <td>
                                @if($row->is_urgent)
                                <span class="badge text-danger fw-bold">Urgent</span>
                                @else
                                <span class="badge text-secondary">Normal</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Belum ada pengajuan biaya
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('pages.finance.operasional.biaya.modal-tambah-biaya')
    @include('pages.finance.operasional.biaya.modal-tambah-penerima')


    <script>
        window.appRoutes = {
            storeKontak: "{{ route('finance.kontak.store') }}",
            storePengajuan: "{{ route('finance.pengajuan-biaya.store') }}"
        };
    </script>
    @vite (['resources/js/operasional/pengajuan.js'])

    <script>
        $(document).on('click', '.btnDetailPengajuan', function() {
            loadDetailPengajuan($(this).data('id'));
        });
    </script>

    @endsection