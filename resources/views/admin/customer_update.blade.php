@extends('app.template')

@section('content')

<a class="btn btn-danger mb-3" href="{{ url('customer') }}">Kembali</a>

<div class="card">
    <div class="card-body">
        {{-- Form update customer --}}
        <form action="{{ url('customer/update/' . $customer->id) }}" method="POST">
            @method('put')
            @csrf

    {{-- Nama Perusahaan --}}
    <div class="mb-3">
        <label for="nama_perusahaan" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
        <input type="text" name="nama_perusahaan" id="nama_perusahaan"
            class="form-control @error('nama_perusahaan') is-invalid @enderror"
            value="{{ old('nama_perusahaan', $customer->nama_perusahaan) }}">
        @error('nama_perusahaan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Alamat --}}
            {{-- Provinsi --}}
            <div class="mb-3">
                <label for="provinsi_id">Provinsi</label>
                <select id="provinsi_id" name="provinsi_id" class="form-select">
                    <option value="">-- Pilih Provinsi --</option>
                    @foreach($provinsiList as $prov)
                        <option value="{{ $prov->kode }}" {{ $prov->kode == $customer->provinsi_id ? 'selected' : '' }}>
                            {{ $prov->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kabupaten --}}
            <div class="mb-3">
                <label for="kabupaten_id">Kabupaten / Kota</label>
                <select id="kabupaten_id" name="kabupaten_id" class="form-select">
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                    @foreach($kabupatenList as $kab)
                        <option value="{{ $kab->kode }}" {{ $kab->kode == $customer->kabupaten_id ? 'selected' : '' }}>
                            {{ $kab->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kawasan --}}
            <div class="mb-3">
                <label for="kawasan_id">Kawasan</label>
                <select id="kawasan_id" name="kawasan_id" class="form-select">
                    <option value="">-- Pilih Kawasan --</option>
                    @foreach($kawasanList ?? [] as $kawasan)
                        <option value="{{ $kawasan->id }}" {{ $kawasan->id == $customer->kawasan_id ? 'selected' : '' }}>
                            {{ $kawasan->nama_kawasan }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Detail Alamat --}}
            <div class="mb-3">
                <label for="detail_alamat" class="form-label">Detail lainnya <span class="text-danger">*</span></label>
                <input type="text" name="detail_alamat" id="detail_alamat"
                    class="form-control @error('detail_alamat') is-invalid @enderror"
                    value="{{ old('detail_alamat', $customer->detail_alamat) }}">
                @error('detail_alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

{{-- Status Marketing --}}
<div class="mb-3">
    <label>Status</label>
    <select name="status" id="status" class="form-select">
        <option value="">-- Pilih Status --</option>
        @foreach ($statusList as $status)
            <option value="{{ $status }}" {{ $currentStatus == $status ? 'selected' : '' }}>
                {{ ucfirst($status) }}
            </option>
        @endforeach
    </select>
</div>

{{-- Nama Marketing --}}
<div class="mb-3">
    <label>Marketing</label>
    <select name="marketing_id" id="marketing_id" class="form-select">
        <option value="">-- Pilih Marketing --</option>
        @foreach ($marketingList as $m)
            <option value="{{ $m->id }}" {{ $customer->marketing_id == $m->id ? 'selected' : '' }}>
                {{ $m->nama }}
            </option>
        @endforeach
    </select>
</div>

{{-- Daftar PIC --}}
<div class="mb-3">
    <label class="form-label">PIC Perusahaan</label>

            <div id="pic-container">
                @php
                    $pics = old('pic_perusahaan') ?? (
                        is_string($customer->pic_perusahaan)
                            ? json_decode($customer->pic_perusahaan, true)
                            : ($customer->pic_perusahaan ?? [])
                    );
                @endphp
@foreach ($pics as $i => $pic)
    <div class="card mb-2 p-3 border position-relative">
        <div class="mb-2">
            <label>Nama PIC</label>
            <input type="text" name="pic_perusahaan[{{ $i }}][nama]" value="{{ $pic['nama'] ?? '' }}" class="form-control">
        </div>
        <div class="mb-2">
            <label>Kontak</label>
            <input type="text" name="pic_perusahaan[{{ $i }}][kontak]" value="{{ $pic['kontak'] ?? '' }}" class="form-control">
        </div>
        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="pic_perusahaan[{{ $i }}][email]" value="{{ $pic['email'] ?? '' }}" class="form-control">
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="utama" value="{{ $i }}" {{ !empty($pic['utama']) && $pic['utama'] ? 'checked' : '' }}>
            <label class="form-check-label">Jadikan PIC Utama</label>
        </div>
    </div>
@endforeach


    </div>

    <button type="button" id="add-pic" class="btn btn-outline-primary btn-sm">+ Tambah PIC</button>
</div>


            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    const provinsi_id = "{{ old('provinsi_id', $customer->provinsi_id) }}";
    const kabupaten_id = "{{ old('kabupaten_id', $customer->kabupaten_id) }}";
    const kawasan_id = "{{ old('kawasan_id', $customer->kawasan_id) }}";

    // Preload Kabupaten & Kawasan sesuai data lama
    if (provinsi_id) {
        $.get(`{{ url('wilayah/kabupaten') }}/${provinsi_id}`, function(data) {
            data.forEach(k => {
                $('#kabupaten_id').append(`<option value="${k.kode}" ${k.kode == kabupaten_id ? 'selected' : ''}>${k.nama}</option>`);
            });

            if (kabupaten_id) {
                $.get(`{{ url('kawasan') }}/${kabupaten_id}`, function(kws) {
                    kws.forEach(k => {
                        $('#kawasan_id').append(`<option value="${k.id}" ${k.id == kawasan_id ? 'selected' : ''}>${k.nama_kawasan}</option>`);
                    });
                });
            }
        });
    }

    // Event ganti Provinsi
    $('#provinsi_id').on('change', async function() {
        const provinsiKode = $(this).val();
        $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        $('#kawasan_id').html('<option value="">-- Pilih Kawasan --</option>');

        if (provinsiKode) {
            const kabupatenData = await $.get(`{{ url('wilayah/kabupaten') }}/${provinsiKode}`);
            kabupatenData.forEach(k => {
                $('#kabupaten_id').append(`<option value="${k.kode}">${k.nama}</option>`);
            });
        }
    });

    // Event ganti Kabupaten
    $('#kabupaten_id').on('change', async function() {
        const kabupatenKode = $(this).val();
        $('#kawasan_id').html('<option value="">-- Pilih Kawasan --</option>');

        if (kabupatenKode) {
                const kawasanData = await $.get(`{{ url('kawasan') }}/${kabupatenKode}`);
                kawasanData.forEach(k => {
                $('#kawasan_id').append(`<option value="${k.id}">${k.nama_kawasan}</option>`);
            });
        }
    });

});

document.getElementById('status').addEventListener('change', function() {
    let status = this.value;
    let marketingSelect = document.getElementById('marketing_id');
    marketingSelect.innerHTML = '<option value="">-- Memuat... --</option>';

    if (status) {
        fetch(`{{ url('customer/edit/' . $customer->id) }}?status=${status}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            marketingSelect.innerHTML = '<option value="">-- Pilih Marketing --</option>';
            data.forEach(item => {
                marketingSelect.innerHTML += `<option value="${item.id}">${item.nama}</option>`;
            });
        });
    } else {
        marketingSelect.innerHTML = '<option value="">-- Pilih Marketing --</option>';
    }
});


document.getElementById('add-pic').addEventListener('click', function() {
    const container = document.getElementById('pic-container');
    const index = container.children.length;

    const html = `
        <div class="card mb-2 p-3 border position-relative">
            <div class="mb-2">
                <label>Nama PIC</label>
                <input type="text" name="pic_perusahaan[${index}][nama]" class="form-control">
            </div>
            <div class="mb-2">
                <label>Kontak</label>
                <input type="text" name="pic_perusahaan[${index}][kontak]" class="form-control">
            </div>
            <div class="mb-2">
                <label>Email</label>
                <input type="email" name="pic_perusahaan[${index}][email]" class="form-control">
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="utama" value="${index}">
                <label class="form-check-label">Jadikan PIC Utama</label>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
});

</script>

@endsection