@extends('app.template')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Edit Invoice</h5>
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

            <form id="invoiceForm" action="{{ route('finance.invoice.update', $invoice->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="po_id" value="{{ $invoice->po_id }}">

                {{-- HEADER --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">No Invoice</label>
                        <input type="text" name="no_invoice" class="form-control"
                            value="{{ old('no_invoice', $invoice->no_invoice) }}" required readonly>
                    </div>
                </div>

                {{-- INFORMASI PERUSAHAAN --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Nama Perusahaan</label>
                        <input type="hidden" name="customer_id" value="{{ $invoice->customer_id }}">
                        <input type="text" class="form-control" name="nama_perusahaan"
                            value="{{ old('nama_perusahaan', $invoice->customer->nama_perusahaan) }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Alamat Penagihan</label>
                        <textarea class="form-control" rows="3" readonly>
        {{ collect([
            $invoice->detail_alamat,
            $invoice->kawasan_name,
            $invoice->kabupaten_name,
            $invoice->provinsi->nama ?? '-',
        ])->filter()->implode(', ') }}
    </textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">NPWP</label>
                        <input type="text" class="form-control" name="npwp"
                            value="{{ old('npwp', $invoice->customer->npwp ?? '-') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Referensi Proyek (No PO)</label>
                        <select class="form-control" name="po_id">
                            <option value="{{ $invoice->po_id }}">
                                {{ $invoice->po->no_po ?? '-' }}
                            </option>
                        </select>
                    </div>

                </div>

                {{-- JENIS & TANGGAL --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Jenis Invoice</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_invoice" value="dp"
                                {{ old('jenis_invoice', $invoice->jenis_invoice) === 'dp' ? 'checked' : '' }}>
                            <label class="form-check-label">DP</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_invoice" value="pelunasan"
                                {{ old('jenis_invoice', $invoice->jenis_invoice) === 'pelunasan' ? 'checked' : '' }}>
                            <label class="form-check-label">Pelunasan</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Invoice Sebelumnya</label>
                        @if ($invoice_sebelumnya)
                            <input type="text" class="form-control" value="{{ $invoice_sebelumnya->no_invoice }}"
                                readonly>
                        @else
                            <input type="text" class="form-control" value="Belum ada invoice" readonly>
                        @endif
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" class="form-control" name="keterangan"
                            value="{{ old('keterangan', $invoice->keterangan) }}">
                        <input type="hidden" name="persentase_termin"
                            value="{{ old('persentase_termin', $invoice->persentase_termin) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" class="form-control" name="catatan"
                            value="{{ old('catatan', $invoice->catatan) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tgl Invoice</label>
                        <input type="date" class="form-control" name="tgl_inv"
                            value="{{ old('tgl_inv', $invoice->tgl_inv ? \Carbon\Carbon::parse($invoice->tgl_inv)->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tgl Jatuh Tempo</label>
                        <input type="date" class="form-control" name="tgl_jatuh_tempo"
                            value="{{ old('tgl_jatuh_tempo', $invoice->tgl_jatuh_tempo ? \Carbon\Carbon::parse($invoice->tgl_jatuh_tempo)->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <hr>

                {{-- PRODUK --}}
                <h6 class="mb-3">Produk</h6>
                <div id="items">
                    @foreach ($invoice->produk as $i => $item)
                        <div class="row align-items-end mb-2 item-row" data-tipe-harga="{{ $item->tipe_harga }}">
                            <div class="col-md-3">
                                <label class="form-label">Produk</label>
                                <input type="hidden" name="items[{{ $i }}][perizinan_id]"
                                    value="{{ $item->perizinan_id }}">
                                <input type="text" class="form-control" value="{{ $item->perizinan->jenis }}"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" class="form-control"
                                    name="items[{{ $i }}][description]"
                                    value="{{ old("items.$i.description", $item->description) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Qty</label>
                                <input type="number" class="form-control qty" name="items[{{ $i }}][qty]"
                                    value="{{ old("items.$i.qty", $item->qty) }}"
                                    {{ $item->tipe_harga === 'gabungan' ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Harga</label>
                                <input type="number" class="form-control price"
                                    name="items[{{ $i }}][harga_satuan]"
                                    value="{{ old("items.$i.harga_satuan", $item->harga_satuan) }}"
                                    {{ $item->tipe_harga === 'gabungan' ? 'disabled readonly' : '' }}>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jumlah</label>
                                <input type="text" class="form-control jumlah"
                                    value="{{ $item->qty * $item->harga_satuan }}" disabled>
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr>

                {{-- TOTAL --}}
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Subtotal</span>
                            <strong id="subtotal"> Rp {{ number_format($subtotal, 0, ',', '.') }} </strong>
                            <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $subtotal }}">
                        </div>

                        <input type="hidden" id="hargaGabunganInput"
                            value="{{ old('harga_gabungan', $invoice->harga_gabungan ?? 0) }}">

                        <div class="mb-2 d-flex justify-content-between">
                            <span>Nominal Invoice ({{ old('persentase_termin', $invoice->persentase_termin) }}%)</span>
                            <strong id="nominalInvoice"> Rp
                                {{ number_format(($subtotal * $persentase_termin) / 100, 0, ',', '.') }} </strong>
                            <input type="hidden" name="nominal_invoice" id="nominalInvoiceInput"
                                value="{{ ($subtotal * $persentase_termin) / 100 }}">
                        </div>

                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-label mb-1">Diskon</label>
                                <div class="input-group">
                                    <select class="form-select" id="tipe_diskon" style="max-width:70px">
                                        <option value="persen"
                                            {{ old('tipe_diskon', $invoice->tipe_diskon) === 'persen' ? 'selected' : '' }}>
                                            %</option>
                                        <option value="nominal"
                                            {{ old('tipe_diskon', $invoice->tipe_diskon) === 'nominal' ? 'selected' : '' }}>
                                            Rp</option>
                                    </select>
                                    <input type="number" class="form-control" id="nilai_diskon"
                                        placeholder="Nilai diskon"
                                        value="{{ old('nilai_diskon', $invoice->nilai_diskon) }}">
                                </div>
                            </div>
                            <strong>Rp <span
                                    id="jumlah_diskon">{{ number_format(old('nilai_diskon', $invoice->nilai_diskon), 0, ',', '.') }}</span></strong>
                        </div>

                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Total After Diskon</span>
                            <strong>Rp <span id="total_after_discount">0</span></strong>
                        </div>

                        <input type="hidden" name="tipe_diskon" id="discountTypeInput"
                            value="{{ old('tipe_diskon', $invoice->tipe_diskon) }}">
                        <input type="hidden" name="nilai_diskon" id="discountValueInput"
                            value="{{ old('nilai_diskon', $invoice->nilai_diskon) }}">
                        <input type="hidden" name="total_after_discount" id="totalAfterDiscountInput">

                        {{-- Pajak --}}
                        <div class="mb-2">
                            <label class="form-label">Pajak</label>

                            @foreach ($ppnList as $tax)
                                @php
                                    $isChecked = $invoice->pajak->contains('coa_id', $tax->id); // centang jika ada di invoice->pajak
                                @endphp
                                <div class="form-check">
                                    <input class="form-check-input tax-checkbox" type="checkbox" name="tax[]"
                                        id="tax-{{ $tax->id }}" data-name="{{ $tax->nama_akun }}"
                                        data-type="{{ str_contains(strtolower($tax->nama_akun), 'pph') ? 'pph' : 'ppn' }}"
                                        data-rate="{{ $tax->nilai_coa }}" value="{{ $tax->id }}"
                                        {{ $isChecked ? 'checked' : '' }}>

                                    <label class="form-check-label" for="tax-{{ $tax->id }}">
                                        {{ $tax->nama_akun }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div id="taxContainer" class="mb-3"></div>

                        <hr>

                        <h5>Total: Rp <span id="finalTotal">0</span></h5>
                        <input type="hidden" name="total" id="totalInput">

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Update Invoice</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('pages.finance.modal-akun')

    {{-- Script untuk perhitungan sama seperti form create --}}
    <script>
        function rupiah(n) {
            return Math.round(n).toLocaleString('id-ID');
        }

        // Bisa pakai script create, tapi jalankan updateNominalInvoice() saat load
        document.addEventListener('DOMContentLoaded', function() {
            // hitung ulang total, diskon, pajak, nominal
            updateNominalInvoice();
            hitungDiskon();
            hitungPajak();
            hitungTotalAkhir();

            // jika user ubah qty/harga
            document.querySelectorAll('.qty, .price').forEach(el => el.addEventListener('input',
                hitungSubtotalDanDiskon));
            document.getElementById('nilai_diskon').addEventListener('input', hitungDiskon);
            document.getElementById('tipe_diskon').addEventListener('change', hitungDiskon);
            document.querySelectorAll('.tax-checkbox').forEach(el => el.addEventListener('change', () => {
                hitungPajak();
                hitungTotalAkhir();
            }));
        });

        function getBaseSubtotal() {
            let subtotal = parseFloat(document.getElementById('subtotalInput').value) || 0;
            const hargaGabungan = parseFloat(document.getElementById('hargaGabunganInput')?.value || 0);

            if (subtotal === 0 && hargaGabungan > 0) {
                subtotal = hargaGabungan;
            }
            return subtotal;
        }

        function updateNominalInvoice() {
            const base = getBaseSubtotal();
            const persenTermin = parseFloat("{{ $persentase_termin }}") || 0;

            const nominal = base * persenTermin / 100;

            document.getElementById('nominalInvoice').innerText = rupiah(nominal);
            document.getElementById('nominalInvoiceInput').value = nominal;

            hitungDiskon();
            hitungPajak();
            hitungTotalAkhir();
        }

        // =====================
        // Diskon
        // =====================
        function hitungDiskon() {
            const nominalInvoice = parseFloat(document.getElementById('nominalInvoiceInput').value) || 0;
            const jenis = document.getElementById('tipe_diskon').value;
            const nilai = parseFloat(document.getElementById('nilai_diskon').value); // jangan pakai || 0

            // Hitung diskon seperti biasa
            let diskon = 0;
            if (!isNaN(nilai) && nilai > 0) {
                if (jenis === 'persen') diskon = nominalInvoice * nilai / 100;
                else diskon = nilai;
            }

            if (diskon > nominalInvoice) diskon = nominalInvoice;

            const totalAfterDiscount = nominalInvoice - diskon;

            // Tampilkan di UI, default 0 jika user belum input
            document.getElementById('jumlah_diskon').innerText = (!nilai || nilai <= 0) ? '0' : rupiah(diskon);
            document.getElementById('total_after_discount').innerText = (!nilai || nilai <= 0) ? '0' : rupiah(
                totalAfterDiscount);

            // hidden input tetap pakai angka sebenarnya supaya submit dan pajak tetap benar
            document.getElementById('discountTypeInput').value = jenis;
            document.getElementById('discountValueInput').value = nilai || 0;
            document.getElementById('totalAfterDiscountInput').value = totalAfterDiscount;

            hitungPajak();
            hitungTotalAkhir();
        }

        document.getElementById('tipe_diskon').addEventListener('change', hitungDiskon);
        document.getElementById('nilai_diskon').addEventListener('input', hitungDiskon);

        // =====================
        // Pajak
        // =====================
        function hitungPajak() {
            const base = parseFloat(document.getElementById('totalAfterDiscountInput').value) ||
                parseFloat(document.getElementById('nominalInvoiceInput').value) ||
                0;
            const taxes = document.querySelectorAll('.tax-checkbox:checked');
            const container = document.getElementById('taxContainer');

            container.innerHTML = '';

            taxes.forEach(el => {
                const rate = parseFloat(el.dataset.rate) || 0;
                const name = el.dataset.name;
                const amount = base * rate / 100;

                container.innerHTML += `
            <div class="d-flex justify-content-between mb-1">
                <span>${name}</span>
                <strong>Rp ${rupiah(amount)}</strong>
            </div>`;
            });
        }

        // =====================
        // Total Akhir
        // =====================
        function hitungTotalAkhir() {
            const base = parseFloat(document.getElementById('totalAfterDiscountInput').value) || parseFloat(document
                .getElementById('nominalInvoiceInput').value);
            const taxes = document.querySelectorAll('.tax-checkbox:checked');

            let totalPPN = 0,
                totalPPH = 0;

            taxes.forEach(tax => {
                const rate = parseFloat(tax.dataset.rate) || 0;
                const name = tax.dataset.name.toLowerCase();
                const amount = base * rate / 100;

                if (name.includes('pph')) totalPPH += amount;
                else totalPPN += amount;
            });

            const finalTotal = base + totalPPN - totalPPH;

            document.getElementById('finalTotal').innerText = rupiah(finalTotal);
            document.getElementById('totalInput').value = finalTotal;
        }

        document.querySelectorAll('.tax-checkbox').forEach(el => el.addEventListener('change', () => {
            hitungPajak();
            hitungTotalAkhir();
        }));

        // =====================
        // Subtotal dinamis jika user ubah qty/harga
        // =====================
        function hitungSubtotalDanDiskon() {
            let subtotal = 0;
            let hasNonGabungan = false;

            document.querySelectorAll('.item-row').forEach(row => {
                const tipeHarga = row.dataset.tipeHarga;
                const qty = parseFloat(row.querySelector('.qty')?.value || 0);
                const price = parseFloat(row.querySelector('.price')?.value || 0);

                if (tipeHarga !== 'gabungan' && qty > 0 && price > 0) {
                    subtotal += qty * price;
                    hasNonGabungan = true;
                }
            });

            // Jika semua gabungan → pakai harga gabungan
            if (!hasNonGabungan) {
                subtotal = parseFloat(document.getElementById('hargaGabunganInput')?.value || 0);
            }

            // Update subtotal di UI
            document.getElementById('subtotal').innerText = rupiah(subtotal);
            document.getElementById('subtotalInput').value = subtotal;

            //  Setelah subtotal di-update, baru hitung nominal invoice
            updateNominalInvoice();
        }

        // jalankan saat load
        document.addEventListener('DOMContentLoaded', function() {
            // 1️⃣ Hitung subtotal dulu (pakai harga gabungan jika semua item gabungan)
            hitungSubtotalDanDiskon();

            // 2️⃣ Tambahkan listener untuk input qty/harga
            document.querySelectorAll('.qty, .price').forEach(el => {
                el.addEventListener('input', hitungSubtotalDanDiskon);
            });
        });
    </script>

@endsection
