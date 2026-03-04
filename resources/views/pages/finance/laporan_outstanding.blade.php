@extends('app.template')

@section('content')

<div class="container">

    <h4 class="mb-4">Laporan Outstanding</h4>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row text-center">

                {{-- Total Keseluruhan --}}
                <div class="col-md mb-3">
                    <div class="border rounded p-4 h-100">
                        <h6 class="text-muted">Total Outstanding Keseluruhan</h6>
                        <h5 class="fw-bold text-primary">
                            Rp {{ number_format($totalOutstandingKeseluruhan ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>

                {{-- Per Tahun --}}
                @foreach($outstandingPerTahun as $tahun => $total)
                <div class="col-md mb-3">
                    <div class="border rounded p-4 h-100">
                        <h6 class="text-muted">
                            Total Outstanding {{ $tahun }}
                        </h6>
                        <h5 class="fw-bold text-danger">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
    {{--
    <div class="card mb-3">
    <div class="card-body">
        <div class="row text-center">

            {{-- Total Keseluruhan 
            <div class="col-md mb-3">
                <div class="border rounded p-4 h-100">
                    <h6 class="text-muted">Total Outstanding Keseluruhan</h6>
                    <h5 class="fw-bold text-primary">
                        Rp {{ number_format($totalNominalTermin ?? 0, 0, ',', '.') }}
    </h5>
</div>
</div>

{{-- Per Tahun 
            @foreach($outstandingPerTahun as $tahun => $total)
                <div class="col-md mb-3">
                    <div class="border rounded p-4 h-100">
                        <h6 class="text-muted">
                            Total Outstanding {{ $tahun }}
</h6>
<h5 class="fw-bold text-danger">
    Rp {{ number_format($total, 0, ',', '.') }}
</h5>
</div>
</div>
@endforeach

</div>
</div>
</div> --}}


<form method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-3">
            <select name="tahun" class="form-select" onchange="this.form.submit()">
                <option value="all" {{ $tahunDipilih == 'all' ? 'selected' : '' }}>
                    All
                </option>
                @for($year = $tahunSekarang; $year >= 2023; $year--)
                <option value="{{ $year }}"
                    {{ $tahunDipilih == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
                @endfor
            </select>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light text-center align-middle">
                    <tr>
                        <th>No</th>
                        <th>Tanggal PO</th>
                        <th>Nama Customer</th>
                        <th>Lokasi</th>
                        <th>Nama Pekerjaan</th>
                        <th>Nominal SPK</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $i => $po)

                    @php
                    $rowspan = max($po->termin_list->count(), 1);
                    @endphp

                    @if($po->termin_list->isNotEmpty())

                    @foreach($po->termin_list as $index => $termin)
                    <tr>

                        @if($index == 0)
                        <td rowspan="{{ $rowspan }}">{{ $i+1 }}</td>

                        <td rowspan="{{ $rowspan }}">
                            {{ $po->tgl_po 
                            ? \Carbon\Carbon::parse($po->tgl_po)->format('d-m-Y') 
                            : '-' }}
                        </td>

                        <td rowspan="{{ $rowspan }}">
                            {{ $po->customer->nama_perusahaan ?? '-' }}
                        </td>

                        <td rowspan="{{ $rowspan }}">
                            {{ \Illuminate\Support\Str::title($po->quotation?->kabupaten?->nama ?? '-') }}
                        </td>
                        <td rowspan="{{ $rowspan }}">
                            {{ $po->all_produk->isNotEmpty() 
                                    ? $po->all_produk->implode(', ') 
                                    : '-' 
                                }}
                        </td>

                        <!-- ini kalo pake badge
                             <td rowspan="{{ $rowspan }}">
                                @forelse($po->all_produk as $item)
                                <span class="badge text-dark border me-1">
                                    {{ $item }}
                                </span>
                                @empty
                                <span class="text-muted">-</span>
                                @endforelse
                            </td> -->

                        <td rowspan="{{ $rowspan }}" class="text-end fw-bold">
                            Rp {{ number_format($po->nominal_spk ?? 0,0,',','.') }}
                        </td>
                        @endif

                        <td>
                            {{ $termin['keterangan'] }}
                        </td>

                        <td class="text-end">
                            Rp {{ number_format($termin['nominal'],0,',','.') }}
                        </td>

                    </tr>
                    @endforeach

                    @else

                    {{-- Kalau tidak ada termin --}}
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $po->tgl_po ? \Carbon\Carbon::parse($po->tgl_po)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $po->customer->nama_perusahaan ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::title($po->quotation?->kabupaten?->nama ?? '-') }}</td>
                        <td>
                            @forelse($po->all_produk as $item)
                            <span class="badge text-dark border me-1">
                                {{ $item }}
                            </span>
                            @empty
                            <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($po->nominal_spk ?? 0,0,',','.') }}
                        </td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    @endif

                    @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            Tidak ada data
                        </td>
                    </tr>
                    @endforelse
                    <tr class="table-secondary fw-bold">
                        <td colspan="5" class="text-end">
                            TOTAL
                        </td>
                        <td class="text-end">
                            Rp {{ number_format($totalNominalSPK ?? 0, 0, ',', '.') }}
                        </td>
                        <td></td>
                        <td class="text-end">
                            Rp {{ number_format($totalNominalTermin ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
@endsection