@extends('app.template')

@section('content')
@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
    </script>
@endif

<div class="card">
    <div class="card-header">
       <h5>Edit Projek: {{ $quotation->no_sph ?? '-' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('quotation.update', $quotation->id) }}" method="POST" id="formProyek">
            @csrf
            @method('PUT')

            <br>
            <label>
                <input type="radio" name="mode_update" value="update" checked>
                Ubah Data
            </label>
            <br>
            <label>
                <input type="radio" name="mode_update" value="revisi">
                Simpan sebagai Revisi
            </label>
            <br>
            <br>
            
            {{-- Pilih Customer --}}
            <div class="col md-3 mb-3">
                <label>Nama Perusahaan<span class="text-danger">*</span></label>
                <select id="customer-select" name="customer_id" class="form-select">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $quotation->customer_id == $customer->id ? 'selected' : '' }}>
                            {{ $customer->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Pilih Cabang <span class="text-danger">*</span></label>
            
                <select name="cabang_id"
                        id="cabang_id"
                        class="form-select"
                        {{ auth()->user()->role === 'admin marketing' ? 'readonly disabled' : '' }}>
                    
                    @foreach($cabang as $cb)
                        <option value="{{ $cb->id }}"
                            {{ old('cabang_id', $quotation->cabang_id) == $cb->id ? 'selected' : '' }}>
                            {{ $cb->nama_cabang }}
                        </option>
                    @endforeach
                </select>
            
                {{-- supaya tetap terkirim walau disabled --}}
                @if(auth()->user()->role === 'admin marketing')
                    <input type="hidden" name="cabang_id" value="{{ $quotation->cabang_id }}">
                @endif
            </div>

            {{-- Nomor & Tanggal SPH --}}
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label>Nomor SPH<span class="text-danger">*</span></label>
                    <input type="text"
                           name="no_sph"
                           id="no_sph"
                           class="form-control"
                           value="{{ $quotation->no_sph }}"
                           readonly disabled>
                </div>
                <div class="col-md-6">
                    <label>Tanggal SPH<span class="text-danger">*</span></label>
                    <input type="date" name="tgl_sph" class="form-control" value="{{ $quotation->tgl_sph }}">
                </div>
            </div>

            {{-- Nama Bangunan --}}
<div class="col-md-6 mb-3">
    <label>Nama Bangunan<span class="text-danger">*</span></label>
    <div class="d-flex align-items-center">
        <input type="text" name="nama_bangunan" class="form-control" value="{{ $quotation->nama_bangunan }}">
        <div class="form-check ms-2">
            <input type="hidden" name="is_same_nama_bangunan" value="0">
            <input class="form-check-input" type="checkbox" id="copyNama" name="is_same_nama_bangunan" value="1"
                {{ $quotation->is_same_nama_bangunan ? 'checked' : '' }}>
            <label class="form-check-label" for="copyNama">Sama</label>
        </div>
    </div>
</div>

            <div class="col-md-6 mb-3">
                <label>Pilih Fungsi Bangunan<span class="text-danger">*</span></label>
                <select name="fungsi_bangunan" class="form-select @error('fungsi_bangunan') is-invalid @enderror required">
                    <option value="-" {{ old('fungsi_bangunan', $quotation->fungsi_bangunan ?? '-') === '-' ? 'selected' : '' }}>
                        Pilih Fungsi Bangunan
                    </option>

                    <option value="-">-</option>

                    @foreach ([
                        'Fungsi Hunian',
                        'Fungsi Keagamaan',
                        'Fungsi Usaha',
                        'Fungsi Sosial dan Budaya',
                        'Fungsi Khusus'
                    ] as $item)
                        <option value="{{ $item }}"
                            {{ old('fungsi_bangunan', $quotation->fungsi_bangunan ?? '-') === $item ? 'selected' : '' }}>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
                    @error('fungsi_bangunan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
            </div>


            {{-- Alamat --}}
<div class="col-md-6 mb-3">
    <label>Provinsi<span class="text-danger">*</span></label>
    <div class="d-flex align-items-center gap-2">

    <select id="provinsi_id" name="provinsi_id" class="form-select">
        <option value="">-- Pilih Provinsi --</option>
        @foreach($provinsiList as $prov)
            <option value="{{ $prov->kode }}" {{ $quotation->provinsi_id == $prov->kode ? 'selected' : '' }}>
                {{ $prov->nama }}
            </option>
        @endforeach
    </select>

    <div class="form-check mb-0">
        <input type="hidden" name="is_same_alamat" value="0">
        <input class="form-check-input" type="checkbox" id="copyAlamat" name="is_same_alamat" value="1"
            {{ $quotation->is_same_alamat ? 'checked' : '' }}>
        <label class="form-check-label" for="copyAlamat">Sama</label>
    </div>
</div>
</div>
                <div class="row mb-3">
                <div class="col-md-6">
                    <label>Kabupaten / Kota<span class="text-danger">*</span></label>
                    <select id="kabupaten_id" name="kabupaten_id" class="form-select"></select>
                </div>
                <div class="col-md-6">
                    <label>Kawasan<span class="text-danger">*</span></label>
                    <select id="kawasan_id" name="kawasan_id" class="form-select"></select>
                </div>
                <div class="col-md-6">
                <label>Detail Alamat<span class="text-danger">*</span></label>
                <input type="text" name="detail_alamat" class="form-control" value="{{ $quotation->detail_alamat }}">
            </div>
            </div>

            {{-- Lama Pekerjaan --}}
            <div class="mb-3">
                <label>Lama Pekerjaan (hari)<span class="text-danger">*</span></label>
                <input type="number" name="lama_pekerjaan" class="form-control" value="{{ $quotation->lama_pekerjaan }}">
            </div>

            <div class="mb-3">
                <label>Jumlah Termin Pembayaran <span class="text-danger">*</span></label>
                <select name="jumlah_termin" id="jumlah_termin" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="1" {{ count($terminLama) == 1 ? 'selected' : '' }}>1 Termin</option>
                    <option value="2" {{ count($terminLama) == 2 ? 'selected' : '' }}>2 Termin</option>
                    <option value="3" {{ count($terminLama) == 3 ? 'selected' : '' }}>3 Termin</option>
                    <option value="4" {{ count($terminLama) == 4 ? 'selected' : '' }}>4 Termin</option>
                </select>
            </div>

            <div id="formTermin">
                {{-- Jika sudah ada data (edit), tampilkan otomatis --}}
                @if($terminLama)
                    <div class="row">
                    @foreach($terminLama as $t)
                        <div class="col-md-4 mb-3">
                            <label>Termin {{ $t['urutan'] }} (%)</label>
                            <input type="number"
                                name="termin[{{ $t['urutan'] }}]"
                                class="form-control termin-input"
                                value="{{ $t['persen'] }}"
                                min="1" max="100">
                        </div>
                    @endforeach
                    </div>
                @endif
            </div>

            <div id="validasiTotal" class="mt-2 fw-bold"></div>

            {{-- Pilih Jenis Perizinan --}}
            <div class="mb-3">
                <label>Jenis Perizinan</label>
                <div id="daftar-perizinan" class="row">
                    @foreach($perizinan as $p)
                    <div class="col-md-4 mb-2">
                        <label class="border rounded p-3 d-block">
                            <input type="checkbox" name="perizinan_id[]" value="{{ $p->id }}" class="jenis-checkbox" 
                                {{ $quotationPerizinan->pluck('perizinan_id')->contains($p->id) ? 'checked' : '' }}>
                            {{ $p->jenis }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tipe Harga --}}
            <div class="mb-3" id="tipeHargaGroup">
                <label>Tipe Harga</label>
                <select name="harga_tipe" id="harga_tipe" class="form-select">
                    <option value="">-- Pilih --</option>
                    <option value="satuan" {{ $quotation->harga_tipe == 'satuan' ? 'selected' : '' }}>Harga Satuan</option>
                    <option value="gabungan" {{ $quotation->harga_tipe == 'gabungan' ? 'selected' : '' }}>Harga Gabungan</option>
                </select>
            </div>

            {{-- Harga Gabungan --}}
            <div class="mb-3" id="hargaGabunganGroup" style="{{ $quotation->harga_tipe=='gabungan'?'display:block':'display:none' }}">
                <label>Harga Gabungan (Rp)</label>
                <input type="text" name="harga_gabungan" id="harga_gabungan" class="form-control format-angka" 
                    value="{{ old('harga_gabungan', $quotation->harga_gabungan) }}">
            </div>

            {{-- Form harga perizinan --}}
<div id="formHargaPerizinan">
@foreach($quotationPerizinan as $qp)
@php
    $id = $qp->perizinan_id;
    $qty = $qp->qty ?? 1;
    $harga = $qp->harga_satuan ?? 0;
    $subtotal = $qty * $harga;

    $labelUpper = strtoupper($qp->perizinan->jenis);
    $luasField = null;

    if(str_contains($labelUpper, 'SLF')) $luasField = 'luas_slf';
    elseif(str_contains($labelUpper, 'PBG')) $luasField = 'luas_pbg';
    elseif(str_contains($labelUpper, 'SHGB')) $luasField = 'luas_shgb';

@endphp

<div class="card mb-3 perizinan-card" id="harga-{{ $id }}" data-id="{{ $id }}">
    <div class="card-body">

        <h6 class="fw-bold">{{ $qp->perizinan->jenis }}</h6>
                <div class="row align-items-end">

        <input type="hidden" name="perizinan_id[]" value="{{ $id }}">

                {{-- Jenis --}}
        <div class="col-md-2 mb-2">
            <label>Satuan</label>
            <select class="form-select satuan-select">
                <option value="">-- Pilih Satuan --</option>
                @foreach($satuanPerizinans as $s)
                    <option value="{{ $s->id }}"
                        {{ $qp->satuan_id == $s->id ? 'selected' : '' }}>
                        {{ $s->nama }}
                    </option>
                @endforeach
            </select>

            <input type="hidden"
                   name="satuan_id[{{ $id }}]"
                   class="satuan-hidden"
                   value="{{ $qp->satuan_id }}">
        </div>

        {{-- QTY --}}
        <div class="col-md-3 mb-2">
            <label>Qty</label>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary btn-minus">−</button>

                <input type="number"
                       class="form-control text-center qty-input"
                       value="{{ $qty }}"
                       min="1">

                <input type="hidden"
                       name="qty[{{ $id }}]"
                       class="qty-hidden"
                       value="{{ $qty }}">

                <button type="button" class="btn btn-outline-secondary btn-plus">+</button>
            </div>
        </div>

        
<div class="col-md-3 mb-2">
    <label>Harga Satuan (Rp)</label>

    {{-- tampilan --}}
    <input type="text"
           class="form-control format-angka harga-view"
           value="{{ number_format($harga,0,',','.') }}">

    {{-- nilai asli (WAJIB ADA) --}}
    <input type="hidden"
           name="harga_satuan[{{ $id }}]"
           class="harga-asli"
           value="{{ $harga }}">
</div>



        @if($luasField)

<div class="col-md-3 mb-2">
    <label>Luas (m²)</label>
    <input type="number"
           step="any"
           name="{{ $luasField }}[{{ $id }}]"
           class="form-control"
           value="{{ $quotation->$luasField }}">
</div>
@endif

        {{-- SUBTOTAL --}}
        <div>
            <small>Subtotal</small>
            <div class="fw-bold subtotal-text">
                Rp {{ number_format($subtotal,0,',','.') }}
            </div>
        </div>
                </div>
    </div>
</div>
@endforeach
</div>

{{-- ===================== --}}
{{-- DISKON --}}
{{-- ===================== --}}
<div class="card mt-3" id="diskonWrapper">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Diskon</h6>

        <div class="row">
            <div class="col-md-4">
                <label>Tipe Diskon</label>
                <select name="diskon_tipe" id="diskon_tipe" class="form-select">
                    <option value="">Tanpa Diskon</option>
                    <option value="persen" {{ $quotation->diskon_tipe=='persen'?'selected':'' }}>Persen (%)</option>
                    <option value="nominal" {{ $quotation->diskon_tipe=='nominal'?'selected':'' }}>Nominal (Rp)</option>
                </select>
            </div>

            {{-- Diskon Persen --}}
            <div class="col-md-4 {{ $quotation->diskon_tipe=='persen'?'':'d-none' }}" id="diskonPersen">
                <label>Diskon (%)</label>
                <input type="number"
                       id="diskon_persen"
                       name="diskon_persen"
                       class="form-control"
                       min="0" max="100"
                       value="{{ $quotation->diskon_tipe=='persen' ? $quotation->diskon_nilai : '' }}">
            </div>

            {{-- Diskon Nominal --}}
            <div class="col-md-4 {{ $quotation->diskon_tipe=='nominal'?'':'d-none' }}" id="diskonNominal">
                <label>Diskon Nominal</label>

                {{-- tampilan --}}
                <input type="text"
                       id="diskon_nominal_view"
                       class="form-control format-angka"
                       value="{{ $quotation->diskon_tipe=='nominal' ? number_format($quotation->diskon_nilai,0,',','.') : '' }}">

                {{-- nilai asli --}}
                <input type="hidden"
                       id="diskon_nominal"
                       name="diskon_nominal"
                       value="{{ $quotation->diskon_tipe=='nominal' ? $quotation->diskon_nilai : '' }}">
            </div>
        </div>

        <hr>
        <div class="row fw-semibold">
            <div class="col-md-4">
                Total Harga<br>
                <span id="totalHarga" class="text-primary">Rp 0</span>
            </div>
            <div class="col-md-4">
                Diskon<br>
                <span id="totalDiskon" class="text-danger">Rp 0</span>
            </div>
            <div class="col-md-4">
                Total Keseluruhan<br>
                <span id="grandTotal" class="text-success">Rp 0</span>
            </div>
        </div>
    </div>
</div>


            <button type="submit" class="btn btn-success mt-3">Update</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

// untuk ribuan
function formatRibuan(angka) {
    if(!angka && angka !== 0) return "";

    // ubah ke integer dulu, buang desimal
    let intAngka = Math.floor(Number(angka));

    // format ribuan
    return intAngka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

$(".format-angka").each(function(){
    let val = $(this).val();
    if(val) $(this).val(formatRibuan(val));
});
// saat mengetik
$(document).on("input", ".format-angka", function () {
    let value = $(this).val().replace(/\./g, ""); // hapus titik ribuan
    if(value === "") { $(this).val(""); return; }
    $(this).val(formatRibuan(value));
});

// sebelum submit ke DB
$("form").on("submit", function () {
    $(".format-angka").each(function () {
        this.value = this.value.replace(/\./g, ""); // hapus titik ribuan
    });
});

    // Load kabupaten otomatis
    let selectedProv = '{{ $quotation->provinsi_id }}';
    let selectedKab = '{{ $quotation->kabupaten_id }}';
    let selectedKaw = '{{ $quotation->kawasan_id }}';
    let selectedAlamat = '{{ $quotation->detail_alamat }}';

const baseKabupatenUrl = "{{ url('wilayah/kabupaten') }}";
const baseKawasanUrl = "{{ url('kawasan') }}";

if(selectedProv) {
    $.get(`${baseKabupatenUrl}/${selectedProv}`, function(kab){
        $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        kab.forEach(k=>{
            $('#kabupaten_id').append(`<option value="${k.kode}" ${k.kode==selectedKab?'selected':''}>${k.nama}</option>`);
        });

        // Load kawasan setelah kabupaten tersedia
        if(selectedKab) {
            $.get(`${baseKawasanUrl}/${selectedKab}`, function(kaw){
            console.log(kaw); // lihat apakah data kembali
            $('#kawasan_id').html('<option value="">-- Pilih Kawasan --</option>');
            kaw.forEach(k=>{
                $('#kawasan_id').append(`<option value="${k.id}" ${k.id==selectedKaw?'selected':''}>${k.nama_kawasan}</option>`);
            });
        });

        }
    });
}

function loadKabupaten(provId) {
    return $.get(`${baseKabupatenUrl}/${provId}`, function(kab){
        $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        kab.forEach(k => {
            $('#kabupaten_id').append(`<option value="${k.kode}">${k.nama}</option>`);
        });
    });
}

function loadKawasan(kabId) {
    return $.get(`${baseKawasanUrl}/${kabId}`, function(kaw){
        $('#kawasan_id').html('<option value="">-- Pilih Kawasan --</option>');
        kaw.forEach(k => {
            $('#kawasan_id').append(`<option value="${k.id}">${k.nama_kawasan}</option>`);
        });
    });
}


$('#copyAlamat').change(function(){
    const checked = $(this).is(':checked');

if(checked){
        // Prefill otomatis
        $('#provinsi_id').val(selectedProv);

        // Load kabupaten dulu
        loadKabupaten(selectedProv).then(()=>{
            $('#kabupaten_id').val(selectedKab);

            // Load kawasan
            loadKawasan(selectedKab).then(()=>{
                $('#kawasan_id').val(selectedKaw);

                // Baru set detail alamat
                $('input[name="detail_alamat"]').val(selectedAlamat);
            });
        });
    }  else {
        // Reset semua untuk input manual
        $('#provinsi_id').val('');
        $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        $('#kawasan_id').html('<option value="">-- Pilih Kawasan --</option>');
        $('input[name= "detail_alamat"]').val('');
    }
});

// Event manual dropdown
$('#provinsi_id').change(function(){
    let provId = $(this).val();
    $('#provinsi_id_hidden').val(provId);
    loadKabupaten(provId);
});

$('#kabupaten_id').change(function(){
    let kabId = $(this).val();
    $('#kabupaten_id_hidden').val(kabId);
    loadKawasan(kabId);
});

$(document).on('input', '#diskon_nominal_view', function(){
    let angka = this.value.replace(/\D/g,'');
    $('#diskon_nominal').val(angka);
    hitungTotal();
});
$(document).ready(function(){
    const hidden = $('#diskon_nominal').val();
    if(hidden){
        $('#diskon_nominal_view').val(formatRibuan(hidden));
    }
});


//harga
function buatCardPerizinan(id, label) {
    const luasMap = { 'SLF': 'luas_slf', 'PBG': 'luas_pbg', 'SHGB': 'luas_shgb' };
    const perizinanButuhLuas = ['SLF','PBG','SHGB'];
    const labelUpper = label.toUpperCase();
    let luasFieldName = null;
    const found = perizinanButuhLuas.find(nama => labelUpper.includes(nama));
    if(found) luasFieldName = luasMap[found];

    let luasHtml = '';
    if(luasFieldName){
        luasHtml = `
        <div class="mb-2">
            <label>Luas ${label} (m²) - isi dengan titik</label>
            <input type="number" name="${luasFieldName}[${id}]" class="form-control" step="any">
        </div>`;
    }

    return `
            <div class="card mb-3 perizinan-card" id="harga-${id}" data-id="${id}">
            <div class="card-body">
                <h6 class="fw-bold mb-3">${label}</h6>

                <!-- ROW ATAS -->
                <div class="row align-items-end">

                    <!-- JENIS -->
                    <div class="col-md-2 mb-2">
                        <label>Jenis</label>
                        <select class="form-select satuan-select">
                            <option value="">-- Pilih Satuan --</option>
                            @foreach ($satuanPerizinans as $satuan)
                                <option value="{{ $satuan->id }}">
                                    {{ $satuan->nama }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden"
                        name="satuan_id[${id}]"
                        class="satuan-hidden">
                    </div>

                    <!-- QTY -->
                    <div class="col-md-3 mb-2">
                        <label>Qty</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary btn-minus">−</button>
                            <input type="number"
                                   class="form-control text-center qty-input"
                                   value="1"
                                   min="1">

                            <input type="hidden"
                                name="qty[${id}]"
                                class="qty-hidden"
                                value="1">
                            <button type="button" class="btn btn-outline-secondary btn-plus">+</button>
                        </div>
                    </div>

                    <!-- HARGA SATUAN -->
                    <div class="col-md-3 mb-2 harga-satuan-group">
                        <label>Harga Satuan (Rp)</label>

                        <div class="harga-wrapper">
                            <input type="text"
                                class="form-control format-angka"
                                placeholder="0">

                            <input type="hidden"
                                name="harga_satuan[${id}]"
                                class="harga-asli">
                        </div>
                    </div>

            ${luasHtml}
        </div>
                        <div class="mt-2 subtotal-group">
                    <small class="text-muted">
                        Subtotal:
                        <strong class="subtotal-text">Rp 0</strong>
                    </small>
                    <input type="hidden"
                           name="subtotal[${id}]"
                           class="subtotal-asli">
                </div>

    </div>`;
}

function toggleSubtotal(card, show = true) {
    const subtotalGroup = card.querySelector('.subtotal-group');
    if (!subtotalGroup) return;

    subtotalGroup.style.display = show ? 'block' : 'none';
}


//Event listener checkbox perizinan
const checkboxes = document.querySelectorAll('input[name="perizinan_id[]"]');
const tipeHargaGroup = document.getElementById('tipeHargaGroup');
const hargaGabunganGroup = document.getElementById('hargaGabunganGroup');
const formHargaPerizinan = document.getElementById('formHargaPerizinan');
const hargaTipe = document.getElementById('harga_tipe');
const inputGabungan = document.querySelector('input[name="harga_gabungan"]');

function hitungSubtotal(card){
    const qty = parseInt(card.querySelector('.qty-hidden')?.value || 1);

    // ambil dari hidden (SUMBER DATA ASLI)
    const hargaHidden = card.querySelector('.harga-asli');
    const harga = parseInt(hargaHidden?.value || 0);

    const subtotal = qty * harga;

    card.querySelector('.subtotal-text').innerText =
        'Rp ' + subtotal.toLocaleString('id-ID');

    card.querySelector('.subtotal-asli').value = subtotal;

    hitungTotal();
}

//tambah dan kurang qty
document.addEventListener('click', function(e) {
    const card = e.target.closest('.perizinan-card');
    if (!card) return;

    const qtyInput  = card.querySelector('.qty-input');
    const qtyHidden = card.querySelector('.qty-hidden');

    if (e.target.classList.contains('btn-plus')) {
        qtyInput.value = parseInt(qtyInput.value) + 1;
        qtyHidden.value = qtyInput.value; // WAJIB
        hitungSubtotal(card);
    }

    if (e.target.classList.contains('btn-minus')) {
        if (qtyInput.value > 1) {
            qtyInput.value = parseInt(qtyInput.value) - 1;
            qtyHidden.value = qtyInput.value; // WAJIB
            hitungSubtotal(card);
        }
    }
});

// qty sync
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty-input')) {
        const card = e.target.closest('.perizinan-card');
        if (!card) return;
        card.querySelector('.qty-hidden').value = e.target.value;
        hitungSubtotal(card);
    }
});

//satuan sync
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('satuan-select')) {
        const card = e.target.closest('.perizinan-card');
        card.querySelector('.satuan-hidden').value = e.target.value;
    }
});

// 🔹 fungsi hitung total harga satuan
function hitungTotalHargaSatuan() {
    let total = 0;
    checkboxes.forEach(cb => {
        if (cb.checked) {
            const hargaInput = document.querySelector(`input[name="harga_satuan[${cb.value}]"]`);
            if (hargaInput && hargaInput.value) {
                total += parseFloat(hargaInput.value) || 0;
            }
        }
    });
    return total;
}


document.addEventListener('input', function(e){
    if(e.target.classList.contains('format-angka')){
        const card = e.target.closest('.perizinan-card');
        if(!card) return;

        const angka = e.target.value.replace(/\D/g,'') || 0;

        // sync ke hidden
        card.querySelector('.harga-asli').value = angka;

        // hitung ulang
        hitungSubtotal(card);
    }
});

checkboxes.forEach(cb => {
    cb.addEventListener('change', function(){
        const id = this.value;
        const label = this.parentElement.textContent.trim();
        const card = document.getElementById(`harga-${id}`);
        const tipeSekarang = hargaTipe.value; // ⬅️ cek tipe harga aktif

        // Tampilkan tipe harga jika ada minimal 1 checkbox dipilih
        const adaYangDipilih = Array.from(checkboxes).some(c => c.checked);
        tipeHargaGroup.style.display = adaYangDipilih ? 'block' : 'none';

        if(this.checked){
            if(!card){
                const cardHtml = buatCardPerizinan(id,label);
                formHargaPerizinan.insertAdjacentHTML('beforeend', cardHtml);
            }

            const cardEl = document.getElementById(`harga-${id}`);
            cardEl.classList.remove('d-none');

            // Kalau sedang dalam mode gabungan → sembunyikan input harga satuan
            if(tipeSekarang === 'gabungan'){
                const hargaInput = cardEl.querySelector('input[name^="harga_satuan"]');
                if(hargaInput) hargaInput.closest('.mb-2').style.display = 'none';
            }

        } else {
            if(card) card.classList.add('d-none');
        }
    });
});

// 🔹 Event tipe harga
hargaTipe.addEventListener('change', function(){
    const tipe = this.value;

    if(tipe === 'gabungan'){
        hargaGabunganGroup.style.display = 'block';
        formHargaPerizinan.style.display = 'block';

        // tampilkan semua card tapi sembunyikan input harga satuan
        const cards = formHargaPerizinan.querySelectorAll('.card');
        cards.forEach(card => {
            card.classList.remove('d-none');
            const hargaInput = card.querySelector('input[name^="harga_satuan"]');
            if(hargaInput) hargaInput.closest('.mb-2').style.display = 'none';
        });

        //  Hitung total harga satuan dan tampilkan di harga gabungan
        const total = hitungTotalHargaSatuan();
        inputGabungan.value = total > 0 ? total : '';

    } else if(tipe === 'satuan'){
        hargaGabunganGroup.style.display = 'none';
        formHargaPerizinan.style.display = 'block';
        checkboxes.forEach(cb => {
            const card = document.getElementById(`harga-${cb.value}`);
            if(card){
                if(cb.checked){
                    card.classList.remove('d-none');
                    const hargaInput = card.querySelector('input[name^="harga_satuan"]');
                    if(hargaInput) hargaInput.closest('.mb-2').style.display = 'block';
                } else {
                    card.classList.add('d-none');
                }
            }
        });
    } else {
        hargaGabunganGroup.style.display = 'none';
        formHargaPerizinan.style.display = 'none';
    }
});


// 🔹 Pre-fill card harga lama (edit)
const qpData = @json($quotationPerizinan->keyBy('perizinan_id'));
const quotationData = @json([
    'luas_slf' => $quotation->luas_slf,
    'luas_pbg' => $quotation->luas_pbg,
    'luas_shgb' => $quotation->luas_shgb
]);

checkboxes.forEach(cb => {
    const id = parseInt(cb.value);
    const label = cb.parentElement.textContent.trim();

    if(qpData[id]){
        cb.checked = true;

        // buat card jika belum ada
        if(!document.getElementById(`harga-${id}`)){
            const cardHtml = buatCardPerizinan(id,label);
            formHargaPerizinan.insertAdjacentHTML('beforeend', cardHtml);
        }

        const cardEl = document.getElementById(`harga-${id}`);
        if(cardEl){
            // set harga

            // const hargaInput = cardEl.querySelector(`input[name="harga_satuan[${id}]"]`);
            // if(hargaInput) hargaInput.value = formatRibuan(qpData[id].harga_satuan);

            const hargaHidden = cardEl.querySelector(`input[name="harga_satuan[${id}]"]`);
            const hargaView   = cardEl.querySelector('.format-angka');

            if(hargaHidden && hargaView){
                hargaHidden.value = qpData[id].harga_satuan; // angka murni
                hargaView.value   = formatRibuan(qpData[id].harga_satuan);
            }

            // set luas sesuai jenis
            let jenisUpper = label.toUpperCase();
            let luasField = null;
            if(jenisUpper.includes('SLF')) luasField = 'luas_slf';
            else if(jenisUpper.includes('PBG')) luasField = 'luas_pbg';
            else if(jenisUpper.includes('SHGB')) luasField = 'luas_shgb';

            if(luasField){
                const luasInput = cardEl.querySelector(`input[name="${luasField}[${id}]"]`);
                if(luasInput) luasInput.value = quotationData[luasField] ?? '';
            }
        }
    }
});

// ----------------------------
// Set tampilan tipe harga sesuai data quotation
// ----------------------------
const tipe = '{{ $quotation->harga_tipe ?? "satuan" }}';
hargaTipe.value = tipe;

if(tipe === 'gabungan'){
    hargaGabunganGroup.style.display = 'block';
    formHargaPerizinan.style.display = 'block';

    // sembunyikan input harga tapi tampilkan luas
    const cards = formHargaPerizinan.querySelectorAll('.card');
    cards.forEach(card => {
        card.classList.remove('d-none');
        const hargaInput = card.querySelector('input[name^="harga_satuan"]');
        if(hargaInput) hargaInput.closest('.mb-2').style.display = 'none';
    });

        if(inputGabungan){
            let val = parseFloat('{{ $quotation->harga_gabungan ?? 0 }}') || 0;
            inputGabungan.value = formatRibuan(val);
        }

} else {
    hargaGabunganGroup.style.display = 'none';
    formHargaPerizinan.style.display = 'block';
}


function formatRupiah(angka){
    return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
}

function hitungTotal(){
    let totalAwal = 0;
    const tipeHarga = $('#harga_tipe').val();

    // === HARGA SATUAN ===
    if(tipeHarga === 'satuan'){
        $('input[name^="harga_satuan"]').each(function(){
            const card = $(this).closest('.perizinan-card');
            const harga = parseInt($(this).val().replace(/\D/g,'')) || 0;
            const qty   = parseInt(card.find('.qty-hidden').val()) || 1;
            totalAwal += harga * qty;
        });
    }

    // === HARGA GABUNGAN ===
    if(tipeHarga === 'gabungan'){
        totalAwal = parseInt($('#harga_gabungan').val().replace(/\D/g,'') || 0);
    }

    // === DISKON ===
    let diskon = 0;
    const tipeDiskon = $('#diskon_tipe').val();

    if(tipeDiskon === 'persen'){
        diskon = totalAwal * (parseFloat($('#diskon_persen').val() || 0) / 100);
    }

    if(tipeDiskon === 'nominal'){
        diskon = parseInt($('#diskon_nominal').val() || 0);
    }

    if(diskon > totalAwal) diskon = totalAwal;

    $('#totalHarga').text(formatRupiah(totalAwal));
    $('#totalDiskon').text(formatRupiah(diskon));
    $('#grandTotal').text(formatRupiah(totalAwal - diskon));
}

// ===== EVENT =====
$(document).on('input change', `
    #harga_tipe,
    input[name^="harga_satuan"],
    #harga_gabungan,
    #diskon_tipe,
    #diskon_persen,
    #diskon_nominal_view
`, hitungTotal);

// sinkron nominal view → hidden
$('#diskon_nominal_view').on('input', function(){
    let angka = this.value.replace(/\D/g,'');
    $('#diskon_nominal').val(angka);
    hitungTotal();
});

// toggle input diskon
$('#diskon_tipe').on('change', function(){
    $('#diskonPersen').addClass('d-none');
    $('#diskonNominal').addClass('d-none');

    if(this.value === 'persen') $('#diskonPersen').removeClass('d-none');
    if(this.value === 'nominal') $('#diskonNominal').removeClass('d-none');

    hitungTotal();
});

// AUTO HITUNG SAAT PAGE LOAD
$(document).ready(function(){
    hitungTotal();
});


//edit untuk termin
$('#jumlah_termin').on('change', function() {
    const jumlah = parseInt($(this).val());
    const formTermin = $('#formTermin');

    formTermin.html('');

    if (!jumlah) return;

    let html = '<div class="row">';

    for (let i = 1; i <= jumlah; i++) {
        html += `
            <div class="col-md-4 mb-3">
                <label>Termin ${i} (%)</label>
                <input type="number"
                       name="termin[${i}]"
                       class="form-control termin-input"
                       min="1" max="100"
                       placeholder="Masukkan persentase...">
            </div>
        `;
    }

    html += '</div>';
    formTermin.html(html);
});

// VALIDASI TOTAL
$(document).on('input', '.termin-input', function() {
    let total = 0;
    $('.termin-input').each(function() {
        total += Number($(this).val()) || 0;
    });

    if (total === 100) {
        $('#validasiTotal').html(`<span class="text-success">Total persentase: 100% ✔</span>`);
    } else {
        $('#validasiTotal').html(`<span class="text-danger">Total persentase: ${total}% (harus 100%)</span>`);
    }
});

//auto no sph
const previewSphUrl = "{{ url('quotation/preview-sph') }}";
document.getElementById('cabang_id').addEventListener('change', function() {
    let cabangId = this.value;

    if (!cabangId) {
        document.getElementById('no_sph').value = "";
        return;
    }
    
    fetch(previewSphUrl + '/' + cabangId)
        .then(res => res.json())
        .then(data => {
            document.getElementById('no_sph').value = data.no_sph;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('no_sph').value = "";
        });
});

});
</script>
@endsection
