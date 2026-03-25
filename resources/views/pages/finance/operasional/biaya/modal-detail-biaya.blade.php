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

<!-- ================= MODAL DETAIL ================= -->
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

                <div class="d-flex align-items-center gap-2">

                    <button class="btn btn-sm btn-primary btnEditPengajuan"
                        id="btnEditFromDetail"
                        data-id="">
                        Edit</button>

                    <button class="btn-close" data-bs-dismiss="modal"></button>

                </div>

            </div>

            <!-- BODY -->
            <div class="modal-body position-relative">

                <!-- LOADING -->
                <div id="detail_loading" class="erp-loading d-none">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2 small text-muted">Memuat data pengajuan...</div>
                </div>

                <div class="row">

                    <!-- LEFT -->
                    <div class="col-lg-8">

                        <!-- INFO -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body">

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="text-muted small">Tanggal Pengajuan</label>
                                        <div class="fw-semibold" id="detail_tgl_pengajuan"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="text-muted small">Jenis Pengajuan</label>
                                        <div class="fw-semibold" id="detail_jenis_pengajuan"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="text-muted small">Metode Pembayaran</label>
                                        <div class="fw-semibold" id="detail_metode_pembayaran"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="text-muted small">Kontak</label>
                                        <div class="fw-semibold" id="detail_kontak_nama"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="text-muted small">Project</label>
                                        <div class="fw-semibold" id="detail_project"></div>
                                    </div>

                                </div>

                            </div>
                        </div>


                        <!-- ITEMS -->
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-0">

                                <div class="table-responsive">

                                    <table class="table table-hover align-middle mb-0">

                                        <thead class="table-light">
                                            <tr>
                                                <th>Deskripsi</th>
                                                <th width="80">Qty</th>
                                                <th width="140">Harga</th>
                                                <th width="140">Diskon</th>
                                                <th width="140">Pajak</th>
                                                <th width="160">Jumlah</th>
                                            </tr>
                                        </thead>

                                        <tbody id="detail_items_table">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    Pilih pengajuan untuk melihat detail
                                                </td>
                                            </tr>
                                        </tbody>

                                    </table>

                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- RIGHT -->
                    <div class="col-lg-4">

                        <!-- TOTAL -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body">

                                <h6 class="fw-bold mb-3">Ringkasan Biaya</h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="detail_subtotal"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Diskon</span>
                                    <span id="detail_diskon"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Pajak</span>
                                    <span id="detail_ppn"></span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between fw-bold fs-5 text-primary">
                                    <span>Total</span>
                                    <span id="detail_grand_total"></span>
                                </div>

                            </div>
                        </div>


                        <!-- LAMPIRAN -->
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body">

                                <h6 class="fw-bold mb-3">Lampiran</h6>

                                <div id="preview_lampiran" class="text-muted">
                                    Tidak ada lampiran
                                </div>

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

    function formatRupiah(angka) {

        if (!angka) angka = 0;

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);

    }

    function showLoading() {
        $('#detail_loading').removeClass('d-none');
    }

    function hideLoading() {
        $('#detail_loading').addClass('d-none');
    }


    async function loadDetailPengajuan(id) {

        showLoading();

        $('#detail_items_table').html(`
        <tr>
        <td colspan="6" class="text-center text-muted py-4">
        Memuat item...
        </td>
        </tr>
        `);

        try {

            await loadMaster();

            const res = await $.get(`/finance/pengajuan-biaya/detail/${id}`);

            if (res.status !== 'success') {
                throw new Error('Data tidak ditemukan');
            }

            let header = res.data.header;
            let items = res.data.items;

            // set id tombol edit
            $('#btnEditFromDetail').attr('data-id', header.id);

            // HEADER
            $('#detail_nomor_pengajuan').text(header.nomor_pengajuan);
            $('#detail_tgl_pengajuan').text(header.tgl_pengajuan);
            $('#detail_jenis_pengajuan').text(header.jenis_pengajuan);
            $('#detail_metode_pembayaran').text(header.metode_pembayaran);

            $('#detail_kontak_nama').text(
                kontakMap[header.kontak_id] ?? header.kontak_nama ?? '-'
            );

            $('#detail_project').text(
                projectMap[header.project_id] ?? '-'
            );


            // STATUS
            let statusClass = 'bg-secondary';

            if (header.status === 'disetujui') statusClass = 'bg-success';
            if (header.status === 'ditolak') statusClass = 'bg-danger';
            if (header.status === 'pending') statusClass = 'bg-warning';

            $('#badge_status')
                .removeClass()
                .addClass(`badge ${statusClass}`)
                .text(header.status);


            // URGENT
            if (header.is_urgent) {
                $('#badge_urgent').removeClass('d-none');
            } else {
                $('#badge_urgent').addClass('d-none');
            }


            // TOTAL
            $('#detail_subtotal').text(formatRupiah(header.subtotal));
            $('#detail_diskon').text(formatRupiah(header.total_diskon));
            $('#detail_ppn').text(formatRupiah(header.total_ppn));
            $('#detail_grand_total').text(formatRupiah(header.grand_total));


            // ITEMS
            let html = '';

            if (items.length === 0) {

                html = `
                <tr>
                <td colspan="6" class="text-center text-muted py-4">
                Tidak ada item pengajuan
                </td>
                </tr>
                `;

            } else {

                items.forEach(item => {

                    html += `
                    <tr>
                    <td>${item.deskripsi ?? '-'}</td>
                    <td>${item.qty ?? 0}</td>
                    <td>${formatRupiah(item.harga)}</td>
                    <td>${item.diskon ?? 0}%</td>
                    <td>${item.nama_pajak ?? '-'}</td>
                    <td class="fw-semibold">${formatRupiah(item.jumlah)}</td>
                    </tr>
                    `;

                });

            }

            $('#detail_items_table').html(html);


            // LAMPIRAN
            if (header.lampiran) {

                $('#preview_lampiran').html(`
                <a href="${header.lampiran}" target="_blank"
                class="btn btn-sm btn-outline-primary w-100">
                Lihat Lampiran
                </a>
                `);

            } else {

                $('#preview_lampiran').html(`
                <span class="text-muted">Tidak ada lampiran</span>
                `);

            }

            modalDetail.show();

        } catch (err) {

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal memuat detail pengajuan',
                confirmButtonColor: '#3085d6'
            });

            console.error(err);

        } finally {

            hideLoading();

        }

    }

    $(document).on('click', '.btnEditPengajuan', function() {

        let id = $(this).data('id');

        console.log("ID diterima dari tombol:", id);

        loadEditPengajuan(id);

    });
</script>