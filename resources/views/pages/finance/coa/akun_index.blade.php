@extends('app.template')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Data Akun </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAkun">
                <i class="bi bi-plus-circle"></i> Tambah Akun
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Kode Akun</th>
                        <th>Nama Akun</th>
                        <th width="20%">Kategori</th>
                        <th width="10%">Pajak</th>
                        <th width="15%">Saldo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($akun as $header)
                    {{-- HEADER AKUN --}}
                    <tr class="table-secondary fw-semibold"
                        data-bs-toggle="collapse"
                        data-bs-target=".child-{{ $header->id }}"
                        style="cursor:pointer">

                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <i class="bi bi-caret-down-fill me-1"></i>
                            {{ $header->kode_akun }}
                        </td>
                        <td>{{ $header->nama_akun }}</td>
                        <td>{{ $header->kategori_akun }}</td>
                        <td class="text-center">-</td>
                        <td class="text-end">-</td>
                    </tr>

                    {{-- SUB AKUN --}}
                    @foreach ($header->children as $child)
                    <tr class="collapse child-{{ $header->id }}">
                        <td></td>
                        <td class="ps-4 text-muted">
                            └ {{ $child->kode_akun }}
                        </td>
                        <td>{{ $child->nama_akun }}</td>
                        <td>{{ $child->kategori_akun }}</td>
                        <td class="text-center">
                            {{ $child->nilai_coa ?? '-' }}
                        </td>
                        <td class="text-end">
                            {{ number_format($child->saldo ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach

                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Data akun belum tersedia
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('pages.finance.coa.modal_tambah')
@endsection