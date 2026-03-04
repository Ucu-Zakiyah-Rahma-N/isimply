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

<!-- MODAL PENGAJUAN BIAYA -->
<div class="modal fade" id="modalDetailPengajuan">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Pengajuan Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- ================= HEADER ================= -->
                <div class="row g-4 mb-4 align-items-stretch">

                    <!-- LEFT SIDE -->
                    <div class="col-lg-9">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">

                            <div class="row g-4">

                                <!-- Jenis Pengajuan -->
                                <div class="col-md-4">
                                    <label class="form-label label-saas">Jenis Pengajuan</label>
                                    <select class="form-select input-saas" name="jenis_pengajuan">
                                        <option value="biaya">Biaya</option>
                                        <option value="pengeluaran">Pengeluaran</option>
                                    </select>
                                </div>

                                <!-- Penerima -->
                                <div class="col-md-4">
                                    <label class="form-label label-saas">Penerima</label>

                                    <div class="select2-group">
                                        <select id="kontakSelectEdit" name="kontak_id" class="input-saas">
                                            <option value=""></option>
                                        </select>

                                        <button type="button"
                                            id="btnOpenKontak"
                                            class="btn btn-saas-add">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Metode Pembayaran -->
                                <div class="col-md-4">
                                    <label class="form-label label-saas">Metode Pembayaran</label>
                                    <select class="form-select input-saas" name="metode_pembayaran">
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label label-saas">Tanggal Pengajuan</label>
                                    <input type="date"
                                        class="form-control input-saas"
                                        name="tanggal_pengajuan"
                                        value="{{ date('Y-m-d') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label label-saas">Project</label>
                                    <select id="projectSelectEdit" name="project_id" class="input-saas">
                                        <option value=""></option>
                                    </select>
                                    <input type="hidden" name="jenis_project_detail" id="jenis_project_detail">
                                </div>

                            </div>

                        </div>
                    </div>

                    <!-- RIGHT SIDE TOTAL -->
                    <div class="col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 p-4 total-card h-100 d-flex flex-column justify-content-between">

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="urgent-label">Urgent</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_urgent">
                                </div>
                            </div>

                            <div class="mt-4">
                                <small class="text-muted">Grand Total</small>
                                <h2 class="fw-bold total-amount mb-0">
                                    <span id="grandTotal">0</span>
                                </h2>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- ================= ITEM ================= -->
                <div class="border rounded p-3 mb-4">

                    <div class="row fw-semibold text-muted mb-2">
                        <div class="col-md-3">Deskripsi</div>
                        <div class="col-md-1">Qty</div>
                        <div class="col-md-2">Harga</div>
                        <div class="col-md-2">Diskon</div>
                        <div class="col-md-2">Pajak</div>
                        <div class="col-md-2 text-end">Jumlah</div>
                    </div>

                    <div id="itemContainer">
                        <div class="row g-2 align-items-center mb-2 item-row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="deskripsi[]">
                            </div>
                            <div class="col-md-1">
                                <input type="number" class="form-control qty" name="qty[]" value="1" min="1">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control harga" name="harga[]" min="0">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control diskon" name="diskon[]" value="0">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select pajak"
                                    name="pajak_id[]"
                                    data-selected="{{ $detail->pajak_id ?? 0 }}">
                                    <option value="0">Non Pajak</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <input type="text" class="form-control jumlah text-end" readonly>
                                <button type="button" class="btn btn-outline-danger btnRemove">−</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnTambahItem">
                        + Tambah Item
                    </button>
                </div>

                <!-- ================= FOOTER ================= -->
                <div class="row mt-4">

                    <div class="col-md-6">
                        <label class="form-label">Lampiran</label>
                        <input type="file" class="form-control">
                    </div>

                    <div class="col-md-6">

                        <div class="d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span>Rp <span id="subtotal">0</span></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Diskon</span>
                            <span class="text-danger">Rp <span id="totalDiskon">0</span></span>
                        </div>

                        <div id="pajakSummary"></div>

                        <hr>

                        <div class="d-flex justify-content-between fw-bold fs-4">
                            <span>Total</span>
                            <span>Rp <span id="summaryTotal">0</span></span>
                        </div>

                        <div class="text-end mt-3">
                            <button class="btn btn-warning px-5">
                                Edit
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
        $(function() {

            const modal = $('#modalDetailPengajuan');
            const kontakSelect = $('#kontakSelectEdit');
            const projectSelect = $('#projectSelectEdit');

            let kontakLoaded = false;
            let projectLoaded = false;

            /* ================= INIT SELECT2 (SEKALI SAJA) ================= */
            kontakSelect.select2({
                dropdownParent: modal,
                placeholder: 'Cari Penerima...',
                allowClear: true,
                width: '100%'
            });

            projectSelect.select2({
                dropdownParent: modal,
                placeholder: 'Pilih Project',
                allowClear: true,
                width: '100%'
            });

            /* ================= EVENT PROJECT ================= */
            projectSelect.on('select2:select', function(e) {
                const jenis = $(e.params.data.element).data('jenis');
                $('#jenis_project_detail').val(jenis ?? '');
            });

            projectSelect.on('select2:clear', function() {
                $('#jenis_project_detail').val('');
            });

            /* ================= MODAL OPEN ================= */
            modal.on('shown.bs.modal', function() {

                if (!kontakLoaded) {
                    loadKontak();
                    kontakLoaded = true;
                }

                if (!projectLoaded) {
                    loadProject();
                    projectLoaded = true;
                }
            });

            /* ================= LOAD KONTAK ================= */
            function loadKontak(selectedId = null) {

                fetch("{{ url('/finance/get/kontak') }}", {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {

                        if (!Array.isArray(data)) return;

                        kontakSelect.append('<option value=""></option>');

                        data.forEach(item => {
                            kontakSelect.append(
                                new Option(
                                    item.nama,
                                    item.id,
                                    false,
                                    selectedId == item.id
                                )
                            );
                        });

                        if (selectedId) {
                            kontakSelect.val(selectedId).trigger('change');
                        }

                    })
                    .catch(err => console.error(err));
            }

            /* ================= LOAD PROJECT ================= */
            function loadProject(selectedId = null) {

                fetch("{{ url('/finance/get/project-gabungan') }}", {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {

                        if (!Array.isArray(data)) return;

                        projectSelect.append('<option value=""></option>');

                        data.forEach(item => {

                            const option = new Option(
                                item.label,
                                item.id,
                                false,
                                selectedId == item.id
                            );

                            $(option).attr('data-jenis', item.jenis_project);
                            projectSelect.append(option);
                        });

                        if (selectedId) {
                            projectSelect.val(selectedId).trigger('change');

                            const selectedOption = projectSelect.find('option:selected');
                            $('#jenis_project_detail')
                                .val(selectedOption.data('jenis') ?? '');
                        }

                    })
                    .catch(err => console.error(err));
            }

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let pajakList = [];

            /* ================= LOAD PAJAK ================= */
            fetch("{{ url('/finance/get/coa-pajak') }}", {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Gagal mengambil data pajak');
                    return res.json();
                })
                .then(data => {
                    pajakList = Array.isArray(data) ? data : [];
                    document.querySelectorAll('.pajak').forEach(select => {
                        isiSelectPajak(select);
                    });
                })
                .catch(err => console.error('ERROR LOAD PAJAK:', err));


            /* ================= ISI SELECT PAJAK (SUPPORT EDIT) ================= */
            function isiSelectPajak(select, selectedId = null) {

                if (!select) return;

                const currentValue = selectedId ?? select.dataset.selected ?? 0;

                select.innerHTML = `<option value="0">Non Pajak</option>`;

                pajakList.forEach(pajak => {
                    const opt = document.createElement('option');
                    opt.value = pajak.id;
                    opt.textContent = `${pajak.nama_akun} (${pajak.nilai_coa}%)`;

                    opt.dataset.nilai = pajak.nilai_coa ?? 0;
                    opt.dataset.kategori = (pajak.kategori_pajak ?? '').toUpperCase();

                    if (parseInt(pajak.id) === parseInt(currentValue)) {
                        opt.selected = true;
                    }

                    select.appendChild(opt);
                });

                select.value = currentValue;
            }


            /* ================= HITUNG SEMUA ================= */
            function hitungSemua() {

                let subtotal = 0;
                let totalDiskon = 0;
                let pajakSummary = {};
                let totalPajakSemua = 0;

                document.querySelectorAll('.item-row').forEach(row => {

                    const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
                    const harga = parseFloat(row.querySelector('.harga')?.value) || 0;
                    const diskon = parseFloat(row.querySelector('.diskon')?.value) || 0;

                    const pajakSelect = row.querySelector('.pajak');
                    const selectedOption = pajakSelect?.options[pajakSelect.selectedIndex];

                    const pajakPersen = parseFloat(selectedOption?.dataset.nilai) || 0;
                    const kategoriPajak = selectedOption?.dataset.kategori || '';

                    const total = qty * harga;
                    const nilaiDiskon = total * (diskon / 100);
                    const setelahDiskon = total - nilaiDiskon;

                    let nilaiPajak = setelahDiskon * (pajakPersen / 100);

                    if (kategoriPajak === 'PPH') {
                        nilaiPajak *= -1;
                    }

                    const jumlah = setelahDiskon + nilaiPajak;

                    row.querySelector('.jumlah').value =
                        jumlah.toLocaleString('id-ID');

                    subtotal += total;
                    totalDiskon += nilaiDiskon;

                    if (kategoriPajak) {
                        if (!pajakSummary[kategoriPajak]) {
                            pajakSummary[kategoriPajak] = 0;
                        }
                        pajakSummary[kategoriPajak] += nilaiPajak;
                    }

                    totalPajakSemua += nilaiPajak; // 🔥 FIX penting
                });


                /* ===== Render Pajak Summary ===== */
                const pajakContainer = document.getElementById('pajakSummary');
                pajakContainer.innerHTML = '';

                Object.keys(pajakSummary).forEach(kategori => {

                    const nilai = pajakSummary[kategori];
                    const isMinus = nilai < 0;

                    const div = document.createElement('div');
                    div.className = 'd-flex justify-content-between';

                    div.innerHTML = `
                <span>${kategori}</span>
                <span class="${isMinus ? 'text-danger' : ''}">
                    Rp ${Math.abs(nilai).toLocaleString('id-ID')}
                </span>
            `;

                    pajakContainer.appendChild(div);
                });

                const grandTotal = subtotal - totalDiskon + totalPajakSemua;

                document.getElementById('subtotal').innerText =
                    subtotal.toLocaleString('id-ID');

                document.getElementById('totalDiskon').innerText =
                    totalDiskon.toLocaleString('id-ID');

                document.getElementById('summaryTotal').innerText =
                    grandTotal.toLocaleString('id-ID');

                document.getElementById('grandTotal').innerText =
                    'Rp ' + grandTotal.toLocaleString('id-ID');
            }


            /* ================= AUTO HITUNG ================= */
            document.addEventListener('input', hitungSemua);
            document.addEventListener('change', hitungSemua);


            /* ================= TAMBAH ITEM ================= */
            document.getElementById('btnTambahItem').addEventListener('click', function() {

                const container = document.getElementById('itemContainer');
                const template = container.querySelector('.item-row');
                const row = template.cloneNode(true);

                row.querySelectorAll('input').forEach(i => i.value = '');
                row.querySelector('.qty').value = 1;
                row.querySelector('.diskon').value = 0;

                isiSelectPajak(row.querySelector('.pajak'));

                container.appendChild(row);
                hitungSemua();
            });


            /* ================= HAPUS ITEM ================= */
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btnRemove')) {
                    const rows = document.querySelectorAll('.item-row');
                    if (rows.length > 1) {
                        e.target.closest('.item-row').remove();
                        hitungSemua();
                    }
                }
            });


            /* ================= LOAD DETAIL MODAL ================= */
            window.loadDetailModal = function(detailItems) {

                const container = document.getElementById('itemContainer');
                container.innerHTML = '';

                detailItems.forEach(item => {

                    const template = document.querySelector('.item-row-template');
                    const row = template.cloneNode(true);

                    row.classList.remove('item-row-template');
                    row.classList.add('item-row');

                    row.querySelector('.qty').value = item.qty;
                    row.querySelector('.harga').value = item.harga;
                    row.querySelector('.diskon').value = item.diskon;

                    const pajakSelect = row.querySelector('.pajak');
                    isiSelectPajak(pajakSelect, item.pajak_id);

                    container.appendChild(row);
                });

                hitungSemua(); // 🔥 WAJIB supaya summary muncul
            };

        });
    </script>