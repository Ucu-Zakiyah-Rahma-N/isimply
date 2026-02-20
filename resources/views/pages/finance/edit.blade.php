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
                    <input class="form-check-input" type="checkbox" id="sameWithPo" checked>
                    <label class="form-check-label fw-semibold" for="sameWithPo">
                        Sama dengan PO
                    </label>
                </div>


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

                        {{-- Subtotal --}}
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Subtotal</span>
                            <strong id="subtotal"> Rp
                                {{ number_format(old('subtotal', $invoice->subtotal), 0, ',', '.') }} </strong>
                            <input type="hidden" name="subtotal" id="subtotalInput"
                                value="{{ old('subtotal', $invoice->subtotal) }}">
                        </div>

                        {{-- Harga Gabungan --}}
                        <input type="hidden" id="hargaGabunganInput"
                            value="{{ old('harga_gabungan', $invoice->harga_gabungan ?? 0) }}">

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

                        <hr>

                        {{-- Nominal Invoice (Persentase Termin) --}}
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Nominal Invoice ({{ old('persentase_termin', $invoice->persentase_termin) }}%)</span>
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
                                    <select class="form-select" id="tipe_diskon" style="max-width:70px">
                                        <option value="persen"
                                            {{ old('tipe_diskon', $invoice->tipe_diskon) === 'persen' ? 'selected' : '' }}>
                                            %
                                        </option>
                                        <option value="nominal"
                                            {{ old('tipe_diskon', $invoice->tipe_diskon) === 'nominal' ? 'selected' : '' }}>
                                            Rp
                                        </option>
                                    </select>
                                    <input type="number" class="form-control" id="nilai_diskon"
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
                            <strong>Rp <span
                                    id="total_after_discount">{{ number_format(old('total_after_discount', $invoice->total_after_diskon_inv ?? 0), 0, ',', '.') }}</span></strong>
                        </div>

                        <input type="hidden" name="tipe_diskon" id="discountTypeInput"
                            value="{{ old('tipe_diskon', $invoice->tipe_diskon) }}">
                        <input type="hidden" name="nilai_diskon" id="discountValueInput"
                            value="{{ old('nilai_diskon', $invoice->nilai_diskon) }}">
                        <input type="hidden" name="total_after_discount" id="totalAfterDiscountInput"
                            value="{{ old('total_after_discount', $invoice->total_after_diskon_inv ?? 0) }}">

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

                        <hr>

                        <h5>Total: Rp <span
                                id="finalTotal">{{ number_format(old('total', $invoice->grand_total ?? 0), 0, ',', '.') }}</span>
                        </h5>
                        <input type="hidden" name="total" id="totalInput"
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
        const poItems = @json($perizinans);
        const oldItems = @json($invoice->produk);

        document.addEventListener('DOMContentLoaded', function() {

            const checkbox = document.getElementById('sameWithPo');
            const itemsContainer = document.getElementById('items');
            const addButton = document.querySelector('.add-item');

            function rupiah(num) {
                return Math.round(num).toLocaleString('id-ID');
            }

            // =====================
            // Load PO Items
            // =====================
            function loadPoItems() {
                itemsContainer.innerHTML = '';
                addButton.style.display = 'none';

                poItems.forEach((item, i) => {
                    const qty = item.pivot?.qty ?? 1;
                    const harga = item.pivot?.harga_satuan ?? 0;
                    const jumlah = qty * harga;

                    itemsContainer.insertAdjacentHTML('beforeend', `
                <div class="row align-items-end mb-2 item-row">
                    <div class="col-md-3">
                        <input type="hidden" name="items[${i}][perizinan_id]" value="${item.id}">
                        <input type="text" class="form-control" value="${item.jenis}" readonly>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="items[${i}][description]" value="">
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control qty" name="items[${i}][qty]" value="${qty}" readonly>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control price" name="items[${i}][harga_satuan]" value="${harga}" readonly>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control jumlah" value="${jumlah}" readonly>
                    </div>
                </div>
            `);
                });

                updateSubtotal();
            }

            // =====================
            // Load Manual Items (untuk edit)
            // =====================
            function loadManualForm() {
                itemsContainer.innerHTML = '';
                addButton.style.display = 'inline-block';

                oldItems.forEach((item, i) => {
                    const jumlah = item.qty * item.harga_satuan;
                    itemsContainer.insertAdjacentHTML('beforeend', `
                <div class="row align-items-end mb-2 item-row">
                    <div class="col-md-3">
                        <input type="hidden" name="items[${i}][perizinan_id]" value="${item.perizinan_id}">
                        <input type="text" class="form-control" value="${item.perizinan.jenis}" readonly>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="items[${i}][description]" value="${item.description}">
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control qty" name="items[${i}][qty]" value="${item.qty}">
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control price" name="items[${i}][harga_satuan]" value="${item.harga_satuan}">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control jumlah" value="${jumlah}" readonly>
                    </div>
                </div>
            `);
                });

                updateSubtotal();
            }

            // =====================
            // Tambah manual row kosong
            // =====================
            function addManualRow() {
                const index = itemsContainer.querySelectorAll('.item-row').length;
                itemsContainer.insertAdjacentHTML('beforeend', `
            <div class="row align-items-end mb-2 item-row">
                <div class="col-md-3"><input type="text" class="form-control" name="items[${index}][produk]" placeholder="Produk"></div>
                <div class="col-md-3"><input type="text" class="form-control" name="items[${index}][description]" placeholder="Deskripsi"></div>
                <div class="col-md-2"><input type="number" class="form-control qty" name="items[${index}][qty]" value="1"></div>
                <div class="col-md-2"><input type="number" class="form-control price" name="items[${index}][harga_satuan]" value="0"></div>
                <div class="col-md-2"><input type="text" class="form-control jumlah" value="0" readonly></div>
            </div>
        `);
            }

            // =====================
            // Update subtotal, nominal invoice, dll
            // =====================
            function updateSubtotal() {
                let subtotal = 0;
                document.querySelectorAll('#items .item-row').forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
                    const harga = parseFloat(row.querySelector('.price')?.value) || 0;
                    const jumlah = qty * harga;
                    row.querySelector('.jumlah').value = jumlah;
                    subtotal += jumlah;
                });

                document.getElementById('subtotal').innerText = 'Rp ' + rupiah(subtotal);
                document.getElementById('subtotalInput').value = subtotal;

                // Hitung nominal invoice
                const diskonPO = parseFloat(document.getElementById('diskonPoInput')?.value) || 0;
                const terminPersen = {{ $persentaseTermin ?? 0 }};
                let nominal = subtotal - diskonPO;
                if (nominal < 0) nominal = 0;
                nominal = nominal * terminPersen / 100;

                document.querySelector('input[name="nominal_invoice"]').value = nominal;
                document.querySelectorAll('[data-role="nominalInvoice"]').forEach(el => el.innerText = 'Rp ' +
                    rupiah(nominal));
            }

            // =====================
            // Event listeners
            // =====================
            checkbox.addEventListener('change', function() {
                if (this.checked) loadPoItems();
                else loadManualForm();
            });

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('qty') || e.target.classList.contains('price'))
                    updateSubtotal();
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('add-item')) addManualRow();
            });

            // Load default
            if (checkbox.checked) loadPoItems();
            else loadManualForm();
        });

        function rupiah(n) {
            return Math.round(n).toLocaleString('id-ID');
        }


        let firstLoadDpp = true;

        function hitungPajak() {
            const base = parseFloat(document.getElementById('totalAfterDiscountInput').value) ||
                parseFloat(document.getElementById('nominalInvoiceInput').value) || 0;

            const taxes = document.querySelectorAll('.tax-checkbox');
            const container = document.getElementById('taxContainer');
            const dppContainer = document.getElementById('dppContainer');

            container.innerHTML = '';

            let totalPPN = 0;
            let totalPPH = 0;

            taxes.forEach(el => {
                const rate = parseFloat(el.dataset.rate) || 0;
                const name = el.dataset.name;
                const checked = el.checked;
                const amount = checked ? base * rate / 100 : 0;

                if (checked) {
                    container.innerHTML += `
                <div class="d-flex justify-content-between mb-1">
                    <span>${name}</span>
                    <strong>Rp ${rupiah(amount)}</strong>
                </div>
            `;
                }

                if (checked) {
                    if (name.toLowerCase().includes('pph')) totalPPH += amount;
                    else totalPPN += amount;
                }
            });

            // --- tampilkan DPP ---
            if (firstLoadDpp && totalPPN > 0) {
                const oldDpp = parseFloat(document.getElementById('oldDpp').value) || 0;
                if (oldDpp > 0) {
                    dppContainer.innerHTML = `
                <div class="d-flex justify-content-between mb-1">
                    <span>DPP</span>
                    <strong>Rp ${rupiah(oldDpp)}</strong>
                </div>
            `;
                } else {
                    const dpp = Math.round((base * 11) / 12);
                    dppContainer.innerHTML = `
                <div class="d-flex justify-content-between mb-1">
                    <span>DPP</span>
                    <strong>Rp ${rupiah(dpp)}</strong>
                </div>
            `;
                }
                firstLoadDpp = false; // sudah dipakai
            } else if (!firstLoadDpp && totalPPN > 0) {
                const dpp = Math.round((base * 11) / 12);
                dppContainer.innerHTML = `
            <div class="d-flex justify-content-between mb-1">
                <span>DPP</span>
                <strong>Rp ${rupiah(dpp)}</strong>
            </div>
        `;
            }
        }

        // =====================
        // Total Akhir
        // =====================
        function hitungTotalAkhir() {
            const base = parseFloat(document.getElementById('totalAfterDiscountInput').value) || parseFloat(
                document
                .getElementById('nominalInvoiceInput').value);
            const taxes = document.querySelectorAll('.tax-checkbox:checked');

            let totalPPN = 0,

                taxes.forEach(tax => {
                    const rate = parseFloat(tax.dataset.rate) || 0;
                    const name = tax.dataset.name.toLowerCase();

                    if (type === 'ppn') {
                        totalPPN += Math.round((dpp * 12) / 100);
                    } else {
                        totalPPH += Math.round((base * rate) / 100);
                    }
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
