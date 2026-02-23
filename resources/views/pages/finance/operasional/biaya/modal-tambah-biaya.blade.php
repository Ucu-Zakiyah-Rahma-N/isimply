<style>
    /* Wrapper seperti input-group */
    .select2-group {
        display: flex;
        align-items: stretch;
    }

    /* Container select2 */
    .select2-group .select2-container {
        flex: 1;
    }

    /* Styling select2 agar sama dengan input-saas */
    .select2-container--default .select2-selection--single {
        height: 42px;
        border-radius: 14px 0 0 14px;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        padding-left: 12px;
    }

    /* Hilangkan border kanan supaya menyatu */
    .select2-container--default .select2-selection--single {
        border-right: none;
    }

    /* Tombol tambah menyatu */
    .select2-group .btn-saas-add {
        border-radius: 0 14px 14px 0;
        height: 42px;
        border: 1px solid #e5e7eb;
        border-left: none;
        background: #f1f3f5;
    }

    .select2-group .btn-saas-add:hover {
        background: #e9ecef;
    }

    /* Focus effect */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    /* Label lebih soft */
    .label-saas {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 6px;
    }

    /* Input Modern */
    .input-saas {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        padding: 10px 14px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .input-saas:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    /* Button + kecil modern */
    .btn-saas-add {
        border-radius: 12px;
        font-weight: 600;
        background: #f1f3f5;
        border: none;
        width: 42px;
    }

    .btn-saas-add:hover {
        background: #e9ecef;
    }

    /* Card look */
    .card {
        background: #ffffff;
    }

    /* Total Card */
    .total-card {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border: 1px solid #f1f3f5;
    }

    .total-amount {
        font-size: 1.8rem;
        letter-spacing: 0.5px;
        color: #111827;
    }

    /* Urgent label */
    .urgent-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #dc3545;
    }

    /* Modal feel premium */
    .modal-content {
        border-radius: 24px;
        border: none;
    }
</style>
<!-- MODAL PENGAJUAN BIAYA -->
<div class="modal fade" id="modalPengajuanBiaya">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Form Pengajuan Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formPengajuan" enctype="multipart/form-data">
                    @csrf

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
                                            <option>Biaya</option>
                                            <option>Pengeluaran</option>
                                        </select>
                                    </div>

                                    <!-- Penerima -->
                                    <div class="col-md-4">
                                        <label class="form-label label-saas">Penerima</label>

                                        <div class="select2-group">
                                            <select id="kontakSelect"
                                                name="kontak_id"
                                                class="form-select">
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
                                            <option>Cash</option>
                                            <option>Transfer</option>
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
                                        <select class="form-select input-saas" id="projectSelect" name="project_id"></select>
                                        <input type="hidden" name="jenis_project" id="jenisProject">
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
                                <span>Rp <span id="totalDiskon">0</span></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span>PPN</span>
                                <span>Rp <span id="totalPPN">0</span></span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between fw-bold fs-4">
                                <span>Total</span>
                                <span>Rp <span id="summaryTotal">0</span></span>
                            </div>

                            <div class="text-end mt-3">
                                <button class="btn btn-success px-5">
                                    Buat
                                </button>
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
    $(document).ready(function() {

        $('#projectSelect').select2({
            dropdownParent: $('#modalPengajuanBiaya'), // modal parent
            placeholder: 'Pilih Project',
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ url('/finance/get/project-gabungan') }}",
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    // map data agar sesuai format Select2
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.label,
                                jenis: item.jenis_project
                            };
                        })
                    };
                }
            }
        });

        // Set hidden field saat project dipilih
        $('#projectSelect').on('select2:select', function(e) {
            const data = e.params.data;
            $('#jenisProject').val(data.jenis);
        });

        // Kosongkan hidden jika project di-clear
        $('#projectSelect').on('select2:clear', function() {
            $('#jenisProject').val('');
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        let pajakList = [];

        /* ================= LOAD PAJAK ================= */
        fetch("{{ url('/finance/get/coa-pajak') }}", {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                pajakList = data;
                document.querySelectorAll('.pajak').forEach(isiSelectPajak);
            });

        function isiSelectPajak(select) {
            select.innerHTML = `<option value="0">Non Pajak</option>`;
            pajakList.forEach(pajak => {
                const opt = document.createElement('option');
                opt.value = pajak.id;
                opt.textContent = `${pajak.nama_akun} (${pajak.nilai_coa}%)`;
                opt.dataset.nilai = pajak.nilai_coa;
                select.appendChild(opt);
            });
        }

        /* ================= HITUNG ================= */
        function hitungSemua() {
            let subtotal = 0;
            let totalDiskon = 0;
            let totalPPN = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
                const harga = parseFloat(row.querySelector('.harga')?.value) || 0;
                const diskon = parseFloat(row.querySelector('.diskon')?.value) || 0;

                const pajakSelect = row.querySelector('.pajak');
                const pajakPersen = parseFloat(
                    pajakSelect?.options[pajakSelect.selectedIndex]?.dataset.nilai
                ) || 0;

                const total = qty * harga;
                const nilaiDiskon = total * (diskon / 100);
                const setelahDiskon = total - nilaiDiskon;
                const nilaiPPN = setelahDiskon * (pajakPersen / 100);
                const jumlah = setelahDiskon + nilaiPPN;

                row.querySelector('.jumlah').value =
                    jumlah.toLocaleString('id-ID');

                subtotal += total;
                totalDiskon += nilaiDiskon;
                totalPPN += nilaiPPN;
            });

            const grandTotal = subtotal - totalDiskon + totalPPN;

            document.getElementById('subtotal').innerText =
                subtotal.toLocaleString('id-ID');
            document.getElementById('totalDiskon').innerText =
                totalDiskon.toLocaleString('id-ID');
            document.getElementById('totalPPN').innerText =
                totalPPN.toLocaleString('id-ID');
            document.getElementById('summaryTotal').innerText =
                grandTotal.toLocaleString('id-ID');
            document.getElementById('grandTotal').innerText =
                'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        document.addEventListener('input', hitungSemua);
        document.addEventListener('change', hitungSemua);

        /* ================= TAMBAH ITEM ================= */
        document.getElementById('btnTambahItem').addEventListener('click', function() {
            const container = document.getElementById('itemContainer');
            const row = container.querySelector('.item-row').cloneNode(true);

            row.querySelectorAll('input').forEach(i => {
                if (!i.classList.contains('qty')) i.value = '';
            });

            row.querySelector('.qty').value = 1;
            row.querySelector('.diskon').value = 0;
            row.querySelector('.jumlah').value = '';

            isiSelectPajak(row.querySelector('.pajak'));

            container.appendChild(row);
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

    });
</script>

<script>
    $('#kontakSelect').select2({
        dropdownParent: $('#modalPengajuanBiaya'),
        placeholder: 'Cari Penerima...',
        allowClear: true,
        width: '100%'
    });
</script>