<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border-radius: 14px !important;
        border: 1px solid #e5e7eb !important;
        background: #fff;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        padding: 0 14px;
    }

    /* Perfect vertical alignment */
    .select2-container--default .select2-selection__rendered {
        padding-left: 0 !important;
        line-height: 42px !important;
    }

    .select2-container--default .select2-selection__arrow {
        height: 42px !important;
        right: 12px;
    }

    /* Focus */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
    }

    /* Dropdown */
    .select2-dropdown {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    /* ===== GROUP MODE ===== */

    .select2-group {
        display: flex;
        align-items: stretch;
    }

    .select2-group .select2-selection--single {
        border-radius: 14px 0 0 14px !important;
        border-right: none !important;
    }

    .select2-group .btn-saas-add {
        height: 42px;
        width: 42px;
        border-radius: 0 14px 14px 0;
        border: 1px solid #e5e7eb;
        border-left: none;
        background: #f1f3f5;
    }

    /* ============================= */
    /* ====== FORM STYLE =========== */
    /* ============================= */

    .label-saas {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 6px;
    }

    .input-saas {
        height: 42px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        padding: 0 14px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .input-saas:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    /* ============================= */
    /* ====== CARD & MODAL ========= */
    /* ============================= */

    .card {
        background: #ffffff;
    }

    .total-card {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border: 1px solid #f1f3f5;
    }

    .total-amount {
        font-size: 1.8rem;
        letter-spacing: 0.5px;
        color: #111827;
    }

    .urgent-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #dc3545;
    }

    .modal-content {
        border-radius: 24px;
        border: none;
    }
</style>

<!-- ================= MODAL DETAIL PENGAJUAN ================= -->
<div class="modal fade" id="modalDetailPengajuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">

            <form id="formDetailPengajuan">

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Pengajuan Biaya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <input type="hidden" name="pengajuan_id" id="pengajuan_id">

                    <!-- HEADER FORM -->
                    <div class="row g-4 mb-4">

                        <div class="col-lg-9">
                            <div class="card border-0 shadow-sm rounded-4 p-4">

                                <div class="row g-4">

                                    <div class="col-md-4">
                                        <label class="form-label">Jenis Pengajuan</label>
                                        <select class="form-select" name="jenis_pengajuan">
                                            <option value="biaya">Biaya</option>
                                            <option value="pengeluaran">Pengeluaran</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Penerima</label>
                                        <div class="d-flex">
                                            <select id="kontakSelectEdit" name="kontak_id" class="form-select w-100">
                                                <option></option>
                                            </select>

                                            <button type="button"
                                                class="btn btn-light border ms-2 rounded-3"
                                                id="btnOpenKontak">
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Metode Pembayaran</label>
                                        <select class="form-select" name="metode_pembayaran">
                                            <option value="cash">Cash</option>
                                            <option value="transfer">Transfer</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Pengajuan</label>
                                        <input type="date" class="form-control" name="tanggal_pengajuan">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Project</label>
                                        <select id="projectSelectEdit" name="project_id" class="form-select">
                                            <option></option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- TOTAL CARD -->
                        <div class="col-lg-3">

                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between">

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-danger small">Urgent</span>

                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="is_urgent"
                                            value="1">
                                    </div>

                                </div>

                                <div class="mt-4">
                                    <small class="text-muted">Grand Total</small>
                                    <h2 class="fw-bold mb-0">
                                        Rp <span id="grandTotal">0</span>
                                    </h2>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- ================= ITEM SECTION ================= -->

                    <div class="border rounded-4 p-3 mb-4">

                        <div class="row fw-semibold text-muted mb-2">

                            <div class="col-md-3">Deskripsi</div>
                            <div class="col-md-1">Qty</div>
                            <div class="col-md-2">Harga</div>
                            <div class="col-md-2">Diskon (%)</div>
                            <div class="col-md-2">Pajak</div>
                            <div class="col-md-2 text-end">Jumlah</div>

                        </div>

                        <!-- ROWS HERE -->
                        <div id="itemContainer"></div>

                        <button type="button"
                            class="btn btn-sm btn-outline-primary mt-2"
                            id="btnTambahItem">

                            + Tambah Item

                        </button>

                    </div>

                    <!-- ================= FOOTER ================= -->

                    <div class="row">

                        <div class="col-md-6">

                            <label class="form-label">Lampiran</label>

                            <input type="file"
                                name="lampiran"
                                class="form-control">

                        </div>

                        <div class="col-md-6">

                            <div class="d-flex justify-content-between">
                                <span>Subtotal</span>
                                <span>Rp <span id="subtotalEdit">0</span></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span>Diskon</span>
                                <span class="text-danger">
                                    Rp <span id="totalDiskonEdit">0</span>
                                </span>
                            </div>

                            <div id="pajakSummaryEdit"></div>

                            <hr>

                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span>Rp <span id="summaryTotalEdit">0</span></span>
                            </div>

                            <div class="text-end mt-3">

                                <button type="submit"
                                    id="btnSubmitEdit"
                                    class="btn btn-warning px-4">

                                    Simpan Perubahan

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </form>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modalEl = document.getElementById('modalDetailPengajuan');
        if (!modalEl) return;

        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const form = document.getElementById('formDetailPengajuan');
        const itemContainer = document.getElementById('itemContainer');
        const btnTambah = document.getElementById('btnTambahItem');

        const kontakSelect = $('#kontakSelectEdit');
        const projectSelect = $('#projectSelectEdit');

        let pajakList = [];
        let masterLoaded = false;
        let debounceTimer = null;

        /* ================= SELECT2 ================= */

        kontakSelect.select2({
            dropdownParent: $('#modalDetailPengajuan'),
            placeholder: 'Cari penerima',
            allowClear: true,
            width: '100%'
        });

        projectSelect.select2({
            dropdownParent: $('#modalDetailPengajuan'),
            placeholder: 'Pilih project',
            allowClear: true,
            width: '100%'
        });

        /* ================= UTIL ================= */

        function formatNumber(num) {
            return Number(num || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        function debounceHitung() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(hitungSemua, 100);
        }

        function resetForm() {

            form.reset();

            $('#pengajuan_id').val('');

            kontakSelect.val(null).trigger('change');
            projectSelect.val(null).trigger('change');

            itemContainer.innerHTML = '';

            $('#subtotalEdit').text('0');
            $('#totalDiskonEdit').text('0');
            $('#summaryTotalEdit').text('0');
            $('#grandTotal').text('0');

            $('#pajakSummaryEdit').empty();
        }

        /* ================= LOAD MASTER ================= */

        async function loadMaster() {

            if (masterLoaded) return;

            try {

                const [kontakRes, projectRes, pajakRes] = await Promise.all([
                    fetch("{{ url('/finance/get/kontak') }}"),
                    fetch("{{ url('/finance/get/project-gabungan') }}"),
                    fetch("{{ url('/finance/get/coa-pajak') }}")
                ]);

                const kontak = await kontakRes.json();
                const project = await projectRes.json();
                pajakList = await pajakRes.json();

                kontakSelect.empty().append('<option></option>');
                kontak.forEach(k => {
                    kontakSelect.append(new Option(k.nama, k.id));
                });

                projectSelect.empty().append('<option></option>');
                project.forEach(p => {
                    projectSelect.append(new Option(p.label, p.id));
                });

                masterLoaded = true;

            } catch (e) {
                console.error("Load master gagal", e);
            }
        }

        /* ================= CREATE ROW ================= */

        function createRow(data = {}) {

            const pajakOptions = pajakList.map(p => {

                return `
            <option value="${p.id}"
                data-nilai="${p.nilai_coa ?? 0}"
                data-kategori="${(p.kategori_pajak ?? '').toUpperCase()}">
                ${p.nama_akun} (${p.nilai_coa}%)
            </option>
        `;

            }).join('');

            const html = `
        <div class="row g-2 align-items-center mb-2 item-row">

            <div class="col-md-3">
                <input type="text"
                    class="form-control"
                    name="deskripsiEdit[]"
                    value="${data.deskripsi ?? ''}">
            </div>

            <div class="col-md-1">
                <input type="number"
                    class="form-control qtyEdit"
                    name="qtyEdit[]"
                    value="${data.qty ?? 1}">
            </div>

            <div class="col-md-2">
                <input type="number"
                    class="form-control hargaEdit"
                    name="hargaEdit[]"
                    value="${data.harga ?? 0}">
            </div>

            <div class="col-md-2">
                <input type="number"
                    class="form-control diskonEdit"
                    name="diskonEdit[]"
                    value="${data.diskon ?? 0}">
            </div>

            <div class="col-md-2">
                <select class="form-select pajakEdit"
                    name="pajak_idEdit[]">

                    <option value="0"
                        data-nilai="0"
                        data-kategori="">
                        Non Pajak
                    </option>

                    ${pajakOptions}

                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <input type="text"
                    class="form-control jumlah text-end"
                    readonly>

                <button type="button"
                    class="btn btn-outline-danger btnRemove">
                    −
                </button>
            </div>

        </div>
        `;

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;

            const row = wrapper.firstElementChild;

            if (data.pajak_id) {
                row.querySelector('.pajakEdit').value = data.pajak_id;
            }

            return row;
        }

        /* ================= HITUNG ================= */

        function hitungSemua() {

            let subtotal = 0;
            let totalDiskon = 0;
            let totalPajak = 0;
            let pajakMap = {};

            itemContainer.querySelectorAll('.item-row').forEach(row => {

                const qty = parseFloat(row.querySelector('.qtyEdit').value) || 0;
                const harga = parseFloat(row.querySelector('.hargaEdit').value) || 0;
                const diskon = parseFloat(row.querySelector('.diskonEdit').value) || 0;

                const select = row.querySelector('.pajakEdit');
                const opt = select.options[select.selectedIndex];

                const persen = parseFloat(opt?.dataset.nilai) || 0;
                const kategori = opt?.dataset.kategori || '';

                const total = qty * harga;
                const nilaiDiskon = total * diskon / 100;

                let nilaiPajak = (total - nilaiDiskon) * persen / 100;

                if (kategori === 'PPH') {
                    nilaiPajak *= -1;
                }

                const jumlah = total - nilaiDiskon + nilaiPajak;

                row.querySelector('.jumlah').value = formatNumber(jumlah);

                subtotal += total;
                totalDiskon += nilaiDiskon;
                totalPajak += nilaiPajak;

                if (persen > 0) {
                    const label = opt.text;
                    pajakMap[label] = (pajakMap[label] || 0) + nilaiPajak;
                }

            });

            const grandTotal = subtotal - totalDiskon + totalPajak;

            $('#subtotalEdit').text(formatNumber(subtotal));
            $('#totalDiskonEdit').text(formatNumber(totalDiskon));
            $('#summaryTotalEdit').text(formatNumber(grandTotal));
            $('#grandTotal').text(formatNumber(grandTotal));

            const pajakSummary = $('#pajakSummaryEdit');
            pajakSummary.empty();

            Object.entries(pajakMap).forEach(([nama, nilai]) => {

                pajakSummary.append(`
                <div class="d-flex justify-content-between">
                    <span>${nama}</span>
                    <span class="${nilai < 0 ? 'text-danger' : ''}">
                        Rp ${formatNumber(Math.abs(nilai))}
                    </span>
                </div>
            `);

            });

        }

        /* ================= ADD ROW ================= */

        function tambahRow(data = {}) {

            const row = createRow(data);

            itemContainer.appendChild(row);

            hitungSemua();
        }

        if (btnTambah) {
            btnTambah.addEventListener('click', () => tambahRow({}));
        }

        /* ================= EVENTS ================= */

        modalEl.addEventListener('input', function(e) {

            if (e.target.closest('.item-row')) {
                debounceHitung();
            }

        });

        modalEl.addEventListener('click', function(e) {

            if (e.target.closest('.btnRemove')) {

                const rows = itemContainer.querySelectorAll('.item-row');

                if (rows.length > 1) {

                    e.target.closest('.item-row').remove();
                    hitungSemua();

                }

            }

        });

        /* ================= LOAD DETAIL ================= */

        window.loadDetailPengajuan = async function(id) {

            try {

                resetForm();

                await loadMaster();

                const res = await fetch(`/finance/pengajuan-biaya/detail/${id}`);
                const response = await res.json();

                if (response.status !== 'success') {
                    throw new Error('Response tidak valid');
                }

                const header = response.data.header ?? {};
                const items = response.data.items ?? [];

                $('#pengajuan_id').val(header.id ?? '');

                if (form.jenis_pengajuan) {
                    form.jenis_pengajuan.value = header.jenis_pengajuan ?? '';
                }

                if (form.metode_pembayaran) {
                    form.metode_pembayaran.value = header.metode_pembayaran ?? '';
                }

                if (form.tanggal_pengajuan) {
                    form.tanggal_pengajuan.value = header.tgl_pengajuan ?? '';
                }

                const urgent = form.querySelector('[name="is_urgent"]');
                if (urgent) {
                    urgent.checked = !!header.is_urgent;
                }

                kontakSelect.val(header.kontak_id).trigger('change');
                projectSelect.val(header.project_id).trigger('change');

                itemContainer.innerHTML = '';

                const dataItems = Array.isArray(items) ? items : [];

                if (dataItems.length === 0) {
                    tambahRow({});
                } else {
                    dataItems.forEach(item => tambahRow(item));
                }

                bsModal.show();

            } catch (err) {

                console.error("LOAD DETAIL ERROR", err);
                alert('Gagal memuat detail pengajuan');

            }

        };

    });
</script>