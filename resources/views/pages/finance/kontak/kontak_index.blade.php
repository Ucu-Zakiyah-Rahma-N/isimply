@extends('app.template')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Data Kontak </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKontak">
                <i class="bi bi-plus-circle"></i> Tambah kontak
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Bank</th>
                        <th>Rekening</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($kontak as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ ucfirst($item->tipe_kontak) }}
                            </span>
                        </td>
                        <td>{{ $item->email ?? '-' }}</td>
                        <td>{{ $item->no_hp ?? '-' }}</td>
                        <td>{{ $item->nama_bank ?? '-' }}</td>
                        <td>{{ $item->no_rekening ?? '-' }}</td>
                        <td class="text-center">

                            <!-- EDIT -->
                            <button
                                class="btn btn-sm btn-warning btnEdit"
                                data-id="{{ $item->id }}"
                                data-nama="{{ $item->nama }}"
                                data-tipe="{{ $item->tipe_kontak }}"
                                data-email="{{ $item->email }}"
                                data-no_hp="{{ $item->no_hp }}"
                                data-alamat="{{ $item->alamat }}"
                                data-nama_bank="{{ $item->nama_bank }}"
                                data-no_rek="{{ $item->no_rekening }}"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditKontak">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <!-- DELETE -->
                            <form action="{{ route('finance.kontak.destroy', $item->id) }}"
                                method="POST"
                                class="d-inline formDelete">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-3">
                            Tidak ada data
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('pages.finance.kontak.modal_tambah')
@include('pages.finance.kontak.modal_edit')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('success') }}",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: "{{ session('error') }}",
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
        @endif

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.formDelete').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Hapus kontak?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

            });
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const editButtons = document.querySelectorAll('.btnEdit');
        const form = document.getElementById('formEditKontak');

        editButtons.forEach(button => {

            button.addEventListener('click', function() {

                const id = this.dataset.id;

                document.getElementById('edit_id').value = id;
                document.querySelector('#modalEditKontak input[name="nama"]').value = this.dataset.nama;
                document.querySelector('#modalEditKontak select[name="tipe_kontak"]').value = this.dataset.tipe;
                document.querySelector('#modalEditKontak input[name="email"]').value = this.dataset.email;
                document.querySelector('#modalEditKontak input[name="no_hp"]').value = this.dataset.no_hp;
                document.querySelector('#modalEditKontak textarea[name="alamat"]').value = this.dataset.alamat;
                document.querySelector('#modalEditKontak input[name="nama_bank"]').value = this.dataset.nama_bank;
                document.querySelector('#modalEditKontak input[name="no_rek"]').value = this.dataset.no_rek;

                // set dynamic action
                form.action = `/finance/kontak/${id}`;
            });

        });

    });
</script>
@endsection