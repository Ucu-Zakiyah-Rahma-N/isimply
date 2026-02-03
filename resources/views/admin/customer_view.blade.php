@extends('app.template')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <h5 class="card-title mb-0">Data Customer</h5>
            <a href="{{ url('customer/create') }}" class="btn btn-primary fw-semibold rounded-pill px-3">
                 <i class="bi bi-plus-circle me-1"></i>Tambah Customer</a>
        </div>
    </div>

    <div class="card-body table-responsive">
        <p class="text-muted small">Cek data customer terlebih dahulu sebelum menambahkan </p>

        @php
            $no = ($customer->currentPage() - 1) * $customer->perPage() + 1;
        @endphp
<form method="GET" action="{{ route('customer.index') }}" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Cari Nama Perusahaan</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Masukkan nama perusahaan...">
        </div>

        <div class="col-md-auto d-flex gap-1">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>

            @if(request('search'))
                <a href="{{ route('customer.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </div>
    </div>
</form>


        <table class="table table-bordered align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th rowspan="2" class="align-middle">No</th>
                    <th rowspan="2" class="align-middle">Nama Perusahaan</th>
                    <th colspan="3" class="align-middle bg-light fw-semibold text-dark">Lokasi</th>
                    <th colspan="2" class="align-middle bg-light fw-semibold text-dark">PIC Marketing</th>
                    <th colspan="3" class="align-middle bg-light fw-semibold text-dark">PIC Perusahaan</th>
                    <th rowspan="2" class="align-middle">Aksi</th>
                </tr>
                <tr>
                    <th>Kabupaten</th>
                    <th>Kawasan</th>
                    <th>Detail Alamat</th>
                    <th>Status</th>
                    <th>Nama</th>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customer as $c)
                    @php
                        // decode json pic_perusahaan
                        $picList = is_string($c->pic_perusahaan)
                            ? json_decode($c->pic_perusahaan, true)
                            : $c->pic_perusahaan;

                        // cari PIC utama
                        $utama = collect($picList ?? [])->firstWhere('utama', true);
                    @endphp

                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $c->nama_perusahaan }}</td>
                        <td>{{ $c->kabupaten_name ?? '-' }}</td>
                        <td>{{ $c->kawasan_industri->nama_kawasan ?? '-' }}</td>
                        <td>{{ $c->detail_alamat ?? '-' }}</td>
                        <td>{{ $c->marketing->status ?? '-' }}</td>
                        <td>{{ $c->marketing->nama ?? '-' }}</td>

                        @if($utama)
                            <td>{{ $utama['nama'] ?? '-' }}</td>
                            <td>{{ $utama['kontak'] ?? '-' }}</td>
                            <td>{{ $utama['email'] ?? '-' }}</td>
                        @else
                            <td colspan="3" class="text-center"><em>Tidak ada PIC utama</em></td>
                        @endif

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('customer.edit', $c->id) }}"
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                        
                                <form action="{{ route('customer.destroy', $c->id) }}"
                                      method="POST"
                                      class="delete-customer-form d-inline">
                                    @csrf
                                    @method('DELETE')
                        
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            <strong>Tidak ada nama perusahaan yang terlampir</strong>
            
                            @if(request('search'))
                                <div class="small mt-1">
                                    Kata kunci: <b>{{ request('search') }}</b>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3 d-flex justify-content-end">
        {{ $customer->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>


<script>
        //DELETE 
        const deleteForms = document.querySelectorAll('.delete-customer-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // hentikan submit default

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data customer akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // submit form jika konfirmasi
                }
            });
        });
    });
</script>
@endsection
