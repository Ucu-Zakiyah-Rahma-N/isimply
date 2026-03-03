@extends('app.template')

@section('content')
<div class="container">

    <h4 class="mb-4">Laporan Outstanding</h4>

    {{-- Summary --}}
    <div class="card mb-3">
        <div class="card-body">
            <h5>Total Outstanding</h5>
            <h3 class="text-danger">
                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
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
                            <th>Tanggal Inv</th>
                            <th>Customer</th>
                            <th>Lokasi</th>
                            <th>Nama Pekerjaan</th>
                            <th>Nominal PO</th>
                            <th>Sudah Di Invoice</th>
                            <th>Sudah Dibayar</th>
                            <th>Sisa Invoice</th>
                            <th>Belum Di Invoice</th>
                            <th>Total Outstanding</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data as $i => $po)
                        <tr>
                            <td>{{ $i+1 }}</td>

                            <td>
                                @forelse($po->tgl_invoice_list as $tgl)
                                {{ \Carbon\Carbon::parse($tgl)->format('d-m-Y') }}<br>
                                @empty
                                -
                                @endforelse
                            </td>
                            <td>{{ $po->customer->nama_perusahaan ?? '-' }}</td>
                            <td>{{ $po->lokasi ?? '-' }}</td>
                            <td>
                                @forelse($po->all_produk as $item)
                                <span class="badge text-dark border me-1">
                                    {{ $item->perizinan?->jenis 
                ?? $item->perizinan_lainnya 
                ?? '-' }}
                                </span>
                                @empty
                                <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>
                                Rp {{ number_format($po->nominal_po,0,',','.') }}
                            </td>

                            <td>
                                Rp {{ number_format($po->invoices->sum('grand_total'),0,',','.') }}
                            </td>

                            <td>
                                Rp {{ number_format(
                                    $po->invoices->sum(function($inv){
                                        return $inv->payments->sum('nominal')
                                             + $inv->payments->sum('nilai_pph');
                                    }),0,',','.')
                                }}
                            </td>

                            <td class="text-warning fw-bold">
                                Rp {{ number_format($po->sisa_invoice,0,',','.') }}
                            </td>

                            <td class="text-info fw-bold">
                                Rp {{ number_format($po->sisa_belum_invoice,0,',','.') }}
                            </td>

                            <td class="text-danger fw-bold">
                                Rp {{ number_format($po->outstanding,0,',','.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">
                                Tidak ada data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>
@endsection