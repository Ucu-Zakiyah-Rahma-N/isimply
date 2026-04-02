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
<h3>Laporan Outstanding Projek</h3>

@if($tahun == 'all')

<div style="margin-bottom:25px;">

    <table width="100%">
        <tr>

            {{-- KIRI --}}
            <td style="vertical-align:top;">

                <div style="font-size:14px; color:#666; margin-top:9px">
                    Data Outstanding Keseluruhan
                </div>

                <div style="font-size:26px; font-weight:bold; margin-top:17px;">
                    Rp {{ number_format($totalOutstandingKeseluruhan ?? 0, 0, ',', '.') }}
                </div>

            </td>

            {{-- KANAN --}}
            <td style="vertical-align:top; text-align:right;">

                @foreach($outstandingPerTahun as $year => $total)
                    <div style="border-bottom:1px solid #ddd; padding:6px 0;">

                        <span style="display:inline-block; width:60px; text-align:left; font-size:13px;">
                            {{ $year }}
                        </span>

                        <span style="display:inline-block; width:160px; text-align:right; font-size:13px; font-weight:bold;">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </span>

                    </div>
                @endforeach

            </td>

        </tr>
    </table>

</div>

    {{-- 🔹 TABEL DETAIL PER TAHUN (KODE KAMU, TIDAK DIUBAH) --}}
    @foreach($data as $year => $items)

    @php
        $no = 1;
        $totalSpk = 0;
        $totalTermin = 0;
    @endphp

    <div style="margin-bottom:25px;">

        <div style="font-weight:bold; margin-bottom:10px;">
            Tahun {{ $year }}
        </div>

        <table width="100%" border="1" cellspacing="0" cellpadding="5" style="border-collapse:collapse;">
            <thead style="background:#f5f5f5;">
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

            <tbody>

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

                    <td rowspan="{{ $rowspan }}" align="right">
                        {{ number_format($po->nominal_spk,0,',','.') }}
                    </td>
                    @endif

                    <td>{{ $termin['keterangan'] }}</td>

                    <td align="right">
                        {{ number_format($termin['nominal'],0,',','.') }}
                    </td>

                </tr>

                @endforeach

            </tbody>

            @endforeach

            <tbody>
                <tr>
                    <td colspan="5" align="right"><strong>TOTAL</strong></td>
                    <td align="right">
                        <strong>{{ number_format($totalSpk,0,',','.') }}</strong>
                    </td>
                    <td></td>
                    <td align="right">
                        <strong>{{ number_format($totalTermin,0,',','.') }}</strong>
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