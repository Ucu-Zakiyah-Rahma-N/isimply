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

                {{-- RIGHT SUMMARY --}}
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
                </div>

            </div>

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

                <div class="col-md-2">
                    <label class="form-label">Keterangan</label>
                    <input type="text" class="form-control" name="keterangan" value="{{ $invoice->keterangan }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total Tagihan</label>
                    <input type="text" class="form-control" value="{{ number_format($invoice->grand_total) }}" readonly>
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
                <div class="col-md-3">
                    <label class="form-label">Nominal diterima</label>
                    <input type="text" name="nominal" id="nominal_diterima" class="form-control" readonly>
                </div>
            </div>

            <hr>

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
function hitungPph() {

    let rate = parseFloat(document.getElementById('pph_rate').value) || 0;

    let grandTotal = {{ $invoice->grand_total }};
    let nominalInvoice = {{ $invoice->nominal_invoice }};
    let totalAfterDiskonInv = {{ $invoice->total_after_diskon_inv ?? 0 }};

    // Tentukan DPP yang benar
    let dpp = totalAfterDiskonInv > 0 ? totalAfterDiskonInv : nominalInvoice;

    let nilaiPph = 0;
    let diterima = grandTotal;

    // Kalau pakai PPh
    if (rate > 0) {
        nilaiPph = dpp * rate / 100;
        diterima = grandTotal - nilaiPph;
    }

    document.getElementById('nilai_pph').value = nilaiPph.toFixed(2);
    document.getElementById('nominal_diterima').value = diterima.toFixed(2);
}

// Trigger saat dropdown berubah
document.getElementById('pph_rate').addEventListener('change', hitungPph);

// Hitung saat pertama load
window.addEventListener('load', hitungPph);
</script>

<!-- <script>
document.getElementById('pph_rate').addEventListener('change', function () {
    let rate = parseFloat(this.value);
    let grandTotal = {{ $invoice->grand_total }};
    let nominalInvoice = {{ $invoice->nominal_invoice }};
    let totalAfterDiskonInv = {{ $invoice->total_after_diskon_inv ?? 0 }};

    let nilaiPph;

    if (totalAfterDiskonInv && totalAfterDiskonInv > 0) {
        nilaiPph = totalAfterDiskonInv * rate / 100;
    } else {
        nilaiPph = nominalInvoice * rate / 100;
    }

    let diterima = grandTotal - nilaiPph;

    // tampilkan formatted untuk user
    document.getElementById('nilai_pph').value = nilaiPph.toFixed(2); // kirim angka mentah
    document.getElementById('nominal_diterima').value = diterima.toFixed(2);
});
</script> -->
@endsection