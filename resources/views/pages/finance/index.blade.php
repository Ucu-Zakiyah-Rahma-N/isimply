@extends('app.template')

@section('content')
<style>
    .table td,
    .table th {
        border: 1px solid #cec8c8 !important;
    }

    /* .table tbody tr {
    border-bottom: 1.5px solid #aaaaaa;
} */
    th {
        position: relative;
    }


    th button {
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        margin-left: 5px;
        cursor: pointer;
        outline: none !important;
        box-shadow: none !important;
    }

    .toggle-btn {
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 12px;
    }

    #columnMenu {
        z-index: 999;
        min-width: 200px;
        max-height: 300px;
        overflow-y: auto;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
</style>
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Data BAST Finance</h5>
        </div>
    </div>

    <div class="card-body">
        
        <div class="d-flex justify-content-between align-items-center mb-3">

            <!-- KIRI -->
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

            <!-- KANAN -->
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

        <div class="card-body p-0">
            <div class="table-responsive" style="position: relative;">
                <table class="table table-bordered mb-0" border="1" id="myTable">
                    <thead class="table-secondary">
                        <tr class="text-center align-middle">
                            <th>No</th>
                            <th>Tgl PO</th>
                            <th>Kode Projek</th>
                            <th>Nama Customer</th>
                            <th>Lokasi</th>
                            <th>PIC Marketing</th>
                            <th>No PO</th>
                            <th>Nama Perizinan</th>
                            <th>Nominal SPK</th>
                            <th>Lama Pekerjaan</th>
                            <th class="border px-4 py-2">Lama Pengerjaan</th>
                            <th>Termin</th>
                            <th>Aksi</th>
                            <th>Status</th>
                            <th>Hold</th>
                            <th>File PO</th>
                            <th>Tgl BAST</th>


                            <!-- <th>Nama Bangunan</th> -->
                            <!-- <th>Detail Alamat</th> -->
                            <!-- <th>Luasan</th> -->
                            <!-- <th>PIC Keuangan</th>
                            <th>Kontak</th> -->
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($po as $po)
                        @php
                        $today = \Carbon\Carbon::today();
                        $tglPO = \Carbon\Carbon::parse($po->tgl_po);
                        $lama_pekerjaan = $po->quotation->lama_pekerjaan ?? 0;

                        $lama_sdh = $tglPO->diffInDays($today);

                        if($lama_sdh >= $lama_pekerjaan) {
                        $bg = 'bg-danger text-white';
                        } elseif($lama_sdh >= $lama_pekerjaan - 5) {
                        $bg = 'bg-warning text-white';
                        } else {
                        $bg = '';
                        }
                        @endphp

                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($po->tgl_po)->format('d-m-Y') }}</td>
                            <td>{{ $po->kode_project ?? '-'}}</td>
                            <td>{{ optional($po->customer)->nama_perusahaan ?? '-' }}</td>
                            <td>
                                {{ \Illuminate\Support\Str::title(strtolower($po->quotation->kabupaten->nama ?? '-')) }}
                            </td>
                            <td>{{ optional($po->customer->marketing)->nama ?? '-' }}</td>
                            <td>{{ $po->no_po ?? '-'}}</td>
                            <!-- <td>
                                    @if (!empty($po->jenis_perizinan))
                                        @foreach (explode(',', $po->jenis_perizinan) as $izin)
                                            <span class="text-dark border me-1">
                                                {{ trim($izin) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td> -->
                            <td>
                                @if (!empty($po->jenis_perizinan))
                                {{ collect(explode(',', $po->jenis_perizinan))
                                            ->map(fn($izin) => trim($izin))
                                            ->join(', ') }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                {{ number_format(optional($po->quotation)->grand_total ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                {{ optional($po->quotation)->lama_pekerjaan ? optional($po->quotation)->lama_pekerjaan . ' hari' : '-' }}
                            </td>
                            <!-- dari tgl po ke hari ini (selalu di hitung selama invoice blm lunas) -->
                            <td class="text-center {{ $bg }}">
                                {{ $lama_sdh }} hari
                            </td>

                            <td>
                                @if (!empty($po->quotation?->termin_persentase))
                                {{ collect($po->quotation->termin_persentase)
                                            ->pluck('persen')
                                            ->map(fn($p) => $p . '%')
                                            ->join(', ') }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">

                                @if ($po->sisa_termin > 0)
                                <a href="{{ route('finance.create', $po->id) }}"
                                    class="btn btn-sm btn-primary {{ $po->status_label === 'hold' ? 'disabled' : '' }}"
                                    {{ $po->status_label === 'hold' ? 'onclick=return false;' : '' }}>

                                    Buat Invoice ({{ $po->invoice_terbuat + 1 }}/{{ $po->total_termin }})
                                </a>
                                @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    Invoice Lengkap
                                </button>
                                @endif

                            </td>

                            <td>
                                <span class="badge 
                                        @if($po->status_label === 'ongoing') bg-primary
                                        @elseif($po->status_label === 'done') bg-success
                                        @elseif($po->status_label === 'hold') bg-warning
                                        @endif">
                                    {{ ucfirst($po->status_label) }}
                                </span>
                            </td>

                            <td>
                                @if($po->status_label === 'ongoing')
                                <form method="POST" action="{{ route('finance.hold.invoice', $po->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-warning">Hold</button>
                                </form>
                                @elseif($po->status_label === 'hold')
                                <form method="POST" action="{{ route('finance.unhold.invoice', $po->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Unhold</button>
                                </form>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($po->file_path)
                                <button class="btn btn-sm btn-danger"
                                    onclick="openPDFModal('{{ route('files.view', $po->file_path) }}')">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat
                                </button>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($po->bast_verified_at)->format('d-m-Y H:i') }}</td>

                            <!-- <td>{{ optional($po->quotation)->nama_bangunan ?? '-' }}</td> -->
                            <!-- <td>
                                    {{ collect([
                                        $po->quotation->detail_alamat ?? null,
                                        optional($po->quotation->kawasan_industri)->nama_kawasan,
                                        optional($po->quotation->kabupaten)->nama
                                            ? \Illuminate\Support\Str::title(strtolower($po->quotation->kabupaten->nama))
                                            : null,
                                        optional($po->quotation->provinsi)->nama
                                            ? \Illuminate\Support\Str::title(strtolower($po->quotation->provinsi->nama))
                                            : null,
                                    ])->filter()->implode(', ') }}
                                </td> -->
                            <!-- <td>{{ $po->luasan ?? '-' }}</td> -->
                            <!-- <td>{{ $po->nama_pic_keuangan }}</td>
                                <td>{{ $po->kontak_pic_keuangan }}</td> -->
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center text-muted">
                                Data BAST belum tersedia
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                   <div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                    Preview File
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-0">
                                <iframe id="pdfViewer" src="" width="100%" height="600"
                                    style="border: none;"></iframe>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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


    //lihat pddf
    function openPDFModal(fileUrl) {
        console.log(fileUrl);
        // Set src iframe
        document.getElementById('pdfViewer').src = fileUrl;

        // Tampilkan modal (Bootstrap 5)
        var pdfModal = new bootstrap.Modal(document.getElementById('pdfModal'));
        pdfModal.show();
    }
</script>
@endsection