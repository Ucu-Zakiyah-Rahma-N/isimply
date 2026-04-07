<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .logo {
            height: 60px;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 15px;
        }

        .info table {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f3f4f6;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        .text-right {
            text-align: right;
        }

        .total {
            margin-top: 10px;
            width: 40%;
            float: right;
        }

        .signature {
            margin-top: 60px;
            width: 100%;
        }

        .signature div {
            width: 30%;
            display: inline-block;
            text-align: center;
        }

        .status {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .approved {
            background: #d1fae5;
            color: #065f46;
        }

        .waiting {
            background: #fef3c7;
            color: #92400e;
        }

        .pending {
            background: #e0f2fe;
            color: #075985;
        }

        .reject {
            background: #fee2e2;
            color: #991b1b;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    @foreach($data as $row)

    <!-- HEADER -->
    <div class="header">
        <div>
            <img src="{{ public_path('logo.png') }}" class="logo">
        </div>
        <div>
            <b>PT. Isimply Dimensi Indonesia</b><br>
            Karawang, Indonesia
        </div>
    </div>

    <div class="title">
        LAPORAN PENGAJUAN BIAYA
    </div>

    <!-- INFO -->
    <div class="info">
        <table>
            <tr>
                <td width="20%">Nomor</td>
                <td>: {{ $row['header']['nomor_pengajuan'] }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $row['header']['tgl_pengajuan'] }}</td>
            </tr>
            <tr>
                <td>Penerima</td>
                <td>: {{ $row['header']['kontak'] }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>:
                    @php
                    $status = $row['header']['status'] ?? 'waiting';
                    @endphp

                    <span class="status 
                        {{ $status == 'disetujui' ? 'approved' : 
                        ($status == 'ditolak' ? 'reject' : 
                        ($status == 'pending' ? 'pending' : 'waiting')) }}">
                        {{ strtoupper($status) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- TABLE ITEM -->
    <table>
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th width="10%">Qty</th>
                <th width="20%">Harga</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($row['items'] as $item)
            <tr>
                <td>{{ $item['deskripsi'] }}</td>
                <td class="text-right">{{ $item['qty'] }}</td>
                <td class="text-right">Rp {{ number_format($item['harga']) }}</td>
                <td class="text-right">Rp {{ number_format($item['jumlah']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTAL -->
    <table class="total">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp {{ number_format($row['header']['subtotal']) }}</td>
        </tr>
        <tr>
            <td>Diskon</td>
            <td class="text-right">Rp {{ number_format($row['header']['total_diskon']) }}</td>
        </tr>
        <tr>
            <td>Pajak</td>
            <td class="text-right">Rp {{ number_format($row['header']['total_pajak']) }}</td>
        </tr>
        <tr>
            <td><b>Grand Total</b></td>
            <td class="text-right"><b>Rp {{ number_format($row['header']['grand_total']) }}</b></td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <!-- SIGNATURE -->
    <div class="signature">
        <div>
            Dibuat Oleh<br><br><br>
            ___________________
        </div>
        <div>
            Diperiksa<br><br><br>
            ___________________
        </div>
        <div>
            Disetujui<br><br><br>
            ___________________
        </div>
    </div>

    <div class="page-break"></div>

    @endforeach

</body>

</html>