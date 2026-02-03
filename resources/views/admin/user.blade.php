@extends('app.template')

@section('content')

{{-- @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
    </script>
@endif --}}

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 2000
        });
    </script>
    {{ session()->forget('error') }}
@endif

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <h5 class="card-title mb-0">Data User</h5>
            <a href="{{ url('user/create') }}" class="btn btn-primary">Tambah User</a>
        </div>
    </div>
    <div class="card-body">
        <div class="responsive">
            <table class="table">
                <thead>
                    <th>No</th>
                    {{-- <th>Nama Perusahaan</th> --}}
                    <th>Username</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </thead>
                <tbody>
                    @php
                    $nomor = ($users->currentPage() - 1) * $users->perPage() + 1;
                    @endphp
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $nomor++ }}</td>
                            {{-- <td>{{ $user->customer->nama_perusahaan ?? '-' }}</td> --}}
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                            <div class="dropdown">
                            <a class="btn btn-secondary btn-sm dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Opsi
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ url('ubah_password/' . $user->id) }}">Ubah Password</a></li>
                                <li>
                                    <a href="javascript:void(0);" 
                                    class="dropdown-item btn-delete-user" 
                                    data-id="{{ $user->id }}" 
                                    data-nama="{{ $user->username }}">
                                    Hapus User
                                    </a>
                                </li>
                            </ul> 
                            </td>
                        </tr> 
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-end mt-3">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('.btn-delete-user').click(function () {
        const userId = $(this).data('id');
        const userName = $(this).data('nama');
        const row = $(this).closest('tr'); // ambil baris tabel yang akan dihapus

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "User: " + userName,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('user.destroy', ':id') }}".replace(':id', userId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        // Hapus baris dari tabel dulu
                        row.fadeOut(400, function() {
                            $(this).remove();
                        });

                        // Baru tampilkan alert sukses
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menghapus user.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        });
    });
});
</script>


@endsection