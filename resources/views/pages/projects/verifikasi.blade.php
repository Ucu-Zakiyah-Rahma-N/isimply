@extends('app.template')

@section('content')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-0">
                <h5 class="card-title mb-0">Verifikasi Dokumen
                    {{-- {{ $project->nama_project ?? 'Project #' . $project->id }} --}}
                </h5>
                <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm">← Kembali</a>
            </div>
        </div>

        <div class="card-body">
            {{-- Informasi singkat project --}}
            <div class="mb-4">
                <h6 class="fw-bold">Informasi Proyek</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td style="width:100px">Nama Perusahaan</td>
                        <td>: {{ $project->po?->customer?->nama_perusahaan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px">Nama Bangunan</td>
                        <td>: {{ $project->po?->quotation?->nama_bangunan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px">No PO</td>
                        <td>: {{ optional($project->po)->no_po ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jenis Perizinan</td>
                        <td>:
                            @foreach ($project->project_perizinan as $pp)
                                <span class="badge bg-info me-1">{{ optional($pp->perizinan)->jenis ?? '—' }}</span>
                            @endforeach
                        </td>
                    </tr>
                    @php
                        // Ambil semua tahapan survey (misal tahapan_id = 6)
                        $surveyTahapan = $project->project_tahapan->where('tahapan_id', 6);

                        // Ambil list petugas kalau ada
                        $petugasList = [];
                        if ($surveyTahapan->isNotEmpty()) {
                            foreach ($surveyTahapan as $pt) {
                                if ($pt->petugas) {
                                    $decoded = json_decode($pt->petugas);
                                    if ($decoded) {
                                        $petugasList = array_merge($petugasList, $decoded);
                                    }
                                }
                            }
                        }
                    @endphp

                    {{-- Tampilkan hanya kalau ada tahapan survey --}}
                    @if ($surveyTahapan->isNotEmpty())
                        <tr>
                            <td>Petugas Survey</td>
                            <td>:
                                {{ $petugasList ? implode(', ', $petugasList) : '-' }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        
                        <td>Deadline Project</td>
                        <td id="deadline-project">
                            <div> :
                                @if ($deadlineProject)
                                    {{ $deadlineProject->format('d-m-Y') }}
                                @else
                                    Dihitung setelah tahapan Collect Dokumen selesai
                                @endif
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>Sisa Waktu</td>
                        <td id="sisa-waktu">
                            <div> :
                                @if ($deadlineProject)
                                    @if ($sisaHari > 0)
                                        {{ $sisaHari }} hari lagi
                                    @elseif($sisaHari == 0)
                                        Deadline hari ini
                                    @else
                                        Deadline sudah lewat {{ abs($sisaHari) }} hari
                                    @endif
                                @else
                                    Sisa waktu di hitung setelah Dokumen lengkap
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="mb-2 d-flex justify-content-between align-items-center">
                
                {{-- Tahapan Project --}}
                <h6 class="fw-bold mb-0"></h6>
                {{-- buat catatan --}}
                <!-- Button untuk buka modal -->
                <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCatatan">
                    Catatan Project
                </a>
            </div>

                <!-- Modal Catatan -->
                <div class="modal fade" id="modalCatatan" tabindex="-1" aria-labelledby="modalCatatanLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold" id="modalCatatanLabel">Catatan Project</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <!-- Modal Body -->
                            <div class="modal-body">
                                 @admin
                                <!-- Form Buat Catatan -->
                                <div class="table-responsive">
                                   
                                    <h6 class="fw-bold mb-3">Buat Catatan Baru</h6>
                                    <form action="{{ route('catatan.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                                        
                                        <div class="mb-3">
                                            <label for="tahapan_id" class="form-label">Tahapan</label>
                                            <select name="tahapan_id" class="form-select" required>
                                                <option value="">-- Pilih Tahapan --</option>
                                                @foreach ($tahapanProject as $tp)
                                                    <option value="{{ $tp->tahapan_id }}">
                                                        {{ optional($tp->tahapan)->nama_tahapan }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="isi_catatan" class="form-label">Isi Catatan</label>
                                            <textarea name="isi_catatan" class="form-control" rows="4" placeholder="Tulis catatan..." required></textarea>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                                        </div>
                                    </form>
                                </div>
                                @endadmin

                                <!-- Tabel Rekap Catatan -->
                                <div class="table-responsive">
                                    <h6 class="fw-bold mb-2">Rekap Catatan</h6>
                                    <table class="table table-bordered table-sm align-middle mb-0">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="25%">Tahapan</th>
                                                <th width="20%">Tanggal</th>
                                                <th width="20%">Pemroses</th>
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
                                                    <td colspan="5" class="text-center text-muted">Belum ada catatan
                                                        untuk project ini.</td>
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
        
            @php
                $perizinanIds = $project->project_perizinan->pluck('perizinan_id')->toArray();
            @endphp

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th width="40%">Tahapan</th>
                            <th width="20%">Timeline</th>
                            <th width="5%">Hasil Persentase</th>
                            <th width="20%">Progress Persentase (%)</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tahapanProject as $tahapan)
                            @php
                                $namaTahapan = strtolower(optional($tahapan->tahapan)->nama_tahapan ?? '');
                                $punyaSub =
                                    isset($subTahapanMap[$tahapan->id]) && $subTahapanMap[$tahapan->id]->isNotEmpty();
                                $persentaseTarget = $tahapan->persentase_target ?? 100;

                                // Timeline

                                $start = $tahapan->rencana_start
                                    ? \Carbon\Carbon::parse($tahapan->rencana_start)
                                    : null;
                                $end = $tahapan->rencana_end ? \Carbon\Carbon::parse($tahapan->rencana_end) : null;
                                $today = \Carbon\Carbon::today();

                                $badgeColor = 'secondary';
                                $badgeText = '-';

                                if ($start && $end) {
                                    if ($today->gt($end)) {
                                        // Sudah lewat deadline
                                        $badgeColor = 'danger';
                                        $badgeText = 'Lewat Deadline';
                                    } elseif ($today->between($start, $end)) {
                                        // Sedang berjalan → hitung sisa hari
                                        $remaining = $today->diffInDays($end, false);
                                        $badgeColor = $remaining <= 3 ? 'warning' : 'success';
                                        $badgeText = "Sisa $remaining Hari";
                                    } elseif ($today->lt($start)) {
                                        // Belum mulai
                                        $badgeColor = 'info';
                                        $badgeText = 'Waiting';
                                    }
                                }

                            @endphp

                            {{-- Jika ini tahapan Collect Dokumen --}}
                            @if (stripos($namaTahapan, 'collect') !== false)
                                @php
                                    $dokumenForProject = collect();
                                    foreach ($ceklistDokumen as $pid => $list) {
                                        if (in_array($pid, $perizinanIds)) {
                                            $dokumenForProject = $dokumenForProject->concat($list);
                                        }
                                    }

                                    $persentaseTarget = $tahapan->persentase_target ?? 0;
                                    // Ambil dokumen yang tidak di-exclude
                                    $excludedIds = $project->ceklisExclude
                                        ->where('is_active', 0)
                                        ->pluck('ceklis_perizinan_id')
                                        ->toArray();

                                    $filteredDokumen = $dokumenForProject->filter(function ($dok) use ($excludedIds) {
                                        return !in_array($dok->id, $excludedIds); // hanya yang aktif
                                    });

                                    // hitung total dokumen setelah exclude
                                    $totalDok = $filteredDokumen->count();

                                    // hitung yang sudah diverifikasi
                                    $verifiedDok = $verifikasiList
                                        ->where('tahapan_id', $tahapan->tahapan_id)
                                        ->where('verified', 1)
                                        ->whereIn('project_perizinan_id', $project->project_perizinan->pluck('id'))

                                        ->whereIn('ceklis_perizinan_id', $filteredDokumen->pluck('id'))
                                        ->count();

                                    // cari hasil verifikasi utk tahapan ini
                                    $resultForThisTahapan = collect($results)->firstWhere(
                                        'nama_tahapan',
                                        optional($tahapan->tahapan)->nama_tahapan,
                                    );
                                @endphp


                                <tr class="align-middle">
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>

                                    <td class="fw-semibold">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ optional($tahapan->tahapan)->nama_tahapan ?? 'Collect Dokumen' }}</span>
                                            @admin
                                                <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal"
                                                    data-bs-target="#editTahapan-{{ $tahapan->id }}">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <div class="modal fade" id="editTahapan-{{ $tahapan->id }}">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('projects.update', $tahapan->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')

                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit
                                                                        {{ $tahapan->nama_tahapan }}
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>

                                                                <div class="modal-body">

                                                                    <label>Rencana Mulai</label>
                                                                    <input type="date" name="rencana_start"
                                                                        class="form-control"
                                                                        value="{{ $tahapan->rencana_start }}">

                                                                    <label class="mt-3">Rencana Selesai</label>
                                                                    <input type="date" name="rencana_end"
                                                                        class="form-control"
                                                                        value="{{ $tahapan->rencana_end }}">

                                                                    <label class="mt-3">Target (%)</label>
                                                                    <input type="number" class="form-control"
                                                                        name="persentase_target"
                                                                        value="{{ $tahapan->persentase_target }}">
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Simpan</button>
                                                                </div>

                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endadmin
                                        </div>
                                    </td>

                                    {{-- ketika sudah terverifikasi dan selesai --}}
                                    @php
                                        // ========== AMBIL DATA DASAR ==========
                                        $start = $tahapan->rencana_start
                                            ? Carbon\Carbon::parse($tahapan->rencana_start)->startOfDay()
                                            : null;
                                        $end = $tahapan->rencana_end
                                            ? Carbon\Carbon::parse($tahapan->rencana_end)->startOfDay()
                                            : null;
                                        $today = \Carbon\Carbon::today()->startOfDay();

                                        $badgeColor = 'secondary';
                                        $badgeText = '-';

                                        // ========== TIMELINE NORMAL ==========
                                        if ($start && $end) {
                                            if ($tahapan->actual_end) {
                                                // timeline normal disembunyikan ketika sudah selesai
                                                $badgeColor = null;
                                                $badgeText = null;
                                            } else {
                                                if ($today->gt($end)) {
                                                    $badgeColor = 'danger';
                                                    $badgeText = 'Lewat Deadline';
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

                                        if (
                                            strtolower(optional($tahapan->tahapan)->nama_tahapan) ===
                                                'collect dokumen' &&
                                            $tahapan->actual_end &&
                                            $verifiedDok == $totalDok
                                        ) {
                                            // Ambil verifikasi terakhir yang benar
                                            $verifikasiTerakhir = \App\Models\VerifikasiProject::where(
                                                'project_id',
                                                $tahapan->project_id,
                                            )
                                                // ->where('project_perizinan_id', $tahapan->project_perizinan_id)
                                                ->where('tahapan_id', $tahapan->tahapan_id)
                                                ->whereNotNull('verified_at')
                                                ->latest('verified_at')
                                                ->first();

                                            // $verif = $verifikasiTerakhir->verified_at ?? null;

                                            if ($verifikasiTerakhir) {
                                                $deadline = Carbon\Carbon::parse($tahapan->rencana_end)->startOfDay();
                                                $tglVerif = Carbon\Carbon::parse(
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
                                                    $statusVerif = "Selesai - Lebih Awal $diff Hari";
                                                    $verifBadgeColor = 'success';
                                                }
                                            }
                                        }
                                    @endphp

                                    <td class="text-center" id="timeline-{{ $tahapan->tahapan_id }}">

                                        {{-- Tanggal Start — End --}}
                                        <div class="fw-semibold">
                                            {{ $start ? $start->format('d M Y') : '-' }} —
                                            {{ $end ? $end->format('d M Y') : '-' }}
                                        </div>

                                        {{-- Tampilkan timeline normal jika belum selesai --}}
                                        <div class="timeline-badge-container mt-1">
                                            @if (!$statusVerif && $badgeColor)
                                                <span class="badge bg-{{ $badgeColor }}">{{ $badgeText }}</span>
                                            @endif

                                            {{-- Tampilkan status verifikasi jika selesai --}}
                                            @if ($statusVerif)
                                                <span
                                                    class="badge bg-{{ $verifBadgeColor }} d-block">{{ $statusVerif }}</span>
                                            @endif
                                        </div>

                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-primary" id="persen-{{ $tahapan->tahapan_id }}">
                                                {{ isset($resultForThisTahapan) ? $resultForThisTahapan['nilaiRealisasi'] : '0' }}%
                                            </span>
                                            <span>dari</span>
                                            <span class="badge bg-secondary">{{ (int) $persentaseTarget }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($resultForThisTahapan)
                                            <span class="badge bg-primary" id="badge-collect-{{ $tahapan->tahapan_id }}">
                                                {{ $resultForThisTahapan['verified'] }} /
                                                {{ $resultForThisTahapan['total'] }} Dokumen Terverifikasi
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button
                                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseCollect{{ $tahapan->id }}">
                                            <i class="bi bi-chevron-down small"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Collapse daftar dokumen --}}
                                <tr>
                                    <td colspan="6" class="p-0 border-0">
                                        <div class="collapse" id="collapseCollect{{ $tahapan->id }}">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light text-center">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="80%">Nama Dokumen</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $i = 1; @endphp
                                                    @foreach ($ceklistDokumen as $perizinanId => $dokumenList)
                                                        @if (in_array($perizinanId, $perizinanIds))
                                                            <tr class="table-secondary">
                                                                <td colspan="4" class="ps-3">
                                                                    <strong>{{ optional($project->project_perizinan->firstWhere('perizinan_id', $perizinanId)->perizinan)->jenis ?? 'Perizinan #' . $perizinanId }}</strong>
                                                                </td>
                                                            </tr>

                                                            @foreach ($dokumenList as $dok)
                                                                @php
                                                                    // cek status verifikasi berdasarkan tahapan & dokumen
                                                                    $verifikasi = $verifikasiList
                                                                        ->where('tahapan_id', $tahapan->tahapan_id)
                                                                        ->where('ceklis_perizinan_id', $dok->id)
                                                                        ->first();
                                                                    $status = (
                                                                        $project->ceklisExclude ?? collect()
                                                                    )->firstWhere('ceklis_perizinan_id', $dok->id);
                                                                    $isActive = $status ? $status->is_active : true; // default = aktif

                                                                @endphp

                                                                <tr>
                                                                    <td class="text-center">{{ $i++ }}</td>
                                                                    <td>{{ $dok->nama_dokumen }}</td>
                                                                    @php
                                                                        $isAdmin = in_array(
                                                                            strtolower(trim(auth()->user()->role)),
                                                                            ['admin 1', 'admin 2', 'admin 3', 'admin 4', 'admin 5', 'admin 6', 'admin 7', 'admin 8', 'admin 9', 'admin 10', 'admin 11'],
                                                                        );
                                                                    @endphp

                                                                    <td
                                                                        class="text-center d-flex justify-content-center gap-2">

                                                                        {{-- Tombol Verifikasi hanya muncul jika dokumen masih aktif --}}

                                                                        @if ($isActive)
                                                                            @if (optional($verifikasi)->verified)
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-success"
                                                                                    disabled>
                                                                                    Terverifikasi
                                                                                </button>
                                                                            @else
                                                                                <form
                                                                                    action="{{ route('projects.verifikasi.dokumen', [
                                                                                        'projectPerizinanId' => $project->project_perizinan->firstWhere('perizinan_id', $perizinanId)->id,
                                                                                        'ceklisId' => $dok->id,
                                                                                    ]) }}"
                                                                                    method="POST"
                                                                                    onsubmit="return confirmVerifikasi(event, this)"
                                                                                    data-type="verifikasi"
                                                                                    {{-- <=== WAJIB DITAMBAHKAN --}}
                                                                                    class="d-inline me-1">
                                                                                    @csrf
                                                                                    @method('PATCH')
                                                                                    <input type="hidden"
                                                                                        name="tahapan_id"
                                                                                        value="{{ $tahapan->tahapan_id }}">
                                                                                    <button type="submit"
                                                                                        class="btn btn-sm {{ $dok->status == 'verified' ? 'btn-success' : 'btn-primary' }}"
                                                                                        {{ $dok->status == 'verified' || !$isAdmin ? 'disabled' : '' }}>
                                                                                        {{ $dok->status == 'verified' ? 'Terverifikasi' : 'Verifikasi' }}
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        @endif


                                                                        {{-- Tombol Exclude / Include --}}
                                                                        @if (!optional($verifikasi)->verified)
                                                                            {{-- <=== tambah syarat ini --}}
                                                                            <button type="button"
                                                                                class="btn btn-sm toggle-exclude-btn {{ $isActive ? 'btn-outline-danger' : 'btn-danger' }}"
                                                                                data-project="{{ $project->id }}"
                                                                                data-project-perizinan="{{ $project->project_perizinan->firstWhere('perizinan_id', $perizinanId)->id }}"
                                                                                data-id="{{ $dok->id }}"
                                                                                data-tahapan="{{ $tahapan->tahapan_id }}"
                                                                                data-active="{{ $isActive ? 1 : 0 }}"
                                                                                title="{{ $isActive ? 'Klik untuk menonaktifkan dokumen dari perhitungan' : 'Klik untuk mengaktifkan kembali dokumen' }}"
                                                                                {{ optional($verifikasi)->verified || !$isAdmin ? 'disabled' : '' }}>
                                                                                {{ $isActive ? 'x' : '↺' }}
                                                                            </button>
                                                                        @endif

                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    @endforeach

                                                    @if ($dokumenForProject->isEmpty())
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted">Tidak ada
                                                                dokumen untuk perizinan ini.</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                {{-- Tahapan lain --}}
                                @php
                                    // Ambil target tahapan
                                    $persentaseTarget = $tahapan->persentase_target ?? 0;

                                    if ($punyaSub) {
                                        // ===== Ambil nilai progress terakhir untuk masing-masing sub =====
                                        $values = [];

                                        foreach ($subTahapanMap[$tahapan->id] as $sub) {
                                            $last =
                                                \App\Models\ProjectTahapanProgress::where(
                                                    'project_tahapan_id',
                                                    $tahapan->id,
                                                )
                                                    ->where('sub_tahapan_id', $sub->id)
                                                    ->latest('tanggal_update')
                                                    ->value('persentase_actual') ?? 0;

                                            $values[] = $last;
                                        }

                                        // Jika semua sub = 100%
                                        $allDone = collect($values)->every(fn($v) => $v == 100);

                                        if ($allDone) {
                                            $persentaseActual = 100;
                                        } else {
                                            $persentaseActual = collect($values)->avg() ?? 0;
                                        }
                                    } else {
                                        // ===== Tanpa sub-tahapan =====
                                        $lastProgress = \App\Models\ProjectTahapanProgress::where(
                                            'project_tahapan_id',
                                            $tahapan->id,
                                        )
                                            ->whereNull('sub_tahapan_id')
                                            ->latest('tanggal_update')
                                            ->value('persentase_actual');

                                        $persentaseActual = $lastProgress ?? ($tahapan->persentase_actual ?? 0);
                                    }

                                    // ===== Rumus utama tampilan badge =====
                                    $persentaseTampil = ($persentaseActual * $persentaseTarget) / 100;
                                @endphp

                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>

                                    <td class="fw-semibold">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ optional($tahapan->tahapan)->nama_tahapan ?? '-' }}</span>
                                            @admin
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                    data-bs-target="#editTahapan-{{ $tahapan->id }}">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <div class="modal fade" id="editTahapan-{{ $tahapan->id }}">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('projects.update', $tahapan->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')

                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit
                                                                        {{ $tahapan->nama_tahapan }}
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>

                                                                <div class="modal-body">

                                                                    <label>Rencana Mulai</label>
                                                                    <input type="date" name="rencana_start"
                                                                        class="form-control"
                                                                        value="{{ $tahapan->rencana_start }}">

                                                                    <label class="mt-3">Rencana Selesai</label>
                                                                    <input type="date" name="rencana_end"
                                                                        class="form-control"
                                                                        value="{{ $tahapan->rencana_end }}">

                                                                    <label class="mt-3">Target (%)</label>
                                                                    <input type="number" class="form-control"
                                                                        name="persentase_target"
                                                                        value="{{ $tahapan->persentase_target }}">
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Simpan</button>
                                                                </div>

                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endadmin

                                        </div>
                                    </td>
                                    @php

                                        // ========== AMBIL DATA DASAR ==========
                                        $start = $tahapan->rencana_start
                                            ? Carbon\Carbon::parse($tahapan->rencana_start)->startOfDay()
                                            : null;
                                        $end = $tahapan->rencana_end
                                            ? Carbon\Carbon::parse($tahapan->rencana_end)->startOfDay()
                                            : null;
                                        $today = \Carbon\Carbon::today()->startOfDay();

                                        $badgeColor = 'secondary';
                                        $badgeText = '-';

                                        // ========== TIMELINE NORMAL ==========
                                        if ($start && $end) {
                                            if ($tahapan->verifikasi) {
                                                // kalau sudah diverifikasi → tidak tampil timeline normal
                                                $badgeColor = null;
                                                $badgeText = null;
                                            } else {
                                                if ($today->gt($end)) {
                                                    $badgeColor = 'danger';
                                                    $badgeText = 'Lewat Deadline';
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

                                        // ========== STATUS VERIFIKASI ==========
                                        $verif = optional($tahapan->verifikasi)->verified_at;
                                        $statusVerif = null;
                                        $verifBadgeColor = null;

                                        if ($verif) {
                                            $deadline = Carbon\Carbon::parse($tahapan->rencana_end)->startOfDay();
                                            $tglVerif = Carbon\Carbon::parse($verif)->startOfDay();

                                            // Selisih hari akurat tanpa jam mempengaruhi hasil
                                            $diff = $tglVerif->diffInDays($deadline, false);

                                            // if ($diff === 0) {
                                            if ($diff == 0) {
                                                $statusVerif = 'Selesai - Tepat Waktu';
                                                $verifBadgeColor = 'success'; // hijau
                                            } elseif ($diff < 0) {
                                                $statusVerif = 'Selesai - Lebih ' . abs($diff) . ' Hari (Terlambat)';
                                                $verifBadgeColor = 'danger'; // hijau
                                            } else {
                                                $statusVerif = "Selesai - Lebih Awal $diff Hari";
                                                $verifBadgeColor = 'success'; // hijau
                                            }
                                        }
                                    @endphp

                                    {{-- ========== KOLOM TIMELINE ========== --}}
                                    <td class="text-center timeline-col">



                                        {{-- Tanggal Start — End --}}
                                        <div class="fw-semibold">
                                            {{ $start ? $start->format('d M Y') : '-' }} —
                                            {{ $end ? $end->format('d M Y') : '-' }}
                                        </div>

                                        {{-- Timeline normal hanya muncul bila belum diverifikasi --}}
                                        @if (!$statusVerif && $badgeColor)
                                            <span
                                                class="badge bg-{{ $badgeColor }} timeline-badge">{{ $badgeText }}</span>
                                        @endif

                                        {{-- Status verifikasi tampil paling atas --}}
                                        @if ($statusVerif)
                                            <span class="badge bg-{{ $verifBadgeColor }} d-block mb-1 timeline-badge">
                                                {{ $statusVerif }}
                                            </span>
                                        @endif
                                    </td>



                                    {{-- <div class="fw-semibold">
                    {{ $start ? $start->format('d M Y') : '-' }} — {{ $end ? $end->format('d M Y') : '-' }}
                </div>
                <span class="badge bg-{{ $badgeColor }}">{{ $badgeText }}</span>
            </td> --}}


                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary" id="badge-tahapan-{{ $tahapan->id }}">
                                                {{ round($persentaseTampil, 2) }}%
                                            </span>
                                            <span>dari</span>
                                            <span class="badge bg-secondary">{{ (int) $persentaseTarget }}%</span>
                                        </div>
            </div>
            </td>

            <td>
                @php
                    $sudahVerif = \App\Models\VerifikasiProject::where('project_id', $project->id)
                        ->where('tahapan_id', $tahapan->tahapan_id)
                        ->exists();
                @endphp


                {{-- dengan sub tahapan --}}
                @if ($punyaSub)
                    <ul class="list-group">
                        @foreach ($subTahapanMap[$tahapan->id] as $sub)
                            @php
                                $progress = \App\Models\ProjectTahapanProgress::where(
                                    'project_tahapan_id',
                                    $tahapan->id,
                                )
                                    ->where('sub_tahapan_id', $sub->id)
                                    // ->orderByDesc('id') // tidak di pakai karena sudah pakaii tanggal update (datetime)
                                    ->latest('tanggal_update')
                                    ->first();
                                $persentaseActual = $progress->persentase_actual ?? 0;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>{{ $sub->nama_sub }}</div>
                                <div class="d-flex align-items-center" style="gap:8px;">
                                    <input type="number" min="0" max="100"
                                        class="form-control form-control-sm" id="progress-sub-{{ $sub->id }}"
                                        data-projecttahapan="{{ $tahapan->id }}" data-subtahapan="{{ $sub->id }}"
                                        {{-- untuk menampilkan persentase update di form edit (dengan sub tahapan) --}}
                                        value="{{ rtrim(rtrim(number_format($persentaseActual, 2, '.', ''), '0'), '.') }}"
                                        readonly style="width:100px;" @if ($sudahVerif) disabled @endif>
                                    @admin
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            id="btn-sub-{{ $sub->id }}"
                                            onclick="toggleEditSaveSub({{ $sub->id }})"
                                            @if ($sudahVerif) disabled @endif>
                                            Edit
                                        </button>
                                    @endadmin
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    {{-- update untuk tahapan tanpa sub  --}}
                    <input type="number" min="0" max="100"
                        class="form-control form-control-sm d-inline-block" id="persentase-{{ $tahapan->id }}"
                        value="{{ round($persentaseActual, 2) }}" readonly style="width:120px;"
                        @if ($sudahVerif) disabled @endif>
                    @admin
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-{{ $tahapan->id }}"
                            onclick="toggleEditSave({{ $tahapan->id }})" @if ($sudahVerif) disabled @endif>

                            Edit
                        </button>
                    @endadmin
                @endif
            </td>
            @php
                $isAdmin = in_array(strtolower(trim(auth()->user()->role)), ['admin 1', 'admin 2', 'admin 3', 'admin 4', 'admin 5', 'admin 6', 'admin 7', 'admin 8', 'admin 9', 'admin 10', 'admin 11']);
            @endphp

            <td class="text-center">

                <form data-tahapan-id="{{ $tahapan->id }}"
                    action="{{ route('projects.verifikasi.tahapan', $tahapan->id) }}" method="POST"
                    onsubmit="return confirmVerifikasiTahapan(event, this)">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $sudahVerif ? 'btn-success' : 'btn-primary' }}"
                        {{ $sudahVerif || !$isAdmin ? 'disabled' : '' }}>
                        {{ $sudahVerif ? 'Terverifikasi' : 'Verifikasi' }}
                    </button>
                </form>
            </td>
            </tr>
            @endif
            @endforeach
            </tbody>

            </table>
            @admin
            <!-- TOMBOL PEMBUKA MODAL -->
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalTambahTahapan">
                + Tambah Tahapan
            </button>
            @endadmin
            <!-- MODAL -->
            <div class="modal fade" id="modalTambahTahapan" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('tahapan.opsional.store', $project->id) }}" method="POST">
                        @csrf

                        <div class="modal-content">

                            <div class="modal-header">
                                <div class="d-flex flex-column">
                                    <h5 class="modal-title mb-0">Tambah Tahapan Opsional</h5>
                                    <small class="text-muted">Centang untuk mengisi rencana & posisi sisip</small>
                                </div>

                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                @foreach ($opsionalTahapan as $tahap)
                                    @php $slug = Str::slug($tahap->nama_tahapan, '_') @endphp

                                    <div class="card p-3 mb-2 border shadow-sm">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong>{{ $tahap->nama_tahapan }}</strong>

                                            <input type="checkbox" name="tahapan_opsional[]" value="{{ $tahap->id }}"
                                                class="form-check-input pilih-opsional" data-id="{{ $tahap->id }}">
                                        </div>


                                        <div id="opsi-{{ $tahap->id }}" class="mt-3 d-none">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rencana Mulai</label>
                                                    <input type="date"
                                                        name="rencana_mulai_opsional[{{ $slug }}]"
                                                        class="form-control">
                                                </div>

                                                <div class="col-md-6">
                                                    <label>Rencana Selesai</label>
                                                    <input type="date"
                                                        name="rencana_selesai_opsional[{{ $slug }}]"
                                                        class="form-control">
                                                </div>
                                            </div>

                                            <label class="mt-2">Persentase Target (%)</label>
                                            <input type="number" name="persentase_opsional[{{ $slug }}]"
                                                class="form-control" placeholder="0">

                                            <label class="mt-3">Sisipkan Setelah Tahapan:</label>
                                            <select name="sisip_setelah[{{ $tahap->id }}]" class="form-select">
                                                <option value="">— Pilih —</option>

                                                @foreach ($tahapanProject as $p)
                                                    <option value="{{ $p->urutan }}">
                                                        Setelah {{ $p->urutan }} — {{ $p->tahapan->nama_tahapan }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <input type="hidden" name="nama[{{ $tahap->id }}]"
                                                value="{{ $tahap->nama_tahapan }}">
                                        </div>

                                    </div>
                                @endforeach

                            </div>

                            <div class="modal-footer">
                                <button type="submit" id="btnSimpanTahapan" class="btn btn-success">Simpan Tahapan</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>

        <script>


            function confirmVerifikasi(event, form) {
                event.preventDefault();

                Swal.fire({
                    title: 'Yakin ingin memverifikasi dokumen ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Verifikasi!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    const formData = new FormData(form);

                    fetch(form.action, {
                            method: form.method,
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            }
                        })
                        .then(response => response.json())
                        .then(data => {

                            if (!data.success) {
                                return Swal.fire('Error', data.message ||
                                    'Terjadi kesalahan saat verifikasi dokumen.', 'error');
                            }

                            // SUCCESS MESSAGE
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });

                            // Tombol
                            const btn = form.querySelector('button');
                            btn.textContent = "Terverifikasi";
                            btn.classList.replace('btn-primary', 'btn-success');
                            btn.disabled = true;

                            // Hilangkan tombol exclude
                            const excludeBtn = form.parentElement.querySelector('.toggle-exclude-btn');
                            if (excludeBtn) excludeBtn.remove();

                            // Update jumlah dan persen
                            const tahapanId = data.tahapan_id;
                            const badge = document.getElementById(`badge-collect-${tahapanId}`);
                            if (badge) badge.textContent =
                                `${data.verified_now} / ${data.total_doc} Dokumen Terverifikasi`;

                            const persenBadge = document.getElementById(`persen-${tahapanId}`);
                            if (persenBadge) persenBadge.textContent = `${data.nilai_realisasi}%`;

                            // UPDATE TIMELINE REALTIME
                            const timelineContainer = document.querySelector(
                                `#timeline-${tahapanId} .timeline-badge-container`);
                            if (timelineContainer &&
                                data.verified_now == data.total_doc && // <-- WAJIB
                                data.verified_at &&
                                data.rencana_end
                            ) {
                                timelineContainer.querySelectorAll('.badge').forEach(b => b.remove());

                                const selesai = new Date(data.verified_at);
                                const deadline = new Date(data.rencana_end);
                                const diff = Math.floor((selesai - deadline) / (1000 * 60 * 60 * 24));

                                let statusText = '';
                                let badgeColor = '';

                                if (diff > 0) {
                                    statusText = `Selesai - Terlambat ${diff} Hari`;
                                    badgeColor = 'danger';
                                } else if (diff === 0) {
                                    statusText = 'Selesai - Tepat Waktu';
                                    badgeColor = 'success';
                                } else {
                                    statusText = `Selesai - Lebih Awal ${Math.abs(diff)} Hari`;
                                    badgeColor = 'success';
                                }

                                timelineContainer.insertAdjacentHTML('beforeend',
                                    `<span class="badge bg-${badgeColor} d-block">${statusText}</span>`);
                            }

                            // UPDATE INDEX (kolom dokumen)
                            const statusDoc = document.querySelector(`#status-doc-${data.project_id}`);

                            if (statusDoc && data.verified_now == data.total_doc) {
                                statusDoc.innerHTML = `
                    <span class="fw-semibold text-success">
                        Dokumen Lengkap <i class="bi bi-check-circle-fill"></i>
                    </span>
                `;
                            }

                            // UPDATE DEADLINE PROJECT DAN SISA HARI
                            const deadlineElem = document.querySelector('#deadline-project');
                            const sisaElem = document.querySelector('#sisa-waktu');

                            if (deadlineElem && data.deadline_project) {
                                // ubah dari YYYY-MM-DD ke DD-MM-YYYY
                                const d = new Date(data.deadline_project);
                                const formatted = ("0" + d.getDate()).slice(-2) + "-" +
                                    ("0" + (d.getMonth() + 1)).slice(-2) + "-" +
                                    d.getFullYear();
                                deadlineElem.textContent = ': ' + formatted;
                            }

                            if (sisaElem && data.sisa_hari != null) {
                                let sisaText = '';
                                if (data.sisa_hari > 0) sisaText = `${data.sisa_hari} hari lagi`;
                                else if (data.sisa_hari == 0) sisaText = 'Deadline hari ini';
                                else sisaText = `Deadline sudah lewat ${Math.abs(data.sisa_hari)} hari`;
                                sisaElem.textContent = ': ' + sisaText;
                            }

                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Gagal memproses verifikasi dokumen.', 'error');
                        });

                });
            }

            // UNTUK EXCLUDE / INCLUDE DOKUMEN
            $(document).on('click', '.toggle-exclude-btn', function() {
                let btn = $(this);
                let newActiveState = btn.data('active') == 1 ? 0 : 1;
                let projectId = btn.data('project');
                let projectPerizinanId = btn.data('project-perizinan');
                let ceklisId = btn.data('id');
                let tahapanId = btn.data('tahapan'); // wajib ada di button

                $.ajax({
                    url: "{{ route('project.ceklis.exclude') }}",
                    type: "POST",
                    data: {
                        project_id: projectId,
                        project_perizinan_id: projectPerizinanId,
                        ceklis_perizinan_id: ceklisId,
                        is_active: newActiveState,
                        tahapan_id: tahapanId, // kirim tahapan
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (!res || !res.hasOwnProperty('is_active')) {
                            alert('Response tidak valid dari server.');
                            return;
                        }

                        // update tombol state
                        btn.data('active', newActiveState);
                        let td = btn.closest('td');

                        if (newActiveState == 1) {
                            // INCLUDE -> tampilkan verifikasi
                            btn.removeClass('btn-danger').addClass('btn-outline-danger').text('x')
                                .attr("title", "Klik untuk menonaktifkan dokumen dari perhitungan");
                            let form = td.find('form[data-type="verifikasi"]');
                            if (form.length) {
                                form.removeClass('d-none');
                            } else {
                                // buat ulang form jika hilang (gunakan route blade base jika perlu)
                                td.prepend(`
                        <form action="/projects/verifikasi/dokumen/${projectPerizinanId}/${ceklisId}"
                              method="POST"
                              onsubmit="return confirmVerifikasi(event, this)"
                              data-type="verifikasi"
                              class="d-inline me-1">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="tahapan_id" value="${tahapanId}">
                            <button type="submit" class="btn btn-sm btn-primary">Verifikasi</button>
                        </form>
                    `);
                            }
                        } else {
                            // EXCLUDE -> sembunyikan verifikasi
                            btn.removeClass('btn-outline-danger').addClass('btn-danger').text('↺')
                                .attr("title", "Klik untuk mengaktifkan kembali dokumen");
                            let form = td.find('form[data-type="verifikasi"]');
                            if (form.length) form.addClass('d-none');
                        }

                        // === UPDATE BADGE PADA UI (berdasarkan tahapan) ===
                        let tahapan = res.tahapan_id ?? tahapanId;
                        // badge id sesuai blade: badge-collect-<tahapan->id> dan persen-<tahapan_id>
                        let badgeCount = document.getElementById(`badge-collect-${tahapan}`);
                        if (badgeCount) badgeCount.textContent =
                            `${res.verified_now} / ${res.total_doc} Dokumen Terverifikasi`;

                        let persenBadge = document.getElementById(`persen-${tahapan}`);
                        if (persenBadge) persenBadge.textContent = `${res.nilai_realisasi}%`;

                        // jika kamu juga punya badge-tahapan (persentase tampil utama)
                        let mainBadge = document.getElementById(`badge-tahapan-${tahapan}`);
                        if (mainBadge && typeof res.nilai_realisasi !== 'undefined') {
                            mainBadge.innerText = `${res.nilai_realisasi}%`;
                        }
                    },
                    error: function(err) {
                        console.error(err);
                        alert('Terjadi kesalahan, silakan coba lagi.');
                    }
                });
            });


            function confirmVerifikasiTahapan(event, form) {
                event.preventDefault(); // cegah submit default

                const tahapanId = form.dataset.tahapanId; // pastikan di form ada data-tahapan-id="{{ $tahapan->id }}"

                Swal.fire({
                    title: 'Yakin ingin memverifikasi tahapan ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Verifikasi',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(async (result) => {
                    if (!result.isConfirmed) return;

                    const formData = new FormData(form);

                    try {
                        const res = await fetch(form.action, {
                            method: form.method,
                            body: formData,
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tidak Bisa Diverifikasi',
                                text: data.message || 'Terjadi kesalahan saat proses verifikasi.'
                            });
                            return;
                        }

                        // ✅ sukses verifikasi
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // ubah tampilan tombol Verifikasi
                        const btnVerifikasi = form.querySelector('button[type="submit"]');
                        btnVerifikasi.textContent = "Terverifikasi";
                        btnVerifikasi.classList.remove('btn-primary');
                        btnVerifikasi.classList.add('btn-success');
                        btnVerifikasi.disabled = true;

                        // NONAKTIFKAN INPUT / TOMBOL EDIT TAHAPAN UTAMA
                        const inputMain = document.getElementById(`persentase-${tahapanId}`);
                        const btnMain = document.getElementById(`btn-${tahapanId}`);
                        if (inputMain) inputMain.setAttribute('readonly', true);
                        if (btnMain) btnMain.disabled = true;

                        // NONAKTIFKAN INPUT / TOMBOL SUB-TAHAPAN
                        document.querySelectorAll(`input[data-projecttahapan="${tahapanId}"]`).forEach(input => {
                            input.setAttribute('readonly', true);
                        });
                        document.querySelectorAll(`button[id^="btn-sub-"]`).forEach(btn => {
                            if (btn.dataset.projecttahapan == tahapanId) {
                                btn.disabled = true;
                                btn.classList.replace('btn-success', 'btn-secondary');
                            }
                        });

                        // UPDATE TIMELINE REALTIME
                        const tdTimeline = form.closest('tr').querySelector('td.timeline-col');
                        if (tdTimeline && data.verified_at && data.rencana_end) {
                            const today = new Date(data.verified_at);
                            const deadline = new Date(data.rencana_end);

                            let diff = Math.floor((deadline - today) / (1000 * 60 * 60 * 24));
                            let statusText = '';
                            let badgeColor = '';

                            if (diff === 0) {
                                statusText = 'Selesai - Tepat Waktu';
                                badgeColor = 'success';
                            } else if (diff < 0) {
                                statusText = 'Selesai - Terlambat ' + Math.abs(diff) + ' Hari';
                                badgeColor = 'danger';
                            } else {
                                statusText = 'Selesai - Lebih Awal ' + diff + ' Hari';
                                badgeColor = 'success';
                            }

                            const timelineBadge = tdTimeline.querySelector('.timeline-badge');
                            if (timelineBadge) {
                                timelineBadge.textContent = statusText;
                                timelineBadge.className = `badge bg-${badgeColor} d-block mb-1 timeline-badge`;
                            } else {
                                tdTimeline.insertAdjacentHTML('afterbegin',
                                    `<span class="badge bg-${badgeColor} d-block mb-1 timeline-badge">${statusText}</span>`
                                );
                            }
                        }

                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat proses verifikasi.'
                        });
                    }
                });

                return false;
            }


            //untuk catatan
            document.addEventListener('DOMContentLoaded', function () {
                @if (session('open_catatan_modal'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: '{{ session('success') }}',
                        showConfirmButton: false,
                        timer: 2000
                    });
            
                    const modalEl = document.getElementById('modalCatatan');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                @endif
            });

            function toggleEditSave(id) {
                const input = document.getElementById(`persentase-${id}`);
                const btn = document.getElementById(`btn-${id}`);


                if (input.hasAttribute('readonly')) {
                    input.removeAttribute('readonly');
                    btn.textContent = "Simpan";
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success');
                } else {
                    const value = input.value;

                    const formData = new FormData();
                    formData.append('persentase_actual', value);

        fetch(`{{ route('projects.updateProgress', ':id') }}`
            .replace(':id', id), {

                            method: "POST", // 🔹 Ganti PATCH → POST
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                                "Accept": "application/json"
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                const nilai = parseFloat(data.data.realisasi);
                                const tampil = (nilai % 1 === 0) ? nilai.toFixed(0) : nilai.toString().replace('.', ',');
                                document.querySelector(`#badge-tahapan-${id}`).innerText = `${tampil}%`;

                                input.setAttribute('readonly', true);
                                btn.textContent = "Edit";
                                btn.classList.remove('btn-success');
                                btn.classList.add('btn-outline-primary');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Gagal memperbarui progress.', 'error'));
                }
            }


            function toggleEditSaveSub(subId) {
                const input = document.getElementById(`progress-sub-${subId}`);
                const btn = document.getElementById(`btn-sub-${subId}`);

                if (input.hasAttribute('readonly')) {
                    input.removeAttribute('readonly');
                    btn.textContent = 'Save';
                    btn.classList.replace('btn-outline-primary', 'btn-success');
                } else {
                    const projectTahapanId = input.dataset.projecttahapan;
                    const subTahapanId = input.dataset.subtahapan;
                    const value = input.value;

                    const formData = new FormData();
                    formData.append('sub_tahapan_id', subTahapanId);
                    formData.append('persentase_actual', value);

fetch(`{{ route('projects.updateProgress', ':id') }}`
    .replace(':id', projectTahapanId), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                input.setAttribute('readonly', true);
                                btn.textContent = 'Edit';
                                btn.classList.replace('btn-success', 'btn-outline-primary');

                                // === 🔹 Update badge utama secara realtime ===
                                const badgeUtama = document.querySelector(`#badge-tahapan-${projectTahapanId}`);
                                if (badgeUtama && data.data.realisasi !== undefined) {
                                    const nilai = parseFloat(data.data.realisasi);
                                    const tampil = (nilai % 1 === 0) ?
                                        nilai.toFixed(0) :
                                        nilai.toString().replace('.', ',');
                                    badgeUtama.innerText = `${tampil}%`;
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Tersimpan!',
                                    text: 'Progress sub-tahapan berhasil disimpan.',
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menyimpan progress.'
                            });
                        });
                }
            }

        //store tambahan tahapan
        document.addEventListener('DOMContentLoaded', function () {
        
            // toggle opsi saat checkbox dicentang
            document.querySelectorAll('.pilih-opsional').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const id = this.dataset.id;
                    const box = document.getElementById('opsi-' + id);
                    box.classList.toggle('d-none', !this.checked);
                });
            });
        
            // validasi sebelum submit
            const btn = document.getElementById('btnSimpanTahapan');
            if (!btn) return;
        
            btn.addEventListener('click', function (e) {
                const tahapanDipilih = document.querySelectorAll(
                    'input[name="tahapan_opsional[]"]:checked'
                );
        
                if (tahapanDipilih.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tahapan belum dipilih',
                        text: 'Pilih minimal satu tahapan opsional dulu ya',
                    });
                    return;
                }
        
                for (const cb of tahapanDipilih) {
                    const tahapId = cb.value;
                    const sisipSelect = document.querySelector(
                        `select[name="sisip_setelah[${tahapId}]"]`
                    );
        
                    if (!sisipSelect || sisipSelect.value === '') {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Posisi tahapan belum ditentukan',
                            text: 'Pilih “sisipkan setelah tahapan apa” untuk setiap tahapan yang dipilih',
                        });
                        return;
                    }
                }
            });
        });

        </script>
    @endsection
