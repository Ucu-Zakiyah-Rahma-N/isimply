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

                <!-- ================= HEADER ================= -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Pengajuan Biaya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- ================= BODY ================= -->
                <div class="modal-body">

                    <input type="hidden" name="pengajuan_id" id="pengajuan_id">

                    <!-- ================= HEADER SECTION ================= -->
                    <div class="row g-4 mb-4">

                        <!-- LEFT -->
                        <div class="col-lg-9">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <div class="row g-4">

                                    <div class="col-md-4">
                                        <label class="form-label">Jenis Pengajuan</label>
                                        <select class="form-select" name="jenis_pengajuan" required>
                                            <option value="biaya">Biaya</option>
                                            <option value="pengeluaran">Pengeluaran</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Penerima</label>
                                        <div class="d-flex">
                                            <select id="kontakSelectEdit"
                                                name="kontak_id"
                                                class="form-select w-100"
                                                required>
                                                <option value=""></option>
                                            </select>
                                            <button type="button"
                                                id="btnOpenKontak"
                                                class="btn btn-light border ms-2 rounded-3">
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Metode Pembayaran</label>
                                        <select class="form-select"
                                            name="metode_pembayaran"
                                            required>
                                            <option value="cash">Cash</option>
                                            <option value="transfer">Transfer</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Pengajuan</label>
                                        <input type="date"
                                            class="form-control"
                                            name="tanggal_pengajuan"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Project</label>
                                        <select id="projectSelectEdit"
                                            name="project_id"
                                            class="form-select"
                                            required>
                                            <option value=""></option>
                                        </select>
                                        <input type="hidden"
                                            name="jenis_project_detail"
                                            id="jenis_project_detail">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RIGHT TOTAL -->
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

                        <div id="itemContainer"></div>

                        <!-- TEMPLATE -->
                        <template id="itemTemplate">
                            <div class="row g-2 align-items-center mb-2 item-row">

                                <div class="col-md-3">
                                    <input type="text"
                                        class="form-control"
                                        name="deskripsiEdit[]"
                                        required>
                                </div>

                                <div class="col-md-1">
                                    <input type="number"
                                        class="form-control qtyEdit"
                                        name="qtyEdit[]"
                                        value="1"
                                        min="1"
                                        step="1"
                                        required>
                                </div>

                                <div class="col-md-2">
                                    <input type="number"
                                        class="form-control hargaEdit"
                                        name="hargaEdit[]"
                                        min="0"
                                        step="0.01"
                                        required>
                                </div>

                                <div class="col-md-2">
                                    <input type="number"
                                        class="form-control diskonEdit"
                                        name="diskonEdit[]"
                                        value="0"
                                        min="0"
                                        max="100"
                                        step="0.01">
                                </div>

                                <div class="col-md-2">
                                    <select class="form-select pajakEdit"
                                        name="pajak_idEdit[]">
                                        <option value="0">Non Pajak</option>
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
                        </template>

                        <button type="button"
                            class="btn btn-sm btn-outline-primary mt-2"
                            id="btnTambahItem">
                            + Tambah Item
                        </button>

                    </div>

                    <!-- ================= FOOTER TOTAL ================= -->
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

        /* ================= GUARD ================= */

        const modalEl = document.getElementById('modalDetailPengajuan');
        if (!modalEl) return;

        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const form = document.getElementById('formDetailPengajuan');
        const itemContainer = document.getElementById('itemContainer');
        const itemTemplate = document.getElementById('itemTemplate');
        const btnTambah = document.getElementById('btnTambahItem');

        if (!form || !itemContainer || !itemTemplate || !btnTambah) {
            console.error('Element modal tidak lengkap.');
            return;
        }

        const kontakSelect = $('#kontakSelectEdit');
        const projectSelect = $('#projectSelectEdit');

        let pajakList = [];
        let masterLoaded = false;
        let debounceTimer = null;

        /* ================= SELECT2 ================= */

        kontakSelect.select2({
            dropdownParent: $('#modalDetailPengajuan'),
            placeholder: 'Cari Penerima...',
            allowClear: true,
            width: '100%'
        });

        projectSelect.select2({
            dropdownParent: $('#modalDetailPengajuan'),
            placeholder: 'Pilih Project',
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

        function resetForm() {
            form.reset();
            $('#pengajuan_id').val('');
            kontakSelect.val(null).trigger('change');
            projectSelect.val(null).trigger('change');
            itemContainer.innerHTML = '';
            $('#pajakSummaryEdit').empty();
            $('#subtotalEdit').text('0');
            $('#totalDiskonEdit').text('0');
            $('#summaryTotalEdit').text('0');
            $('#grandTotal').text('0');
        }

        function debounceHitung() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(hitungSemua, 100);
        }

        /* ================= LOAD MASTER ================= */

        async function loadMaster() {

            if (masterLoaded) return;

            const [kontakRes, projectRes, pajakRes] = await Promise.all([
                fetch("{{ url('/finance/get/kontak') }}"),
                fetch("{{ url('/finance/get/project-gabungan') }}"),
                fetch("{{ url('/finance/get/coa-pajak') }}")
            ]);

            if (!kontakRes.ok || !projectRes.ok || !pajakRes.ok) {
                throw new Error('Gagal load master data');
            }

            const kontakData = await kontakRes.json();
            const projectData = await projectRes.json();
            pajakList = await pajakRes.json();

            kontakSelect.empty().append('<option value=""></option>');
            kontakData.forEach(k => {
                kontakSelect.append(new Option(k.nama, k.id));
            });

            projectSelect.empty().append('<option value=""></option>');
            projectData.forEach(p => {
                projectSelect.append(new Option(p.label, p.id));
            });

            masterLoaded = true;
        }

        /* ================= CREATE ROW ================= */
        function createRow(data = null) {

            const template = document.getElementById('itemTemplate');
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('.item-row');
            document.getElementById('itemContainer').appendChild(row);
            if (!row) return null;

            const pajakSelect = row.querySelector('.pajakEdit');

            pajakSelect.innerHTML = '<option value="0" data-nilai="0" data-kategori="">Non Pajak</option>';

            if (Array.isArray(pajakList)) {
                pajakList.forEach(p => {
                    const opt = document.createElement('option');

                    opt.value = p.id;
                    opt.textContent = `${p.nama_akun || p.nama || ''} (${p.nilai_coa || p.nilai || 0}%)`;

                    opt.dataset.nilai = p.nilai_coa || p.nilai || 0;
                    opt.dataset.kategori = (p.kategori_pajak || '').toUpperCase();

                    pajakSelect.appendChild(opt);
                });
            }

            if (data) {
                row.querySelector('[name="deskripsiEdit[]"]').value = data.deskripsi ?? '';
                row.querySelector('.qtyEdit').value = data.qty ?? 1;
                row.querySelector('.hargaEdit').value = data.harga ?? 0;
                row.querySelector('.diskonEdit').value = data.diskon ?? 0;
                row.querySelector('.jumlah').value = data.jumlah ?? 0;

                if (data.pajak_id) {
                    pajakSelect.value = data.pajak_id;
                }
            }

            row.querySelector('.btnRemove').addEventListener('click', function() {
                row.remove();
                hitungSemua();
            });

            return row; // ✅ RETURN ROW
        }
        // /* ================= CREATE ROW ================= */
        // function createRow(data = null) {

        //     const fragment = itemTemplate.content.cloneNode(true);
        //     const row = fragment.querySelector('.item-row');

        //     if (!row) return null;

        //     const pajakSelect = row.querySelector('.pajakEdit');

        //     pajakSelect.innerHTML = '<option value="0" data-nilai="0" data-kategori="">Non Pajak</option>';

        //     pajakList.forEach(p => {

        //         const opt = document.createElement('option');

        //         opt.value = p.id;
        //         // opt.textContent = `${p.nama_akun} (${p.nilai_coa}%)`;

        //         // opt.dataset.nilai = p.nilai_coa ?? 0;
        //         // opt.dataset.kategori = (p.kategori_pajak ?? '').toUpperCase();
        //         opt.textContent = `${p.nama_akun || p.nama} (${p.nilai_coa || p.nilai || 0}%)`;

        //         opt.dataset.nilai = p.nilai_coa || p.nilai || 0;

        //         opt.dataset.kategori = (p.kategori_pajak || '').toUpperCase();

        //         pajakSelect.appendChild(opt);

        //     }
        // );

        //     if (data) {

        //         row.querySelector('[name="deskripsiEdit[]"]').value = data.deskripsi ?? '';
        //         row.querySelector('.qtyEdit').value = data.qty ?? 1;
        //         row.querySelector('.hargaEdit').value = data.harga ?? 0;
        //         row.querySelector('.diskonEdit').value = data.diskon ?? 0;

        //         // SET PAJAK
        //         if (data.pajak_id) {
        //             pajakSelect.value = data.pajak_id;
        //         } else {
        //             pajakSelect.value = 0;
        //         }

        //     }

        //     return row;
        // }

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
                const nilaiDiskon = total * (diskon / 100);

                let nilaiPajak = (total - nilaiDiskon) * (persen / 100);
                if (kategori === 'PPH') nilaiPajak *= -1;

                const jumlah = total - nilaiDiskon + nilaiPajak;

                row.querySelector('.jumlah').value = formatNumber(jumlah);

                subtotal += total;
                totalDiskon += nilaiDiskon;
                totalPajak += nilaiPajak;

                if (persen > 0) {
                    pajakMap[opt.text] = (pajakMap[opt.text] || 0) + nilaiPajak;
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

        /* ================= EVENTS ================= */

        btnTambah.addEventListener('click', function() {
            const row = createRow();
            if (row) {
                itemContainer.appendChild(row);
                hitungSemua();
            }
        });

        modalEl.addEventListener('input', function(e) {
            if (e.target.closest('.item-row')) debounceHitung();
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
        window.loadDetailPengajuan = async function(id) {
            console.log('Load Detail Pengajuan ID:', id); // pastikan ID ada

            try {
                resetForm();
                await loadMaster();

                const res = await fetch(`/finance/pengajuan-biaya/detail/${id}`);
                console.log('Fetch status:', res.status); // cek 200?
                if (!res.ok) throw new Error('Gagal ambil detail');

                const response = await res.json();
                console.log('Response data:', response); // cek apakah data header/items ada

                if (response.status !== 'success') {
                    throw new Error('Response tidak valid');
                }

                // ✅ Hanya setelah ini items bisa diakses
                const {
                    header,
                    items
                } = response.data;
                console.log("ITEMS:", items); // pindahkan di sini
                console.log("ITEM CONTAINER:", itemContainer);
                console.log("ITEM DATA:", items);
                console.log("TEMPLATE:", itemTemplate);

                $('#pengajuan_id').val(header.id ?? '');
                form.jenis_pengajuan.value = header.jenis_pengajuan ?? '';
                form.metode_pembayaran.value = header.metode_pembayaran ?? '';
                form.tanggal_pengajuan.value = header.tgl_pengajuan ?? '';
                form.is_urgent.checked = !!header.is_urgent;

                kontakSelect.val(header.kontak_id).trigger('change');
                projectSelect.val(header.project_id).trigger('change');

                itemContainer.innerHTML = '';

                if (items && items.length > 0) {
                    items.forEach(item => {

                        const row = createRow(item);

                        console.log("ROW:", row);

                        if (row) itemContainer.appendChild(row);

                    });
                } else {
                    const rowFragment = createRow();
                    if (rowFragment) itemContainer.appendChild(rowFragment);
                }

                hitungSemua();
                bsModal.show();

            } catch (err) {
                console.error(err);
                alert('Gagal memuat detail.');
            }
        };
    });
</script>