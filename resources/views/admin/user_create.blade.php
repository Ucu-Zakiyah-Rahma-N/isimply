@extends('app.template')

@section('content')

<a class="btn btn-danger mb-3" href="{{ url('users') }}">Kembali</a>

<div class="card">
    <div class="card-body">
        <form action="{{ route('user.store') }}" method="POST">
            @csrf

            {{-- Pilih nama perusahaan --}}
            <div class="mb-3">
                <label for="username" class="form-label">Nama Perusahaan</label>
                <select class="form-control @error('customer_id') is-invalid @enderror"
                id="customer-select" name="customer_id" required >
                    <option value="">Ketik atau pilih nama perusahaan...</option>
                    {{-- khusus untuk akun karyawan --}}
                    <option value="simply">PT Simply Dimensi Indonesia</option> 

                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->nama_perusahaan }}</option>
                    @endforeach
                </select>
                {{-- Error message tampil di sini --}}
                @error('customer_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username <span class="text-danger" >*</span></label>
                <input type="text" class="form-control" id="username" name="username" value="username">
            </div>

            {{-- Password --}}
            <div class="mb-3 position-relative">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                        id="password" name="password" value="{{ old('password') }}">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- role --}}
            <div class="mb-3">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="customer">Customer</option>
                        <option value="admin marketing">Admin Marketing</option>
                        <option value="manager marketing">Manager Marketing</option>
                        <option value="manager proyek">Manager Proyek</option>
                        <option value="manager finance">Manager Finance</option>
                    </select>
            </div>


            <button type="submit" class="btn btn-success">Tambahkan User</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#customer-select').select2({
        placeholder: 'Pilih nama perusahaan...',
        allowClear: true,
        width: '100%'
    });
});

 document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    });
</script>
@endpush
