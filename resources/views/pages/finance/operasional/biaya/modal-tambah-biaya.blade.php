<style>
    /* ===== SELECT2 RESET CLEAN ===== */

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
<div class="modal fade" id="modalPengajuanBiaya">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Form Pengajuan Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formPengajuan" enctype="multipart/form-data">
                    @csrf

                    <!-- ================= HEADER ================= -->
                    <div class="row g-4 mb-4">

                        <!-- LEFT -->
                        <div class="col-lg-9">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <div class="row g-4">

                                    <div class="col-md-4">
                                        <label class="label-saas">Jenis Pengajuan</label>
                                        <select class="form-select input-saas" name="jenis_pengajuan">
                                            <option value="biaya">Biaya</option>
                                            <option value="pengadaan">Pengadaan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="label-saas">Penerima</label>
                                        <div class="select2-group">
                                            <select id="kontakSelect" name="kontak_id"></select>
                                            <button type="button" class="btn btn-saas-add">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="label-saas">Metode Pembayaran</label>
                                        <select class="form-select input-saas" name="metode_pembayaran">
                                            <option value="cash">Cash</option>
                                            <option value="transfer">Transfer</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="label-saas">Tanggal</label>
                                        <input type="date" class="form-control input-saas" name="tanggal_pengajuan">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="label-saas">Project</label>
                                        <select id="projectSelect" class="input-saas" name="project_id"></select>
                                        <input type="hidden" id="jenis_project" name="jenis_project">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RIGHT TOTAL -->
                        <div class="col-lg-3">
                            <div class="card border-0 shadow-sm rounded-4 p-4 total-card h-100 d-flex flex-column justify-content-between">

                                <div class="d-flex justify-content-between">
                                    <span class="urgent-label">Urgent</span>
                                    <input type="checkbox" class="form-check-input" name="is_urgent">
                                </div>

                                <div class="mt-4">
                                    <small class="text-muted">Grand Total</small>
                                    <h2 class="fw-bold total-amount">
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
                                    <input type="number" class="form-control qty" name="qty[]" value="1">
                                </div>

                                <div class="col-md-2">
                                    <input type="number" class="form-control harga" name="harga[]">
                                </div>

                                <div class="col-md-2">
                                    <div class="d-flex">
                                        <input type="number" class="form-control diskon" name="diskon[]" value="0">
                                        <select class="form-select diskon-type" name="diskon_type[]" style="max-width:70px">
                                            <option value="percent">%</option>
                                            <option value="nominal">Rp</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <select class="form-select pajak" name="pajak_id[]">
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

                    <!-- ================= SUMMARY ================= -->
                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">
                            <label>Lampiran</label>
                            <input type="file" class="form-control">
                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">

                            <!-- GLOBAL DISKON -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <input type="checkbox" id="useDiskonGlobal" name="use_diskon_global">
                                    <label>Diskon Global</label>
                                </div>

                                <div class="d-flex" style="width:200px">
                                    <input type="number" id="diskonGlobal" name="diskon_global" class="form-control" value="0" disabled>
                                    <select id="diskonGlobalType" name="diskon_global_type" class="form-select" style="max-width:70px" disabled>
                                        <option value="percent">%</option>
                                        <option value="nominal">Rp</option>
                                    </select>
                                </div>
                            </div>

                            <!-- GLOBAL PAJAK -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <input type="checkbox" id="usePajakGlobal" name="use_pajak_global">
                                    <label>Pajak Global</label>
                                </div>

                                <select id="pajakGlobal" name="pajak_global_id" class="form-select" style="width:200px" disabled>
                                    <option value="0">Non Pajak</option>
                                </select>
                            </div>

                            <hr>

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
                                <button class="btn btn-success px-5">Buat</button>
                            </div>

                        </div>

                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadKontak();
    });

    function loadKontak(selectedId = null) {
        fetch("{{ url('/finance/get/kontak') }}", {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {

                const select = document.querySelector("select[name='kontak_id']");
                select.innerHTML = `<option value="">Pilih Penerima</option>`;

                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.text = item.nama;

                    if (selectedId && selectedId == item.id) {
                        option.selected = true;
                    }

                    select.appendChild(option);
                });

            })
            .catch(err => console.error(err));
    }
</script>

<script>
    function loadProject(selectedId = null) {

        fetch("{{ url('/finance/get/project-gabungan') }}", {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {

                const select = $('#projectSelect');
                select.empty();

                // OPTION MANUAL
                select.append('<option value="">Pilih Project</option>');
                select.append('<option value="MARKETING" data-jenis="NON_PO">MARKETING</option>');
                select.append('<option value="OFFICE" data-jenis="NON_PO">OFFICE</option>');
                select.append('<option value="PROJECT" data-jenis="NON_PO">PROJECT</option>');

                // DATA API
                data.forEach(item => {
                    const option = new Option(
                        item.label,
                        item.id,
                        false,
                        selectedId === item.id
                    );

                    $(option).attr('data-jenis', item.jenis_project);
                    select.append(option);
                });

                // Refresh Select2
                select.trigger('change');

                // Jika ada selectedId (mode edit), langsung set jenis
                if (selectedId) {
                    const selectedOption = select.find('option:selected');
                    $('#jenis_project').val(selectedOption.data('jenis') ?? '');
                }
            })
            .catch(err => console.error(err));
    }

    $(document).ready(function() {

        const select = $('#projectSelect');

        select.select2({
            dropdownParent: $('#modalPengajuanBiaya'),
            placeholder: 'Pilih Project',
            allowClear: true,
            width: '100%'
        });

        // Event khusus Select2 (lebih stabil daripada change biasa)
        select.on('select2:select', function(e) {
            const jenis = $(e.params.data.element).data('jenis');
            $('#jenis_project').val(jenis ?? '');
        });

        // Jika di-clear
        select.on('select2:clear', function() {
            $('#jenis_project').val('');
        });

        loadProject(); // load awal
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        let pajakList = [];

        /* ================= LOAD PAJAK ================= */
        fetch("{{ url('/finance/get/coa-pajak') }}")
            .then(res => res.json())
            .then(data => {
                pajakList = Array.isArray(data) ? data : [];

                document.querySelectorAll('.pajak').forEach(isiSelectPajak);
                isiSelectPajak(document.getElementById('pajakGlobal'));
            });

        function isiSelectPajak(select) {
            if (!select) return;

            select.innerHTML = `<option value="0">Non Pajak</option>`;

            pajakList.forEach(pajak => {
                const opt = document.createElement('option');
                opt.value = pajak.id;
                opt.textContent = `${pajak.nama_akun} (${pajak.nilai_coa}%)`;

                opt.dataset.nilai = pajak.nilai_coa ?? 0;
                opt.dataset.kategori = (pajak.kategori_pajak ?? '').toUpperCase();

                select.appendChild(opt);
            });
        }

        /* ================= ENGINE ================= */

        function hitungItem(item) {
            const total = item.qty * item.harga;

            let diskon = item.diskonType === 'percent' ?
                total * (item.diskon / 100) :
                item.diskon;

            if (diskon > total) diskon = total;

            const setelahDiskon = total - diskon;

            let pajak = setelahDiskon * (item.pajak.persen / 100);

            if (item.pajak.kategori === 'PPH') {
                pajak *= -1;
            }

            return {
                total,
                diskon,
                setelahDiskon,
                pajak,
                grand: setelahDiskon + pajak
            };
        }

        function hitungSemua(items, globalConfig) {

            let subtotal = 0;
            let totalDiskonItem = 0;
            let totalPajakItem = 0;
            let pajakSummary = {};

            items.forEach(item => {
                const res = hitungItem(item);

                subtotal += res.total;
                totalDiskonItem += res.diskon;
                totalPajakItem += res.pajak;

                const key = item.pajak.kategori || 'LAINNYA';
                pajakSummary[key] = (pajakSummary[key] || 0) + res.pajak;
            });

            /* ===== GLOBAL DISKON ===== */
            let diskonGlobal = 0;

            if (globalConfig.useDiskon) {
                diskonGlobal = globalConfig.diskonType === 'percent' ?
                    subtotal * (globalConfig.diskon / 100) :
                    globalConfig.diskon;

                if (diskonGlobal > subtotal) diskonGlobal = subtotal;
            }

            /* ===== GLOBAL PAJAK ===== */
            let pajakGlobal = 0;

            if (globalConfig.usePajak) {
                const dasar = subtotal - totalDiskonItem - diskonGlobal;

                pajakGlobal = dasar * (globalConfig.pajak.persen / 100);

                if (globalConfig.pajak.kategori === 'PPH') {
                    pajakGlobal *= -1;
                }

                pajakSummary['GLOBAL'] = (pajakSummary['GLOBAL'] || 0) + pajakGlobal;
            }

            const grandTotal =
                subtotal -
                totalDiskonItem -
                diskonGlobal +
                totalPajakItem +
                pajakGlobal;

            return {
                subtotal,
                totalDiskon: totalDiskonItem + diskonGlobal,
                pajakSummary,
                grandTotal
            };
        }

        /* ================= EXTRACT ================= */

        function ambilItems() {
            const items = [];

            document.querySelectorAll('.item-row').forEach(row => {

                const pajakSelect = row.querySelector('.pajak');
                const opt = pajakSelect.options[pajakSelect.selectedIndex];

                items.push({
                    qty: parseFloat(row.querySelector('.qty').value) || 0,
                    harga: parseFloat(row.querySelector('.harga').value) || 0,
                    diskon: parseFloat(row.querySelector('.diskon').value) || 0,
                    diskonType: row.querySelector('.diskon-type').value,

                    pajak: {
                        persen: parseFloat(opt?.dataset.nilai) || 0,
                        kategori: opt?.dataset.kategori || ''
                    }
                });
            });

            return items;
        }

        function ambilGlobal() {

            const useDiskon = document.getElementById('useDiskonGlobal').checked;
            const usePajak = document.getElementById('usePajakGlobal').checked;

            const pajakSelect = document.getElementById('pajakGlobal');
            const opt = pajakSelect.options[pajakSelect.selectedIndex];

            return {
                useDiskon,
                usePajak,

                diskon: parseFloat(document.getElementById('diskonGlobal').value) || 0,
                diskonType: document.getElementById('diskonGlobalType').value,

                pajak: {
                    persen: parseFloat(opt?.dataset.nilai) || 0,
                    kategori: opt?.dataset.kategori || ''
                }
            };
        }

        /* ================= RENDER ================= */

        function render(data) {

            document.getElementById('subtotal').innerText =
                data.subtotal.toLocaleString('id-ID');

            document.getElementById('totalDiskon').innerText =
                data.totalDiskon.toLocaleString('id-ID');

            document.getElementById('summaryTotal').innerText =
                data.grandTotal.toLocaleString('id-ID');

            document.getElementById('grandTotal').innerText =
                'Rp ' + data.grandTotal.toLocaleString('id-ID');

            const container = document.getElementById('pajakSummary');
            container.innerHTML = '';

            Object.keys(data.pajakSummary).forEach(key => {
                const val = data.pajakSummary[key];

                container.innerHTML += `
                <div class="d-flex justify-content-between">
                    <span>${key}</span>
                    <span class="${val < 0 ? 'text-danger' : ''}">
                        Rp ${Math.abs(val).toLocaleString('id-ID')}
                    </span>
                </div>
            `;
            });
        }

        /* ================= MAIN ================= */

        function hitung() {
            const items = ambilItems();
            const globalConfig = ambilGlobal();
            const hasil = hitungSemua(items, globalConfig);

            render(hasil);
        }

        /* ================= CHECKBOX CONTROL ================= */

        document.getElementById('useDiskonGlobal').addEventListener('change', function() {
            document.getElementById('diskonGlobal').disabled = !this.checked;
            document.getElementById('diskonGlobalType').disabled = !this.checked;
            hitung();
        });

        document.getElementById('usePajakGlobal').addEventListener('change', function() {
            document.getElementById('pajakGlobal').disabled = !this.checked;
            hitung();
        });

        /* ================= EVENT ================= */

        document.getElementById('itemContainer')
            .addEventListener('input', hitung);

        document.getElementById('itemContainer')
            .addEventListener('change', hitung);

        document.getElementById('diskonGlobal')
            .addEventListener('input', hitung);

        document.getElementById('diskonGlobalType')
            .addEventListener('change', hitung);

        document.getElementById('pajakGlobal')
            .addEventListener('change', hitung);

        /* ================= TAMBAH ITEM ================= */

        document.getElementById('btnTambahItem').addEventListener('click', function() {

            const container = document.getElementById('itemContainer');
            const row = container.querySelector('.item-row').cloneNode(true);

            row.querySelectorAll('input').forEach(i => {
                if (!i.classList.contains('qty')) i.value = '';
            });

            row.querySelector('.qty').value = 1;
            row.querySelector('.diskon').value = 0;

            isiSelectPajak(row.querySelector('.pajak'));

            container.appendChild(row);
        });

        /* ================= HAPUS ITEM ================= */

        document.addEventListener('click', function(e) {
            if (e.target.closest('.btnRemove')) {
                const rows = document.querySelectorAll('.item-row');

                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                    hitung();
                }
            }
        });

    });
</script>

<script>
    $(document).ready(function() {

        // Init Select2 kontak
        $('#kontakSelect').select2({
            dropdownParent: $('#modalPengajuanBiaya'),
            placeholder: 'Cari Penerima...',
            allowClear: true,
            width: '100%'
        });

        // Load data setelah select2 aktif
        loadKontak();

    });
</script>