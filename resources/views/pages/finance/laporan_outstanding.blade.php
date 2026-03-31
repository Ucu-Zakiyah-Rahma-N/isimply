@extends('app.template')

@section('content')
<style>
    .toggle-header {
        cursor: pointer;
        transition: 0.2s ease;
    }

    .toggle-header:hover {
        background-color: #f8f9fa;
    }

    .transition {
        transition: 0.3s ease;
    }
</style>
<div class="container">

    <h4 class="mb-4">Laporan Outstanding</h4>

    <div class="card mb-3 shadow-sm">
        <div class="card-body">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center 
                    border rounded p-4 toggle-header"
                data-bs-toggle="collapse"
                data-bs-target="#detailOutstanding"
                aria-expanded="false"
                id="toggleButton">

                <div>
                    <h6 class="text-muted mb-1">
                        Data Outstanding Keseluruhan
                    </h6>
                    <h4 class="fw-bold text-primary mb-0">
                        Rp {{ number_format($totalOutstandingKeseluruhan ?? 0, 0, ',', '.') }}
                    </h4>
                </div>

                {{-- ICON --}}
                <i class="bi bi-chevron-down fs-4 transition" id="toggleIcon"></i>
            </div>

            {{-- DETAIL PER TAHUN --}}
            <div class="collapse mt-3" id="detailOutstanding">
                <ul class="list-group list-group-flush">
                    @foreach($outstandingPerTahun as $tahun => $total)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $tahun }}</span>
                        <strong class="text-danger">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </strong>
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>

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
                            <th>Termin</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>

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
                                {{ number_format($po->nominal_spk ?? 0,0,',','.') }}
                            </td>
                            @endif

                            <td>
                                {{ $termin['keterangan'] }}
                            </td>

                            <td class="text-end">
                                {{ number_format($termin['nominal'],0,',','.') }}
                            </td>
                            <td class="text-center">
                                @if($termin['status'] == 'invoice')
                                <span class="badge text-warning text-dark">Sudah Invoice</span>
                                @else
                                <span class="badge text-secondary">Belum Invoice</span>
                                @endif
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
                                {{ number_format($po->nominal_spk ?? 0,0,',','.') }}
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
                                {{ number_format($totalNominalSPK ?? 0, 0, ',', '.') }}
                            </td>
                            <td></td>
                            <td class="text-end">
                                {{ number_format($totalNominalTermin ?? 0, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    document.getElementById('detailOutstanding')
        .addEventListener('show.bs.collapse', function() {
            document.getElementById('toggleIcon')
                .classList.replace('bi-chevron-down', 'bi-chevron-up');
        });

    document.getElementById('detailOutstanding')
        .addEventListener('hide.bs.collapse', function() {
            document.getElementById('toggleIcon')
                .classList.replace('bi-chevron-up', 'bi-chevron-down');
        });
</script>
@endsection