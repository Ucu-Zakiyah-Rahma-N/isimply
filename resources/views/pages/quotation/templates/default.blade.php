@extends('app.template')

@section('content')

<style>
.page {
    width: 210mm;
    min-height: 297mm;
    padding: 70px 80px;
    margin: auto;
    background-image: url('{{ asset("assets/images/template_SPH.png") }}');
    background-size: 100% 100%;
    background-repeat: no-repeat;
    background-position: center top;
    font-size: 14px;
    line-height: 1.7;
}
</style>

@php
function terbilang($angka) {
    $angka = abs($angka);
    $baca = ["", "Satu", "Dua", "Tiga", "Empat", "Lima",
        "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
    if ($angka < 12) return $baca[$angka];
    elseif ($angka < 20) return terbilang($angka-10)." Belas";
    elseif ($angka < 100) return terbilang(floor($angka/10))." Puluh ".terbilang($angka%10);
    elseif ($angka < 200) return "Seratus ".terbilang($angka-100);
    elseif ($angka < 1000) return terbilang(floor($angka/100))." Ratus ".terbilang($angka%100);
    elseif ($angka < 2000) return "Seribu ".terbilang($angka-1000);
    elseif ($angka < 1000000) return terbilang(floor($angka/1000))." Ribu ".terbilang($angka%1000);
    elseif ($angka < 1000000000) return terbilang(floor($angka/1000000))." Juta ".terbilang($angka%1000000);
    return "";
}
@endphp

<div class="container-fluid">
    <div class="card shadow">

        {{-- PAGE 1 --}}
        <div class="page">
            <div class="text-end">
                Karawang, {{ \Carbon\Carbon::parse($quotation->tgl_sph)->translatedFormat('d F Y') }}
            </div>

            <table style="width:100%; margin-top:15px;">
                <tr><td style="width:120px;">Nomor</td><td>:</td><td>{{ $quotation->no_sph ?? '-' }}</td></tr>
                <tr><td>Lampiran</td><td>:</td><td>1 (Satu) berkas</td></tr>
                <tr><td>Perihal</td><td>:</td><td>Penawaran Kerjasama {{ ($quotation->perizinan->pluck('jenis')->implode(', ')) }}</td></tr>
            </table>

            <div class="mt-4">
                <strong>Kepada Yth,</strong><br>
                {{ strtoupper($quotation->customer->nama_perusahaan) }}<br>
                {{ $quotation->detail_alamat }}
                <br><br>
                Dengan hormat,<br>
                Sehubungan dengan adanya permintaan untuk pekerjaan pengkajian teknis bangunan guna
                Pengurusan {{ ($quotation->perizinan->pluck('jenis')->implode(', ')) }} untuk:
            </div>

            {{-- DATA BANGUNAN --}}
            <table class="table table-bordered table-sm bg-white mt-3">
                <tr><th width="35%">Nama Bangunan</th><td>{{ $quotation->nama_bangunan }}</td></tr>
                <tr><th>Fungsi Bangunan</th><td>{{ $quotation->fungsi_bangunan ?? '-' }}</td></tr>
                <tr><th>Lokasi</th><td>{{ $quotation->detail_alamat }}, {{ $quotation->kabupaten->nama ?? '' }}, {{ $quotation->provinsi->nama ?? '' }}</td></tr>
                <tr>
                    <th>Luas Bangunan</th>
                    <td>
                        @php
                            $luas = [];
                            if($quotation->luas_slf) $luas[] = "SLF: " . number_format($quotation->luas_slf,0,',','.') . " m²";
                            if($quotation->luas_pbg) $luas[] = "PBG: " . number_format($quotation->luas_pbg,0,',','.') . " m²";
                            if($quotation->luas_shgb) $luas[] = "SHGB: " . number_format($quotation->luas_shgb,0,',','.') . " m²";
                        @endphp
                        {{ count($luas) ? implode(', ', $luas) : '-' }}
                    </td>
                </tr>
            </table>

            {{-- RINCIAN BIAYA --}}
            <div class="mt-4">
                @php
                    $totalHarga  = $quotation->total_harga;
                    $totalDiskon = $quotation->total_diskon;
                    $grandTotal  = $quotation->grand_total;
                    $hasDiskon   = $totalDiskon > 0;
                    $jenisText   = ($quotation->perizinan->pluck('jenis')->implode(', '));
                @endphp

                <p>● Pekerjaan Pengurusan {{ $jenisText }} sebesar <strong>Rp {{ number_format($totalHarga,0,',','.') }}</strong> ({{ terbilang($totalHarga) }} Rupiah).</p>

                @if($hasDiskon)
                <p class="fw-bold">● Diskon sebesar <strong>Rp {{ number_format($totalDiskon,0,',','.') }}</strong></p>
                <p class="fw-bold">● Total biaya setelah diskon <strong>Rp {{ number_format($grandTotal,0,',','.') }}</strong> ({{ terbilang($grandTotal) }} Rupiah)</p>
                @else
                <p class="fw-bold">● Total biaya pekerjaan sebesar <strong>Rp {{ number_format($totalHarga,0,',','.') }}</strong> ({{ terbilang($totalHarga) }} Rupiah)</p>
                @endif

                <!--{{-- Tabel rincian untuk harga satuan --}}-->
                <!--@if($quotation->harga_tipe === 'satuan')-->
                <!--<h6 class="fw-bold mt-3">Rincian Jenis Perizinan</h6>-->
                <!--<table class="table table-sm table-bordered w-100 bg-white">-->
                <!--    <thead>-->
                <!--        <tr>-->
                <!--            <th width="70%">Jenis Perizinan</th>-->
                <!--            <th class="text-end">Harga Satuan (Rp)</th>-->
                <!--        </tr>-->
                <!--    </thead>-->
                <!--    <tbody>-->
                <!--        @foreach($quotation->perizinan as $izin)-->
                <!--        <tr>-->
                <!--            <td>{{ $izin->jenis }}</td>-->
                <!--            <td class="text-end">{{ number_format($izin->pivot->harga_satuan,0,',','.') }}</td>-->
                <!--        </tr>-->
                <!--        @endforeach-->
                <!--        <tr>-->
                <!--            <td><strong>Total</strong></td>-->
                <!--            <td class="text-end"><strong>Rp {{ number_format($totalHarga,0,',','.') }}</strong></td>-->
                <!--        </tr>-->
                <!--        @if($hasDiskon)-->
                <!--        <tr>-->
                <!--            <td><strong>Diskon</strong></td>-->
                <!--            <td class="text-end"><strong>Rp {{ number_format($totalDiskon,0,',','.') }}</strong></td>-->
                <!--        </tr>-->
                <!--        <tr>-->
                <!--            <td><strong>Total Setelah Diskon</strong></td>-->
                <!--            <td class="text-end"><strong>Rp {{ number_format($grandTotal,0,',','.') }}</strong></td>-->
                <!--        </tr>-->
                <!--        @endif-->
                <!--    </tbody>-->
                <!--</table>-->
                <!--@endif-->

                <p class="mt-2">● Harga tersebut belum termasuk PPN.<br>● Tidak termasuk kekurangan dokumen dari perusahaan.</p>
            </div>

            {{-- PENUTUP --}}
            <div class="mt-4">
                Demikian kami sampaikan surat penawaran harga ini.<br>
                Atas perhatian dan kerjasamanya, kami ucapkan banyak terima kasih.<br>
                Hormat Kami, <br>
                <strong>PT. Simply Dimensi Indonesia</strong>
                    <div style="position: relative; width: 260px; height: 150px; margin-top: 5px;">

        <!-- TTD -->
        <img src="{{ asset('assets/images/ttd_pak_jejen.png') }}"
             style="position: absolute; left: 0; top: -10px; width: 110px; z-index: 3;">

        <!-- STEMPL -->
        <img src="{{ asset('assets/images/stempel_simply.png') }}"
             style="position: absolute; left: 55px; top: 10px; width: 85px; opacity: 0.8; z-index: 2;">
    </div>

    <div style="margin-top: -69px;">
        <strong>Jaenudin, ST</strong> <br>
        Direktur
    </div>
    </div>
        </div> {{-- END PAGE 1 --}}

<br>
        {{-- PAGE 2 LAMPIRAN --}}
        <div class="page">
            <h5 class="fw-bold">LAMPIRAN I</h5>
            <h6 class="fw-bold mb-3">PENJELASAN PEKERJAAN {{ strtoupper($quotation->perizinan->pluck('jenis')->implode(', ')) }}</h6>

            <strong>I. BIAYA PEKERJAAN</strong>
            <p>● Biaya jasa sebesar <strong>Rp {{ number_format($totalHarga,0,',','.') }}</strong> ({{ terbilang($totalHarga) }} Rupiah).</p>

            @if($quotation->harga_tipe === 'satuan')
            <table class="table table-sm table-bordered bg-white w-100 mb-3">
                <thead class="table-light">
                    <tr><th width="70%">Jenis Perizinan</th><th class="text-end">Harga Satuan (Rp)</th></tr>
                </thead>
                <tbody>
                    @foreach($quotation->perizinan as $izin)
                    <tr><td>{{ $izin->jenis }}</td><td class="text-end">{{ number_format($izin->pivot->harga_satuan,0,',','.') }}</td></tr>
                    @endforeach
                    <tr><td><strong>Total</strong></td><td class="text-end"><strong>Rp {{ number_format($totalHarga,0,',','.') }}</strong></td></tr>
                    @if($hasDiskon)
                    <tr><td><strong>Diskon</strong></td><td class="text-end"><strong>Rp {{ number_format($totalDiskon,0,',','.') }}</strong></td></tr>
                    <tr><td><strong>Total Setelah Diskon</strong></td><td class="text-end"><strong>Rp {{ number_format($grandTotal,0,',','.') }}</strong></td></tr>
                    @endif
                </tbody>
            </table>
            @endif

            <strong>III. TERMIN PEMBAYARAN</strong>
            <ul>
                @foreach(json_decode($quotation->termin_persentase ?? '[]') as $row)
                <li>Termin {{ $row->urutan }} — {{ $row->persen }}%</li>
                @endforeach
            </ul>

            <strong>IV. JANGKA WAKTU</strong>
            <p>Jangka waktu Pengurusan {{ ($quotation->perizinan->pluck('jenis')->implode(', ')) }} : {{ $quotation->lama_pekerjaan }} Hari</p>

            <strong>V. PEMBAYARAN</strong>
            <p>
                Bank Mandiri — 173-00-1294451-9 (Simply Dimensi Indon)<br>
                Bank BCA — 109-2625138 (Simply Dimensi Indonesia PT)
            </p>
        </div> {{-- END PAGE 2 --}}
    </div>
</div>

@endsection
