<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        h3 {
            margin-bottom: 10px;
        }

        /* 🔥 FIX: jangan pakai avoid di sini */
        .year-section {
            margin-bottom: 30px;
        }

        /* 🔥 biar judul nempel ke tabel */
        .year-title {
            font-weight: bold;
            font-size: 13px;
            margin: 15px 0 5px 0;
            page-break-after: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background: #eee;
            text-align: center;
        }

        td {
            vertical-align: top;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background: #f2f2f2;
        }

        /* 🔥 INI KUNCI BIAR ROWSPAN GA RUSAK */
        .po-group {
            page-break-inside: avoid;
        }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }
    </style>
</head>
<body>

<h3>Laporan Outstanding</h3>

@if($tahun == 'all')

    @foreach($data as $year => $items)

    @php
        $no = 1;
        $totalSpk = 0;
        $totalTermin = 0;
    @endphp

    <div class="year-section">

        <div class="year-title">Tahun {{ $year }}</div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Lokasi</th>
                    <th>Pekerjaan</th>
                    <th>Nominal SPK</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                </tr>
            </thead>

            @foreach($items as $po)

            @php
                $rowspan = max($po->termin_list->count(), 1);
                $totalSpk += $po->nominal_spk;
            @endphp

            <tbody class="po-group">

                @foreach($po->termin_list as $index => $termin)

                @php
                    $totalTermin += $termin['nominal'];
                @endphp

                <tr>

                    @if($index == 0)
                    <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>

                    <td rowspan="{{ $rowspan }}">
                        {{ \Carbon\Carbon::parse($po->tgl_po)->format('d-m-Y') }}
                    </td>

                    <td rowspan="{{ $rowspan }}">
                        {{ $po->customer->nama_perusahaan ?? '-' }}
                    </td>

                    <td rowspan="{{ $rowspan }}">
                        {{ \Illuminate\Support\Str::title($po->quotation?->kabupaten?->nama ?? '-') }}
                    </td>

                    <td rowspan="{{ $rowspan }}">
                        {{ $po->all_produk->implode(', ') }}
                    </td>

                    <td rowspan="{{ $rowspan }}" class="text-end">
                        {{ number_format($po->nominal_spk,0,',','.') }}
                    </td>
                    @endif

                    <td>{{ $termin['keterangan'] }}</td>

                    <td class="text-end">
                        {{ number_format($termin['nominal'],0,',','.') }}
                    </td>

                </tr>

                @endforeach

            </tbody>

            @endforeach

            <tbody>
                <tr class="total-row">
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-end">
                        {{ number_format($totalSpk,0,',','.') }}
                    </td>
                    <td></td>
                    <td class="text-end">
                        {{ number_format($totalTermin,0,',','.') }}
                    </td>
                </tr>
            </tbody>

        </table>

    </div>

    @endforeach

@else

    @php
        $no = 1;
        $totalSpk = 0;
        $totalTermin = 0;
    @endphp

    <div class="year-section">

        <div class="year-title">Tahun {{ $tahun }}</div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Lokasi</th>
                    <th>Pekerjaan</th>
                    <th>Nominal SPK</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                </tr>
            </thead>

            @foreach($data as $po)

            @php
                $rowspan = max($po->termin_list->count(), 1);
                $totalSpk += $po->nominal_spk;
            @endphp

            <tbody class="po-group">

                @foreach($po->termin_list as $index => $termin)

                @php
                    $totalTermin += $termin['nominal'];
                @endphp

                <tr>

                    @if($index == 0)
                    <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>

                    <td rowspan="{{ $rowspan }}">
                        {{ \Carbon\Carbon::parse($po->tgl_po)->format('d-m-Y') }}
                    </td>

                    <td rowspan="{{ $rowspan }}">
                        {{ $po->customer->nama_perusahaan ?? '-' }}
                    </td>

                    <td rowspan="{{ $rowspan }}">
                        {{ \Illuminate\Support\Str::title($po->quotation?->kabupaten?->nama ?? '-') }}
                    </td>

                    <td rowspan="{{ $rowspan }}">
                        {{ $po->all_produk->implode(', ') }}
                    </td>

                    <td rowspan="{{ $rowspan }}" class="text-end">
                        {{ number_format($po->nominal_spk,0,',','.') }}
                    </td>
                    @endif

                    <td>{{ $termin['keterangan'] }}</td>

                    <td class="text-end">
                        {{ number_format($termin['nominal'],0,',','.') }}
                    </td>

                </tr>

                @endforeach

            </tbody>

            @endforeach

            <tbody>
                <tr class="total-row">
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-end">
                        {{ number_format($totalSpk,0,',','.') }}
                    </td>
                    <td></td>
                    <td class="text-end">
                        {{ number_format($totalTermin,0,',','.') }}
                    </td>
                </tr>
            </tbody>

        </table>

    </div>

@endif

</body>
</html>