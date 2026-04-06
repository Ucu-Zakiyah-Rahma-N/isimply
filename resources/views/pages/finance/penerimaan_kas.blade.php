@extends('app.template')

@section('content')
<div class="container">

    <!-- <h4 class="mb-4">Penerimaan</h4> -->

    {{-- NAVBAR / TAB --}}
    {{-- NAVBAR / TAB --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'kas' || request()->tab == null ? 'active' : '' }}"
                href="{{ route('finance.invoice.penerimaan_kas', ['tab' => 'kas']) }}">
                Penerimaan Kas
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'monthly' ? 'active' : '' }}"
                href="{{ route('finance.invoice.penerimaan_kas', ['tab' => 'monthly']) }}">
                Monthly
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'rekap' ? 'active' : '' }}"
                href="{{ route('finance.invoice.penerimaan_kas', ['tab' => 'rekap']) }}">
                Rekap Penerimaan
            </a>
        </li>
    </ul>

    <div class="card">
        <div class="card-body">

            {{-- ========================= --}}
            {{--  PENERIMAAN KAS--}}
            {{-- ========================= --}}
            @if(request()->tab == 'kas' || request()->tab == null)

        <h5 class="mb-3">Penerimaan Kas</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
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

                        @forelse($kas as $i => $row)
                        @php $total += $row->nominal; @endphp
                        <tr>
                            {{-- NO --}}
                            <td class="text-center">{{ $i + 1 }}</td>

                            {{-- TANGGAL --}}
                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>

                            {{-- TRANSAKSI (BANK) --}}
                            <td>{{ $row->bank ?? '-' }}</td>

                            {{-- NAMA BARANG / JASA --}}
                            <td>
                                {{ $row->keterangan ?? '-' }} - termin {{ $row->termin_inv ?? '-' }}
                                @if($row->is_partial ?? false)
                                🟡
                                @endif
                            </td>

                            {{-- PENERIMA (CUSTOMER) --}}
                            <td>{{ $row->customer ?? '-' }}</td>

                            {{-- KODE PROJEK --}}
                            <td>-</td>

                            {{-- AKUN (COA) --}}
                            <td>{{ $row->coa ?? '-' }}</td>

                            {{-- PEMASUKAN --}}
                            <td class="text-end">
                                Rp {{ number_format($row->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse

                        {{-- TOTAL --}}
                        <tr class="table-secondary fw-bold">
                            <td colspan="7" class="text-end">TOTAL</td>
                            <td class="text-end">
                                Rp {{ number_format($total,0,',','.') }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>


            {{-- ========================= --}}
            {{-- MONTHLY --}}
            {{-- ========================= --}}
            @elseif(request()->tab == 'monthly')

            <h5 class="mb-3">Penerimaan per Bulan</h5>

            @forelse($monthly as $bulan => $items)
            @php
            // Ubah '2026-01' jadi 'Januari 2026'
            $formattedBulan = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            @endphp

            <div class="mb-4">
                <h6 class="fw-bold">{{ $formattedBulan }}</h6>

                <table class="table table-bordered table-sm">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
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

                        @foreach($items as $i => $row)
                        @php $total += $row->nominal; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            {{-- TANGGAL --}}
                            <td>
                                {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}
                            </td>

                            {{-- TRANSAKSI (BANK) --}}
                            <td>
                                {{ $row->bank ?? '-' }}
                            </td>

                            <!-- {{-- NAMA BARANG / JASA (KETERANGAN JURNAL) --}}
                                    <td>
                                        {{ $row->keterangan . ' - termin ' . ($row->termin_inv) ?? '-' }}
                                    </td> -->
                            <td>
                                {{ $row->keterangan ?? '-' }} - termin {{ $row->termin_inv ?? '-' }}
                                @if($row->is_partial ?? false)
                                🟡
                                @endif
                            </td>
                            {{-- PENERIMA (CUSTOMER) --}}
                            <td>
                                {{ $row->customer ?? '-' }}
                            </td>

                            {{-- KODE PROJEK (KOSONG DULU) --}}
                            <td>-</td>

                            {{-- AKUN (COA) --}}
                            <td>
                                @if($row->ref_type == 'invoice_payment')
                                    Piutang Usaha
                                @else
                                    {{ $row->coa ?? '-' }}
                                @endif
                            </td>

                            {{-- PEMASUKAN --}}
                            <td class="text-end">
                                Rp {{ number_format($row->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach

                        <tr class="table-secondary fw-bold">
                            <td colspan="7" class="text-end">TOTAL</td>
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
            
            
             <h5 class="mb-3" text-center>Rekapitulasi Dana Masuk</h5>

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

            @php
            $grandTotal = $rekap->sum('total');
            @endphp

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

                    <tr class="table-light text-bold">
                        <td colspan="2" class="text-end"><b>TOTAL</b></td>
                        <td class="text-end">
                            <b>Rp {{ number_format($grandTotal, 0, ',', '.') }} </b>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            @endif
        </div>
    </div>

</div>
@endsection