@extends('app.template')

@section('content')
<style>
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
#column-handles {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
}

.col-handle {
    position: absolute;
    width: 14px;
    height: 30px;
    background: #ccc;
    cursor: pointer;
    text-align: center;
    font-size: 12px;
    line-height: 30px;
    border: 1px solid #999;
    z-index: 100;
}
</style>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Data BAST Finance</h5>
            </div>
        </div>  
        
          <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
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
            </div>
        </form>
        
        <div class="card-body p-0">
            <div class="table-responsive" style="position: relative;">
                <table class="table table-bordered mb-0" border="1" id="myTable">
                <thead class="table-light">
                        <tr class="text-center align-middle">
                            <th>No</th>
                            <th>Tgl PO</th>
                            <th>Nama Customer</th>
                            <th>Lokasi</th>
                            <th>PIC Marketing</th>
                            <th>Kode Projek</th>
                            <th>No PO</th>
                            <th>Nama Perizinan</th>
                            <th>Nominal SPK</th>
                            <th>Lama Pekerjaan</th>
                            <th class="border px-4 py-2">Lama Pengerjaan</th>
                            <th>Termin</th>
                            <th>Aksi</th>
                            <th>Status<button onclick="hideCol(13)"><i class="bi bi-eye-slash"></i></button></th>
                            <th>Hold</th>
                            <th>Tgl BAST<button onclick="hideCol(15)"><i class="bi bi-eye-slash"></i></button></th>

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
                                <td>{{ optional($po->customer)->nama_perusahaan ?? '-' }}</td>
                                <td>
                                    {{ \Illuminate\Support\Str::title(strtolower($po->quotation->kabupaten->nama ?? '-')) }}                                            
                                </td>
                                <td>{{ optional($po->customer->marketing)->nama ?? '-' }}</td>
                                <td>Kode Projek</td>
                                <td>{{ $po->no_po }}</td>
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
                                    Rp {{ number_format(optional($po->quotation)->grand_total ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    {{ optional($po->quotation)->lama_pekerjaan ? optional($po->quotation)->lama_pekerjaan . ' hari' : '-' }}
                                </td>

                                <td class="{{ $bg }}">
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
                    
                                <!-- <td class="text-center">
                                    @if ($po->sisa_termin > 0)
                                        <a href="{{ route('finance.create', $po->id) }}" class="btn btn-sm btn-primary">
                                            Buat Invoice ({{ $po->invoice_terbuat + 1 }}/{{ $po->total_termin }})
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            Invoice Lengkap
                                        </button>
                                    @endif
                                </td> -->
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
                 <div id="column-handles"></div>
            </div>
        </div>
    </div>
    </div>

<script>
function hideCol(colIndex) {
    const table = document.getElementById("myTable");
    const rows = table.rows;

    for (let i = 0; i < rows.length; i++) {
        let cell = rows[i].cells[colIndex];
        if (cell) cell.style.display = 'none';
    }

    createHandle(colIndex);
}

function showCol(colIndex) {
    const table = document.getElementById("myTable");
    const rows = table.rows;

    for (let i = 0; i < rows.length; i++) {
        let cell = rows[i].cells[colIndex];
        if (cell) cell.style.display = '';
    }

    // hapus handle
    document.querySelector(`[data-col='${colIndex}']`)?.remove();
}

function createHandle(colIndex) {
    const table = document.getElementById("myTable");
    const th = table.rows[0].cells[colIndex - 1];

    if (!th) return;

    const container = document.getElementById("column-handles");

    const tableRect = table.getBoundingClientRect();
    const thRect = th.getBoundingClientRect();

    const handle = document.createElement("div");
    handle.className = "col-handle";
    handle.innerHTML = ">";
    
    // posisi RELATIF ke table
    handle.style.left = (thRect.right - tableRect.left) + "px";
    handle.style.top = "0px";

    handle.dataset.col = colIndex;

    handle.onclick = () => showCol(colIndex);

    container.appendChild(handle);
}
</script>
@endsection

