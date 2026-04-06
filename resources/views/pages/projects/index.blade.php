@extends('app.template')
{{-- auto refresh halaman --}}
@section('meta-cache')
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
@endsection

@php
    function formatDesimal($angka)
    {
        return rtrim(rtrim(number_format($angka, 2, '.', ''), '0'), '.');
    }
@endphp

@section('content')
    <div class="card">
        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center mb-0">
                <h5 class="card-title mb-0">Data Projek</h5>
            </div>
        </div>

        {{-- REKAP PROJECT --}}
        <div class="card-body">
            <div class="row g-3 justify-content-center">

                {{-- Belum Mulai --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <i class="ti ti-clock fs-4"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted">Belum Mulai</h6>
                                <h4 class="fw-bold mb-0">{{ $rekap['belum_mulai'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- On Progress --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <i class="ti ti-loader fs-4"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted">On Progress</h6>
                                <h4 class="fw-bold mb-0">{{ $rekap['on_progress'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Selesai --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <i class="ti ti-check fs-4"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted">Selesai</h6>
                                <h4 class="fw-bold mb-0">{{ $rekap['selesai'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Project --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <i class="ti ti-list fs-4"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted">Total Project</h6>
                                <h4 class="fw-bold mb-0">{{ $rekap['total'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <br>

            {{-- 🔹 Filter Section --}}
            <div class="row align-items-end mb-3 g-2">
                
                <div class="col-md-2">
                    <label for="filterKabupaten" class="form-label fw-semibold">Kabupaten</label>
                    <select id="filterKabupaten" class="form-select">
                        <option value="">Semua Kabupaten</option>
                        @foreach ($wilayahs->where('jenis', 'kabupaten') as $kab)
                            <option value="{{ $kab->kode }}" {{ request('kabupaten') == $kab->kode ? 'selected' : '' }}>
                                {{ $kab->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filterKawasan" class="form-label fw-semibold">Kawasan</label>
                    <select id="filterKawasan" class="form-select">
                        <option value="">Semua Kawasan</option>
                        @foreach ($projects->pluck('kawasan_name')->unique()->filter() as $kawasan)
                            <option value="{{ $kawasan }}" {{ request('kawasan') == $kawasan ? 'selected' : '' }}>
                                {{ $kawasan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                            <label for="filterPerizinan" class="form-label fw-semibold">
                                Jenis Perizinan
                            </label>
                            <select id="filterPerizinan" class="form-select">
                                <option value="">Semua Jenis Perizinan</option>
                                @foreach ($perizinan as $izin)
                                    <option value="{{ $izin->jenis }}"
                                        {{ request('perizinan') == $izin->jenis ? 'selected' : '' }}>
                                        {{ $izin->jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cari Perusahaan / Bangunan</label>
                    <input type="text"
                        id="searchGlobal"
                        name="searchGlobal"
                        value="{{ request('searchGlobal') }}"
                        class="form-control"
                        placeholder="Nama perusahaan atau nama bangunan...">
                </div>

                <div class="col-md-3">
                    <label for="searchPO" class="form-label fw-semibold">Cari No PO</label>
                    <div class="d-flex">
                        <input type="text" id="searchPO" class="form-control me-2" placeholder="Masukkan No PO">
                        <button id="resetFilter" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
                </div>
            </div>

        </div>

        <div class="table-responsive px-3 pb-3">
            @php
                $no = ($projects->currentPage() - 1) * $projects->perPage() + 1;
            @endphp

            <table id="projectsTable" class="table table-bordered align-middle mb-0">
                <thead class="table-light text-center align-middle">
                    <tr>
                        <th>No</th>
                        <th>PIC Projek</th>
                        <th>Nama Perusahaan</th>
                        <th>Nama Bangunan</th>
                        <th>Kabupaten</th>
                        <th>Kawasan</th>
                        <th>Detail Alamat</th>
                        <th>Luasan</th>
                        <th>Jenis Perizinan</th>
                        <th>No PO</th>
                        <th>Tanggal PO</th>
                        <th>Lama Pekerjaan</th>
                        <th>Sisa Waktu</th>
                        <th>Kekurangan Dokumen</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                        <th>Tgl BAST</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $project->nama ?? '-' }}</td>
                            <td>{{ $project->nama_perusahaan ?? '-' }}</td>
                            <td>{{ $project->nama_bangunan }}</td>
                            <td>{{ $project->kabupaten_name ?? '-' }}</td>
                            <td>{{ $project->kawasan_name ?? '-' }}</td>
                            <td>{{ $project->detail_alamat ?? '-' }}</td>
                            <td>{{ $project->luasan ?? '-' }}</td>
                            <td>
                                @if (!empty($project->jenis_perizinan))
                                    @foreach (explode(',', $project->jenis_perizinan) as $izin)
                                        <span class="badge bg-primary-subtle text-dark border me-1">
                                            {{ trim($izin) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $project->no_po ?? '-' }}</td>
                            <td>{{ $project->tgl_po ? \Carbon\Carbon::parse($project->tgl_po)->format('d-m-Y') : '-' }}
                            </td>
                            <td>{{ $project->lama_pekerjaan ? $project->lama_pekerjaan . ' hari' : '-' }}</td>
                            <td>
                                @if (!is_null($project->sisaHari))
                                    @if ($project->sisaHari > 0)
                                        {{ $project->sisaHari }} hari lagi
                                    @elseif($project->sisaHari == 0)
                                        Deadline hari ini
                                    @else
                                        Deadline sudah lewat {{ abs($project->sisaHari) }} hari
                                    @endif
                                @else
                                    {{-- Collect Dokumen belum selesai, deadline belum dihitung --}}
                                    Dimulai setelah dokumen lengkap
                                @endif
                            </td>
                            <td class="fw-semibold">
                                <div id="status-doc-{{ $project->project_id }}">

                                    {{-- Project belum dibuat --}}
                                    @if (!$project->sudah_buat_project)
                                        -

                                        {{-- Punya tahapan collect dokumen --}}
                                    @elseif($project->punya_collect_dokumen)
                                        @php
                                            $unverified = $project->jumlah_unverified ?? 0;
                                            $actual = $project->persentase_actual ?? 0;
                                            $target = $project->persentase_target ?? 0;

                                            $dokumenLengkap = $unverified == 0 && $actual == $target;
                                        @endphp

                                        {{-- Jika dokumen lengkap --}}
                                        @if ($dokumenLengkap)
                                            <span class="fw-semibold text-success">
                                                Dokumen Lengkap <i class="bi bi-check-circle-fill"></i>
                                            </span>

                                            {{-- Jika belum lengkap --}}
                                        @else
                                            <div class="d-flex flex-column">
                                                <div class="mb-1">
                                                    <span class="badge bg-info">
                                                        {{ $unverified }} Dokumen
                                                    </span>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary">
                                                        {{ formatDesimal($actual) }}%
                                                    </span>

                                                    <span class="small">dari</span>

                                                    <span class="badge bg-secondary">
                                                        {{ formatDesimal($target) }}%
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Tidak punya tahapan collect dokumen --}}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>

                            {{-- ini kalo mau nampilin list seluruh dokumen yang blm di verif (tidak dipakai karena kurang efisien tampilannya) 
                            <td>
                            @if (count($project->kekurangan_dokumen) == 0)
                                <span class="text-success">Lengkap</span>
                            @else
                                <ul class="m-0 ps-3">
                                    @foreach ($project->kekurangan_dokumen as $dokumen)
                                        <li>{{ $dokumen }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td> --}}
                            <td>{{ $project->catatan_terakhir ?? '-' }}</td>
                            {{-- masih eror ketika nambah po --}}

                            <td>
                                <span
                                    class="badge 
                                    @if ($project->status_project == 'Belum Mulai') bg-secondary
                                    @elseif($project->status_project == 'On Progress') bg-warning
                                    @elseif($project->status_project == 'Selesai') bg-success
                                    @else bg-dark @endif">
                                    {{ $project->status_project }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $adminRoles = ['admin 1', 'admin 2', 'admin 3', 'admin 4', 'admin 5', 'admin 6', 'admin 7', 'admin 8', 'admin 9', 'admin 10', 'admin 11'];
                                    $viewerRoles = ['CEO', 'direktur', 'manager projek', 'manager finance', 'manager marketing'];
                                @endphp



                                @if (in_array(auth()->user()->role, $adminRoles))
                                    <div class="dropdown">
                                        <a class="btn btn-secondary btn-sm dropdown-toggle" href="#" role="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            Opsi
                                        </a>
                                        <ul class="dropdown-menu">

                                            {{-- TOMBOL TAMBAH --}}
                                            @if (!$project->sudah_buat_project)
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('projects.create', $project->po_id) }}">
                                                        Tambah
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <span class="dropdown-item text-muted disabled"
                                                        style="opacity:0.6; cursor:not-allowed;">
                                                        Tambah (Sudah Dibuat)
                                                    </span>
                                                </li>
                                            @endif

                                            {{-- TOMBOL VERIFIKASI --}}
                                            @if ($project->sudah_buat_project)
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('projects.verifikasi', $project->po_id) }}">
                                                        Verifikasi
                                                    </a>
                                                </li>
                                            @endif

                                        </ul>
                                    </div>
                                @endif

                                @if (in_array(auth()->user()->role, $viewerRoles) && $project->sudah_buat_project)
                                    <a class="btn btn-sm btn-info"
                                        href="{{ route('projects.verifikasi', $project->po_id) }}">
                                        Detail
                                    </a>
                                @endif


                            </td>
                            <td>{{ \Carbon\Carbon::parse($project->bast_verified_at)->format('d-m-Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                        <td colspan="17" class="text-center text-muted py-4">
                            <i class="bi bi-search me-1"></i>
                            <strong>Data tidak ditemukan</strong>
                            <div class="small mt-1">
                                Silakan ubah atau reset filter
                            </div>
                        </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $projects->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>




    {{-- Script Filter --}}
    {{-- Script Filter --}}
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
         <script>
            document.addEventListener('DOMContentLoaded', function() {

            const params = new URLSearchParams(window.location.search);

            function reloadWith(key, value) {
                if (value) {
                    params.set(key, value);
                } else {
                    params.delete(key);
                }
                params.delete('page'); // reset page saat filter
                window.location.search = params.toString();
            }

            $('#filterKabupaten, #filterKawasan').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih atau ketik untuk mencari...',
                allowClear: true,
                width: '100%'
            });

            // 🔹 EVENT CHANGE TETAP JALAN
            $('#filterKabupaten').on('change', function() {
                reloadWith('kabupaten', $(this).val());
            });

            $('#filterKawasan').on('change', function() {
                reloadWith('kawasan', $(this).val());
            });

// PERIZINAN
            document.getElementById('filterPerizinan').addEventListener('change', function() {
                reloadWith('perizinan', this.value);
            });

            let typingTimer;
            const delay = 600;

            document.getElementById('searchPO').addEventListener('input', function() {
                clearTimeout(typingTimer);
                const value = this.value;

                typingTimer = setTimeout(() => {
                    reloadWith('searchPO', value);
                }, delay);
            });

            document.getElementById('searchGlobal').addEventListener('input', function () {
                clearTimeout(typingTimer);
                const value = this.value;

                typingTimer = setTimeout(() => {
                    reloadWith('searchGlobal', value);
                }, delay);
            });

            document.getElementById('resetFilter').addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = window.location.pathname;
            });

    //DELETE 
        const deleteForms = document.querySelectorAll('.delete-project-form');
        })
    </script>


@endsection
