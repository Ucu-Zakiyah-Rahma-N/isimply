@extends('app.template')

@section('content')
<div class="container">

    <h4 class="mb-4">Laporan Piutang</h4>

    {{-- Summary --}}
    <div class="card mb-3">
        <div class="card-body">
            <h5>Total Piutang Aktif</h5>
            <h3 class="text-danger">
                Rp {{ number_format($totalPiutang, 0, ',', '.') }}
            </h3>
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
                            <th>Total Tagihan</th>
                            <th>Status</th>
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

                            <td>
                                @if($row->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                                @elseif($row->status === 'posted')
                                <span class="badge bg-warning text-dark">Belum Lunas</span>
                                @endif
                            </td>
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
@endsection