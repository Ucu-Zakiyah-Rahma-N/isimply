@extends('app.template')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <h5 class="card-title mb-0">Data Marketing</h5>
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus me-1"></i> Tambah Marketing
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">No</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th style="width: 10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($marketing as $m)
                        <tr>
                            <td>{{ $loop->iteration + ($marketing->currentPage() - 1) * $marketing->perPage() }}</td>
                            <td>{{ $m->nama }}</td>
                            <td>{{ ucfirst($m->status) }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                <form action="{{ route('marketing.destroy', $m->id) }}" method="POST"
                                class="delete-marketing">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                                </button>
                                </form>
                                </div>
                            </td>
                        </tr> 
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada data marketing</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- ✅ Pagination di kanan bawah --}}
        <div class="d-flex justify-content-end mt-3">
            {{ $marketing->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>



{{-- Modal Tambah Marketing --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-light text-dark rounded-top-4 border-bottom">
            <h5 class="modal-title fw-semibold" id="modalTambahLabel">
                <i class="fas fa-file-signature me-2 text-secondary"></i> Tambah Marketing
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

            <form action="{{ route('marketing.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control form-control-lg rounded-3 @error('nama') is-invalid @enderror"
                               id="nama"
                               name="nama"
                               placeholder="Masukkan nama marketing"
                               required>
                        @error('nama_tahapan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">
                            Status <span class="text-danger">*</span>
                        </label>
                    
                        <select
                            class="form-select form-select-lg rounded-3 @error('status') is-invalid @enderror"
                            id="status"
                            name="status"
                            required
                        >
                            <option value="">-- Pilih Status --</option>
                            <option value="internal"  {{ old('status') == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="cabang"    {{ old('status') == 'cabang' ? 'selected' : '' }}>Cabang</option>
                            <option value="freelance" {{ old('status') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        </select>
                    
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-warning text-dark rounded-pill px-4 fw-semibold shadow-sm border-0" 
                    data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
        //DELETE 
        const deleteForms = document.querySelectorAll('.delete-marketing');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // hentikan submit default

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data staf marketing akan dihapus permanen!",
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
