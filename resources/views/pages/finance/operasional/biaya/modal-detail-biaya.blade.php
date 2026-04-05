<style>
    .erp-loading {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.85);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
        backdrop-filter: blur(2px);
    }
</style>

<div class="modal fade" id="modalPengajuanDetailBiaya" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">

            <!-- HEADER -->
            <div class="modal-header bg-light border-0">
                <div>
                    <h5 class="fw-bold mb-1">
                        Pengajuan Biaya
                        <span id="detail_nomor_pengajuan" class="text-primary"></span>
                    </h5>

                    <div class="d-flex gap-2">
                        <span id="badge_status" class="badge bg-secondary">Status</span>
                        <span id="badge_urgent" class="badge bg-danger d-none">URGENT</span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary btnEditPengajuan"
                        id="btnEditFromDetail"
                        data-id="">
                        Edit
                    </button>

                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <!-- BODY -->
            <div class="modal-body position-relative">

                <!-- LOADING -->
                <div id="detail_loading" class="erp-loading d-none">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2 small text-muted">Memuat data...</div>
                </div>

                <div class="row">

                    <!-- LEFT -->
                    <div class="col-lg-8">

                        <!-- INFO -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="small text-muted">Tanggal</label>
                                        <div id="detail_tgl_pengajuan" class="fw-semibold"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small text-muted">Jenis</label>
                                        <div id="detail_jenis_pengajuan" class="fw-semibold"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small text-muted">Metode</label>
                                        <div id="detail_metode_pembayaran" class="fw-semibold"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="small text-muted">Kontak</label>
                                        <div id="detail_kontak_nama" class="fw-semibold"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="small text-muted">Project</label>
                                        <div id="detail_project" class="fw-semibold"></div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ITEMS -->
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-0">

                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Deskripsi</th>
                                                <th width="80">Qty</th>
                                                <th width="120">Harga</th>
                                                <th width="140">Diskon</th>
                                                <th width="140">Pajak</th>
                                                <th width="150">Jumlah</th>
                                            </tr>
                                        </thead>

                                        <tbody id="detail_items_table"></tbody>

                                    </table>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="col-lg-4">

                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body">

                                <h6 class="fw-bold mb-3">Ringkasan</h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="detail_subtotal"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Diskon Item</span>
                                    <span id="detail_diskon" class="text-danger"></span>
                                </div>

                                <!-- GLOBAL DINAMIS -->
                                <div id="detail_global_section"></div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Pajak</span>
                                    <span id="detail_pajak"></span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between fw-bold text-primary fs-5">
                                    <span>Total</span>
                                    <span id="detail_grand_total"></span>
                                </div>

                            </div>
                        </div>

                        <!-- LAMPIRAN -->
                        <div class="card border-0 shadow-sm rounded-4 mt-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Lampiran</h6>
                                <div id="preview_lampiran"></div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let masterLoaded = false;

    let kontakMap = {};
    let projectMap = {};
    let pajakMap = {};

    let modalDetail = null;

    document.addEventListener("DOMContentLoaded", function() {
        const modalEl = document.getElementById('modalPengajuanDetailBiaya');
        modalDetail = new bootstrap.Modal(modalEl);
    });

    async function loadMaster() {

        if (masterLoaded) return;

        const [kontakRes, projectRes, pajakRes] = await Promise.all([
            fetch("/finance/get/kontak"),
            fetch("/finance/get/project-gabungan"),
            fetch("/finance/get/coa-pajak")
        ]);

        const kontakData = await kontakRes.json();
        const projectData = await projectRes.json();
        const pajakData = await pajakRes.json();

        kontakData.forEach(k => kontakMap[k.id] = k.nama);
        projectData.forEach(p => projectMap[p.id] = p.label);
        pajakData.forEach(p => pajakMap[p.id] = p.nama_akun);

        masterLoaded = true;

    }

    function showLoading() {
        $('#detail_loading').removeClass('d-none');
    }

    function hideLoading() {
        $('#detail_loading').addClass('d-none');
    }


    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka || 0);
    }

    async function loadDetailPengajuan(id) {

        $('#detail_loading').removeClass('d-none');

        try {

            const res = await $.get(`/finance/pengajuan-biaya/detail/${id}`);

            let h = res.data.header;
            let items = res.data.items;

            /** ================= HEADER ================= */

            $('#detail_nomor_pengajuan').text(h.nomor_pengajuan);
            $('#detail_tgl_pengajuan').text(h.tgl_pengajuan);
            $('#detail_jenis_pengajuan').text(h.jenis_pengajuan);
            $('#detail_metode_pembayaran').text(h.metode_pembayaran);
            $('#detail_kontak_nama').text(h.kontak_nama ?? '-');
            $('#detail_project').text(projectMap[h.project_id] ?? '-');

            /** ================= STATUS ================= */

            $('#badge_status').text(h.status);
            $('#badge_urgent').toggleClass('d-none', !h.is_urgent);

            /** ================= TOTAL ================= */

            $('#detail_subtotal').text(formatRupiah(h.subtotal));

            // 🔥 DISKON ITEM (hanya tampil kalau TIDAK pakai global)
            if (!h.use_diskon_global) {
                $('#detail_diskon').text(formatRupiah(h.diskon_item));
            } else {
                $('#detail_diskon').text('—'); // biar tidak misleading
            }

            $('#detail_ppn').text(formatRupiah(h.total_pajak));
            $('#detail_grand_total').text(formatRupiah(h.grand_total));

            /** ================= GLOBAL SECTION ================= */

            let globalHtml = '';

            if (h.use_diskon_global) {
                globalHtml += `
            <div class="d-flex justify-content-between mb-2">
                <span>Diskon Global (${h.diskon_global_type === 'percent' ? '%' : 'Rp'})</span>
                <span class="text-danger">-${formatRupiah(h.diskon_global)}</span>
            </div>`;
            }

            if (h.use_pajak_global) {
                globalHtml += `
            <div class="d-flex justify-content-between mb-2">
                <span>Pajak Global</span>
                <span>${formatRupiah(h.pajak_global)}</span>
            </div>`;
            }

            $('#detail_global_section').html(globalHtml);

            /** ================= ITEMS ================= */

            let html = '';

            if (items.length === 0) {

                html = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    Tidak ada item
                </td>
            </tr>`;

            } else {

                items.forEach(item => {

                    let diskonText = item.diskon_type === 'percent' ?
                        item.diskon + '%' :
                        formatRupiah(item.diskon);

                    html += `
                <tr>
                    <td>${item.deskripsi ?? '-'}</td>
                    <td>${item.qty}</td>
                    <td>${formatRupiah(item.harga)}</td>
                    <td>${diskonText}</td>
                    <td>${item.nama_pajak ?? '-'}</td>
                    <td class="fw-semibold">${formatRupiah(item.jumlah)}</td>
                </tr>`;
                });

            }

            $('#detail_items_table').html(html);

            /** ================= LAMPIRAN ================= */

            if (h.lampiran) {
                $('#preview_lampiran').html(`
                <a href="${h.lampiran}" target="_blank"
                class="btn btn-sm btn-outline-primary w-100">
                    Lihat Lampiran
                </a>
            `);
            } else {
                $('#preview_lampiran').html(`<span class="text-muted">Tidak ada</span>`);
            }

            modalDetail.show();

        } catch (err) {

            console.error(err);

            alert('Gagal load data');

        } finally {

            $('#detail_loading').addClass('d-none');

        }
    }

    $(document).on('click', '.btnEditPengajuan', function() {

        let id = $(this).attr('data-id');

        console.log("ID diterima dari tombol:", id);

        loadEditPengajuan(id);

    });
</script>