@extends('app.template')

<style>
/* Card perizinan default */
#daftar-perizinan label {
    background-color: #f0f0f0; /* abu muda */
    color: #333;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}

/* Saat hover */
#daftar-perizinan label:hover {
    background-color: #e0e0e0; /* sedikit lebih gelap */
}

/* Saat checkbox dicentang */
#daftar-perizinan input[type="checkbox"]:checked + span,
#daftar-perizinan input[type="checkbox"]:checked ~ label,
#daftar-perizinan input[type="checkbox"]:checked + label {
    background-color: #007bff; /* biru bootstrap */
    color: #fff;
    border-color: #007bff;
}

/* Biar pojokan halus & ada efek bayangan */
#daftar-perizinan label {
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

</style>

@section('content')

<div class="card">
    <div class="card-header">
        <h5>Form SPH</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('quotation.store') }}" method="POST" id="formProyek">
            @csrf   

            {{-- Pilih Customer --}}
            <div class="col md-3 mb-3">
                <label for="customer_id">Nama Perusahaan<span class="text-danger">*</span></label>
                <select id="customer-select" name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" >
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>Pilih Cabang <span class="text-danger">*</span></label>
            
                <select name="cabang_id" id="cabang_id" class="form-select" required>
                    @if($cabang->count() > 1)
                        <option value="">-- Pilih Cabang --</option>
                    @endif
            
                    @foreach($cabang as $cb)
                        <option value="{{ $cb->id }}"
                            {{ $cabang->count() === 1 ? 'selected' : '' }}>
                            {{ $cb->nama_cabang }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Nomor & Tanggal SPH --}}
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="no_sph" class="form-label fw-bold">Nomor SPH <span class="text-danger">*</span></label>

                    <input 
                        type="text"
                        id="no_sph"
                        name="no_sph"
                        class="form-control @error('no_sph') is-invalid @enderror"
                        readonly
                        placeholder="Nomor SPH akan muncul otomatis"
                        required>
                    @error('no_sph')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label>Tanggal SPH<span class="text-danger">*</span></label>
                    <input type="date" name="tgl_sph" class="form-control @error('tgl_sph') is-invalid @enderror" value="{{ old('tgl_sph') }}" required>
                    @error('tgl_sph')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
            
            {{-- Nama Bangunan --}}
            <div class="col-md-6 mb-3">
                <label>Nama Bangunan<span class="text-danger">*</span></label>
                <div class="d-flex align-items-center">
                    <input type="text" name="nama_bangunan" id="nama_bangunan" class="form-control me-2">
                    <div class="form-check">
                        <input type="hidden" name="is_same_nama_bangunan" value="0">
                        <input class="form-check-input" type="checkbox" id="copyNama" name="is_same_nama_bangunan" value="1">
                        <label class="form-check-label" for="copyNama">Sama</label>
                    </div>
                </div>
            </div>

            {{-- Alamat --}}
            <div class="col-md-6 mb-3">
                <label for="provinsi_id">Provinsi<span class="text-danger">*</span></label>
                <div class="d-flex align-items-center gap-2">
                    <select id="provinsi_id" name="provinsi_id" class="form-select" required>
                        <option value="">-- Pilih Provinsi --</option>
                        @foreach($provinsiList as $prov)
                            <option value="{{ $prov->kode }}">{{ $prov->nama }}</option>
                        @endforeach
                    </select>
                        <div class="form-check mb-0">
                            <input type="hidden" name="is_same_alamat" value="0">
                            <input class="form-check-input" type="checkbox" id="copyAlamat" name="is_same_alamat" value="1">
                            <label class="form-check-label mb-0" for="copyAlamat">Sama</label>
                        </div>
                </div>

                <!-- Hidden input untuk submit -->
                <input type="hidden" id="provinsi_id_hidden" name="provinsi_id">
                <input type="hidden" id="kabupaten_id_hidden" name="kabupaten_id">
                <input type="hidden" id="kawasan_id_hidden" name="kawasan_id">
                <input type="hidden" id="detail_alamat_hidden" name="detail_alamat">
            </div>

            <div class="row mb-3">
                <div class="col md-6">
                    <label for="kabupaten_id">Kabupaten / Kota<span class="text-danger">*</span></label>
                    <select id="kabupaten_id" name="kabupaten_id" class="form-select" required>
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                    </select>
                </div>

                <div class="col md-6">
                    <label for="kawasan_id">Kawasan</label>
                    <select id="kawasan_id" name="kawasan_id" class="form-select" >
                        <option value="">-- Pilih Kawasan --</option>
                    </select>
                </div>

                <div class="col md-6">
                    <label for="detail_alamat">Detail Alamat<span class="text-danger">*</span></label>
                    <input type="text" name="detail_alamat" id="detail_alamat" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label>Lama Pekerjaan (hari)<span class="text-danger">*</span></label>
                <input type="number" name="lama_pekerjaan" class="form-control">
                @error('lama_pekerjaan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Jumlah Termin Pembayaran <span class="text-danger">*</span></label>
                <select name="jumlah_termin" id="jumlah_termin" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="1">1 Termin</option>
                    <option value="2">2 Termin</option>
                    <option value="3">3 Termin</option>
                    <option value="4">4 Termin</option>
                    <option value="5">5 Termin</option>
                </select>
            </div>

            <div id="formTermin"></div>

            <div id="validasiTotal" class="mt-2 fw-bold"></div>

            <br>
            {{-- Pilih Jenis Perizinan --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label fw-bold mb-0">Pilih Jenis Perizinan<span class="text-danger">*</span>
                    <button class="btn btn-sm btn-outline-primary d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePerizinan" aria-expanded="false">
                        <span class="me-1">Tampilkan</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    </label>
                </div>

                <div class="collapse mt-2" id="collapsePerizinan">
                    <div id="daftar-perizinan" class="row">
                        @foreach($perizinan as $p)
                        <div class="col-md-4 mb-2">
                            <label class="border rounded p-3 d-block">
                                <input type="checkbox" name="perizinan_id[]" value="{{ $p->id }}" class="me-2 jenis-checkbox">
                                {{ $p->jenis }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Pilih Tipe Harga --}}
            <div class="mb-3" id="tipeHargaGroup" style="display:none;">
                <label class="form-label fw-bold">Tipe Harga<span class="text-danger">*</span></label>
                <select name="harga_tipe" id="harga_tipe" class="form-select @error('harga_tipe') is-invalid @enderror">
                    <option value="">-- Pilih --</option>
                    <option value="satuan" {{ old('harga_tipe') == 'satuan' ? 'selected' : '' }}>Harga Satuan (per izin)</option>
                    <option value="gabungan" {{ old('harga_tipe') == 'gabungan' ? 'selected' : '' }}>Harga Gabungan (total langsung)</option>
                </select>
                @error('harga_tipe')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            

            {{-- Form Harga Gabungan (hidden by default) --}}
            <div class="mb-3" id="hargaGabunganGroup" style="display:none;">
                <label class="form-label fw-bold">Harga Gabungan (Rp)</label>
                <div class="harga-wrapper">
                    <input type="text"
                        class="form-control format-rupiah"
                        placeholder="Masukkan total harga gabungan...">

                    <input type="hidden"
                        class="harga-asli"
                        name="harga_gabungan">
                </div>
            </div>

            {{-- Form harga dinamis untuk per izin --}}
            <div id="formHargaPerizinan"></div>

            {{-- DISKON --}}
            <div id="diskonWrapper" class="card mt-3 d-none">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Diskon</h6>
            
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Tipe Diskon</label>
                            <select name="diskon_tipe" id="diskon_tipe" class="form-select">
                                <option value="">Tanpa Diskon</option>
                                <option value="persen">Persentase (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>
            
                        <div class="col-md-4 d-none" id="diskonPersen">
                            <label class="form-label">Diskon (%)</label>
                            <input type="number" id="diskon_persen" name="diskon_persen" class="form-control" min="0" max="100">
                        </div>
            
                        <div class="col-md-4 d-none" id="diskonNominal">
                            <label class="form-label">Diskon Nominal</label>
                            <input type="text" id="diskon_nominal_view" class="form-control format-rupiah">
                            <input type="hidden" id="diskon_nominal" name="diskon_nominal">
                        </div>
                    </div>
            
                    {{-- HASIL PERHITUNGAN --}}
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


            <button type="submit" class="btn btn-success mt-3">Submit</button>
        </form>
    </div>
</div>
@endsection

{{-- SCRIPT --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#customer-select').select2({ placeholder: 'Pilih nama perusahaan...', width: '100%' });

    // =======================
    // FUNGSI GET CUSTOMER
    function getCustomerData(customer_id, callback) {
        $.get(`{{ url('customer') }}/${customer_id}/get-customer`, function(res) {
            if (callback) callback(res);
        }).fail(function() {
            Swal.fire('Error', 'Gagal mengambil data perusahaan.', 'error');
        });
    }
    
    const baseKabupatenUrl = "{{ url('wilayah/kabupaten') }}";
    const baseKawasanUrl = "{{ url('kawasan') }}";

    // =======================
    // FUNGSI LOAD WILAYAH
    function loadKabupaten(provId, selectedKabId = null, callback = null) {
        $.get(`${baseKabupatenUrl}/${provId}`, function(kab) {
            $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
            kab.forEach(k => {
                $('#kabupaten_id').append(`<option value="${k.kode}" ${k.kode == selectedKabId ? 'selected' : ''}>${k.nama}</option>`);
            });
            if (callback) callback();
        });
    }


    function loadKawasan(kabId, selectedKawasanId = null, callback = null) {
        $.get(`${baseKawasanUrl}/${kabId}`, function(kaw) {
            $('#kawasan_id').html('<option value="">-- Pilih Kawasan --</option>');
            kaw.forEach(k => {
                $('#kawasan_id').append(`<option value="${k.id}" ${k.id == selectedKawasanId ? 'selected' : ''}>${k.nama_kawasan}</option>`);
            });
            if (callback) callback();
        });
    }

    // =======================
    // COPY NAMA
    $('#copyNama').on('change', function() {
        const customer_id = $('#customer-select').val();
        if (this.checked) {
            if (!customer_id) {
                Swal.fire('Oops!', 'Silakan pilih nama perusahaan terlebih dahulu.', 'warning');
                $(this).prop('checked', false);
                return;
            }
            getCustomerData(customer_id, function(res) {
                $('#nama_bangunan').val(res.nama_perusahaan).prop('readonly', true);
            });
        } else {
            $('#nama_bangunan').val('').prop('readonly', false);
        }
    });

    // =======================
    // COPY ALAMAT
    $('#copyAlamat').on('change', function() {
        const customer_id = $('#customer-select').val();

        if (this.checked) {
            if (!customer_id) {
                Swal.fire('Oops!', 'Silakan pilih customer terlebih dahulu.', 'warning');
                $(this).prop('checked', false);
                return;
            }

            getCustomerData(customer_id, function(res) {
                $('#provinsi_id').val(res.provinsi_id).prop('disabled', true);
                $('#provinsi_id_hidden').val(res.provinsi_id);

                loadKabupaten(res.provinsi_id, res.kabupaten_id, function() {
                    $('#kabupaten_id').prop('disabled', true);
                    $('#kabupaten_id_hidden').val(res.kabupaten_id);

                    loadKawasan(res.kabupaten_id, res.kawasan_id, function() {
                        $('#kawasan_id').prop('disabled', true);
                        $('#kawasan_id_hidden').val(res.kawasan_id);
                        $('#detail_alamat').val(res.detail_alamat).prop('readonly', true);
                        $('#detail_alamat_hidden').val(res.detail_alamat);
                    });
                });
            });

        } else {
            $('#detail_alamat').val('').prop('readonly', false);
            $('#provinsi_id, #kabupaten_id, #kawasan_id').prop('disabled', false).val('');
            $('#provinsi_id_hidden, #kabupaten_id_hidden, #kawasan_id_hidden, #detail_alamat_hidden').val('');
        }
    });

    // =======================
    // Dropdown manual wilayah
    $('#provinsi_id').change(function() {
        let provId = $(this).val();
        $('#provinsi_id_hidden').val(provId);
        loadKabupaten(provId);
    });

    $('#kabupaten_id').change(function() {
        let kabId = $(this).val();
        $('#kabupaten_id_hidden').val(kabId);
        loadKawasan(kabId);
    });

// Putar ikon panah saat collapse dibuka/tutup
$(document).ready(function() {
    const toggleBtn = $('[data-bs-target="#collapsePerizinan"]');
    const icon = toggleBtn.find('i');

    $('#collapsePerizinan').on('show.bs.collapse', function() {
        icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        toggleBtn.find('span').text('Sembunyikan');
    });

    $('#collapsePerizinan').on('hide.bs.collapse', function() {
        icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        toggleBtn.find('span').text('Tampilkan');
    });
});

// FORM HARGA PERIZINAN FINAL
const checkboxes = document.querySelectorAll('input[name="perizinan_id[]"]');
const tipeHargaGroup = document.getElementById('tipeHargaGroup');
const hargaGabunganGroup = document.getElementById('hargaGabunganGroup');
const hargaTipe = document.getElementById('harga_tipe');
const formHargaPerizinan = document.getElementById('formHargaPerizinan');

// Mapping nama perizinan ke field DB untuk luas
const luasMap = {
    'SLF': 'luas_slf',
    'PBG': 'luas_pbg',
    'SHGB': 'luas_shgb'
};
const perizinanButuhLuas = ['SLF', 'PBG', 'SHGB'];

// Fungsi buat card harga perizinan
function buatCardPerizinan(id, label) {
    const labelUpper = label.toUpperCase();
    let luasFieldName = null;
    const found = perizinanButuhLuas.find(nama => labelUpper.includes(nama));
    if (found) luasFieldName = luasMap[found];

    let luasHtml = '';
    if (luasFieldName) {
        luasHtml = `
            <div class="col-md-3 mb-2 luas-group">
                <label>Luas (m²)</label>
                <input type="number"
                       name="${luasFieldName}[${id}]"
                       class="form-control"
                       step="any">
            </div>
        `;
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
                            @foreach ($satuan_perizinans as $satuan)
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
                                class="form-control format-rupiah"
                                placeholder="0">

                            <input type="hidden"
                                name="harga_satuan[${id}]"
                                class="harga-asli">
                        </div>
                    </div>

                    <!-- LUAS -->
                    ${luasHtml}

                </div>

                <!-- ROW SUBTOTAL (KHUSUS SATUAN) -->
                <!-- SUBTOTAL (TEXT SAJA) -->
                <div class="mt-2 subtotal-group">
                    <small class="text-muted">
                        Subtotal:
                        <strong class="subtotal-text">Rp 0</strong>
                    </small>
                    <input type="hidden"
                           name="subtotal[${id}]"
                           class="subtotal-asli">
                </div>

            </div>
        </div>
    `;
}

function toggleSubtotal(card, show = true) {
    const subtotalGroup = card.querySelector('.subtotal-group');
    if (!subtotalGroup) return;

    subtotalGroup.style.display = show ? 'block' : 'none';
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
    }
});

// satuan sync
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('satuan-select')) {
        const card = e.target.closest('.perizinan-card');
        if (!card) return;
        card.querySelector('.satuan-hidden').value = e.target.value;
    }
});

function hitungSubtotal(card) {
    const tipeHarga = document.getElementById('harga_tipe').value;

    // JIKA HARGA GABUNGAN → JANGAN HITUNG SUBTOTAL
    if (tipeHarga === 'gabungan') {
        // pastikan nilainya nol (biar ga keikut total)
        card.querySelector('.subtotal-text').innerText = 'Rp 0';
        card.querySelector('.subtotal-asli').value = 0;
        return;
    }

    // === HARGA SATUAN ===
    const harga = parseInt(card.querySelector('.harga-asli')?.value || 0);
    const qty   = parseInt(card.querySelector('.qty-input')?.value || 1);

    const subtotal = harga * qty;

    card.querySelector('.subtotal-text').innerText =
        'Rp ' + subtotal.toLocaleString('id-ID');

    card.querySelector('.subtotal-asli').value = subtotal;

    hitungTotal();
}

//agar format harga ada titik nya per ribu
// document.addEventListener('input', function(e) {
//     if (e.target.classList.contains('format-rupiah')) {

//         let angka = e.target.value.replace(/\D/g, ''); // hanya digit
//         let format = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

//         e.target.value = format; // tampil titik

//         // simpan angka murni ke hidden input
//         e.target.parentElement.querySelector('.harga-asli').value = angka;
//     }
// });


document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('format-rupiah')) return;

    let angka = e.target.value.replace(/\D/g, '');
    e.target.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    const hidden = e.target
        .closest('.harga-wrapper')
        ?.querySelector('.harga-asli');

    if (hidden) hidden.value = angka;

    const card = e.target.closest('.perizinan-card');
    if (card) hitungSubtotal(card);

    hitungTotal();
});

//qty ubah manual
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty-input')) {
        const card = e.target.closest('.perizinan-card');
        hitungSubtotal(card);
    }
});

// Event checkbox toggle
checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        const label = this.parentElement.textContent.trim();
        const id = this.value;

        // Tampilkan tipe harga jika ada minimal 1 checkbox dipilih
        const adaYangDipilih = Array.from(checkboxes).some(c => c.checked);
        
        // STEP 1 → Tampilkan dropdown "Tipe Harga" dulu
        tipeHargaGroup.style.display = adaYangDipilih ? 'block' : 'none';

        // STEP 2 → Jangan tampilkan card apapun sampai tipe harga dipilih
        // formHargaPerizinan.style.display = "none";

        // Ambil card jika sudah ada
        let card = document.getElementById(`harga-${id}`);
        const tipeHargaSekarang = hargaTipe.value;

        if (this.checked) {
            // Jika card belum ada → buat baru
            if (!card) {
                const cardHtml = buatCardPerizinan(id, label);
                formHargaPerizinan.insertAdjacentHTML('beforeend', cardHtml);
                card = document.getElementById(`harga-${id}`);
            }

            // // SELALU sembunyikan card dulu sampai tipe harga dipilih
            applyHargaTipeToCard(card);
            card.classList.remove('d-none');

            if (!tipeHargaSekarang) {
                // belum pilih tipe harga → sembunyikan
                card.classList.add('d-none');
            } else {
                // SUDAH pilih tipe harga → langsung tampilkan
                card.classList.remove('d-none');

                if (tipeHargaSekarang === 'gabungan') {
                    // sembunyikan harga & subtotal
                    card.querySelector('.harga-asli')?.closest('.mb-2')?.style.setProperty('display','none');
                    card.querySelector('.subtotal-asli')?.closest('.mb-2')?.style.setProperty('display','none');
                }

                if (tipeHargaSekarang === 'satuan') {
                    // tampilkan harga & subtotal
                    card.querySelector('.harga-asli')?.closest('.mb-2')?.style.setProperty('display','block');
                    card.querySelector('.subtotal-asli')?.closest('.mb-2')?.style.setProperty('display','block');
                }
            }

        } else {
            // Jika uncheck → tetap sembunyikan
            if (card) card.classList.add('d-none');
        }
    });
});

// Event tipe harga
hargaTipe.addEventListener('change', function() {
    const tipe = this.value;
    
    if (tipe === 'gabungan') {
        resetHargaSatuan();    // hapus semua harga satuan
    }

    if (tipe === 'satuan') {
        resetHargaGabungan();  // hapus harga gabungan
    }


    // Tunjukkan wrapper form harga (karena tipe harga dipilih)
    formHargaPerizinan.style.display = 'block';

    if (tipe === 'gabungan') {
        hargaGabunganGroup.style.display = 'block';

        checkboxes.forEach(cb => {
            const card = document.getElementById(`harga-${cb.value}`);

            if (cb.checked && card) {
                card.classList.remove('d-none'); // ← tampilkan card
                nonaktifkanSubtotal(card);
                toggleSubtotal(card, false); // ⛔ sembunyikan subtotal


                
                // SEMBUNYIKAN harga satuan
                card.querySelector('.harga-satuan-asli')?.closest('.mb-2')
                    ?.style.setProperty('display', 'none');

                //  SEMBUNYIKAN subtotal
                card.querySelector('.subtotal-asli')?.closest('.mb-2')
                    ?.style.setProperty('display', 'none');

                // sembunyikan input harga
                const hargaInputGroup = card.querySelector('input[name^="harga_satuan"]')?.closest('.mb-2');
                if (hargaInputGroup) hargaInputGroup.style.display = 'none';

                // tampilkan luas (jika ada)
                const luasInputGroup = card.querySelector('input[name*="luas"]')?.closest('.mb-2');
                if (luasInputGroup) luasInputGroup.style.display = 'block';
            }
        });
    } 
    
    else if (tipe === 'satuan') {
        hargaGabunganGroup.style.display = 'none';

        checkboxes.forEach(cb => {
            const card = document.getElementById(`harga-${cb.value}`);

            if (cb.checked && card) {
                card.classList.remove('d-none'); // ← tampilkan card
                toggleSubtotal(card, true); 

                // tampilkan harga satuan
                card.querySelector('.harga-satuan-asli')?.closest('.mb-2')
                    ?.style.setProperty('display', 'block');

                //  tampilkan subtotal
                card.querySelector('.subtotal-asli')?.closest('.mb-2')
                    ?.style.setProperty('display', 'block');

                // tampilkan input harga
                const hargaInputGroup = card.querySelector('input[name^="harga_satuan"]')?.closest('.mb-2');
                if (hargaInputGroup) hargaInputGroup.style.display = 'block';
            }
        });
    } 
    
    else {
        hargaGabunganGroup.style.display = 'none';
        formHargaPerizinan.style.display = 'none';
    }
    
        // optional tapi bagus
    if (typeof hitungTotal === 'function') {
        hitungTotal();
    }
});


function applyHargaTipeToCard(card) {
    const tipe = document.getElementById('harga_tipe').value;

    const hargaGroup = card.querySelector('.harga-satuan-asli')?.closest('.mb-2');
    const subtotalGroup = card.querySelector('.subtotal-asli')?.closest('.mb-2');

    if (tipe === 'gabungan') {
        if (hargaGroup) hargaGroup.style.display = 'none';
        if (subtotalGroup) subtotalGroup.style.display = 'none';
    }

    if (tipe === 'satuan') {
        if (hargaGroup) hargaGroup.style.display = 'block';
        if (subtotalGroup) subtotalGroup.style.display = 'block';
    }
}

function nonaktifkanSubtotal(card) {
    const subtotalInput = card.querySelector('.subtotal-asli');
    const subtotalText  = card.querySelector('.subtotal-text');

    if (subtotalInput) subtotalInput.value = 0;
    if (subtotalText) subtotalText.innerText = 'Rp 0';
}


function resetHargaSatuan() {
    document.querySelectorAll('#formHargaPerizinan .harga-asli').forEach(input => {
        input.value = '';
    });

    document.querySelectorAll('#formHargaPerizinan .format-rupiah').forEach(input => {
        input.value = '';
    });
}

function resetHargaGabungan() {
    const gabunganView = document.querySelector('#hargaGabunganGroup .format-rupiah');
    const gabunganAsli = document.querySelector('#hargaGabunganGroup .harga-asli');

    if (gabunganView) gabunganView.value = '';
    if (gabunganAsli) gabunganAsli.value = '';
}

// ===============================
// ELEMENT TAMBAHAN DISKON & TOTAL
// ===============================
const diskonWrapper = document.getElementById('diskonWrapper');
const diskonTipe = document.getElementById('diskon_tipe');
const totalHargaEl = document.getElementById('totalHarga');
const totalDiskonEl = document.getElementById('totalDiskon');
const grandTotalEl = document.getElementById('grandTotal');

// ===============================
// CEK TAMPILKAN DISKON
// ===============================
function cekTampilkanDiskon() {
    const adaPerizinan = Array.from(
        document.querySelectorAll('input[name="perizinan_id[]"]')
    ).some(cb => cb.checked);

    const tipeHarga = document.getElementById('harga_tipe').value;

    if (adaPerizinan && tipeHarga) {
        diskonWrapper.classList.remove('d-none');
    } else {
        diskonWrapper.classList.add('d-none');
    }
}

// ===============================
// FORMAT RUPIAH
// ===============================
function formatRupiah(angka) {
    return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
}

// ===============================
// HITUNG TOTAL
// ===============================
function hitungTotal() {
    let totalAwal = 0;
    const tipeHarga = document.getElementById('harga_tipe').value;

    // === HARGA SATUAN ===
    if (tipeHarga === 'satuan') {
        // document.querySelectorAll('.harga-asli').forEach(el => {
            document.querySelectorAll('.subtotal-asli').forEach(el => {
            totalAwal += parseInt(el.value || 0);
        });
    }

    // === HARGA GABUNGAN ===
    if (tipeHarga === 'gabungan') {
        const hargaGabungan = document.querySelector('#hargaGabunganGroup .harga-asli');
        totalAwal = parseInt(hargaGabungan?.value || 0);
    }

    // === DISKON ===
    let diskon = 0;

    if (diskonTipe.value === 'persen') {
        const persen = parseFloat(
            document.getElementById('diskon_persen').value || 0
        );
        diskon = totalAwal * persen / 100;
    }

    if (diskonTipe.value === 'nominal') {
        const nominal = document
            .getElementById('diskon_nominal_view')
            .value.replace(/\D/g, '');
        diskon = parseInt(nominal || 0);
    }

    if (diskon > totalAwal) diskon = totalAwal;

    // === OUTPUT ===
    totalHargaEl.innerText = formatRupiah(totalAwal);
    totalDiskonEl.innerText = formatRupiah(diskon);
    grandTotalEl.innerText = formatRupiah(totalAwal - diskon);
}

// ===============================
// EVENT LISTENER TAMBAHAN
// ===============================
document.addEventListener('input', function(e) {
    if (
        e.target.classList.contains('format-rupiah') ||
        e.target.id === 'diskon_persen'
    ) {
        hitungTotal();
    }
});

document.addEventListener('input', function (e) {
    if (e.target.id === 'diskon_nominal_view') {
        const angka = e.target.value.replace(/\D/g, '');
        document.getElementById('diskon_nominal').value = angka;
        hitungTotal();
    }
});


diskonTipe.addEventListener('change', function() {
    document.getElementById('diskonPersen').classList.add('d-none');
    document.getElementById('diskonNominal').classList.add('d-none');

    if (this.value === 'persen') {
        document.getElementById('diskonPersen').classList.remove('d-none');
    }

    if (this.value === 'nominal') {
        document.getElementById('diskonNominal').classList.remove('d-none');
    }

    hitungTotal();
});

// ===============================
// PANGGIL OTOMATIS SAAT INTERAKSI
// ===============================
document.addEventListener('change', function(e) {
    if (
        e.target.name === 'perizinan_id[]' ||
        e.target.id === 'harga_tipe'
    ) {
        cekTampilkanDiskon();
        hitungTotal();
    }
});


// =======================
// TERMIN PEMBAYARAN
// =======================

$('#jumlah_termin').on('change', function() {
    const jumlah = parseInt($(this).val());
    const formTermin = $('#formTermin');

    formTermin.html(''); // reset form

    if (!jumlah) return;

    let html = '<div class="row">';

    for (let i = 1; i <= jumlah; i++) {
        html += `
            <div class="col-md-4 mb-3">
                <label>Termin ${i} (%)</label>
                <input type="number" 
                       name="termin[${i}]" 
                       class="form-control termin-input" 
                       min="1" 
                       max="100"
                       placeholder="Masukkan persentase...">
            </div>
        `;
    }

    html += '</div>';

    formTermin.html(html);
});

// VALIDASI TOTAL = 100%
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

//auto untuk no sph 
const previewSphUrl = "{{ url('quotation/preview-sph') }}";
const cabangSelect = document.getElementById('cabang_id');
const noSphInput = document.getElementById('no_sph');

function loadNoSph(cabangId) {
    if (!cabangId) {
        noSphInput.value = "";
        return;
    }

    fetch(previewSphUrl + '/' + cabangId)
        .then(res => res.json())
        .then(data => {
            noSphInput.value = data.no_sph;
        })
        .catch(err => {
            console.error(err);
            noSphInput.value = "";
        });
}

// 🔹 Saat user ganti cabang manual
cabangSelect.addEventListener('change', function () {
    loadNoSph(this.value);
});

// 🔹 Saat halaman pertama kali dibuka (AUTO)
if (cabangSelect.value) {
    loadNoSph(cabangSelect.value);
}


});
</script>