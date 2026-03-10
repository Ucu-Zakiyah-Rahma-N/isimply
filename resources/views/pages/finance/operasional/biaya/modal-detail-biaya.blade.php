<style>
    /* ============================= */
    /* ====== SELECT2 STYLE ======== */
    /* ============================= */

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

    .select2-container--default .select2-selection__rendered {
        padding-left: 0 !important;
        line-height: 42px !important;
    }

    .select2-container--default .select2-selection__arrow {
        height: 42px !important;
        right: 12px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .select2-dropdown {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    /* ============================= */
    /* ====== FORM STYLE =========== */
    /* ============================= */

    .label-saas {
        font-size: .8rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 6px;
    }

    .input-saas {
        height: 42px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        padding: 0 14px;
        font-size: .9rem;
        transition: .2s;
    }

    .input-saas:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    /* ============================= */
    /* ====== CARD STYLE =========== */
    /* ============================= */

    .card {
        background: #fff;
    }

    .total-card {
        background: linear-gradient(135deg, #fff, #f8fafc);
        border: 1px solid #f1f3f5;
    }

    .total-amount {
        font-size: 1.8rem;
        letter-spacing: .5px;
        color: #111827;
    }

    .urgent-label {
        font-size: .85rem;
        font-weight: 600;
        color: #dc3545;
    }

    .modal-content {
        border-radius: 24px;
        border: none;
    }

    /* ============================= */
    /* ====== ITEM SECTION ========= */
    /* ============================= */

    #itemContainer {
        min-height: 40px;
    }

    .item-row {
        padding: 8px 0;
        border-bottom: 1px dashed #e5e7eb;
    }

    .item-row input,
    .item-row select {
        height: 38px;
        font-size: .9rem;
    }

    .item-row .jumlah {
        font-weight: 600;
        display: block;
        padding-top: 6px;
    }

    /* tombol tambah item */

    #btnTambahItem {
        border-radius: 10px;
    }
</style>

<!-- ================= MODAL DETAIL PENGAJUAN ================= -->
<div class="modal fade" id="modalDetailPengajuan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <form id="formDetailPengajuan">

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Pengajuan Biaya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <input type="hidden" id="pengajuan_id" name="pengajuan_id">

                    <div class="row g-4 mb-4">

                        <!-- LEFT SECTION -->
                        <div class="col-lg-9">

                            <div class="card shadow-sm rounded-4 p-4 h-100">

                                <div class="row g-4">

                                    <div class="col-md-4">
                                        <label class="label-saas">Jenis Pengajuan</label>
                                        <select class="form-select" name="jenis_pengajuan">
                                            <option value="biaya">Biaya</option>
                                            <option value="pengeluaran">Pengeluaran</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="label-saas">Penerima</label>
                                        <div class="d-flex">
                                            <select id="kontakSelectEdit" name="kontak_id" class="form-select"></select>
                                            <button type="button" id="btnOpenKontak" class="btn btn-light border ms-2">+</button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="label-saas">Metode Pembayaran</label>
                                        <select class="form-select" name="metode_pembayaran">
                                            <option value="cash">Cash</option>
                                            <option value="transfer">Transfer</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="label-saas">Tanggal Pengajuan</label>
                                        <input type="date" class="form-control" name="tgl_pengajuan">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="label-saas">Project</label>
                                        <select id="projectSelectEdit" name="project_id" class="form-select"></select>
                                        <input type="hidden" id="jenis_project_detail" name="jenis_project_detail">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RIGHT TOTAL -->
                        <div class="col-lg-3">

                            <div class="card shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between">

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="urgent-label">Urgent</span>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="is_urgent" value="1">
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

                    <!-- ITEM SECTION -->

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

                        <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="btnTambahItem">
                            + Tambah Item
                        </button>

                    </div>

                    <!-- FOOTER SECTION -->

                    <div class="row">

                        <div class="col-md-6">
                            <label class="label-saas">Lampiran</label>
                            <input type="file" name="lampiran" class="form-control">
                        </div>

                        <div class="col-md-6">

                            <div class="d-flex justify-content-between">
                                <span>Subtotal</span>
                                <span>Rp <span id="subtotalEdit">0</span></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span>Diskon</span>
                                <span class="text-danger">Rp <span id="totalDiskonEdit">0</span></span>
                            </div>

                            <div id="pajakSummaryEdit"></div>

                            <hr>

                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span>Rp <span id="summaryTotalEdit">0</span></span>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" id="btnSubmitEdit" class="btn btn-warning px-4">
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

        console.log("SCRIPT MODAL DETAIL LOADED");

        /* ================= INIT ================= */

        const modalEl = document.getElementById('modalDetailPengajuan');
        if (!modalEl) {
            console.error("Modal tidak ditemukan");
            return;
        }

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

            console.log("Reset form");

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

        function debounceHitung() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(hitungSemua, 120);
        }


        /* ================= LOAD MASTER ================= */

        async function loadMaster() {

            if (masterLoaded) return;

            console.log("Loading master data...");

            const [kontakRes, projectRes, pajakRes] = await Promise.all([
                fetch("{{ url('/finance/get/kontak') }}"),
                fetch("{{ url('/finance/get/project-gabungan') }}"),
                fetch("{{ url('/finance/get/coa-pajak') }}")
            ]);

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


<<<<<<< HEAD
        /* ================= BUILD ROW ================= */

        function buildRow(data = {}) {
=======
            const template = document.getElementById('itemTemplate');
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('.item-row');
            document.getElementById('itemContainer').appendChild(row);
            if (!row) return null;
>>>>>>> a1705e4888b50cb06f86cb15e710ec823f6922f3

            let pajakOptions = `<option value="0" data-nilai="0" data-kategori="">Non Pajak</option>`;

<<<<<<< HEAD
            pajakList.forEach(p => {
                pajakOptions += `
<option value="${p.id}"
data-nilai="${parseFloat(p.nilai_coa||0)}"
data-kategori="${(p.kategori_pajak||'').toUpperCase()}"
${data.pajak_id==p.id?'selected':''}>
${p.nama_akun} (${p.nilai_coa}%)
</option>`;
            });

            return `
<div class="row g-2 align-items-center mb-2 item-row">

<div class="col-md-3">
<input type="text"
class="form-control"
name="deskripsiEdit[]"
value="${data.deskripsi??''}"
required>
</div>

<div class="col-md-1">
<input type="number"
class="form-control qtyEdit"
name="qtyEdit[]"
value="${data.qty??1}"
min="1">
</div>

<div class="col-md-2">
<input type="number"
class="form-control hargaEdit"
name="hargaEdit[]"
value="${data.harga??0}"
min="0">
</div>

<div class="col-md-2">
<input type="number"
class="form-control diskonEdit"
name="diskonEdit[]"
value="${data.diskon??0}"
min="0"
max="100">
</div>

<div class="col-md-2">
<select class="form-select pajakEdit" name="pajak_idEdit[]">
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
=======
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
>>>>>>> a1705e4888b50cb06f86cb15e710ec823f6922f3
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

                const persen = parseFloat(opt.dataset.nilai || 0);
                const kategori = opt.dataset.kategori || '';

                const total = qty * harga;

                const nilaiDiskon = total * (diskon / 100);

                let nilaiPajak = (total - nilaiDiskon) * (persen / 100);

                if (kategori === 'PPH') {
                    nilaiPajak *= -1;
                }

                const jumlah = total - nilaiDiskon + nilaiPajak;

                row.querySelector('.jumlah').value = formatNumber(jumlah);

                subtotal += total;
                totalDiskon += nilaiDiskon;
                totalPajak += nilaiPajak;

                if (persen > 0) {
                    const key = opt.text;
                    pajakMap[key] = (pajakMap[key] || 0) + nilaiPajak;
                }

            });

            const grandTotal = subtotal - totalDiskon + totalPajak;

            $('#subtotalEdit').text(formatNumber(subtotal));
            $('#totalDiskonEdit').text(formatNumber(totalDiskon));
            $('#summaryTotalEdit').text(formatNumber(grandTotal));
            $('#grandTotal').text(formatNumber(grandTotal));

            renderPajakSummary(pajakMap);

        }


        /* ================= PAJAK SUMMARY ================= */

        function renderPajakSummary(map) {

            const el = $('#pajakSummaryEdit');

            el.empty();

            Object.entries(map).forEach(([nama, nilai]) => {
                el.append(`
<div class="d-flex justify-content-between">
<span>${nama}</span>
<span>Rp ${formatNumber(nilai)}</span>
</div>
`);
            });

        }


        /* ================= EVENTS ================= */

        btnTambah.addEventListener('click', function() {

            itemContainer.insertAdjacentHTML('beforeend', buildRow());

            hitungSemua();

        });


        /* remove row */

        modalEl.addEventListener('click', function(e) {

            if (e.target.closest('.btnRemove')) {

                const rows = itemContainer.querySelectorAll('.item-row');

                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                    hitungSemua();
                }

            }

        });
<<<<<<< HEAD


        /* auto hitung saat edit */

        modalEl.addEventListener('input', function(e) {

            if (
                e.target.classList.contains('qtyEdit') ||
                e.target.classList.contains('hargaEdit') ||
                e.target.classList.contains('diskonEdit')
            ) {
                debounceHitung();
            }

        });

        modalEl.addEventListener('change', function(e) {

            if (e.target.classList.contains('pajakEdit')) {
                hitungSemua();
            }

        });


        /* ================= LOAD DETAIL ================= */

=======
>>>>>>> a1705e4888b50cb06f86cb15e710ec823f6922f3
        window.loadDetailPengajuan = async function(id) {
            console.log('Load Detail Pengajuan ID:', id); // pastikan ID ada

            console.log("Load detail pengajuan:", id);

            try {
                resetForm();

                await loadMaster();

                const res = await fetch(`/finance/pengajuan-biaya/detail/${id}`);
<<<<<<< HEAD
=======
                console.log('Fetch status:', res.status); // cek 200?
                if (!res.ok) throw new Error('Gagal ambil detail');

>>>>>>> a1705e4888b50cb06f86cb15e710ec823f6922f3
                const response = await res.json();
                console.log('Response data:', response); // cek apakah data header/items ada

                if (response.status !== 'success') {
                    alert('Data tidak ditemukan');
                    return;
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
                form.tgl_pengajuan.value = header.tgl_pengajuan ?? '';

                form.is_urgent.checked = !!header.is_urgent;

                kontakSelect.val(header.kontak_id).trigger('change');
                projectSelect.val(header.project_id).trigger('change');

                itemContainer.innerHTML = '';

<<<<<<< HEAD
                if (Array.isArray(items) && items.length) {

                    items.forEach(item => {
                        itemContainer.insertAdjacentHTML('beforeend', buildRow(item));
=======
                if (items && items.length > 0) {
                    items.forEach(item => {

                        const row = createRow(item);

                        console.log("ROW:", row);

                        if (row) itemContainer.appendChild(row);

>>>>>>> a1705e4888b50cb06f86cb15e710ec823f6922f3
                    });
                } else {
<<<<<<< HEAD

                    itemContainer.insertAdjacentHTML('beforeend', buildRow());

=======
                    const rowFragment = createRow();
                    if (rowFragment) itemContainer.appendChild(rowFragment);
>>>>>>> a1705e4888b50cb06f86cb15e710ec823f6922f3
                }

                hitungSemua();
                bsModal.show();

            } catch (err) {
                console.error(err);
<<<<<<< HEAD
                alert('Gagal memuat detail pengajuan');

=======
                alert('Gagal memuat detail.');
>>>>>>> a1705e4888b50cb06f86cb15e710ec823f6922f3
            }

        };
    });
</script>