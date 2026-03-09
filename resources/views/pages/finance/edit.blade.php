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

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="sameWithPo" {{ $isSameWithPo ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="sameWithPo">
                    Sama dengan PO
                </label>
            </div>

            {{-- PRODUK --}}
            <h6 class="mb-3">Produk</h6>

            <div class="table-responsive" style="overflow-x:auto;">
                <table class="table table-bordered mb-0">
                    <thead class="fw-bold">
                        <tr>
                            <th style="min-width: 150px;">Produk</th>
                            <th style="min-width: 150px;">Deskripsi</th>
                            <th style="min-width: 100px;">Qty</th>
                            <th style="min-width: 120px;">Harga</th>
                            <th style="min-width: 120px;">Jumlah</th>
                            <th style="width: 50px;" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="items"></tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="button" class="btn btn-primary btn-sm add-item">
                    + Tambah Pekerjaan
                </button>
            </div>

            {{-- <h6 class="mb-3">Produk</h6>
                
                <div id="items">
                    {{-- {{ dd($invoice->produk) }}
            @foreach ($invoice->produk as $i => $item)
            <div class="row align-items-end mb-2 item-row" data-tipe-harga="{{ $item->tipe_harga }}">
                <div class="col-md-3">
                    <label class="form-label">Produk</label>
                    <input type="hidden" name="items[{{ $i }}][perizinan_id]"
                        value="{{ $item->perizinan_id }}">
                    <input type="text" class="form-control" value="{{ $item->perizinan->jenis }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" class="form-control"
                        name="items[{{ $i }}][deskripsi]"
                        value="{{ old("items.$i.deskripsi", $item->deskripsi) }}">
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
    </div> --}}

    <hr>

    {{-- TOTAL --}}
    <div class="row justify-content-end">
        <div class="col-md-6">

            {{-- Subtotal --}}
            <div class="mb-2 d-flex justify-content-between">
                <span>Subtotal</span>
                <strong id="subtotal"> Rp
                    {{ number_format(old('subtotal', $invoice->subtotal), 0, ',', '.') }} </strong>
                <input type="hidden" name="subtotal" id="subtotalInput"
                    value="{{ old('subtotal', $invoice->subtotal) }}">
            </div>

            {{-- Harga Gabungan --}}
            <!-- <input type="hidden" id="hargaGabunganInput" value="{{ old('harga_gabungan', $invoice->harga_gabungan ?? 0) }}"> -->
            <!-- <input type="hidden" name="is_gabungan" value="{{ $isGabungan ? 1 : 0 }}">
                        <input type="hidden" name="harga_gabungan" value="{{ $quotation->harga_gabungan ?? 0 }}"> -->
            <input type="hidden" id="isGabunganInput" value="{{ $isGabungan ? 1 : 0 }}">
            <input type="hidden" id="hargaGabunganInput" value="{{ $invoice->harga_gabungan ?? 0 }}">

            <div class="mb-2 d-flex justify-content-between">
                <span>Diskon PO</span>
                <strong>
                    {{ old('diskon_po', $diskonQuotation) > 0 ? 'Rp ' . number_format(old('diskon_po', $diskonQuotation), 0, ',', '.') : '-' }}
                </strong>
            </div>
            <input type="hidden" id="diskonPoInput" name="diskon_po"
                value="{{ old('diskon_po', $diskonQuotation) }}">

            <div class="mb-2 d-flex justify-content-between">
                <span class="fw-semibold">Nominal PO</span>
                <strong id="nominalPoDisplay">
                    Rp {{ number_format(old('nominal_po', $nominalPO), 0, ',', '.') }}
                </strong>
            </div>
            <input type="hidden" name="nominal_po" id="nominalPoInput"
                value="{{ old('nominal_po', $nominalPO) }}">

            <div id="warningSubtotal" class="alert alert-warning mt-2" style="display:none;"></div>
            <hr>

            {{-- Nominal Invoice (Persentase Termin) --}}
            <div class="mb-2 d-flex justify-content-between">
                <span>Termin ({{ old('persentase_termin', $invoice->persentase_termin) }}%)</span>
                <strong id="nominalInvoice"> Rp
                    {{ number_format(old('nominal_invoice', $invoice->nominal_invoice), 0, ',', '.') }}
                </strong>
                <input type="hidden" name="nominal_invoice" id="nominalInvoiceInput"
                    value="{{ old('nominal_invoice', $invoice->nominal_invoice) }}">
            </div>

            {{-- Diskon Invoice --}}
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <div>
                    <label class="form-label mb-1">Diskon</label>
                    <div class="input-group">
                        <select class="form-select" id="tipe_diskon" name="tipe_diskon" style="max-width:70px">
                            <option value="persen"
                                {{ old('tipe_diskon', $invoice->tipe_diskon) === 'persen' ? 'selected' : '' }}>
                                %
                            </option>
                            <option value="nominal"
                                {{ old('tipe_diskon', $invoice->tipe_diskon) === 'nominal' ? 'selected' : '' }}>
                                Rp
                            </option>
                        </select>
                        <input type="number" class="form-control" id="nilai_diskon" name="nilai_diskon"
                            placeholder="Nilai diskon"
                            value="{{ old('nilai_diskon', $invoice->nilai_diskon) }}">
                    </div>
                </div>
                <strong>Rp <span
                        id="jumlah_diskon">{{ number_format(old('nilai_diskon', $invoice->nilai_diskon), 0, ',', '.') }}</span></strong>
            </div>

            {{-- Total After Diskon --}}
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Total After Diskon</span>
                <strong>
                    Rp <span id="total_after_diskon_inv">
                        {{ number_format(old('total_after_diskon_inv', $invoice->total_after_diskon_inv ?? 0), 0, ',', '.') }}
                    </span>
                </strong>
            </div>

            <input type="hidden" id="discountTypeInput" value="{{ old('tipe_diskon', $invoice->tipe_diskon) }}">
            <input type="hidden" id="discountValueInput" value="{{ old('nilai_diskon', $invoice->nilai_diskon) }}">
            <input type="hidden" name="total_after_diskon_inv" id="totalAfterDiscountInput" value="{{ old('total_after_diskon_inv', $invoice->total_after_diskon_inv ?? 0) }}">

            <div id="dppContainer" class="mb-2">
                @if ($dppOld > 0)
                <div class="d-flex justify-content-between mb-1">
                    <span>DPP</span>
                    <strong>Rp {{ number_format($dppOld, 0, ',', '.') }}</strong>
                </div>
                @endif
            </div>

            <input type="hidden" id="oldDpp" value="{{ $dppOld }}">

            {{-- Pajak --}}
            <div class="mb-2">
                <label class="form-label">Pajak</label>

                @foreach ($ppnList as $tax)
                @php
                $isChecked = $invoice->pajak->contains('coa_id', $tax->id);
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

            <input type="hidden" name="dpp" id="dppInput">
            <input type="hidden" name="ppn" id="ppnInput">
            <hr>

            <h5>Total: Rp <span
                    id="finalTotal">{{ number_format(old('total', $invoice->grand_total ?? 0), 0, ',', '.') }}</span>
            </h5>
            <input type="hidden" name="grand_total" id="totalInput"
                value="{{ old('total', $invoice->grand_total ?? 0) }}">

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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const checkbox = document.getElementById('sameWithPo');
        const itemsContainer = document.getElementById('items');
        const addButton = document.querySelector('.add-item');

        const poItems = @json($perizinans ?? []);
        const invoiceItems = @json($invoice -> produk ?? []);
        const isSameWithPo = @json($isSameWithPo ?? false);
        const perizinanList = @json($perizinan);
        const isGabunganEl = document.getElementById('isGabunganInput');
        let isGabungan = isGabunganEl ? parseInt(isGabunganEl.value) === 1 : false;

        function rupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        /* ===============================
           LOAD PO ITEMS
        =============================== */

        function loadPoItems() {

            itemsContainer.innerHTML = '';
            if (addButton) addButton.style.display = 'none';

            const hargaGabungan = parseFloat(document.getElementById('hargaGabunganInput')?.value) || 0;
            const isGabungan = parseInt(document.getElementById('isGabunganInput')?.value) === 1;

            poItems.forEach((item, i) => {

                const qty = item.pivot?.qty ?? 1;
                const harga = item.pivot?.harga_satuan ?? 0;
                const jumlah = qty * harga;

                let hargaCell = '';
                let jumlahCell = '';

                if (isGabungan) {

                    if (i === 0) {

                        hargaCell = `
                <td rowspan="${poItems.length}" class="align-middle text-center">
                    Rp ${rupiah(hargaGabungan)}
                </td>`;

                        jumlahCell = `
                <td rowspan="${poItems.length}" class="align-middle text-center">
                    Rp ${rupiah(hargaGabungan)}
                </td>`;
                    }

                } else {

                    hargaCell = `
            <td>
                <input type="number"
                       class="form-control price"
                       name="items[${i}][harga_satuan]"
                       value="${harga}"
                       readonly>
            </td>`;

                    jumlahCell = `
            <td>
                <input type="text"
                       class="form-control jumlah"
                       value="${jumlah}"
                       readonly>
            </td>`;
                }

                itemsContainer.insertAdjacentHTML('beforeend', `
        <tr class="item-row">

            <td>
                <input type="hidden" name="items[${i}][perizinan_id]" value="${item.id}">
                <input type="text" class="form-control" value="${item.jenis}" readonly>
            </td>

            <td>
                <input type="text" class="form-control"
                       name="items[${i}][deskripsi]">
            </td>

            <td>
                <input type="number"
                       class="form-control qty"
                       name="items[${i}][qty]"
                       value="${qty}"
                       readonly>
            </td>

            ${hargaCell}
            ${jumlahCell}

            <td></td>

        </tr>
        `);
            });

            recalculateAll();
        }

        // function loadPoItems() {

        //     itemsContainer.innerHTML = '';
        //     if (addButton) addButton.style.display = 'none';

        //     poItems.forEach((item, i) => {

        //         const qty = item.pivot?.qty ?? 1;
        //         const harga = item.pivot?.harga_satuan ?? 0;

        //         itemsContainer.insertAdjacentHTML('beforeend', `
        //     <tr class="item-row">
        //         <td>
        //             <input type="hidden" name="items[${i}][perizinan_id]" value="${item.id}">
        //             <input type="text" class="form-control" value="${item.jenis}" readonly>
        //         </td>
        //         <td>    
        //             <input type="text" class="form-control"
        //                 name="items[${i}][deskripsi]">
        //         </td>
        //         <td>
        //             <input type="number"
        //                 class="form-control qty"
        //                 name="items[${i}][qty]"
        //                 value="${qty}"
        //                 readonly>
        //         </td>
        //         <td>
        //             <input type="number"
        //                 class="form-control price"
        //                 name="items[${i}][harga_satuan]"
        //                 value="${harga}"
        //                 readonly>
        //         </td>
        //         <td>
        //             <input type="text" class="form-control jumlah" readonly>
        //         </td>
        //         <td></td>
        //     </tr>
        //     `);
        //     });

        //     recalculateAll();
        // }

        /* ===============================
           LOAD INVOICE ITEMS
        =============================== */
        // function loadInvoiceItems() {

        //     itemsContainer.innerHTML = '';
        //     if (addButton) addButton.style.display = 'inline-block';

        //     invoiceItems.forEach((item, i) => {

        //         itemsContainer.insertAdjacentHTML('beforeend', `
        //         <tr class="item-row">
        //             <td>
        //                 <input type="hidden" name="items[${i}][perizinan_id]" value="${item.perizinan_id ?? ''}">
        //                 <input type="text" class="form-control"
        //                     name="items[${i}][perizinan_lainnya]"
        //                     value="${item.perizinan ? '' : item.perizinan_lainnya ?? ''}"
        //                     placeholder="Produk (manual)">
        //             </td>
        //             <td>
        //                 <input type="text" class="form-control"
        //                        name="items[${i}][deskripsi]"
        //                        value="${item.deskripsi ?? ''}">
        //             </td>
        //             <td>
        //                 <input type="number" class="form-control qty"
        //                        name="items[${i}][qty]"
        //                        value="${item.qty}">
        //             </td>
        //             <td>
        //                 <input type="number" class="form-control price"
        //                        name="items[${i}][harga_satuan]"
        //                        value="${item.harga_satuan}">
        //             </td>
        //             <td>
        //                 <input type="text" class="form-control jumlah" readonly>
        //             </td>
        //             <td class="text-center">
        //                 <button type="button" class="btn btn-sm btn-outline-danger remove-item">✕</button>
        //             </td>
        //         </tr>
        //     `);
        //     });

        //     recalculateAll();
        // }
        function loadInvoiceItems() {

            itemsContainer.innerHTML = '';
            if (addButton) addButton.style.display = 'inline-block';

            let index = 0;

            // jika sebelumnya sama dengan PO lalu user uncheck
            if (isSameWithPo && checkbox && !checkbox.checked) {
                addManualRow();
                return;
            }

            invoiceItems.forEach((item) => {

                if (!item.perizinan_id && !item.perizinan_lainnya) {
                    return;
                }

                itemsContainer.insertAdjacentHTML('beforeend', `
        <tr class="item-row">
            <td>
                <input type="hidden" name="items[${index}][perizinan_id]" value="${item.perizinan_id ?? ''}">
                <input type="text" class="form-control"
                    name="items[${index}][perizinan_lainnya]"
                    value="${item.perizinan ? '' : item.perizinan_lainnya ?? ''}"
                    placeholder="Produk (manual)">
            </td>

            <td>
                <input type="text" class="form-control"
                       name="items[${index}][deskripsi]"
                       value="${item.deskripsi ?? ''}">
            </td>

            <td>
                <input type="number"
                       class="form-control qty"
                       name="items[${index}][qty]"
                       value="${item.qty}">
            </td>

            <td>
                <input type="number"
                       class="form-control price"
                       name="items[${index}][harga_satuan]"
                       value="${item.harga_satuan}">
            </td>

            <td>
                <input type="text" class="form-control jumlah" readonly>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">✕</button>
            </td>
        </tr>
        `);

                index++;
            });

            if (index === 0) {
                addManualRow();
            }

            recalculateAll();
        }

        /* ===============================
           ADD MANUAL ROW
        =============================== */
        function addManualRow() {

            const index = itemsContainer.querySelectorAll('.item-row').length;

            itemsContainer.insertAdjacentHTML('beforeend', `
        <tr class="item-row">

            <td>
                <select name="items[${index}][perizinan_input]" 
                        class="form-control perizinan-select">
                    <option value="">-- pilih / ketik perizinan --</option>
                    @foreach($perizinan as $p)
                        <option value="id:{{ $p->id }}">{{ $p->jenis }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="text" 
                       class="form-control" 
                       name="items[${index}][deskripsi]" 
                       placeholder="Deskripsi">
            </td>

            <td>
                <input type="number" 
                       class="form-control qty" 
                       name="items[${index}][qty]" 
                       value="1">
            </td>

            <td>
                <input type="number" 
                       class="form-control price" 
                       name="items[${index}][harga_satuan]" 
                       placeholder="Harga Satuan">
            </td>

            <td>
                <input type="text" 
                       class="form-control jumlah" 
                       readonly 
                       placeholder="Jumlah">
            </td>

            <td class="text-center">
                <button type="button" 
                        class="btn btn-sm btn-outline-danger remove-item">✕</button>
            </td>

        </tr>
    `);

            recalculateAll();
        }

        $(document).on('focus', '.perizinan-select', function() {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    tags: true,
                    width: '100%',
                    placeholder: "Pilih atau ketik",
                });
            }
        });
        /* ===============================
           EVENT DELEGATION
        =============================== */
        document.addEventListener('input', function(e) {
            if (
                e.target.classList.contains('qty') ||
                e.target.classList.contains('price') ||
                e.target.id === 'nilai_diskon' ||
                e.target.id === 'diskonPoInput'
            ) {
                recalculateAll();
            }
        });

        document.addEventListener('change', function(e) {
            if (
                e.target.id === 'tipe_diskon' ||
                e.target.classList.contains('tax-checkbox')
            ) {
                recalculateAll();
            }
        });

        document.addEventListener('click', function(e) {

            if (e.target.classList.contains('remove-item')) {
                e.target.closest('.item-row').remove();
                recalculateAll();
            }

            if (e.target.classList.contains('add-item')) {
                addManualRow();
            }
        });

        // if (checkbox) {
        //     checkbox.addEventListener('change', function() {
        //         if (this.checked) {
        //             loadPoItems();
        //         } else {
        //             loadInvoiceItems();
        //         }
        //     });
        // }

        if (checkbox) {
            checkbox.addEventListener('change', function() {

                // bersihkan dulu semua baris
                itemsContainer.innerHTML = '';

                if (this.checked) {

                    loadPoItems();

                } else {

                    loadInvoiceItems();

                    // jika tidak ada data invoice → buat 1 baris manual
                    if (invoiceItems.length === 0) {
                        addManualRow();
                    }

                }
            });
        }
        /* ===============================
           INIT
        =============================== */
        itemsContainer.innerHTML = '';

        if (isSameWithPo) {

            if (checkbox) checkbox.checked = true;
            loadPoItems();

        } else {

            if (checkbox) checkbox.checked = false;
            loadInvoiceItems();

            if (invoiceItems.length === 0) {
                addManualRow();
            }

        }
        // if (isSameWithPo) {
        //     if (checkbox) checkbox.checked = true;
        //     loadPoItems();
        // } else {
        //     if (checkbox) checkbox.checked = false;
        //     loadInvoiceItems();
        // }

        /* ===============================
           HITUNG PAJAK
        =============================== */
        function hitungPajak() {

            const base = parseFloat(document.getElementById('totalAfterDiscountInput')?.value) || 0;
            const taxes = document.querySelectorAll('.tax-checkbox');
            const container = document.getElementById('taxContainer');
            const dppContainer = document.getElementById('dppContainer');

            if (container) container.innerHTML = '';

            let totalPPN = 0;
            let totalPPH = 0;

            taxes.forEach(el => {

                if (el.checked) {

                    const rate = parseFloat(el.dataset.rate) || 0;
                    const name = el.dataset.name;
                    const type = el.dataset.type;

                    const amount = Math.round(base * rate / 100);

                    if (container) {
                        container.innerHTML += `
                        <div class="d-flex justify-content-between mb-1">
                            <span>${name}</span>
                            <strong>Rp ${rupiah(amount)}</strong>
                        </div>
                    `;
                    }

                    if (type === 'pph') totalPPH += amount;
                    else totalPPN += amount;
                }
            });

            if (dppContainer) {
                if (totalPPN > 0) {
                    const dpp = Math.round((base * 11) / 12);
                    dppContainer.innerHTML = `
                    <div class="d-flex justify-content-between mb-1">
                        <span>DPP</span>
                        <strong>Rp ${rupiah(dpp)}</strong>
                    </div>
                `;
                } else {
                    dppContainer.innerHTML = '';
                }
            }

            const finalTotal = base + totalPPN - totalPPH;

            document.getElementById('finalTotal').innerText = rupiah(finalTotal);
            document.getElementById('totalInput').value = finalTotal;
        }

        /* ===============================
           RECALCULATE ALL
        =============================== */
        function recalculateAll() {
            let subtotal = 0;
            const itemRows = itemsContainer.querySelectorAll('.item-row');
            const hargaGabungan = parseFloat(document.getElementById('hargaGabunganInput')?.value) || 0;
            const isGabungan = parseInt(document.getElementById('isGabunganInput')?.value) === 1;

            itemRows.forEach(row => {
                const qtyInput = row.querySelector('.qty');
                const priceInput = row.querySelector('.price');

                const qty = parseFloat(qtyInput?.value) || 0;
                const price = parseFloat(priceInput?.value) || 0;

                // const jumlah = qty * price;
                const jumlah = (qty > 0 && price > 0) ? qty * price : 0;

                const jumlahInput = row.querySelector('.jumlah');
                if (jumlahInput) jumlahInput.value = jumlah;

                subtotal += jumlah;
            });

            if (isGabungan && subtotal === 0) {
                subtotal = hargaGabungan;
            }
            document.getElementById('subtotal').innerText = 'Rp ' + rupiah(subtotal);
            document.getElementById('subtotalInput').value = subtotal;

            const diskonPO = parseFloat(document.getElementById('diskonPoInput')?.value) || 0;
            let nominalPO = subtotal - diskonPO;
            if (nominalPO < 0) nominalPO = 0;

            document.getElementById('nominalPoDisplay').innerText = 'Rp ' + rupiah(nominalPO);
            document.getElementById('nominalPoInput').value = nominalPO;

            const termin = parseFloat(document.querySelector('input[name="persentase_termin"]')?.value) || 0;
            let nominalInvoice = Math.round(nominalPO * termin / 100);

            document.getElementById('nominalInvoice').innerText = 'Rp ' + rupiah(nominalInvoice);
            document.getElementById('nominalInvoiceInput').value = nominalInvoice;

            const tipeDiskon = document.getElementById('tipe_diskon')?.value;
            const nilaiDiskonInput = parseFloat(document.getElementById('nilai_diskon')?.value) || 0;

            let jumlahDiskon = 0;
            if (tipeDiskon === 'persen') {
                jumlahDiskon = nominalInvoice * nilaiDiskonInput / 100;
            } else {
                jumlahDiskon = nilaiDiskonInput;
            }

            document.getElementById('jumlah_diskon').innerText = rupiah(jumlahDiskon);

            let totalAfter = Math.round(nominalInvoice - jumlahDiskon);
            if (totalAfter < 0) totalAfter = 0;

            document.getElementById('total_after_diskon_inv').innerText = rupiah(totalAfter);
            document.getElementById('totalAfterDiscountInput').value = totalAfter;

            // ===============================
            // VALIDASI SUBTOTAL PO
            // ===============================
            const warning = document.getElementById('warningSubtotal');

            let subtotalPO = 0;

            poItems.forEach(item => {
                const qty = parseFloat(item.pivot?.qty) || 0;
                const harga = parseFloat(item.pivot?.harga_satuan) || 0;
                subtotalPO += qty * harga;
            });

            if (subtotalPO === 0) {
                subtotalPO = parseFloat(document.getElementById('hargaGabunganInput')?.value) || 0;
            }

            if (checkbox && !checkbox.checked && Math.round(subtotal) !== Math.round(subtotalPO)) {

                if (warning) {
                    warning.style.display = 'block';
                    warning.innerText =
                        '⚠ Nilai subtotal harus sama dengan subtotal PO (Rp ' + rupiah(subtotalPO) + ')';
                }

            } else {

                if (warning) warning.style.display = 'none';

            }
            // hitung pajak terakhir
            hitungPajak();
        }
    });
</script>

@endsection