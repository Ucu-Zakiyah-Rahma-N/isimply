@extends('app.template')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Create Invoice</h5>
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

            <form id="invoiceForm" action="{{ route('finance.invoice.store') }}" method="POST">
                @csrf
                <input type="hidden" name="po_id" value="{{ $po_id }}">

                {{-- HEADER --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">No Invoice</label>
                        <input type="text" name="no_invoice" class="form-control" value="{{ $no_invoice }}" required
                            readonly>
                    </div>

                    {{-- <div class="col-md-6 text-end">
          <div class="border rounded p-3 bg-light">
            <strong>Total:</strong>
            <h4 class="mb-1">Rp. <span id="grandTotal">0</span></h4>
            <small>Invoice sudah dibuat: 2</small><br>
            <small>Total tagihan: Rp 40.000.000</small><br>
            <small>Sisa tagihan: Rp 40.000.000</small>
          </div>
        </div> --}}
                </div>

                {{-- INFORMASI PERUSAHAAN --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Nama Perusahaan</label>
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <input type="text" class="form-control" name="nama_perusahaan"
                            value="{{ $customer->nama_perusahaan }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Alamat Penagihan</label>
                        <textarea class="form-control" rows="3" readonly>{{ collect([
                            $quotation->detail_alamat,
                            $quotation->kawasan_industri->nama_kawasan ?? null,
                            isset($quotation->kabupaten->nama) ? \Illuminate\Support\Str::title(strtolower($quotation->kabupaten->nama)) : null,
                            isset($quotation->provinsi->nama) ? \Illuminate\Support\Str::title(strtolower($quotation->provinsi->nama)) : null,
                        ])->filter()->implode(', ') }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">NPWP</label>
                        <input type="text" class="form-control" name="npwp" value="{{ $customer->npwp ?? '-' }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">No PO</label>
                        <select class="form-control" name="no_po">
                            <option value="{{ $no_po }}"> {{ $no_po }}</option>
                        </select>
                    </div>
                </div>

                {{-- JENIS & TANGGAL --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Jenis Invoice</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_invoice" value="dp">
                            <label class="form-check-label">DP</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_invoice" value="pelunasan">
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
                            value="Termin {{ $termin_ke }} ({{ $persentaseTermin }}%)" readonly>
                        <input type="hidden" name="persentase_termin" value="{{ $persentaseTermin }}">
                    </div>


                    <div class="col-md-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" class="form-control" name="catatan">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tgl Invoice</label>
                        <input type="date" class="form-control" name="tgl_invoice">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tgl Jatuh Tempo</label>
                        <input type="date" class="form-control" name="tgl_jatuh_tempo">
                    </div>
                </div>

                <hr>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="sameWithPo" checked>
                    <label class="form-check-label fw-semibold" for="sameWithPo">
                        Sama dengan PO
                    </label>
                </div>


                {{-- PRODUK --}}
                <h6 class="mb-3">Produk</h6>

                <div class="row fw-bold border-bottom pb-2 mb-2">
                    <div class="col-md-3">Produk</div>
                    <div class="col-md-3">Deskripsi</div>
                    <div class="col-md-2">Qty</div>
                    <div class="col-md-2">Harga</div>
                    <div class="col-md-1">Jumlah</div>
                    <div class="col-md-1 text-center"></div>
                </div>

                <div id="items"></div>

                <div class="mt-3">
                    <button type="button" class="btn btn-primary btn-sm add-item">
                        + Tambah Pekerjaan
                    </button>
                </div>


                <hr>

                <div class="row justify-content-end">
                    <div class="col-md-6">

                        <div class="mb-2 d-flex justify-content-between">
                            <span>Subtotal</span>
                            <strong id="subtotal"> Rp {{ number_format($subtotal, 0, ',', '.') }} </strong>
                            <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $subtotal }}">
                        </div>
                        <input type="hidden" id="hargaGabunganInput" value="{{ $quotation->harga_gabungan }}">


                        {{-- Diskon dari Quotation --}}
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Diskon PO</span>
                            <strong>
                                {{ $diskonQuotation > 0 ? 'Rp ' . number_format($diskonQuotation, 0, ',', '.') : '-' }}
                            </strong>
                        </div>

                        {{-- Nominal PO --}}
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="fw-semibold">Nominal PO</span>
                            <strong>
                                Rp {{ number_format($nominalPO, 0, ',', '.') }}
                            </strong>
                        </div>
                        <hr>
                        {{-- Nominal Invoice Termin --}}
                        {{-- <div class="mb-2 d-flex justify-content-between">
                            <span>Nominal Invoice ({{ $persentaseTermin }}%)</span>
                            <strong>
                                Rp {{ number_format($nominalInvoice, 0, ',', '.') }}
                            </strong>
                        </div> --}}
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Nominal Invoice ({{ $persentaseTermin }}%)</span>
                            <strong data-role="nominalInvoice">
                                Rp {{ number_format($nominalInvoice, 0, ',', '.') }}
                            </strong>
                        </div>

                        <input type="hidden" name="nominal_invoice" value="{{ $nominalInvoice }}">

                        {{-- <div class="mb-2 d-flex justify-content-between">
                            <span>Nominal Invoice ({{ $persentaseTermin }}%)</span>
                            <strong id="nominalInvoice"> Rp
                                {{ number_format(($subtotal * $persentaseTermin) / 100, 0, ',', '.') }} </strong>
                            <input type="hidden" name="nominal_invoice" id="nominalInvoiceInput"
                                value="{{ ($subtotal * $persentaseTermin) / 100 }}">
                        </div> --}}

                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-label mb-1">Diskon</label>

                                <div class="input-group">
                                    <select class="form-select" id="tipe_diskon" style="max-width:70px">
                                        <option value="persen">%</option>
                                        <option value="nominal">Rp</option>
                                    </select>

                                    <input type="number" class="form-control" id="nilai_diskon"
                                        placeholder="Nilai diskon">
                                </div>
                            </div>

                            <strong>Rp <span id="jumlah_diskon">0</span></strong>

                        </div>

                        <div class="mb-2 d-flex justify-content-between align-items-center">

                            <span class="fw-semibold">Total After Diskon</span>
                            <strong>Rp <span id="total_after_discount">0</span></strong>
                        </div>

                        <input type="hidden" name="tipe_diskon" id="discountTypeInput">
                        <input type="hidden" name="nilai_diskon" id="discountValueInput">
                        <input type="hidden" name="total_after_discount" id="totalAfterDiscountInput">

                        <div id="dppContainer" class="mb-2"></div>

                        <div class="mb-2">
                            <label class="form-label">Pajak</label>

                            @foreach ($ppnList as $tax)
                                <div class="form-check">
                                    <input class="form-check-input tax-checkbox" type="checkbox" name="tax[]"
                                        value="{{ $tax->id }}" data-rate="{{ $tax->nilai_coa }}"
                                        data-name="{{ $tax->nama_akun }}"
                                        data-type="{{ str_contains(strtolower($tax->nama_akun), 'pph') ? 'pph' : 'ppn' }}"
                                        id="tax_{{ $tax->id }}">

                                    <label class="form-check-label" for="tax_{{ $tax->id }}">
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
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                Batal
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Simpan Invoice
                            </button>
                        </div>
            </form>
        </div>
    </div>

    @include('pages.finance.modal-akun')
    {{-- @vite(['resources/js/invoice/invoice_script.js']) --}}


    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const poItems = @json($perizinans);

        document.addEventListener('DOMContentLoaded', function() {

            const checkbox = document.getElementById('sameWithPo');
            const itemsContainer = document.getElementById('items');
            const addButton = document.querySelector('.add-item');
            const poItems = @json($perizinans);

            function loadPoItems() {

                itemsContainer.innerHTML = '';
                addButton.style.display = 'none';

                poItems.forEach((item, i) => {

                    const qty = item.pivot?.qty ?? 1;
                    const harga = item.pivot?.harga_satuan ?? 0;
                    const jumlah = qty * harga;

                    itemsContainer.insertAdjacentHTML('beforeend', `
                <div class="row align-items-center mb-2 item-row">

                    <input type="hidden" name="items[${i}][perizinan_id]" value="${item.id}">

                    <div class="col-md-3">
                        <input type="text" class="form-control"
                            value="${item.jenis}" readonly>
                    </div>

                    <div class="col-md-3">
                        <input type="text" class="form-control"
                            name="items[${i}][deskripsi]">
                    </div>

                    <div class="col-md-2">
                        <input type="number" class="form-control"
                            name="items[${i}][qty]" value="${qty}" readonly>
                    </div>

                    <div class="col-md-2">
                        <input type="number" class="form-control"
                            name="items[${i}][harga_satuan]" value="${harga}" readonly>
                    </div>

                    <div class="col-md-1">
                        <input type="text" class="form-control"
                            value="${jumlah}" readonly>
                    </div>

                    <div class="col-md-1"></div>

                </div>
            `);
                });
            }

            function loadManualForm() {
                itemsContainer.innerHTML = '';
                addButton.style.display = 'inline-block';
                addManualRow();
            }

            function addManualRow() {

                const index = itemsContainer.querySelectorAll('.item-row').length;

                itemsContainer.insertAdjacentHTML('beforeend', `
            <div class="row align-items-center mb-2 item-row">

                <div class="col-md-3">
                    <input type="text"
                        class="form-control"
                        name="items[${index}][produk]">
                </div>

                <div class="col-md-3">
                    <input type="text"
                        class="form-control"
                        name="items[${index}][deskripsi]">
                </div>

                <div class="col-md-2">
                    <input type="number"
                        class="form-control qty"
                        name="items[${index}][qty]"
                        value="1">
                </div>

                <div class="col-md-2">
                    <input type="number"
                        class="form-control price"
                        name="items[${index}][harga_satuan]">
                </div>

                <div class="col-md-1">
                    <input type="text"
                        class="form-control jumlah"
                        readonly>
                </div>

                <div class="col-md-1 text-center">
                    <button type="button"
                        class="btn btn-sm btn-outline-danger remove-item">
                        ✕
                    </button>
                </div>

            </div>
        `);
            }

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    loadPoItems();
                } else {
                    loadManualForm();
                }
            });

            document.addEventListener('click', function(e) {

                if (e.target.classList.contains('remove-item')) {
                    e.target.closest('.item-row').remove();
                }

                if (e.target.classList.contains('add-item')) {
                    addManualRow();
                }
            });

            // Default: Sama dengan PO aktif
            checkbox.checked = true;
            loadPoItems();

        });

        function rupiah(n) {
            return Math.round(n).toLocaleString('id-ID');
        }

        // =====================
        // Ambil Base Nominal Invoice
        // =====================
        function getNominalInvoice() {
            return parseFloat(document.querySelector('input[name="nominal_invoice"]').value) || 0;
        }

        // =====================
        // Diskon
        // =====================
        function hitungDiskon() {

            const nominalInvoice = getNominalInvoice();
            const jenis = document.getElementById('tipe_diskon').value;
            const nilai = parseFloat(document.getElementById('nilai_diskon').value) || 0;

            let diskon = 0;

            if (jenis === 'persen') {
                diskon = nominalInvoice * nilai / 100;
            } else {
                diskon = nilai;
            }

            if (diskon > nominalInvoice) diskon = nominalInvoice;

            const totalAfterDiscount = nominalInvoice - diskon;

            document.getElementById('jumlah_diskon').innerText = rupiah(diskon);
            document.getElementById('total_after_discount').innerText = rupiah(totalAfterDiscount);

            document.getElementById('discountTypeInput').value = jenis;
            document.getElementById('discountValueInput').value = nilai;
            document.getElementById('totalAfterDiscountInput').value = totalAfterDiscount;

            hitungDPP();
            hitungPajak();
            hitungTotalAkhir();
        }

        document.getElementById('tipe_diskon').addEventListener('change', hitungDiskon);
        document.getElementById('nilai_diskon').addEventListener('input', hitungDiskon);

        function hitungDPP() {

            const nominalInvoice = getNominalInvoice();
            const afterDiscount = parseFloat(document.getElementById('totalAfterDiscountInput').value) || 0;

            // Tentukan base
            const base = afterDiscount > 0 && afterDiscount !== nominalInvoice ?
                afterDiscount :
                nominalInvoice;

            // const dpp = base * 11 / 12; ini bilangan bulat
            const dpp = Math.floor((base * 11) / 12); // ini nominal asli

            const container = document.getElementById('dppContainer');

            container.innerHTML = `
        <div class="d-flex justify-content-between mb-1">
            <span>DPP</span>
            <strong>Rp ${rupiah(dpp)}</strong>
        </div>
    `;

            return dpp;
        }

        // =====================
        // Pajak
        // =====================
        function hitungPajak() {

            const nominalInvoice = getNominalInvoice();
            const afterDiscount = parseFloat(document.getElementById('totalAfterDiscountInput').value) || 0;

            const base = (afterDiscount > 0 && afterDiscount !== nominalInvoice) ?
                afterDiscount :
                nominalInvoice;

            // DPP = base × 11/12 (truncate)
            const dpp = Math.floor((base * 11) / 12);

            const taxes = document.querySelectorAll('.tax-checkbox:checked');
            const container = document.getElementById('taxContainer');

            container.innerHTML = '';

            taxes.forEach(el => {

                const rate = parseFloat(el.dataset.rate) || 0;
                const name = el.dataset.name;
                const type = el.dataset.type;

                let amount = 0;

                if (type === 'ppn') {
                    // PPN = DPP × 12%
                    amount = Math.floor((dpp * 12) / 100);
                } else {
                    // PPh = base × rate%
                    amount = Math.floor((base * rate) / 100);
                }

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

            const nominalInvoice = getNominalInvoice();
            const afterDiscount = parseFloat(document.getElementById('totalAfterDiscountInput').value) || 0;

            const base = (afterDiscount > 0 && afterDiscount !== nominalInvoice) ?
                afterDiscount :
                nominalInvoice;

            // DPP truncate
            const dpp = Math.floor((base * 11) / 12);

            const taxes = document.querySelectorAll('.tax-checkbox:checked');

            let totalPPN = 0;
            let totalPPH = 0;

            taxes.forEach(tax => {

                const rate = parseFloat(tax.dataset.rate) || 0;
                const type = tax.dataset.type;

                if (type === 'ppn') {
                    totalPPN += Math.floor((dpp * 12) / 100);
                } else {
                    totalPPH += Math.floor((base * rate) / 100);
                }
            });

            // Total akhir
            const finalTotal = base + totalPPN - totalPPH;

            document.getElementById('finalTotal').innerText = rupiah(finalTotal);
            document.getElementById('totalInput').value = finalTotal;
        }

        document.querySelectorAll('.tax-checkbox').forEach(el =>
            el.addEventListener('change', () => {
                hitungDPP();
                hitungPajak();
                hitungTotalAkhir();
            })
        );

        // =====================
        // INIT
        // =====================
        document.addEventListener('DOMContentLoaded', function() {

            // set default after discount = nominal invoice
            document.getElementById('totalAfterDiscountInput').value = getNominalInvoice();
            document.getElementById('total_after_discount').innerText = rupiah(getNominalInvoice());
            hitungDPP();
            hitungPajak();
            hitungTotalAkhir();
        });

        //resetzz ke manual
        function resetNominal() {

            // Reset angka
            document.getElementById('subtotal').innerText = 'Rp 0';
            document.getElementById('subtotalInput').value = 0;

            document.querySelector('[name="nominal_invoice"]').value = 0;

            document.querySelectorAll('[data-role="nominalInvoice"]').forEach(el => {
                el.innerText = 'Rp 0';
            });

            // Reset diskon invoice
            const diskonInput = document.querySelector('[name="nilai_diskon"]');
            if (diskonInput) diskonInput.value = 0;

            // Reset tampilan total
            document.querySelectorAll('[data-role="totalAfterDiskon"]').forEach(el => {
                el.innerText = 'Rp 0';
            });

            document.querySelectorAll('[data-role="dpp"]').forEach(el => {
                el.innerText = 'Rp 0';
            });

            document.querySelectorAll('[data-role="grandTotal"]').forEach(el => {
                el.innerText = 'Rp 0';
            });
        }

        //kalo manual / ga sama dengan PO
        function hitungSubtotalManual() {

            let subtotal = 0;

            document.querySelectorAll('#items .item-row').forEach(row => {

                const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
                const harga = parseFloat(row.querySelector('.price')?.value) || 0;

                const jumlah = qty * harga;

                const jumlahField = row.querySelector('.jumlah');
                if (jumlahField) jumlahField.value = jumlah;

                subtotal += jumlah;
            });

            document.getElementById('subtotal').innerText = 'Rp ' + rupiah(subtotal);
            document.getElementById('subtotalInput').value = subtotal;

            return subtotal;
        }


        function recalcNominal() {

            const subtotal = parseFloat(document.getElementById('subtotalInput').value) || 0;

            const diskonPO = {{ $diskonQuotation ?? 0 }};
            const persenTermin = {{ $persentaseTermin ?? 0 }};

            // Nominal PO
            const nominalPO = subtotal - diskonPO;

            // Nominal Invoice
            const nominalInvoice = nominalPO * persenTermin / 100;

            // Update tampilan
            document.querySelector('[name="nominal_invoice"]').value = nominalInvoice;

            document.querySelectorAll('[data-role="nominalInvoice"]').forEach(el => {
                el.innerText = 'Rp ' + rupiah(nominalInvoice);
            });

            return nominalInvoice;
        }
        document.addEventListener('input', function(e) {

            if (e.target.classList.contains('qty') ||
                e.target.classList.contains('price')) {

                hitungSubtotalManual();
                recalcNominal();
                hitungDiskon();
            }

        });
    </script>

@endsection
