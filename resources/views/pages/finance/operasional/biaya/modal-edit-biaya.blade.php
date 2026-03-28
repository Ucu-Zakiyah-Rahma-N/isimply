<!-- ================= MODAL EDIT PENGAJUAN ================= -->
<div class="modal fade" id="modalEditPengajuan" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            <form id="formEditPengajuan">

                <!-- ================= HEADER ================= -->

                <div class="modal-header bg-light border-bottom">

                    <h5 class="fw-bold mb-0">
                        Edit Pengajuan
                    </h5>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>

                </div>


                <!-- ================= BODY ================= -->

                <div class="modal-body">

                    <input type="hidden" id="edit_id" name="id">

                    <!-- ================= INFORMASI HEADER ================= -->

                    <div class="card border-0 shadow-sm rounded-3 mb-4">

                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Jenis Pengajuan</label>
                                    <select id="edit_jenis_pengajuan"
                                        class="form-select" name="jenis_pengajuan">

                                        <option value="biaya">Biaya</option>
                                        <option value="pengeluaran">Pengeluaran</option>

                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Penerima</label>

                                    <select id="edit_kontak_id"
                                        class="form-select" name="kontak_id"></select>

                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Metode Pembayaran</label>

                                    <select id="edit_metode_pembayaran"
                                        class="form-select" name="metode_pembayaran">

                                        <option value="transfer">Transfer</option>
                                        <option value="cash">Cash</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Tanggal Pengajuan</label>
                                    <input type="date"
                                        id="edit_tgl_pengajuan"
                                        class="form-control" name="tanggal_pengajuan">
                                </div>

                                

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Projek</label>

                                    <select id="edit_project_id"
                                        class="form-select" name="project_id"></select>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================= TABEL ITEM BIAYA ================= -->

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">

                            <h6 class="fw-bold mb-0">
                                Detail Biaya
                            </h6>

                            <button type="button"
                                class="btn btn-sm btn-primary"
                                id="btnAddItem">

                                <i class="fas fa-plus"></i>
                                Tambah Item

                            </button>

                        </div>


                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0"
                                    id="tableEditItems">

                                    <thead class="table-light">

                                        <tr>

                                            <th width="28%">Deskripsi</th>
                                            <th width="7%">Qty</th>
                                            <th width="13%">Harga</th>
                                            <th width="10%">Diskon / Item</th>
                                            <th width="12%">Total Diskon</th>
                                            <th width="15%">Pajak</th>
                                            <th width="12%" class="text-end">Jumlah</th>
                                            <th width="3%"></th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <!-- Dynamic Items -->

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- ================= TOTAL ================= -->

                        <div class="card-footer bg-white">

                            <div class="row">

                                <div class="col-md-6"></div>

                                <div class="col-md-6">

                                    <table class="table table-sm">

                                        <tr>
                                            <td>Subtotal</td>
                                            <td class="text-end fw-semibold" id="edit_subtotal">Rp 0</td>
                                        </tr>

                                        <tr>
                                            <td>Total Diskon</td>
                                            <td class="text-end fw-semibold" id="edit_total_diskon">Rp 0</td>
                                        </tr>

                                        <tr>
                                            <td>Total Pajak</td>
                                            <td class="text-end fw-semibold" id="edit_total_pajak">Rp 0</td>
                                        </tr>

                                        <tr class="table-light">

                                            <td class="fw-bold">
                                                Grand Total
                                            </td>

                                            <td class="text-end fw-bold fs-5 text-primary"
                                                id="edit_grand_total">

                                                Rp 0

                                            </td>

                                        </tr>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================= FOOTER ================= -->

                <div class="modal-footer border-top">

                    <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button type="submit"
                        class="btn btn-primary">

                        <i class="fas fa-save"></i>
                        Simpan Perubahan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
    let masterLoadedEdit = false;
    let kontakMapEdit = {};
    let projectMapEdit = {};
    let pajakMapEdit = {}; // {id: {nama_akun, nilai_coa}}

    function formatRupiah(angka) {
        let n = parseFloat(angka) || 0;
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    function toNumber(val) {
        if (!val) return 0;
        return parseFloat(String(val).replace(/[^\d.-]/g, '')) || 0;
    }

    async function loadMaster() {

        if (masterLoadedEdit) return;

        const [kontakRes, projectRes, pajakRes] = await Promise.all([
            fetch("/finance/get/kontak"),
            fetch("/finance/get/project-gabungan"),
            fetch("/finance/get/coa-pajak")
        ]);

        const kontakData = await kontakRes.json();
        const projectData = await projectRes.json();
        const pajakData = await pajakRes.json();

        kontakData.forEach(k => {
            kontakMapEdit[k.id] = k.nama;
        });

        projectData.forEach(p => {
            projectMapEdit[p.id] = p.label;
        });

        pajakData.forEach(p => {
            pajakMapEdit[p.id] = {
                nama: p.nama_akun,
                persen: parseFloat(p.nilai_coa) || 0
            };
        });

        populateKontak();
        populateProject();

        masterLoadedEdit = true;
    }

    function populateKontak(selected = null) {

        const select = $('#edit_kontak_id');
        select.empty();

        Object.entries(kontakMapEdit).forEach(([id, nama]) => {
            select.append(`<option value="${id}">${nama}</option>`);
        });

        if (selected) select.val(selected).trigger('change');
    }

    function populateProject(selected = null) {

        const select = $('#edit_project_id');
        select.empty();

        Object.entries(projectMapEdit).forEach(([id, label]) => {
            select.append(`<option value="${id}">${label}</option>`);
        });

        if (selected) select.val(selected).trigger('change');
    }

    function renderPajakOptions(selected = 0) {

        let html = `<option value="0">Non Pajak</option>`;

        Object.entries(pajakMapEdit).forEach(([id, p]) => {

            let selectedAttr = selected == id ? 'selected' : '';

            html += `
        <option value="${id}" ${selectedAttr}>
            ${p.nama} (${p.persen}%)
        </option>`;
        });

        return html;
    }

    function renderPajakOptions(selected = 0) {

        let html = `<option value="0">Non Pajak</option>`;

        Object.entries(pajakMapEdit).forEach(([id, p]) => {

            let selectedAttr = selected == id ? 'selected' : '';

            html += `
        <option value="${id}" ${selectedAttr}>
            ${p.nama} (${p.persen}%)
        </option>`;
        });

        return html;
    }

    function createItemRow(item = {}) {

        return `
<tr>

<input type"hidden" name="item_id[]" class="form-control form-control-sm "
value="${item.item_id || ''}">

<td>
<input class="form-control form-control-sm deskripsi"
value="${item.deskripsi || ''}" name="deskripsi[]">
</td>

<td>
<input type="number"
class="form-control form-control-sm qty"
value="${item.qty || 1}" name="qty[]">
</td>

<td>
<input type="number"
class="form-control form-control-sm harga"
value="${item.harga || 0}" name="harga[]">
</td>

<td>
<input type="number"
class="form-control form-control-sm diskon"
value="${item.diskon || 0}" name="diskon[]">
</td>

<td>
<select class="form-select form-select-sm pajak" name="pajak_id[]">
${renderPajakOptions(item.pajak_id)}
</select>
</td>

<td class="text-end jumlah">
Rp 0
</td>

<td class="text-center">
<button type="button"
    class="btn btn-sm btn-danger btnDeleteItem"
    data-id="${item.item_id || 0}">
    <i class="bi bi-trash"></i>
</button>
</td>

</tr>
`;
    }

    function renderItems(items) {

        const tbody = $('#tableEditItems tbody');
        tbody.empty();

        if (!items || items.length === 0) {
            tbody.append(createItemRow());
            return;
        }

        items.forEach(item => {

            tbody.append(createItemRow(item));

        });

        hitungSemua();
    }

    $(document).on('click', '#btnAddItem', function() {

        $('#tableEditItems tbody').append(createItemRow());

    });

    function hitungSemua() {

        let subtotal = 0;
        let totalDiskon = 0;
        let totalPajak = 0;

        $('#tableEditItems tbody tr').each(function() {

            const row = $(this);

            let qty = toNumber(row.find('.qty').val());
            let harga = toNumber(row.find('.harga').val());
            let diskon = toNumber(row.find('.diskon').val()); // persen
            let pajakId = row.find('.pajak').val();

            let total = qty * harga;

            // ✅ DISKON PERSEN
            let nilaiDiskon = total * (diskon / 100);

            let afterDiskon = total - nilaiDiskon;

            let pajakPersen = pajakMapEdit[pajakId]?.persen || 0;
            let nilaiPajak = afterDiskon * (pajakPersen / 100);

            let jumlah = afterDiskon + nilaiPajak;

            // ================= AKUMULASI =================
            subtotal += total;
            totalDiskon += nilaiDiskon;
            totalPajak += nilaiPajak;

            // ================= RENDER =================
            row.find('.jumlah').text(formatRupiah(jumlah));

        });

        let grandTotal = subtotal - totalDiskon + totalPajak;

        $('#edit_subtotal').text(formatRupiah(subtotal));
        $('#edit_total_diskon').text(formatRupiah(totalDiskon));
        $('#edit_total_pajak').text(formatRupiah(totalPajak));
        $('#edit_grand_total').text(formatRupiah(grandTotal));
    }

    $(document).on('input change',
        '.qty, .harga, .diskon, .pajak',
        function() {

            hitungSemua();

        });

    let currentEditRequest = null;

    async function loadEditPengajuan(id) {

        try {

            await loadMaster();

            // ❗ cancel request sebelumnya
            if (currentEditRequest) {
                currentEditRequest.abort();
            }

            currentEditRequest = $.ajax({
                url: "{{ route('finance.pengajuan-biaya.get-edit', ':id') }}"
                    .replace(':id', id),
                method: 'GET',
                cache: false
            });

            const res = await currentEditRequest;

            if (res.status !== 'success') {
                throw new Error('Data tidak ditemukan');
            }

            let header = res.data.header;

            // ❗ reset dulu biar bersih
            $('#formEditPengajuan')[0].reset();
            $('#tableEditItems tbody').empty();

            $('#edit_id').val(header.id);
            $('#edit_tgl_pengajuan').val(header.tgl_pengajuan);
            $('#edit_jenis_pengajuan').val(header.jenis_pengajuan);
            $('#edit_metode_pembayaran').val(header.metode_pembayaran);

            populateKontak(header.kontak_id);
            populateProject(header.project_id);

            renderItems(res.data.items);

            $('#modalEditPengajuan').modal('show');

        } catch (err) {

            if (err.statusText === 'abort') return; // ❗ abaikan request lama

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal memuat data pengajuan'
            });

            console.error(err);
        }
    }

    $('#formEditPengajuan').on('submit', function(e) {
        e.preventDefault();

        const id = $('#edit_id').val();
        const formData = new FormData(this);

        fetch("{{ route('finance.pengajuan-biaya.update', ':id') }}".replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                Swal.fire('Berhasil', res.message, 'success');
                $('#modalEditPengajuan').modal('hide');
            })
            .catch(err => {
                Swal.fire('Error', 'Gagal update', 'error');
            });
    });

    $(document).on('click', '.btnDeleteItem', function() {

        const btn = $(this);
        const row = btn.closest('tr');
        const itemId = btn.data('id');

        Swal.fire({
            title: 'Yakin?',
            text: 'Item ini akan dihapus',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {

            // ❗ HANYA JALAN JIKA CONFIRM
            if (!result.isConfirmed) return;

            // JIKA ADA DI DB
            if (itemId && itemId != 0) {

                fetch(`/finance/pengajuan-biaya/delete-item/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw res;
                        return res.json();
                    })
                    .then(() => {

                        row.remove();
                        hitungSemua();

                        Swal.fire('Berhasil', 'Item dihapus', 'success');

                    });

            } else {

                // FRONTEND ONLY
                row.remove();
                hitungSemua();

            }

        });

    });
</script>