@extends('app.template')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="text-center mb-4"><strong>DETAIL INFORMASI PROJECT</strong></h5>

            <div class="row">

                {{-- ======================
                KOLOM KIRI: INFO PROJECT & CUSTOMER
            ====================== --}}
                <div class="col-12">
                    <table class="table table-borderless">
                        @php
                            $customer = $project->po->customer;
                            $picList = is_string($customer->pic_perusahaan)
                                ? json_decode($customer->pic_perusahaan, true)
                                : $customer->pic_perusahaan;
                            $utama = collect($picList ?? [])->firstWhere('utama', true);

                            $luas = [];
                            if ($project->po->quotation->luas_slf) {
                                $luas[] =
                                    'SLF: ' . number_format($project->po->quotation->luas_slf, 0, ',', '.') . 'm²';
                            }
                            if ($project->po->quotation->luas_pbg) {
                                $luas[] =
                                    'PBG: ' . number_format($project->po->quotation->luas_pbg, 0, ',', '.') . 'm²';
                            }
                            if ($project->po->quotation->luas_shgb) {
                                $luas[] =
                                    'SHGB: ' . number_format($project->po->quotation->luas_shgb, 0, ',', '.') . 'm²';
                            }
                        @endphp

                        <tr>
                            <td class="py-1"><strong>Nama Perusahaan</strong></td>
                            <td class="py-1">: {{ $customer->nama_perusahaan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1"><strong>Jenis Perizinan</strong></td>
                            <td class="py-1">: 
                                {{ $project->project_perizinan->pluck('perizinan.jenis')->implode(', ') ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1"><strong>No PO</strong></td>
                            <td class="py-1">: {{ $project->po->no_po ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1"><strong>Lokasi</strong></td>
                            <td class="py-1">: {{ $project->po->quotation->detail_alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1"><strong>Luas</strong></td>
                            <td class="py-1">: {{ $luas ? implode(', ', $luas) : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1"><strong>PIC Perusahaan</strong></td>
                            <td class="py-1">: 
                                    @if ($utama)
                                    {{ $utama['nama'] ?? '-' }}<br>
                                    {{ $utama['kontak'] ?? '-' }}<br>
                                    {{ $utama['email'] ?? '-' }}
                                @else
                                    <em>Tidak ada PIC utama</em>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1"><strong>PIC Project</strong></td>
                            <td class="py-1">: {{ $project->marketing->nama ?? '-' }}</td>
                        </tr>
                        {{-- <tr>
                        <td colspan="2">
                            <button class="btn btn-warning btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalCatatan">
                                Lihat Catatan
                            </button>
                        </td>
                    </tr> --}}
                    </table>
                    <hr class="my-2 border-3 border-dark opacity-80">

                </div>

                {{-- <hr class="my-2 border-3 border-dark opacity-80"> --}}

                {{-- ======================
                KOLOM KANAN: TAHAPAN & PROGRESS
            ====================== --}}
                <div class="col-12 border-start">

                <div class="card-header fw-bold fs-6">
                    Status Project
                    <div class="fw-normal text-muted" style="font-size: 0.75rem;">
                        klik nama tahapan untuk melihat timeline
                    </div>
                </div>
                    <ul class="list-unstyled">

                        @foreach ($tahapanProject as $tahapan)
                            @php

                                $namaTahapan = strtolower(optional($tahapan->tahapan)->nama_tahapan ?? '-');

                                $row = $results[$tahapan->id] ?? ['percent_actual' => 0, 'realisasi' => 0];

                                $percentActual = $row['percent_actual'];
                                $percent = $row['realisasi'];

                                // ========== AMBIL DATA DASAR ==========
                                $start = $tahapan->rencana_start
                                    ? \Carbon\Carbon::parse($tahapan->rencana_start)->startOfDay()
                                    : null;
                                $end = $tahapan->rencana_end
                                    ? \Carbon\Carbon::parse($tahapan->rencana_end)->startOfDay()
                                    : null;
                                $today = \Carbon\Carbon::today()->startOfDay();

                                $badgeColor = 'secondary';
                                $badgeText = '-';

                                // ========== TIMELINE NORMAL ==========
                                if ($start && $end) {
                                    if ($tahapan->actual_end) {
                                        // jika sudah selesai → timeline normal disembunyikan
                                        $badgeColor = null;
                                        $badgeText = null;
                                    } else {
                                        if ($today->gt($end)) {
                                            $diffOver = $today->diffInDays($end, false); // akan negatif karena $today > $end
                                            $badgeColor = 'danger';
                                            $badgeText = 'Lewat Deadline' . abs($diffOver) . ' Hari';
                                        } elseif ($today->between($start, $end)) {
                                            $remaining = $today->diffInDays($end, false);
                                            $badgeColor = $remaining <= 3 ? 'warning' : 'success';
                                            $badgeText = "Sisa $remaining Hari";
                                        } elseif ($today->lt($start)) {
                                            $badgeColor = 'info';
                                            $badgeText = 'Waiting';
                                        }
                                    }
                                }

                                // =====================================
                                //      STATUS VERIFIKASI JIKA SELESAI
                                // =====================================
                                $statusVerif = null;
                                $verifBadgeColor = null;

                                if ($tahapan->actual_end) {
                                    $verifikasiTerakhir = \App\Models\VerifikasiProject::where(
                                        'project_id',
                                        $project->id,
                                    )
                                        ->where('tahapan_id', $tahapan->tahapan_id)
                                        ->whereNotNull('verified_at')
                                        ->latest('verified_at')
                                        ->first();

                                    if ($verifikasiTerakhir) {
                                        $deadline = \Carbon\Carbon::parse($tahapan->rencana_end)->startOfDay();
                                        $tglVerif = \Carbon\Carbon::parse(
                                            $verifikasiTerakhir->verified_at,
                                        )->startOfDay();

                                        $diff = $tglVerif->diffInDays($deadline, false);

                                        if ($diff == 0) {
                                            $statusVerif = 'Selesai - Tepat Waktu';
                                            $verifBadgeColor = 'success';
                                        } elseif ($diff < 0) {
                                            $statusVerif = 'Selesai - Terlambat ' . abs($diff) . ' Hari';
                                            $verifBadgeColor = 'danger';
                                        } else {
                                            $statusVerif = 'Selesai - Lebih Awal ' . $diff . ' Hari';
                                            $verifBadgeColor = 'success';
                                        }
                                    }
                                }

                                $collapseDocs = 'collapseDocs_' . $tahapan->id;
                                $collapseSubTahapan = 'collapseSub_' . $tahapan->id;
                                $collapseDetail = 'detail_' . $tahapan->id;
                                $collapseDetailCollect = 'detailCollect_' . $tahapan->id;

                                $isCollect = str_contains($namaTahapan, 'collect dokumen');

                                if (!$isCollect) {
                                    // Ambil verified_at dari verifikasi_project untuk tahapan ini
                                    $verifiedAt = $project->verifikasi_project
                                        ->where('tahapan_id', $tahapan->tahapan_id)
                                        ->where('verified', 1)
                                        ->first()?->verified_at;
                                } else {
                                    // Collect dokumen: biarkan null, karena verifikasi per dokumen
                                    $verifiedAt = null;
                                }

                                $percentActual = $results[$tahapan->id]['percent_actual'] ?? 0;
                                $percentTarget = $results[$tahapan->id]['target'] ?? 0;

                                // format angka: bulat tanpa desimal, pecahan 2 digit
                                $percentActualFormatted =
                                    fmod($percentActual, 1) == 0
                                        ? number_format($percentActual, 0)
                                        : number_format($percentActual, 2, ',', '');
                                $percentTargetFormatted =
                                    fmod($percentTarget, 1) == 0
                                        ? number_format($percentTarget, 0)
                                        : number_format($percentTarget, 2, ',', '');

                            @endphp

                            {{-- HEADER TAHAPAN --}}
                            <li class="mt-2 pb-2 mb-2" style="border-bottom:1px solid #e5e7eb;">

                                <div class="row align-items-center">

                                    {{-- KOLOM 1: NAMA TAHAPAN --}}
                                    <div class="col-md-4 col-12">
                                        <div class="d-flex align-items-center gap-2">

                                            @if ($namaTahapan === 'collect dokumen')
                                                <button class="btn btn-sm btn-outline-primary" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseDocs }}">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>

                                                <button class="btn text-start p-0 fw-semibold" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#{{ $collapseDetailCollect }}">
                                                    {{ optional($tahapan->tahapan)->nama_tahapan }}
                                                </button>
                                            @elseif(in_array($namaTahapan, ['survey', 'gambar']))
                                                <button class="btn btn-sm btn-outline-primary" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseSubTahapan }}">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>
                                                <button class="btn text-start p-0 fw-semibold" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseDetail }}">
                                                    {{ optional($tahapan->tahapan)->nama_tahapan }}
                                                </button>
                                            @else
                                                <button class="btn text-start p-0 fw-semibold" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseDetail }}">
                                                    {{ optional($tahapan->tahapan)->nama_tahapan }}
                                                </button>
                                            @endif

                                        </div>
                                    </div>

                                    {{-- KOLOM 2: TANGGAL VERIFIKASI --}}
                                    <div
                                        class="col-md-3 col-12 text-muted small d-flex justify-content-center justify-content-md-start">
                                        {{ $verifiedAt ? \Carbon\Carbon::parse($verifiedAt)->format('d-m-Y H:i') : '-' }}
                                    </div>

                                    {{-- KOLOM 3: PERSENTASE --}}
                                    <div class="text-center col-md-2 col-6 fw-semibold">
                                        {{ fmod($percent, 1) == 0 ? number_format($percent, 0) : number_format($percent, 2) }}% <span style="font-size: 0.7em;"> dari {{ $percentTargetFormatted }}%</span>
                                    </div>

                                    {{-- KOLOM 4: STATUS --}}
                                    <div class="col-md-3 col-6 text-end">
                                        @if ($statusVerif)
                                            <span class="badge bg-{{ $verifBadgeColor }}">
                                                {{ $statusVerif }}
                                            </span>
                                        @elseif ($badgeText)
                                            <span class="badge bg-{{ $badgeColor }}">
                                                {{ $badgeText }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>

                                </div>
                            </li>

                            {{-- LIST DOKUMEN --}}
                            @if ($namaTahapan === 'collect dokumen')
                                <div class="collapse ms-4 mt-1" id="{{ $collapseDocs }}">
                                    <div class="p-2 border rounded">
                                        @forelse($listDokCollect as $dok)
                                            @if (in_array($dok->id, $excludeList))
                                                @continue
                                            @endif
                                            @php
                                                $cek = $verifikasiList->where('ceklis_perizinan_id', $dok->id)->first();
                                                $isVerified = $cek && $cek->verified == 1;
                                                $verifiedAt = $cek?->verified_at
                                                    ? \Carbon\Carbon::parse($cek->verified_at)->format('d-m-Y H:i')
                                                    : '';
                                            @endphp

                                            <div class="row align-items-center mb-1 small">
                                                {{-- Kolom 1: Icon + Nama dokumen --}}
                                                <div class="col-md-4 d-flex align-items-center gap-2">
                                                    <span class="{{ $isVerified ? 'text-success' : 'text-danger' }}"
                                                        style="font-size:18px;">
                                                        {{ $isVerified ? '✔' : '✖' }}
                                                    </span>
                                                    <span>{{ $dok->nama_dokumen }}</span>
                                                </div>

                                                {{-- Kolom 2: Tanggal verified --}}
                                                <div class="col-md-2 text-muted small d-flex justify-content-start ps-1">
                                                    {{ $verifiedAt }}
                                                </div>

                                                {{-- Kolom 3: Persentase / kosong --}}
                                                <div class="col-md-2">
                                                    {{-- Kosong atau bisa diisi persentase jika ada --}}
                                                </div>

                                                {{-- Kolom 4: Status / kosong --}}
                                                <div class="col-md-3">
                                                    {{-- Kosong atau status tambahan --}}
                                                </div>
                                            </div>

                                        @empty
                                            <div class="small"><em>Tidak ada dokumen.</em></div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                            @if (in_array($namaTahapan, ['survey', 'gambar']))
                                <div class="collapse ms-4 mt-1" id="{{ $collapseSubTahapan }}">
                                    @foreach ($tahapan->subTahapan as $sub)
                                        @php
                                            $progress = $tahapan->progress->firstWhere('sub_tahapan_id', $sub->id);
                                            $persentase = $progress->persentase_actual ?? 0;
                                            $persentase_format =
                                                fmod($persentase, 1) == 0
                                                    ? number_format($persentase, 0)
                                                    : number_format($persentase, 2, ',', '');
                                        @endphp

                                        <div class="row align-items-center mb-1 small">
                                            {{-- Kolom 1: Nama sub-tahapan --}}
                                            <div class="col-md-4 col-12">
                                                {{ $sub->nama_sub ?? '-' }}
                                            </div>

                                            {{-- Kolom 2: kosong untuk tanggal/verifikasi --}}
                                            <div class="col-md-3 col-12 text-muted small">

                                            </div>

                                            {{-- Kolom 3: Persentase --}}
                                            <div class="col-md-2 col-6">
                                                {{ $persentase_format }}%
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- tulisan di bawah nama tahapan --}}
                            {{-- DETAIL TIMELINE COLLECT DOKUMEN --}}
                            @if ($namaTahapan === 'collect dokumen')
                                <div class="collapse ms-4 mt-1" id="{{ $collapseDetailCollect }}">
                                    <div class="p-2 border rounded bg-light">

                                        {{-- Rencana --}}
                                        @php
                                            $rcStart = $tahapan->rencana_start
                                                ? \Carbon\Carbon::parse($tahapan->rencana_start)
                                                : null;
                                            $rcEnd = $tahapan->rencana_end
                                                ? \Carbon\Carbon::parse($tahapan->rencana_end)
                                                : null;
                                        @endphp

                                        <small class="d-block">
                                            <strong>Timeline:</strong>
                                            {{ $rcStart ? $rcStart->isoFormat('DD MMMM YYYY') : '-' }}
                                            -
                                            {{ $rcEnd ? $rcEnd->isoFormat('DD MMMM YYYY') : '-' }}
                                            @if ($rcStart && $rcEnd)
                                                ({{ $rcStart->diffInDays($rcEnd) + 1 }} hari)
                                            @endif
                                        </small>

                                        {{-- Actual --}}
                                        @php
                                            $acStart = $tahapan->actual_start
                                                ? \Carbon\Carbon::parse($tahapan->actual_start)
                                                : null;
                                            $acEnd = $tahapan->actual_end
                                                ? \Carbon\Carbon::parse($tahapan->actual_end)
                                                : null;
                                        @endphp

                                        <small class="d-block">
                                            <strong>Actual:</strong>
                                            {{ $acStart ? $acStart->isoFormat('DD MMMM YYYY') : '-' }}
                                            -
                                            {{ $acEnd ? $acEnd->isoFormat('DD MMMM YYYY') : '-' }}

                                            @if ($acStart && !$acEnd)
                                                (proses berjalan)
                                            @endif

                                            @if ($acStart && $acEnd)
                                                ({{ $acStart->diffInDays($acEnd) + 1 }} hari)
                                            @endif
                                        </small>

                                    </div>
                                </div>
                            @endif

                            {{-- DETAIL TIMELINE UNTUK TAHAPAN NON-COLLECT --}}
                            @if ($namaTahapan !== 'collect dokumen')
                                <div class="collapse ms-4 mt-1" id="{{ $collapseDetail }}">
                                    <div class="p-2 border rounded bg-light">

                                        {{-- Rencana --}}
                                        @php
                                            $rcStart = $tahapan->rencana_start
                                                ? \Carbon\Carbon::parse($tahapan->rencana_start)
                                                : null;
                                            $rcEnd = $tahapan->rencana_end
                                                ? \Carbon\Carbon::parse($tahapan->rencana_end)
                                                : null;
                                        @endphp

                                        <small class="d-block">
                                            <strong>Timeline:</strong>
                                            {{ $rcStart ? $rcStart->isoFormat('DD MMMM YYYY') : '-' }}
                                            -
                                            {{ $rcEnd ? $rcEnd->isoFormat('DD MMMM YYYY') : '-' }}
                                            @if ($rcStart && $rcEnd)
                                                ({{ $rcStart->diffInDays($rcEnd) + 1 }} hari)
                                            @endif
                                        </small>

                                        {{-- Actual --}}
                                        @php
                                            $acStart = $tahapan->actual_start
                                                ? \Carbon\Carbon::parse($tahapan->actual_start)
                                                : null;
                                            $acEnd = $tahapan->actual_end
                                                ? \Carbon\Carbon::parse($tahapan->actual_end)
                                                : null;
                                        @endphp

                                        <small class="d-block">
                                            <strong>Actual:</strong>
                                            {{ $acStart ? $acStart->isoFormat('DD MMMM YYYY') : '-' }}
                                            -
                                            {{ $acEnd ? $acEnd->isoFormat('DD MMMM YYYY') : '-' }}

                                            @if ($acStart && !$acEnd)
                                                <strong>proses berjalan</strong>
                                            @endif

                                            @if ($acStart && $acEnd)
                                                ({{ $acStart->diffInDays($acEnd) + 1 }} hari)
                                            @endif
                                        </small>

                                    </div>
                                </div>
                            @endif
                        @endforeach

                    </ul>
                    <hr class="my-2 border-3 border-dark opacity-80">

                </div>

                {{-- catatan --}}
                <div class="col-12 mt-4">
                    <div class="card border-warning">
                        <div class="card-header fw-bold fs-6">
                            Riwayat dan Catatan Project
                        </div>

                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Tahapan</th>
                                            <th>Tanggal</th>
                                            <th>Pemroses</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($project->catatan()->latest()->get() as $index => $cat)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ optional($cat->tahapan)->nama_tahapan }}</td>
                                                <td class="text-center">{{ $cat->created_at->format('d-m-Y H:i') }}</td>
                                                <td class="text-center">{{ optional($cat->user)->username }}</td>
                                                <td>{{ $cat->isi_catatan }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    Belum ada catatan untuk project ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    {{-- ========================= MODAL REKAP CATATAN ========================= --}}
    {{-- <div class="modal fade" id="modalCatatan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">Rekap Catatan Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Tahapan</th>
                                <th>Tanggal</th>
                                <th>Pemroses</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->catatan()->latest()->get() as $index => $cat)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ optional($cat->tahapan)->nama_tahapan }}</td>
                                    <td>{{ $cat->created_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ optional($cat->user)->username }}</td>
                                    <td>{{ $cat->isi_catatan }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada catatan untuk project ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
 --}}

    {{-- ========================= SCRIPT COLLAPSE ICON ========================= --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("[data-bs-toggle='collapse']").forEach(btn => {
                const icon = btn.querySelector(".arrow-icon");
                if (!icon) return;

                const target = document.querySelector(btn.dataset.bsTarget);

                target.addEventListener("show.bs.collapse", () => {
                    icon.classList.replace("bi-chevron-down", "bi-chevron-up");
                });

                target.addEventListener("hide.bs.collapse", () => {
                    icon.classList.replace("bi-chevron-up", "bi-chevron-down");
                });
            });
        });
    </script>
@endsection
