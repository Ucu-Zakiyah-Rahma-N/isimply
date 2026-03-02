<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Faktur - {{ $invoice->no_invoice }}</title>

    <style>
        @page {
            size: A4;
            margin: 8mm 12mm 12mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ================= TITLE ================= */
        .title-main {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* ================= HEADER ================= */
        .company-info {
            font-size: 11px;
            line-height: 1.4;
        }

        /* ================= TABLE ITEM ================= */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            border: 1px solid #000;
            /* border luar */
        }

        /* HEADER */
        table.data thead th {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
            text-align: center;
        }

        /* BODY */
        table.data tbody td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-top: none;
            border-bottom: none;
            /* hilangkan horizontal */
            padding: 6px;
            font-size: 11px;
            vertical-align: top;
        }

        /* garis bawah terakhir saja */
        table.data tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* ================= SUMMARY ================= */
        .summary {
            width: 40%;
            margin-left: auto;
            margin-top: 15px;
        }

        .summary td {
            padding: 4px 6px;
            font-size: 11px;
        }

        .summary .total {
            font-weight: bold;
            border-top: 1px solid #000;
        }
        table tr td {
    padding-top: 2px !important;
    padding-bottom: 2px !important;
}

.summary-inner td {
    padding: 2px 0 !important;
    line-height: 1.2 !important;
}

        
    </style>
</head>

<body>

    {{-- ================= HEADER WRAPPER ================= --}}
    <div style="position:relative; width:100%; margin-top:-15px;">

        {{-- LOGO --}}
        <div style="position:absolute; left:0; top:0;">
            <img src="{{ $logo }}" width="70">
        </div>

        {{-- TITLE --}}
        <div style="text-align:center; font-size:20px; font-weight:bold; letter-spacing:1px;">
            INVOICE
        </div>

        {{-- ================= HEADER CONTENT ================= --}}
        <table width="100%" style="margin-top:10px;">
            <tr>

                {{-- KIRI --}}
                <td width="60%" valign="top" style="padding-top:40px;">
                    <div style="margin-left:0; margin-top:5px;">
                        <strong>PT SIMPLY DIMENSI INDONESIA</strong><br>
                        Jl. Jakarta No. 13 A Kelurahan Karangpawitan<br>
                        Karawang Barat, Kabupaten Karawang, Jawa Barat<br>
                        Telp: 0267-8407776<br>
                        Email: simplydimensiindonesia@gmail.com
                    </div>
                </td>

                {{-- KANAN --}}
                <td width="40%" valign="top" align="right" style="padding-top:25px;">
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
                                if (isset($pdf)) {
                                    echo $PAGE_NUM . " / " . $PAGE_COUNT;
                                }
                            </script>
                            </td>
                        </tr>
                    </table>
                </td>

            </tr>
        </table>

        <table>
            <div style="margin-top:10px; font-size:12px;"> <strong>To:</strong><br>
                <strong>{{ strtoupper($invoice->customer->nama_perusahaan) }}</strong><br>
                {{ $invoice->customer->detail_alamat ?? '-' }},
                {{ $invoice->po->quotation->kawasan_industri->nama_kawasan ?? '-' }},
                {{ isset($invoice->po->quotation->kabupaten->nama) ? ucwords(strtolower($invoice->po->quotation->kabupaten->nama)) : '-' }},
                {{ isset($invoice->po->quotation->provinsi->nama) ? ucwords(strtolower($invoice->po->quotation->provinsi->nama)) : '-' }}

        </table>

        {{-- ================= ITEM ================= --}}
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
                    $maxRows = 10; // atur supaya pas 1 halaman A4
                    $currentRows = $invoice->produk->count();
                @endphp

                {{-- Produk --}}
                @foreach ($invoice->produk as $item)
                    <tr>
                    <td>
                        {{ $item->perizinan_id 
                            ? $item->perizinan->jenis ?? '-' 
                            : $item->perizinan_lainnya ?? '-' 
                        }}
                    </td>                        
                        <td class="text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format($item->qty * $item->harga_satuan, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- Baris kosong supaya tabel manjang --}}
                @for ($i = $currentRows; $i < $maxRows; $i++)
                    <tr>
                        <td style="height:25px;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor

                <tr>
                    <td colspan="4" style="border-top: 2px solid #000; padding: 0;"></td>
                </tr>
                {{-- Remarks --}}
                <tr>
                    <td colspan="4">
                        <strong>Remarks:</strong> {{ $invoice->remarks ?? '-' }}
                    </td>
                </tr>

                {{-- Summary --}}
                <tr>
                    <td></td>
                    <td colspan="3">
                        <table width="100%" style="border:none; border-collapse:collapse; line-height:1.2;">
                            <tr>
                                <td style="border:none;">Subtotal</td>
                                <td style="border:none; text-align:right;">
                                    Rp {{ number_format($calc['subtotal'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="3">
                        <table width="100%" style="border:none; border-collapse:collapse; line-height:1.2;">
                            <tr>
                                <td style="border:none;">
                                    Nominal Invoice ({{ $invoice->persentase_termin }}%)
                                </td>
                                <td style="border:none; text-align:right;">
                                    Rp {{ number_format($calc['nominalInvoice'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @if (!empty($calc['diskon']) && $calc['diskon'] > 0)
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <table width="100%" style="border:none; border-collapse:collapse; line-height:1.2;">
                                <tr>
                                    <td style="border:none;">
                                        <span>Diskon</span>
                                    </td>
                                    <td style="border:none; text-align:right;">
                                        <span>Rp {{ number_format($calc['diskon'], 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td></td>
                        <td colspan="3">
                            <table width="100%" style="border:none; border-collapse:collapse; line-height:1.2;">
                                <tr>
                                    <td style="border:none;">
                                        <span>Total After Diskon</span>
                                    </td>
                                    <td style="border:none; text-align:right;">
                                        <span>Rp {{ number_format($calc['totalAfterDiscount'], 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if (!empty($calc['ppn']) && $calc['ppn'] > 0)
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <table width="100%" style="border:none; border-collapse:collapse; line-height:1.2;">
                                <tr>
                                    <td style="border:none;">
                                        <span>PPN 11%</span>
                                    </td>
                                    <td style="border:none; text-align:right;">
                                        <span>Rp {{ number_format($calc['ppn'], 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if (!empty($calc['pph']) && $calc['pph'] > 0)
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <table width="100%" style="border:none; border-collapse:collapse; line-height:1.2;">
                                <tr>
                                    <td style="border:none;">
                                        <span>PPH 3,2%</span>
                                    </td>
                                    <td style="border:none; text-align:right;">
                                        <span>Rp {{ number_format($calc['pph'], 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                <tr>
                    <td></td>
                    <td colspan="3">
                        <table width="100%" style="border:none; border-collapse:collapse; line-height:1.2;">
                            <tr>
                                <td style="border:none;">
                                    <strong>TOTAL</strong>
                                </td>
                                <td style="border:none; text-align:right;">
                                    <strong>
                                        Rp {{ number_format($calc['totalAkhir'], 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>


        <div style="width:100%; margin-top:25px;">

            {{-- NOTE KIRI --}}
            <div style="width:55%; float:left; font-size:12px;">
                <strong>Note:</strong>
                <ul style="margin-top:5px; padding-left:15px;">
                    <li>
                        PPh PT. Simply Dimensi Indonesia menggunakan tarif
                        PPh Final Pasal 4 ayat (2) - 3,5% (Jasa Konsultan Konstruksi)
                    </li>
                    <li>
                        <strong>Account Payment:</strong><br>
                        Bank Mandiri<br>
                        No. Rekening 1730012944519<br>
                        a.n PT. Simply Dimensi Indonesia
                    </li>
                    <li>
                        Pembayaran dilakukan paling lambat 14 hari setelah dokumen invoice diserahkan.
                    </li>
                </ul>
            </div>

                {{-- SIGNATURE --}}
                <div style="margin-top:40px; text-align:center;">
                    <p><strong>Issued by Signature</strong></p>
                    <p><strong>PT Simply Dimensi Indonesia</strong></p>

                    <div style="height:80px;"></div>

                    <p style="margin:0; text-decoration:underline;">
                        Melasari Nugraha
                    </p>
                    <p style="margin:0;">Staff Accounting</p>
                </div>
            </div>

            <div style="clear:both;"></div>
        </div>

</body>

</html>
