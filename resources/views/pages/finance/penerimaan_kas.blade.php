@extends('app.template')

@section('content')
<div class="container">

    <h4 class="mb-4">Penerimaan Kas</h4>

    {{-- NAVBAR / TAB --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'rekap' || request()->tab == null ? 'active' : '' }}"
               href="{{ route('finance.invoice.penerimaan_kas', ['tab' => 'rekap']) }}">
                Rekap Penerimaan
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'monthly' ? 'active' : '' }}"
               href="{{ route('finance.invoice.penerimaan_kas', ['tab' => 'monthly']) }}">
                Monthly
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'kas' ? 'active' : '' }}"
               href="{{ route('finance.invoice.penerimaan_kas', ['tab' => 'kas']) }}">
                Penerimaan Kas
            </a>
        </li>
    </ul>

    <div class="card">
        <div class="card-body">

        {{-- ========================= --}}
        {{-- REKAP PENERIMAAN --}}
        {{-- ========================= --}}
        @if(request()->tab == 'rekap' || request()->tab == null)

     <form method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-3">
            <label class="form-label">Pilih Tahun</label>
            <select name="tahun" class="form-select" onchange="this.form.submit()">
                @foreach($listTahun as $th)
                    <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>
                        {{ $th }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>

<h5 class="mb-3">Rekap Penerimaan</h5>

<table class="table table-bordered table-sm">
    <thead class="table-light text-center">
        <tr>
            <th>No</th>
            <th>Bulan</th>
            <th class="text-end">Nominal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rekap as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->bulan }}</td>
            <td class="text-end">
                Rp {{ number_format($row->total, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>


        {{-- ========================= --}}
        {{-- MONTHLY --}}
        {{-- ========================= --}}
        @elseif(request()->tab == 'monthly')

            <h5 class="mb-3">Penerimaan per Bulan</h5>

            @forelse($monthly as $bulan => $items)

                <div class="mb-4">
                    <h6 class="fw-bold">{{ $bulan }}</h6>

                    <table class="table table-bordered table-sm">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Tanggal</th>
                                <th>Transaksi</th>
                                <th>Nama Barang/Jasa</th>
                                <th>Penerima</th>
                                <th>Kode Projek</th>
                                <th>Akun</th>
                                <th>Pemasukan</th>
                            </tr>
                        </thead>
                        <tbody>

                            @php $total = 0; @endphp

                            @foreach($items as $row)
                                @php $total += $row->nominal; @endphp
                                <tr>
                                </tr>
                            @endforeach

                            <tr class="table-secondary fw-bold">
                                <td colspan="6" class="text-end">TOTAL</td>
                                <td class="text-end">
                                    Rp {{ number_format($total,0,',','.') }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            @empty
                <div class="text-center">Tidak ada data</div>
            @endforelse


        {{-- ========================= --}}
        {{-- PENERIMAAN KAS --}}
        {{-- ========================= --}}
        @else

            <h5 class="mb-3">Penerimaan Kas</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Tanggal</th>
                            <th>Transaksi</th>
                            <th>Nama Barang/Jasa</th>
                            <th>Penerima</th>
                            <th>Kode Projek</th>
                            <th>Akun</th>
                            <th>Pemasukan</th>
                        </tr>
                    </thead>
                    <tbody>

                        @php $total = 0; @endphp

                        @forelse($kas as $row)
                            @php $total += $row->nominal; @endphp
                            <tr>
                
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse

                        <tr class="table-secondary fw-bold">
                            <td colspan="6" class="text-end">TOTAL</td>
                            <td class="text-end">
                                Rp {{ number_format($total,0,',','.') }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        @endif

        </div>
    </div>

</div>
@endsection