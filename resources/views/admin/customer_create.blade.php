@extends('app.template')

@section('content')
<div class="container"> 

    {{-- Tombol kembali di atas card --}}
    {{-- <a class="btn btn-danger mb-3" href="{{ url('customer') }}">Kembali</a> --}}


    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Data Customer</h5>
        </div>

        <div class="card-body">
            <form action="{{ url('customer') }}" method="POST">
                @csrf

                {{-- Nama Perusahaan --}}
                <div class="mb-3">
                    <label class="form-label">Nama Perusahaan *</label>
                    <p class="text-muted small">Pastikan nama perusahaan lengkap. Contoh : PT Simply Dimensi Indonesia</p>
                    <input type="text"
                           name="nama_perusahaan"
                           id="nama_perusahaan"
                           class="form-control"
                           autocomplete="off">
                </div>

                {{-- Alamat --}}
                <div class="mb-3">
                    <label for="provinsi_id">Provinsi</label>
                    <select id="provinsi_id" name="provinsi_id" class="form-select">
                        <option value="">-- Pilih Provinsi --</option>
                        @foreach($provinsiList as $prov)
                            <option value="{{ $prov->kode }}">{{ $prov->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="kabupaten_id">Kabupaten / Kota</label>
                    <select id="kabupaten_id" name="kabupaten_id" class="form-select">
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="kawasan_id">Kawasan</label>
                    <select id="kawasan_id" name="kawasan_id" class="form-select">
                        <option value="">-- Pilih Kawasan --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="detail_alamat" class="form-label">Detail lainnya<span class="text-danger">*</span></label>
                    <input type="detail_alamat" name="detail_alamat" id="detail_alamat"
                           class="form-control @error('detail_alamat') is-invalid @enderror"
                           value="{{ old('detail_alamat') }}">
                    @error('detail_alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Pilih Status Marketing --}}
                <div class="mb-3">
                        <label for="status" class="form-label">Status Marketing</label>
                        <select id="status" class="form-select">
                            <option value="">-- Pilih Status --</option>
                            @foreach($statusList as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                {{-- Pilih nama marketing --}}
                    <div class="mb-3">
                        <label for="marketing_id" class="form-label">Nama Marketing</label>
                        <select name="marketing_id" id="marketing_id" class="form-select" required>
                            <option value="">-- Pilih Marketing --</option>
                        </select>
                    </div>

                {{-- PIC Perusahaan --}}
                <div class="mb-3">
                    <label class="form-label">PIC Perusahaan</label>
                    <div id="pic-wrapper">
                        <div class="row g-2 mb-2 pic-item">
                            <div class="col-md-3">
                                <input type="text" name="pic_perusahaan[0][nama]" class="form-control" placeholder="Nama PIC">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="pic_perusahaan[0][kontak]" class="form-control" placeholder="No HP">
                            </div>
                            <div class="col-md-3">
                                <input type="email" name="pic_perusahaan[0][email]" class="form-control" placeholder="Email">
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <input class="form-check-input me-2" type="radio" name="utama" value="0" checked> Utama
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-pic">✕</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-pic" class="btn btn-primary btn-sm mt-2">+ Tambah PIC</button>
                </div>

                <div class="d-flex justify">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalDuplikat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Data Sudah Ada</h5>
            </div>
            <div class="modal-body">
                Nama perusahaan <b id="namaDuplikat"></b> sudah terdaftar.
                <br>
                Silakan pastikan cabang / alamat berbeda.
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    
const inputNama = document.getElementById('nama_perusahaan');
const modal = new bootstrap.Modal(document.getElementById('modalDuplikat'));

let sudahCek = false;

inputNama.addEventListener('blur', function () {
    const nama = this.value.trim();
    if (!nama || sudahCek) return;

    fetch(`/customer/cek-nama?nama=${encodeURIComponent(nama)}`)
        .then(res => res.json())
        .then(res => {
            if (res.exists) {
                document.getElementById('namaDuplikat').innerText = nama;
                modal.show();
            }
            sudahCek = true;
        });
});

// reset kalau user edit ulang
inputNama.addEventListener('input', () => {
    sudahCek = false;
});

$(document).ready(function() {
    // 1️⃣ Ketika provinsi berubah → load kabupaten
    $('#provinsi_id').change(function() {
        let provId = $(this).val();
        resetDropdown(['#kabupaten_id', '#kawasan_id']); // reset kabupaten & kawasan

        if (provId) {
            // $.get(`/wilayah/kabupaten/${provId}`, function(data) {
            $.get(`{{ url('wilayah/kabupaten') }}/${provId}`, function(data) {

                if (data.length > 0) {
                    data.forEach(item => {
                        $('#kabupaten_id').append(`<option value="${item.kode}">${item.nama}</option>`);
                    });
                } else {
                    $('#kabupaten_id').append('<option value="">-- Tidak ada kabupaten --</option>');
                }
            });
        }
    });

    // 2️⃣ Ketika kabupaten berubah → load kawasan
    $('#kabupaten_id').change(function() {
        let kabId = $(this).val();
        $('#kawasan_id').html('<option value="">-- Pilih Kawasan --</option>');
        if(kabId){
            $.get("{{ url('kawasan') }}/" + kabId, function(data){
                if(data.length > 0){
                    data.forEach(item => {
                        $('#kawasan_id').append(`<option value="${item.id}">${item.nama_kawasan}</option>`);
                    });
                } else {
                    $('#kawasan_id').append('<option value="">-- Tidak ada kawasan --</option>');
                }
            });
        }
    });


    // Fungsi reset dropdown
    function resetDropdown(selectors) {
        selectors.forEach(sel => {
            $(sel).html('<option value="">-- Pilih --</option>');
        });
    }
});

document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const marketingSelect = document.getElementById('marketing_id');
    marketingSelect.innerHTML = '<option value="">-- Pilih Marketing --</option>';

    if (status) {
        fetch(`{{ url('customer/create') }}?status=${status}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id; // ✅ ini yang akan dikirim ke backend
                option.textContent = item.nama;
                marketingSelect.appendChild(option);
            });
        });
    }
});


  let index = 1;
    document.getElementById('add-pic').addEventListener('click', function() {
        const wrapper = document.getElementById('pic-wrapper');
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'g-2', 'mb-2', 'pic-item');
        newRow.innerHTML = `
            <div class="col-md-3">
                <input type="text" name="pic_perusahaan[${index}][nama]" class="form-control" placeholder="Nama PIC">
            </div>
            <div class="col-md-3">
                <input type="text" name="pic_perusahaan[${index}][kontak]" class="form-control" placeholder="No HP">
            </div>
            <div class="col-md-3">
                <input type="email" name="pic_perusahaan[${index}][email]" class="form-control" placeholder="Email">
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <input class="form-check-input me-2" type="radio" name="utama" value="${index}"> Utama
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-pic">✕</button>
            </div>
        `;
        wrapper.appendChild(newRow);
        index++;
    });

    // hapus PIC
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-pic')) {
            e.target.closest('.pic-item').remove();
        }
    });
    
</script>
@endpush

@endsection


