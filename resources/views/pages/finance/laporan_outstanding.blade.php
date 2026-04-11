@extends('app.template')

@section('content')
<style>
    .table td,
    .table th {
        border: 1px solid #cec8c8 !important;
    }
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
                    <li class="list-group-item">
                        <div class="d-flex justify-content-center gap-3">
                            
                            <span style="min-width:100px; text-align:left;">
                                <b>{{ $tahun }}</b>
                            </span>

                            <span style="min-width:20px;" class="fw-bold text-danger">
                                Rp
                            </span>

                            <span style="min-width:100px; text-align:left;" class="fw-bold text-danger">
                                {{ number_format($total, 0, ',', '.') }}
                            </span>

                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">

            <form method="GET" class="d-flex">
                <div style="width:150px;">
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
            </form>

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

<!--    <div class="mb-3">-->
<!--    <a href="{{ route('finance.outstanding.pdf', ['tahun' => $tahunDipilih]) }}" -->
<!--       class="btn btn-danger btn-sm">-->
<!--        <i class="bi bi-file-earmark-pdf"></i> Download PDF-->
<!--    </a>-->
<!--</div>-->

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
        
       
        
            <div class="table-responsive">
                <table class="table table-bordered table-sm" border="1" id="myTable">
                    <thead class="table-secondary text-center align-middle">
                        <tr>
                            <th>No</th>
                            <th style="min-width:120px;">Tanggal PO</th>
                            <th style="min-width:250px;">Nama Customer</th>
                            <th style="min-width:170px;">Lokasi</th>
                            <th style="min-width:300px;">Nama Pekerjaan</th>
                            <th style="min-width:150px;">Nominal SPK</th>
                            <th style="min-width:120px;">Termin</th>
                            <th style="min-width:150px;">Nominal</th>
                            <th style="min-width:150px;">Keterangan</th>

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