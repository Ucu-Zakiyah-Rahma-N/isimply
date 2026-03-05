@extends('app.template')

@section('content')
<div class="container">

    <h4 class="mb-4">Laporan Piutang</h4>

    {{-- Summary --}}
<div class="row mb-3">

    {{-- CARD UTAMA --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-90">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center toggle-header"
                     data-bs-toggle="collapse"
                     data-bs-target="#detailPiutang"
                     aria-expanded="false">

                    <div>
                        <h5 class="mb-1">Data Piutang</h5>
                        <h3 class="text-danger mb-0">
                            Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <i class="bi bi-chevron-right fs-3 transition" id="iconPiutang"></i>
                </div>

            </div>
        </div>
    </div>

    {{-- DETAIL (MUNCUL SEJAJAR DI KANAN) --}}
    <div class="col-md-8">
        <div class="collapse" id="detailPiutang">
            <div class="row">

                <div class="col-md-6">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Bulan Ini</h6>
                            <h5 class="fw-bold text-primary">
                                Rp 0
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">30 Hari Mendatang</h6>
                            <h5 class="fw-bold text-warning">
                                Rp 0
                            </h5>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Invoice</th>
                            <th>Customer</th>
                            <th>No Invoice</th>

                            <th>No SPK</th>
                            <th>Nama Perizinan</th>
                            <th>Termin</th>

                            <th>Nominal SPK</th>

                            <th>PPN</th>
                            <th>PPh</th>
                            <th>Sisa Tagihan</th>
                            <!-- <th>Status</th> -->
                        </tr>
                    </thead>
                    @php
                    $sumNominalSpk = 0;
                    $sumTotalTagihan = 0;
                    @endphp

                    <tbody>
                        @forelse($data as $i => $row)

                        @php
                        $nominalSpk = $row->total_after_diskon_inv > 0
                        ? $row->total_after_diskon_inv
                        : $row->nominal_invoice;

                        $ppn = $row->ppn ?? 0;
                        $totalTagihan = $nominalSpk + $ppn;

                        $sumNominalSpk += $nominalSpk;
                        $sumTotalTagihan += $totalTagihan;
                        @endphp

                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_inv)->format('d-m-Y') }}</td>
                            <td>{{ $row->customer->nama_perusahaan ?? '-' }}</td>
                            <td>{{ $row->no_invoice }}</td>
                            <td>{{ $row->po->no_po ?? '-' }}</td>

                            <td>
                                @if ($row->produk->isNotEmpty())
                                @foreach ($row->produk as $item)
                                <span class="badge text-dark border me-1">
                                    {{ $item->perizinan?->jenis ?? $item->perizinan_lainnya ?? '-' }}
                                </span>
                                @endforeach
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>{{ $row->keterangan }}</td>

                            <td class="text-end fw-bold">
                                Rp {{ number_format($nominalSpk, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-bold">
                                {{ $ppn > 0 ? 'Rp ' . number_format($ppn, 0, ',', '.') : '-' }}
                            </td>

                            <td>    
                                {{ $row->nilai_pph > 0 ? 'Rp ' . number_format($row->nilai_pph,0,',','.') : '-' }}
                            </td>

                            <td class="text-end fw-bold">
                                Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                            </td>

                            <!-- <td>
                                @if($row->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                                @elseif($row->status === 'posted')
                                <span class="badge bg-warning text-dark">Belum Lunas</span>
                                @endif
                            </td> -->
                        </tr>

                        @empty
                        <tr>
                            <td colspan="12" class="text-center">
                                Tidak ada data
                            </td>
                        </tr>
                        @endforelse

                        {{-- BARIS TOTAL --}}
                        @if($data->count() > 0)
                        <tr class="table-secondary fw-bold">
                            <td colspan="7" class="text-end">TOTAL</td>

                            <td class="text-end">
                                Rp {{ number_format($sumNominalSpk, 0, ',', '.') }}
                            </td>

                            <td></td>
                            <td></td>

                            <td class="text-end">
                                Rp {{ number_format($sumTotalTagihan, 0, ',', '.') }}
                            </td>

                            <td></td>
                        </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<script>
document.getElementById('detailPiutang')
    .addEventListener('show.bs.collapse', function () {
        document.getElementById('iconPiutang')
            .classList.replace('bi-chevron-right', 'bi-chevron-left');
    });

document.getElementById('detailPiutang')
    .addEventListener('hide.bs.collapse', function () {
        document.getElementById('iconPiutang')
            .classList.replace('bi-chevron-left', 'bi-chevron-right');
    });
</script>
@endsection