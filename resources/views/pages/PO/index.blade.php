@extends('app.template')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <h5 class="card-title mb-0">Data PO/SPK</h5>
            @if (
            auth()->user()->role === 'superadmin' ||
            (auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id == 1))
            <a href="{{ url('PO/create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah PO/SPK
            </a>
            @endif
        </div>
    </div>

    {{-- 🔹 Card Body --}}
    <div class="card-body">
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
                    @foreach ($quotation->pluck('kawasan_name')->unique()->filter() as $kawasan)
                    <option value="{{ $kawasan }}" {{ request('kawasan') == $kawasan ? 'selected' : '' }}>
                        {{ $kawasan }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Perizinan 1 -->
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

            <div class="col-md-6">
                <label class="form-label fw-semibold">Cari</label>

                <div class="d-flex gap-2 align-items-center">

                    {{-- No SPH --}}
                    <input type="text" id="searchSPH"
                        value="{{ request('sph') }}"
                        class="form-control"
                        placeholder="No SPH">

                    {{-- No PO --}}
                    <input type="text" id="searchPO"
                        value="{{ request('po') }}"
                        class="form-control"
                        placeholder="No PO">

                    {{-- Cabang (conditional) --}}
                    @if (!(auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id != 1))
                    <select id="filterCabang" class="form-select" style="max-width: 180px;">
                        <option value="">Cabang</option>
                        @foreach ($cabang as $c)
                        <option value="{{ $c->id }}"
                            {{ request('cabang') == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_cabang }}
                        </option>
                        @endforeach
                    </select>
                    @endif

                    {{-- Reset --}}
                    <button id="resetFilter" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>

                </div>
            </div>

            <div class="table-responsive">
                @php
                $no = ($po->currentPage() - 1) * $po->perPage() + 1;
                @endphp

                <table id="POTable" class="table table-bordered align-middle">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th>No</th>
                            <th>File PO</th>
                            <th>No PO</th>
                            <th>Tgl PO</th>
                            <th>Ref SPH</th>
                            <th>Nama Perusahaan</th>
                            <th>Jenis Perizinan</th>
                            <th>luas</th>
                            <th>Nama Bangunan</th>
                            <th>Kabupaten</th>
                            <th>Kawasan</th>
                            <th>Detail Alamat</th>
                            <th>Nominal Pekerjaan</th>
                            <th>Waktu Pekerjaan</th>
                            <th>Lama Waktu Pekerjaan</th>
                            <th>PIC Perusahaan</th>
                            <th>Kontak</th>
                            <th>PIC Keuangan</th>
                            <th>Kontak</th>
                            <th>Verifikasi</th>
                            <th>Tgl BAST</th>
                            @if (
                            auth()->user()->role === 'superadmin' ||
                            (auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id == 1)
                            )
                            <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($po as $item)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td class="text-center">
                                @if ($item->file_path)
                                <button class="btn btn-sm btn-danger"
                                    onclick="openPDFModal('{{ route('files.view', $item->file_path) }}')">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat
                                </button>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->no_po ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tgl_po)->format('d-m-Y') }}</td>
                            <td>{{ $item->quotation->no_sph }}</td>
                            <td>{{ $item->customer->nama_perusahaan ?? '-' }}</td>
                            {{-- Jenis Perizinan --}}
                            <td>
                                @if ($item->perizinan->isNotEmpty())
                                @foreach ($item->perizinan as $izin)
                                <span
                                    class="badge bg-primary-subtle text-dark border">{{ $izin->jenis }}</span>
                                @endforeach
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            {{-- Luas Bangunan (jika ada) --}}
                            <td>{{ $item->luas_info ?? '-' }} </td>
                            <td>{{ $item->quotation->nama_bangunan ?? '-' }}</td>
                            <td>{{ $item->kabupaten_name ?? '-' }}</td>
                            <td>{{ $item->kawasan_name ?? '-' }}</td>
                            <td>{{ $item->quotation->detail_alamat ?? '-' }}</td>
                            {{-- Harga Pekerjaan --}}
                            <td>
                                Rp {{ number_format(optional($item->quotation)->grand_total ?? 0, 0, ',', '.') }}
                            </td>
                            <td>{{ $item->quotation->lama_pekerjaan . 'hari' ?? '-' }}</td>
                            <!-- <td>
                                    @if($item->tgl_po)
                                        @php
                                            $tanggalPO = \Carbon\Carbon::parse($item->tgl_po)->addDay(); // tambah 1 hari
                                            $today = now();

                                            $lama = $tanggalPO->diffInDays($today);
                                        @endphp

                                        {{ round($lama) }} hari
                                    @else
                                        -
                                    @endif
                                </td> -->
                            <td>
                                {{ $item->umur }} hari
                                @if($item->status_project == 'Selesai')
                                <span class="text-success">(Selesai)</span>
                                @endif
                            </td>
                            <td>
                                @if (!empty($item->primary_pic))
                                {{ $item->primary_pic['nama'] ?? '-' }}
                                @elseif(!empty($picsUtama[$item->customer_id]))
                                {{ $picsUtama[$item->customer_id]->nama_pic ?? '-' }}
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if (!empty($item->primary_pic))
                                {{ $item->primary_pic['kontak'] ?? '-' }}
                                @elseif(!empty($picsUtama[$item->customer_id]))
                                {{ $picsUtama[$item->customer_id]->kontak_pic ?? '-' }}
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $item->nama_pic_keuangan ?? '-' }}</td>
                            <td>{{ $item->kontak_pic_keuangan ?? '-' }}</td>
                            <td>
                                @if ($item->bast_verified)
                                <span class="badge bg-success">BAST Terverifikasi</span>
                                @else
                                @php
                                $canVerify =
                                auth()->user()->role === 'superadmin' ||
                                (auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id == 1);
                                @endphp

                                <button
                                    class="btn btn-warning btn-sm verify-bast-btn {{ !$canVerify ? 'disabled' : '' }}"
                                    data-url="{{ $canVerify ? route('po.verifyBast', $item->id) : '' }}"
                                    {{ !$canVerify ? 'disabled title=Tidak memiliki hak verifikasi' : '' }}>
                                    Verifikasi BAST
                                </button>
                                @endif
                            </td>
                            <td>
                                @if ($item->bast_verified_at)
                                {{ \Carbon\Carbon::parse($item->bast_verified_at)->format('d-m-Y H:i') }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            @if (
                            auth()->user()->role === 'superadmin' ||
                            (auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id == 1)
                            )
                            <td>
                                <a href="{{ route('po.edit', $item->id) }}"
                                    class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
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
            <div class="mt-3 d-flex justify-content-end">
                {{ $po->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                document.querySelectorAll('.verify-bast-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        let url = this.dataset.url;

                        Swal.fire({
                            title: 'Verifikasi BAST?',
                            text: 'Yakin ingin memverifikasi BAST untuk PO ini?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, verifikasi',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
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
                                            setTimeout(() => location.reload(), 1500);
                                        } else {
                                            Swal.fire('Gagal', data.message, 'error');
                                        }
                                    })
                                    .catch(() => {
                                        Swal.fire('Gagal', 'Terjadi kesalahan server', 'error');
                                    });
                            }
                        });
                    });
                });

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


                // // KABUPATEN → langsung reload
                // document.getElementById('filterKabupaten').addEventListener('change', function() {
                //     reloadWith('kabupaten', this.value);
                // });

                // // KAWASAN
                // document.getElementById('filterKawasan').addEventListener('change', function() {
                //     reloadWith('kawasan', this.value);
                // });

                // PERIZINAN
                document.getElementById('filterPerizinan').addEventListener('change', function() {
                    reloadWith('perizinan', this.value);
                });

                // CABANG
                const filterCabang = document.getElementById('filterCabang');

                if (filterCabang) {
                    filterCabang.addEventListener('change', function() {
                        reloadWith('cabang', this.value);
                    });
                }


                // LIVE SEARCH NO SPH
                let typingTimer;
                const delay = 600;
                document.getElementById('searchSPH').addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    const value = this.value;

                    typingTimer = setTimeout(() => {
                        reloadWith('sph', value);
                    }, delay);
                });
                document.getElementById('searchPO').addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    const value = this.value;

                    typingTimer = setTimeout(() => {
                        reloadWith('po', value);
                    }, delay);
                });

                // RESET
                document.getElementById('resetFilter').addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = window.location.pathname;
                });

            });

            //lihat pddf
            function openPDFModal(fileUrl) {
                // Set src iframe
                document.getElementById('pdfViewer').src = fileUrl;

                // Tampilkan modal (Bootstrap 5)
                var pdfModal = new bootstrap.Modal(document.getElementById('pdfModal'));
                pdfModal.show();
            }
        </script>
        @endsection