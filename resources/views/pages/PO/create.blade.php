@extends('app.template')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Form PO/SPK</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('PO.store') }}" method="POST" enctype="multipart/form-data" id="formProyek">
            @csrf

            {{-- No PO & Tanggal PO --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>No PO</label>
                    <input type="text" name="no_po" class="form-control" value="">
                </div>
                <div class="col-md-6">
                    <label>Tanggal PO <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_po" class="form-control" required>
                </div>
            </div>

{{-- Kondisi PO --}}
<div class="row mb-3">
    <div class="col-md-12">
        <label>File PO<span class="text-danger">*</span></label><br>
        
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="po_status" id="po-ada" value="ada">
            <label class="form-check-label" for="po-ada">Ada</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="po_status" id="po-belum" value="belum">
            <label class="form-check-label" for="po-belum">Belum Ada</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="po_status" id="po-tidak" value="tidak">
            <label class="form-check-label" for="po-tidak">Tidak Ada</label>
        </div>
    </div>
</div>

{{-- File PO (disembunyikan default) --}}
<div class="row mb-3" id="file-po-wrapper" style="display: none;">
    <div class="col-md-12">
        <label>File PO</label>
        <input type="file" name="file" accept="application/pdf" class="form-control">
    </div>
</div>


            <!--{{-- File PO (1 baris penuh) --}}-->
            <!--<div class="row mb-3">-->
            <!--    <div class="col-md-12">-->
            <!--        <label>File PO </label>-->
            <!--        <input type="file" name="file" accept= "application/pdf" class="form-control">-->
            <!--    </div>-->
            <!--</div>-->

            {{-- Nama Perusahaan & Referensi SPH --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="customer_id">Nama Perusahaan <span class="text-danger">*</span></label>
                    <select id="customer-select" name="customer_id" class="form-select">
                        <option value="">-- Pilih Perusahaan --</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->nama_perusahaan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="quotation_id">Referensi SPH <span class="text-danger">*</span></label>
                    <select id="sph-select" name="quotation_id" class="form-select">
                        <option value="">-- Pilih SPH --</option>
                    </select>
                </div>
            </div>

            {{-- PIC Keuangan & Kontak --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>PIC Keuangan</label>
                    <input type="text" name="nama_pic_keuangan" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Kontak</label>
                    <input type="text" name="kontak_pic_keuangan" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-3">
                Submit
            </button>
        </form>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            
            
$('input[name="po_status"]').on('change', function() {
    let status = $('input[name="po_status"]:checked').val();

    if (status === 'ada') {
        $('#file-po-wrapper').show(); // tampilkan input file
    } else {
        $('#file-po-wrapper').hide(); // sembunyikan input file
        $('#file-po-wrapper input[type=file]').val(''); // reset file jika sebelumnya ada
    }
});

            // Inisialisasi Select2 (opsional)
            $('#customer-select').select2({
                placeholder: 'Pilih nama perusahaan...',
                width: '100%'
            });

            $('#sph-select').select2({
                placeholder: 'Pilih SPH...',
                width: '100%',
                matcher: function(params, data) {
                    // Kalau input kosong → tampilkan semua opsi
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Ambil teks dari option
                    var term = params.term.toLowerCase();
                    var text = data.text.toLowerCase();

                    // Cek apakah teks dimulai dengan input pencarian
                    if (text.startsWith(term)) {
                        return data;
                    }

                    // Kalau tidak cocok, sembunyikan
                    return null;
                }
            });
            // Kalau klik dropdown SPH sebelum pilih perusahaan → tampilkan alert
            $('#sph-select').on('select2:opening', function(e) {
                var customerId = $('#customer-select').val();
                if (!customerId) {
                    e.preventDefault(); // hentikan dropdown terbuka
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops!',
                        text: 'Silakan pilih nama perusahaan terlebih dahulu.'
                    });
                }
            });

            // Saat perusahaan dipilih → ambil daftar SPH
            $('#customer-select').on('change', function() {
                var customerId = $(this).val();
                var sphSelect = $('#sph-select');
                sphSelect.empty().append('<option value="">-- Pilih SPH --</option>');

                if (!customerId) return;

                sphSelect.prop('disabled', true).append('<option>Loading...</option>');

                $.ajax({
                url: '{{ url("quotation/by-customer") }}/' + customerId,
                    type: 'GET',
                    success: function(data) {
                        sphSelect.empty();
                        if (data.length > 0) {
                            sphSelect.append('<option value="">-- Pilih SPH --</option>');
                            $.each(data, function(index, item) {
                                sphSelect.append('<option value="' + item.id + '">' +
                                    item.no_sph + '</option>');
                            });
                        } else {
                            sphSelect.append('<option value="">(Tidak ada SPH)</option>');
                        }
                        sphSelect.prop('disabled', false);
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memuat data SPH.', 'error');
                        sphSelect.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
