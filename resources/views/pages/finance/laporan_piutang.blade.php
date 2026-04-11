@extends('app.template')

@section('content')
<style>
    .table td,
    .table th {
        border: 1px solid #cec8c8 !important;
    }
</style>
<div class="container">

    <h4 class="mb-4">Laporan Piutang</h4>

    {{-- Summary --}}
<div class="row mb-3">

    {{-- CARD UTAMA --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-90">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center toggle-header"
                     data-bs-toggle="collapse"
                     data-bs-target="#detailPiutang"
                     aria-expanded="false">

                    <div>
                        <h5 class="mb-1">Data Piutang</h5>
                        <h3 class="text-danger mb-0">
                            Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <i class="bi bi-chevron-right fs-3 transition" id="iconPiutang"></i>
                </div>

            </div>
        </div>
    </div>

    {{-- DETAIL (MUNCUL SEJAJAR DI KANAN) --}}
    <div class="col-md-8">
        <div class="collapse" id="detailPiutang">
            <div class="row">

                <div class="col-md-6">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Bulan Ini</h6>
                            <h5 class="fw-bold text-primary">
                                Rp {{ number_format($piutangBulanIni, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-md-6">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">30 Hari Mendatang</h6>
                            <h5 class="fw-bold text-warning">
                                Rp {{ number_format($piutang30Hari, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div> -->

            </div>
        </div>
    </div>

</div>

    {{-- Table --}}
    <div class="card">

        <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
          <div></div>
            <div style="position: relative;">
                <button onclick="toggleColumnMenu()" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2">
                    Pilih Kolom Hide
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div id="columnMenu"
                    style="display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid #ccc; padding:10px; z-index:999;">
                </div>
            </div>
        </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0" border="1" id="myTable"">
                <!-- <table class="table table-bordered table-sm text-nowrap"> -->
                    <thead class="table-secondary text-center align-middle">
                        <tr>
                            <th>No</th>
                            <th style="min-width:120px;">Tanggal Invoice</th>
                            <th style="min-width:250px;">Nama Customer</th>
                            <th style="min-width:160px;">No Invoice</th>
                            <th style="min-width:190px;">No SPK</th>
                            <th style="min-width:300px;">Nama Pekerjaan</th>
                            <th style="min-width:120px;">Termin</th>
                            <th style="min-width:150px;">Nominal SPK</th>
                            <th style="min-width:120px;">PPN</th>
                            <th style="min-width:120px;">PPh</th>
                            <th style="min-width:150px;">Sisa Tagihan</th>
                            <th style="min-width:80px;">Waktu Invoice</th>
                            <th style="min-width:150px;">Selisih
                            </th>         
                        </tr>
                    </thead>
                    @php
                    $sumNominalSpk = 0;
                    $sumTotalTagihan = 0;
                    $sumSisaTagihan = 0;

                    @endphp

                    <tbody>
                        @forelse($data as $i => $row)

                        @php
                        $nominalSpk = $row->total_after_diskon_inv > 0
                        ? $row->total_after_diskon_inv
                        : $row->nominal_invoice;

                        $ppn = $row->ppn ?? 0;
                        $totalTagihan = $nominalSpk + $ppn;

                        $sumNominalSpk += $nominalSpk;
                        $sumTotalTagihan += $totalTagihan;
             
                        @endphp

                        <tr @if(\Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($row->tgl_jatuh_tempo))) 
                                style="background-color:#ffe5e5;" 
                            @endif>
                            <td class="text-center">{{ $i+1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_inv)->format('d-m-Y') }}</td>
                            <!-- <td>{{ \Carbon\Carbon::parse($row->tgl_jatuh_tempo)->format('d-m-Y') }}</td> -->
                           
                            <td>{{ $row->customer->nama_perusahaan ?? '-' }}</td>
                            <td>{{ $row->no_invoice }}</td>
                            <td>{{ $row->po->no_po ?? '-' }}</td>

                            <!-- <td>
                                @if ($row->produk->isNotEmpty())
                                @foreach ($row->produk as $item)
                                <span class="badge text-dark border me-1">
                                    {{ $item->perizinan?->jenis ?? $item->perizinan_lainnya ?? '-' }}
                                </span>
                                @endforeach
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td> -->

                            <td>
                                @if ($row->produk->isNotEmpty())
                                    @php
                                        $first = $row->produk->first();
                                        $nama = $first->perizinan?->jenis ?? $first->perizinan_lainnya ?? '-';
                                        $count = $row->produk->count();
                                    @endphp

                                    {{ $nama }}@if($count > 1), dll @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $row->keterangan }}</td>

                            <td class="text-end fw-bold">
                                {{ number_format($nominalSpk, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-bold">
                                {{ $ppn > 0 ? number_format($ppn, 0, ',', '.') : '-' }}
                            </td>

                            <td>    
                                {{ $row->nilai_pph > 0 ? number_format($row->nilai_pph,0,',','.') : '-' }}
                            </td>

                            <td class="text-end fw-bold">
                                @php
                                    $totalBayar = $row->payments->sum(fn($p) => $p->nominal + $p->nilai_pph);
                                    $sisa = $totalTagihan - $totalBayar;
                                    $sumSisaTagihan += max($sisa, 0);
                                @endphp

                                {{ number_format(max($sisa, 0), 0, ',', '.') }}
                                <!-- Rp {{ number_format($totalTagihan, 0, ',', '.') }} -->
                            </td>

                             @php
                                $today = \Carbon\Carbon::today();
                                $tglInv = \Carbon\Carbon::parse($row->tgl_inv);

                                // 1️⃣ LAMA (selalu tampil)
                                $umur = $tglInv->diffInDays($today);

                                // 2️⃣ JATUH TEMPO
                                $tglTempo = \Carbon\Carbon::parse($row->tgl_jatuh_tempo);

                                // cek apakah sudah lewat
                                $isOverdue = $today->gt($tglTempo);

                                // hitung selisih hanya kalau overdue
                                $selisih = $isOverdue ? $tglTempo->diffInDays($today) : 0;
                            @endphp

                            {{-- LAMA waktu dari tgl invoice ke hari ini --}}
                            <td class="text-center">
                            {{ round($umur) }} hari
                            </td>

                            {{-- SELISIH (HANYA JIKA TERLAMBAT) dari tgl jatuh tempo invoice ke hari ini --}}
                            <td>
                                @if($isOverdue)
                                    <span style="color:red; font-weight:bold;">
                                        Terlambat {{ round($selisih) }} hari
                                    </span>
                                @endif
                            </td>

                            <!-- <td>
                                @if($row->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                                @elseif($row->status === 'posted')
                                <span class="badge bg-warning text-dark">Belum Lunas</span>
                                @endif
                            </td> -->
                        </tr>

                        @empty
                        <tr>
                            <td colspan="13" class="text-center">
                                Tidak ada data
                            </td>
                        </tr>
                        @endforelse

                        {{-- BARIS TOTAL --}}
                        @if($data->count() > 0)
                        <tr class="table-secondary fw-bold">
                            <td colspan="7" class="text-end">TOTAL</td>

                            <td class="text-end">
                                {{ number_format($sumNominalSpk, 0, ',', '.') }}
                            </td>

                            <td></td>
                            <td></td>

                            <td class="text-end">
                                
                                {{ number_format($sumSisaTagihan, 0, ',', '.') }}
                            </td>
                            <td></td>
                            <td></td>

                        </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<script>
document.getElementById('detailPiutang')
    .addEventListener('show.bs.collapse', function () {
        document.getElementById('iconPiutang')
            .classList.replace('bi-chevron-right', 'bi-chevron-left');
    });

document.getElementById('detailPiutang')
    .addEventListener('hide.bs.collapse', function () {
        document.getElementById('iconPiutang')
            .classList.replace('bi-chevron-left', 'bi-chevron-right');
    });



    //fitur hide header
    const menu = document.getElementById("columnMenu");

    // reset dulu biar ga dobel
    menu.innerHTML = '';

    document.querySelectorAll("#myTable thead th").forEach((th, index) => {

        const label = th.cloneNode(true).childNodes[0].textContent.trim();

        const item = document.createElement("div");

        item.innerHTML = `
        <label style="cursor:pointer;">
            <input type="checkbox" checked data-col="${index}">
            ${label}
        </label>
    `;

        menu.appendChild(item);
    });

    // event listener checkbox
    menu.addEventListener("change", function(e) {
        if (e.target.type === "checkbox") {
            toggleCol(e.target.dataset.col);
        }
    });

    function toggleCol(colIndex) {
        const table = document.getElementById("myTable");
        const rows = table.rows;

        let hiddenCols = JSON.parse(localStorage.getItem("hiddenCols")) || [];

        const isHidden = rows[0].cells[colIndex].style.display === 'none';

        for (let i = 0; i < rows.length; i++) {
            let cell = rows[i].cells[colIndex];
            if (cell) cell.style.display = isHidden ? '' : 'none';
        }

        // update storage
        if (isHidden) {
            hiddenCols = hiddenCols.filter(c => c != colIndex);
        } else {
            hiddenCols.push(colIndex);
        }

        localStorage.setItem("hiddenCols", JSON.stringify(hiddenCols));
    }


    function toggleColumnMenu() {
        const menu = document.getElementById("columnMenu");
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }


    //close plihan hide di luar  
    document.addEventListener("click", function(e) {
        const menu = document.getElementById("columnMenu");
        const button = document.querySelector("button[onclick='toggleColumnMenu()']");

        if (!menu.contains(e.target) && !button.contains(e.target)) {
            menu.style.display = 'none';
        }
    });
    menu.addEventListener("click", function(e) {
        e.stopPropagation();
    });



    //simpan hide ketika refresh
    window.addEventListener("load", function() {
        const hiddenCols = JSON.parse(localStorage.getItem("hiddenCols")) || [];

        hiddenCols.forEach(colIndex => {
            const table = document.getElementById("myTable");
            const rows = table.rows;

            for (let i = 0; i < rows.length; i++) {
                let cell = rows[i].cells[colIndex];
                if (cell) cell.style.display = 'none';
            }

            // uncheck checkbox juga
            const checkbox = document.querySelector(`input[data-col='${colIndex}']`);
            if (checkbox) checkbox.checked = false;
        });
    });
    //load saat halaam di buka
    window.addEventListener("load", function() {
        const hiddenCols = JSON.parse(localStorage.getItem("hiddenCols")) || [];

        hiddenCols.forEach(colIndex => {
            const table = document.getElementById("myTable");
            const rows = table.rows;

            for (let i = 0; i < rows.length; i++) {
                let cell = rows[i].cells[colIndex];
                if (cell) cell.style.display = 'none';
            }

            // uncheck checkbox juga
            const checkbox = document.querySelector(`input[data-col='${colIndex}']`);
            if (checkbox) checkbox.checked = false;
        });
    });

</script>
@endsection