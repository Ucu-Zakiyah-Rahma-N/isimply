@extends('app.template')

<script>
</script>
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Form Terima Pembayaran Invoice</h5>
    </div>

    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="invoiceForm" action="{{ route('finance.invoice.storePembayaran') }}" method="POST">
            @csrf

            {{-- HEADER --}}
            <div class="row mb-4 align-items-start">

                <div class="col-md-3">
                    <label class="form-label">No Invoice</label>
                    <input type="text" class="form-control" value="{{ $invoice->no_invoice }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tgl Pembayaran</label>
                    <input type="date" class="form-control" name="tanggal">
                </div>

                <div class="col-md-3">
                    <label class="form-label">COA</label>
                    <input type="text" class="form-control"
                        value="{{ $coaPendapatan->kode_akun }} - {{ $coaPendapatan->nama_akun }}" readonly>
                    <input type="hidden" name="coa_id" value="{{ $coaPendapatan->id }}">
                </div>

                <!-- {{-- RIGHT SUMMARY --}}
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="border rounded p-3 bg-light" style="width:320px">
                        <strong>Total:</strong>
                        <h4 class="mb-2">Rp. <span id="grandTotal">{{ number_format($invoice->grand_total ?? 0) }}</span></h4>

                        <small>Nominal PO : Rp <span id="nominalPo">{{ number_format($invoice->nominal_po ?? 0) }}</span></small><br>
                        <small>Termin : Rp <span id="nominalInvoice">{{ number_format($invoice->nominal_invoice ?? 0) }}</span></small><br>
                        @php
                        $diskon = 0;

                        if ($invoice->tipe_diskon == 'nominal') {
                        $diskon = $invoice->nilai_diskon;
                        } elseif ($invoice->tipe_diskon == 'persen') {
                        $diskon = ($invoice->nominal_invoice * $invoice->nilai_diskon) / 100;
                        }
                        @endphp

                        <small>
                            Diskon : Rp <span id="diskon">{{ number_format($diskon) }}</span>
                        </small><br>
                        <small>
                            Total Setelah Diskon : Rp
                            <span id="total_after_diskon_inv">
                                {{ number_format($invoice->total_after_diskon_inv ?? 0) }}
                            </span>
                        </small><br>
                        <small>PPN : Rp <span id="ppn">{{ number_format($invoice->ppn ?? 0) }}</span></small><br>
                        <small>Grand Total : Rp <span id="grandTotal2">{{ number_format($invoice->grand_total ?? 0) }}</span></small>
                    </div>
                </div> -->

            </div>
            <br><br>
            {{-- INFORMASI PERUSAHAAN --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Nama Perusahaan</label>

                    {{-- kirim id customer --}}
                    <input type="hidden" name="customer_id" value="{{ $invoice->customer->id }}">

                    {{-- tampilkan nama --}}
                    <input type="text"
                        class="form-control"
                        value="{{ $invoice->customer->nama_perusahaan ?? '-' }}"
                        readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Projek</label>
                    {{-- kirim id po --}}
                    <input type="hidden" name="po_id" value="{{ $invoice->po->id }}">

                    {{-- tampilkan nama --}}
                    <input type="text"
                        class="form-control"
                        value="{{ $invoice->po->no_po ?? '-' }}"
                        readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label label-saas">Metode Pembayaran</label>
                    <select class="form-select input-saas" name="metode_pembayaran">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label label-saas">Ke Bank / Kas</label>

                    <select class="form-select input-saas" name="coa_bank_id" class="form-control">
                        <option value="">-- pilih bank/kas --</option>
                        @foreach ($banks as $bank)
                        <option value="{{ $bank->id }}">
                            {{ $bank->nama_akun }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            </div>

            <hr>

            <div class="row mb-3">


                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-success btn-sm" onclick="lunasiInvoice()">
                        Lunasi
                    </button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Keterangan</label>
                    <input type="text" class="form-control" name="keterangan" value="{{ $invoice->keterangan }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total Tagihan</label>
                    <input type="text" class="form-control" value="{{ number_format($invoice->grand_total,0,',','.') }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nominal diterima</label>
                    <input type="text" name="nominal" id="nominal_diterima" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">PPh</label>
                    <select class="form-select input-saas" name="pph_rate" id="pph_rate" class="form-control">
                        <option value="0">Tidak pakai</option>
                        <option value="2">PPh 2%</option>
                        <option value="3.5">PPh 3.5%</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nilai PPh</label>
                    <input type="text" name="nilai_pph" id="nilai_pph" class="form-control" readonly>
                </div>

            </div>

            <hr>

            {{-- SUMMARY --}}
            <div class="row mt-4">
                <div class="col-md-6"></div>

                <div class="col-md-6">

                    <div class="d-flex justify-content-between mb-1">
                        <span>Nominal PO</span>
                        <span>Rp {{ number_format($invoice->nominal_po ?? 0) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Termin ke {{ $invoice->termin_ke ?? 1 }} ({{ $invoice->persentase_termin ?? 0 }}%)</span>
                        <span>Rp {{ number_format($invoice->nominal_invoice ?? 0) }}</span>
                    </div>

                    {{-- Diskon --}}
                    @if(($invoice->nilai_diskon ?? 0) > 0)
                    @php
                    $diskon = 0;
                    if ($invoice->tipe_diskon == 'nominal') {
                    $diskon = $invoice->nilai_diskon;
                    } elseif ($invoice->tipe_diskon == 'persen') {
                    $diskon = ($invoice->nominal_invoice * $invoice->nilai_diskon) / 100;
                    }
                    @endphp

                    <div class="d-flex justify-content-between mb-1">
                        <span>Diskon</span>
                        <span>Rp {{ number_format($diskon) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Total Setelah Diskon</span>
                        <span>Rp {{ number_format($invoice->total_after_diskon_inv ?? 0) }}</span>
                    </div>
                    @endif

                    {{-- PPN --}}
                    @if(($invoice->ppn ?? 0) > 0)
                    <div class="d-flex justify-content-between mb-1">
                        <span>PPN</span>
                        <span>Rp {{ number_format($invoice->ppn) }}</span>
                    </div>
                    @endif

                    <br>
                    {{-- Nominal diterima --}}
                    <div class="d-flex justify-content-between mb-1">
                        <span>Nominal diterima</span>
                        <span id="summary_diterima">Rp 0</span>
                    </div>

                    {{-- PPH --}}
                    <div class="d-flex justify-content-between mb-1" id="summary_pph_row" style="display:none;">
                        <span>PPH</span>
                        <span id="summary_pph">Rp 0</span>
                    </div>

                    <hr>

                    {{-- Sisa tagihan --}}
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Sisa Tagihan</span>
                        <span id="summary_sisa">
                            Rp {{ number_format($sisaTagihan,0,',','.') }}
                        </span>
                    </div>

                </div>
            </div>

            <br><br>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    Buat Penerimaan
                </button>
            </div>
        </form>
    </div>
</div>

<script>

    let grandTotal = {{ $invoice->grand_total }};
    let dpp = {{ $invoice->nominal_invoice }};
    let sisaAwal = {{ $sisaTagihan }};
    let pphRateTerakhir = {{ $pphRateTerakhir ?? 0 }};
    let pphSudahDibayar = {{ $pphSudahDibayar ?? 0 }};

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

function getNumber(value) {
    return parseFloat(value.replace(/[^0-9]/g,'')) || 0;
}

// =========================
// HITUNG PPH PROPOSIONAL
// =========================
function hitungPph() {
    let rate = parseFloat(document.getElementById('pph_rate').value) || 0;
    let cash = getNumber(document.getElementById('nominal_diterima').value);

    // PPH total invoice
    let pphTotal = Math.round(dpp * rate / 100);

    // sisa PPH yang belum dibayar
    let sisaPph = pphTotal - pphSudahDibayar;

    let nilaiPph = 0;

    if (rate > 0 && cash > 0) {
        // Proporsional PPH
        let totalCashInvoice = grandTotal - pphTotal;
        nilaiPph = Math.round((cash / totalCashInvoice) * pphTotal);
        if(nilaiPph > sisaPph) nilaiPph = sisaPph; // jangan lebih dari sisa PPH
        document.getElementById('summary_pph_row').style.display = 'flex';
    } else {
        document.getElementById('summary_pph_row').style.display = 'none';
    }

    let piutangTertutup = cash + nilaiPph;
    let sisa = sisaAwal - piutangTertutup;
    if(sisa < 0) sisa = 0;

    document.getElementById('nilai_pph').value = formatRupiah(nilaiPph);
    document.getElementById('summary_diterima').innerText = ' ' + formatRupiah(cash);
    document.getElementById('summary_pph').innerText = ' ' + formatRupiah(nilaiPph);
    document.getElementById('summary_sisa').innerText = ' ' + formatRupiah(sisa);
}

// =========================
// TOMBOL LUNASI
// =========================
function lunasiInvoice() {
    // pakai PPH terakhir dari coa_pph_id
    let rate = (pphRateTerakhir || 0).toString();
    document.getElementById('pph_rate').value = rate;

    // Hitung sisa PPH yang belum dibayar
    let pphTotal = Math.round(dpp * parseFloat(rate) / 100);
    let sisaPph = pphTotal - pphSudahDibayar;

    let cash = sisaAwal - sisaPph;
    if(cash < 0) cash = 0;

    let pph = sisaAwal - cash;
    if(pph > sisaPph) pph = sisaPph;

    document.getElementById('nominal_diterima').value = formatRupiah(cash);
    document.getElementById('nilai_pph').value = formatRupiah(pph);

    document.getElementById('summary_diterima').innerText = ' ' + formatRupiah(cash);
    document.getElementById('summary_pph').innerText = ' ' + formatRupiah(pph);
    document.getElementById('summary_pph_row').style.display = pph > 0 ? 'flex' : 'none';
}

// =========================
// EVENT LISTENER
// =========================
document.getElementById('nominal_diterima').addEventListener('input', function() {
    this.value = formatRupiah(getNumber(this.value));
    hitungPph();
});

document.getElementById('pph_rate').addEventListener('change', hitungPph);

window.addEventListener('load', hitungPph);

document.getElementById('invoiceForm').addEventListener('submit', function() {
    document.getElementById('nominal_diterima').value = getNumber(document.getElementById('nominal_diterima').value);
});


// let grandTotal = {{ $invoice->grand_total }};
// let dpp = {{ $invoice->nominal_invoice }};

// function formatRupiah(angka){
//     return new Intl.NumberFormat('id-ID').format(angka);
// }

// function getNumber(value){
//     return parseFloat(value.replace(/[^0-9]/g,'')) || 0;
// }

// // =========================
// // FORMAT INPUT NOMINAL
// // =========================
// document.getElementById('nominal_diterima').addEventListener('input', function(){

//     let value = getNumber(this.value);

//     this.value = formatRupiah(value);

//     hitungPph();
// });

// =========================
// TRIGGER SAAT PPH BERUBAH
// =========================
// document.getElementById('pph_rate').addEventListener('change', function(){
//     hitungPph();
// });

// =========================
// HITUNG PPH
// =========================
// function hitungPph(){

//     let rate = parseFloat(document.getElementById('pph_rate').value) || 0;

    // let cash = getNumber(document.getElementById('nominal_diterima').value);

    // let sisaAwal = {{ $sisaTagihan }};

    // let nilaiPph = 0;

    // if(rate > 0){

    //     // PPH total invoice dari DPP
    //     let pphTotal = Math.round(dpp * rate / 100);

    //     // total cash bersih invoice
    //     let totalCashInvoice = grandTotal - pphTotal;

    //     // PPH cicilan proporsional
    //     nilaiPph = Math.round((cash / totalCashInvoice) * pphTotal);

    //     document.getElementById('summary_pph_row').style.display = 'flex';

    // }else{

    //     document.getElementById('summary_pph_row').style.display = 'none';

    // }

    // let piutangTertutup = Math.round(cash) + nilaiPph;

    // let sisa = Math.round(sisaAwal) - piutangTertutup;

    // if(sisa < 0){
    //     sisa = 0;
    // }

    // document.getElementById('nilai_pph').value = formatRupiah(nilaiPph);

    // document.getElementById('summary_diterima').innerText =
    //     'Rp ' + formatRupiah(cash);

    // document.getElementById('summary_pph').innerText =
    //     'Rp ' + formatRupiah(nilaiPph);

    // document.getElementById('summary_sisa').innerText =
    //     'Rp ' + formatRupiah(sisa);
// }

// =========================
// TOMBOL LUNASI
// =========================
// function lunasiInvoice(){

//     let sisa = {{ $sisaTagihan }};
//     let rate = parseFloat({{ $pphRateTerakhir ?? 0 }});

//     // set dropdown pph
//     document.getElementById('pph_rate').value = rate;

//     let cash = sisa;
//     let pph = 0;

//     if(rate > 0){

//         cash = sisa / (1 + rate/100);
//         pph = sisa - cash;

//     }

//     cash = Math.round(cash);
//     pph = Math.round(pph);

//     document.getElementById('nominal_diterima').value = formatRupiah(cash);
//     document.getElementById('nilai_pph').value = formatRupiah(pph);

//     document.getElementById('summary_diterima').innerText =
//         'Rp ' + formatRupiah(cash);

//     document.getElementById('summary_pph_row').style.display = 'flex';

//     document.getElementById('summary_pph').innerText =
//         'Rp ' + formatRupiah(pph);

//     document.getElementById('summary_sisa').innerText =
//         'Rp 0';
// }
// // function lunasiInvoice(){

// //     let sisaTagihan = {{ $sisaTagihan }};
// //     let rate = {{ $pphRateTerakhir ?? 0 }};
// //     let dpp = {{ $invoice->nominal_invoice }};
// //     let pphSudahDibayar = {{ $pphSudahDibayar ?? 0 }};

// //     document.getElementById('pph_rate').value = rate;

// //     let cash = sisaTagihan;
// //     let pph = 0;

// //     if(rate > 0){

// //         // total PPH invoice
// //         let pphTotal = Math.round(dpp * rate / 100);

// //         // sisa PPH yang belum dipakai
// //         let sisaPph = pphTotal - pphSudahDibayar;

// //         // cash terakhir
// //         cash = sisaTagihan - sisaPph;

// //         pph = sisaPph;
// //     }

// //     cash = Math.round(cash);
// //     pph = Math.round(pph);

// //     document.getElementById('nominal_diterima').value = formatRupiah(cash);
// //     document.getElementById('nilai_pph').value = formatRupiah(pph);

// //     document.getElementById('summary_diterima').innerText =
// //         'Rp ' + formatRupiah(cash);

// //     document.getElementById('summary_pph_row').style.display = 'flex';

// //     document.getElementById('summary_pph').innerText =
// //         'Rp ' + formatRupiah(pph);

// //     document.getElementById('summary_sisa').innerText =
// //         'Rp 0';
// // }

// // =========================
// // HITUNG SAAT LOAD
// // =========================
// window.addEventListener('load', function(){
//     hitungPph();
// });

// // =========================
// // BERSIHKAN FORMAT SAAT SUBMIT
// // =========================
// document.getElementById('invoiceForm').addEventListener('submit', function(){

//     let nominalInput = document.getElementById('nominal_diterima');

//     nominalInput.value = getNumber(nominalInput.value);

// });


//     function hitungPph() {

//         let rate = parseFloat(document.getElementById('pph_rate').value) || 0;

//         let cash = getNumber(document.getElementById('nominal_diterima').value);

//         let sisaAwal = {{ $sisaTagihan }};

//         let nilaiPph = 0;

//         if (rate > 0) {
//             nilaiPph = cash * rate / 100;
//             document.getElementById('summary_pph_row').style.display = 'flex';
//         } else {
//             document.getElementById('summary_pph_row').style.display = 'none';
//         }

//         // let piutangTertutup = cash + nilaiPph;
//         let piutangTertutup = Math.round(cash) + Math.round(nilaiPph);

//         // let sisa = sisaAwal - piutangTertutup;
//         let sisa = Math.round(sisaAwal) - piutangTertutup;
//         //mencegah sisa tagihan minus
//         // if (sisa < 0) {
//         //     sisa = 0;
//         // }
//         document.getElementById('nilai_pph').value =
//             formatRupiah(Math.round(nilaiPph));

//         document.getElementById('summary_diterima').innerText =
//             'Rp ' + formatRupiah(cash);

//         document.getElementById('summary_pph').innerText =
//             'Rp ' + formatRupiah(Math.round(nilaiPph));

//         document.getElementById('summary_sisa').innerText =
//             'Rp ' + formatRupiah(sisa);
//     }

//     // trigger jika pph berubah
//     document.getElementById('pph_rate').addEventListener('change', hitungPph);

//     // hitung saat pertama load
//     window.addEventListener('load', hitungPph);

//     document.getElementById('invoiceForm').addEventListener('submit', function() {

//         let nominalInput = document.getElementById('nominal_diterima');

//         nominalInput.value = getNumber(nominalInput.value);

//     });



//     function lunasiInvoice(){

//     let sisa = {{ $sisaTagihan }};

//     // ambil pph terakhir
//     let rate = {{ $pphRateTerakhir ?? 0 }};

//     // set dropdown pph
//     document.getElementById('pph_rate').value = rate;

//     let cash = sisa;
//     let pph = 0;

//     if(rate > 0){
//         cash = sisa / (1 + rate/100);
//         pph = sisa - cash;
//     }

//     cash = Math.round(cash);
//     pph = Math.round(pph);

//     // isi form
//     document.getElementById('nominal_diterima').value = formatRupiah(cash);
//     document.getElementById('nilai_pph').value = formatRupiah(pph);

//     // update summary
//     document.getElementById('summary_diterima').innerText =
//         'Rp ' + formatRupiah(cash);

//     document.getElementById('summary_pph_row').style.display = 'flex';

//     document.getElementById('summary_pph').innerText =
//         'Rp ' + formatRupiah(pph);

//     document.getElementById('summary_sisa').innerText =
//         'Rp 0';
// }

</script>
@endsection