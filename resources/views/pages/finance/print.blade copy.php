<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Faktur - {{ $invoice->no_invoice }}</title>

    <style>
        @page { size: A4; margin: 8mm 12mm 12mm 12mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin:0; padding:0; color:#000; }

        .company-info { font-size:11px; line-height:1.4; margin-top:5px; }

        table.data { width:100%; border-collapse:collapse; margin-top:10px; table-layout:fixed; border:1px solid #000; }
        table.data thead th { border:1px solid #000; padding:6px; font-size:11px; text-align:center; }
        table.data tbody td { border-left:1px solid #000; border-right:1px solid #000; border-top:none; border-bottom:none; padding:6px; font-size:11px; vertical-align:top; }
        table.data tbody tr:last-child td { border-bottom:1px solid #000; }

        .text-right { text-align:right; }
        .text-center { text-align:center; }

        .summary td { padding:4px 6px; font-size:11px; }
        .summary .total { font-weight:bold; border-top:1px solid #000; }

        table tr td { padding-top:2px !important; padding-bottom:2px !important; }
        .summary-inner td { padding:2px 0 !important; line-height:1.2 !important; }

        .signature { margin-top:50px; text-align:center; font-size:12px; }
        .signature-name { margin-top:60px; text-decoration:underline; font-weight:bold; }
        .signature-title { margin:0; }

    </style>
</head>

<body>


    {{-- TITLE INVOICE --}}
{{-- ================= HEADER ================= --}}
<div style="width:100%; margin-bottom:10px;">
    
    {{-- TITLE INVOICE di tengah atas --}}
    <div style="text-align:center; font-size:24px; font-weight:bold; letter-spacing:1px; margin-bottom:10px;">
        INVOICE
    </div>

    <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
        {{-- LOGO + COMPANY INFO --}}
        <div style="display:flex; align-items:flex-start;">
            <div>
                <img src="{{ $logo }}" width="70">
            </div>
            <div class="company-info" style="margin-left:10px;">
                <strong>PT SIMPLY DIMENSI INDONESIA</strong><br>
                Jl. Jakarta No. 13 A Kelurahan Karangpawitan<br>
                Karawang Barat, Kabupaten Karawang, Jawa Barat<br>
                Telp: 0267-8407776<br>
                Email: simplydimensiindonesia@gmail.com
            </div>
        </div>

        {{-- INFO INVOICE KANAN --}}
        <div style="text-align:right; font-size:12px;">
            <table>
                <tr>
                    <td style="width:110px;">No</td>
                    <td style="width:10px;">:</td>
                    <td>{{ $invoice->no_invoice }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->tgl_inv)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>PIC Keuangan</td>
                    <td>:</td>
                    <td>{{ $invoice->po->nama_pic_keuangan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Page</td>
                    <td>:</td>
                    <td>
                        <script type="text/php">
                            if (isset($pdf)) { echo $PAGE_NUM . " / " . $PAGE_COUNT; }
                        </script>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

    {{-- TO CUSTOMER --}}
    <div style="margin-top:10px; font-size:12px;">
        <strong>To:</strong><br>
        <strong>{{ strtoupper($invoice->customer->nama_perusahaan) }}</strong><br>
        {{ $invoice->customer->detail_alamat ?? '-' }},
        {{ $invoice->po->quotation->kawasan_industri->nama_kawasan ?? '-' }},
        {{ isset($invoice->po->quotation->kabupaten->nama) ? ucwords(strtolower($invoice->po->quotation->kabupaten->nama)) : '-' }},
        {{ isset($invoice->po->quotation->provinsi->nama) ? ucwords(strtolower($invoice->po->quotation->provinsi->nama)) : '-' }}
    </div>

    {{-- ================= ITEM TABLE ================= --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:40%">KETERANGAN</th>
                <th style="width:15%">HARGA SATUAN (Rp)</th>
                <th style="width:15%">QTY</th>
                <th style="width:30%">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $maxRows = 10;
                $currentRows = $invoice->produk->count();
            @endphp

            @foreach ($invoice->produk as $item)
            <tr>
                <td>
                    {{ $item->perizinan_id ? $item->perizinan->jenis ?? '-' : $item->perizinan_lainnya ?? '-' }}
                </td>
                <td class="text-right">{{ number_format($item->harga_satuan,0,',','.') }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->qty * $item->harga_satuan,0,',','.') }}</td>
            </tr>
            @endforeach

            @for ($i = $currentRows; $i < $maxRows; $i++)
            <tr>
                <td style="height:25px;"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor
        </tbody>
    </table>

    {{-- NOTE & SIGNATURE --}}
    <div style="margin-top:25px; display:flex; justify-content:space-between;">
        <div style="width:55%; font-size:12px;">
            <strong>Note:</strong>
            <ul style="margin-top:5px; padding-left:15px;">
                <li>PPh PT. Simply Dimensi Indonesia menggunakan tarif PPh Final Pasal 4 ayat (2) - 3,5% (Jasa Konsultan Konstruksi)</li>
                <li>
                    <strong>Account Payment:</strong><br>
                    Bank Mandiri<br>
                    No. Rekening 1730012944519<br>
                    a.n PT. Simply Dimensi Indonesia
                </li>
                <li>Pembayaran dilakukan paling lambat 14 hari setelah dokumen invoice diserahkan.</li>
            </ul>
        </div>

        {{-- SIGNATURE --}}
        <div class="signature">
            <p><strong>Issued by Signature</strong></p>
            <p><strong>PT Simply Dimensi Indonesia</strong></p>
            <div class="signature-name">Melasari Nugraha</div>
            <p class="signature-title">Staff Accounting</p>
        </div>
    </div>

</body>
</html>