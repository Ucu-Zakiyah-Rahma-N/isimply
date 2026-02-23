<!-- MODAL PENGAJUAN BIAYA -->
<div class="modal fade" id="modalPengajuanBiaya" tabindex="-1">
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
                    <div class="row mb-4 align-items-start">
                        <div class="col-md-8">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" name="metode_pembayaran" required>
                                        <option value="">Pilih</option>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Penerima</label>
                                    <div class="input-group">
                                        <select class="form-select" name="kontak_id" required>
                                            <option value="">Pilih Penerima</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary">
                                            + Tambah
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Pengajuan</label>
                                    <input type="date" class="form-control" name="tgl_pengajuan" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Referensi Proyek</label>
                                    <select class="form-select" name="referensi_proyek_id">
                                        <option value="">Pilih Proyek</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <!-- TOTAL BESAR -->
                        <div class="col-md-4 text-end">
                            <div class="border rounded p-3">
                                <div class="form-check form-switch mb-2 text-start">
                                    <input class="form-check-input" type="checkbox" name="is_urgent" value="1">
                                    <label class="form-check-label">Urgent</label>
                                </div>
                                <h3 class="fw-bold mt-3">
                                    Total : <br>
                                    <span id="grandTotal">Rp 0</span>
                                </h3>
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
                                    <input type="text" class="form-control" name="deskripsi[]" required>
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
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Lampiran</label>
                            <input type="file" class="form-control" name="lampiran">
                        </div>

                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">Rp <span id="subtotal">0</span></td>
                                </tr>
                                <tr>
                                    <td>Diskon</td>
                                    <td class="text-end">Rp <span id="totalDiskon">0</span></td>
                                </tr>
                                <tr>
                                    <td>PPN</td>
                                    <td class="text-end">Rp <span id="totalPPN">0</span></td>
                                </tr>
                                <tr class="fw-bold fs-5">
                                    <td>Total</td>
                                    <td class="text-end">Rp <span id="summaryTotal">0</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer justify-content-end">
                <button type="submit" form="formPengajuan" class="btn btn-success px-4">
                    Buat
                </button>
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
    let pajakList = [];

    document.addEventListener('DOMContentLoaded', function() {
        fetch("{{ url('/finance/get/coa-pajak') }}", {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                pajakList = data;
                isiSelectPajak(document.querySelector('.pajak'));
            })
            .catch(err => console.error(err));
    });

    function isiSelectPajak(select) {
        select.innerHTML = `<option value="">Non Pajak</option>`;

        pajakList.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.text = `${item.nama_akun} (${item.nilai_coa}%)`;
            option.dataset.nilai = item.nilai_coa;
            select.appendChild(option);
        });
    }
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
    document.addEventListener('DOMContentLoaded', function() {

        const modalPengajuanEl = document.getElementById('modalPengajuanBiaya');
        const modalKontakEl = document.getElementById('modalTambahKontak');
        const btnOpenKontak = document.getElementById('btnOpenKontak');

        if (!modalKontakEl) {
            console.error('modalTambahKontak TIDAK ditemukan');
            return;
        }

        const modalPengajuan = new bootstrap.Modal(modalPengajuanEl, {
            backdrop: 'static',
            keyboard: false
        });

        const modalKontak = new bootstrap.Modal(modalKontakEl, {
            backdrop: 'static',
            keyboard: false
        });

        btnOpenKontak.addEventListener('click', function() {
            modalKontak.show();
        });

    });
</script>
<script>
    document.getElementById('formPengajuan').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        fetch("{{ route('finance.pengajuan-biaya.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) throw res;
                return res.json();
            })
            .then(res => {
                Swal.fire('Berhasil', 'Pengajuan biaya berhasil disimpan', 'success');
                form.reset();
                $('#modalPengajuanBiaya').modal('hide');
            })
            .catch(async err => {
                let msg = 'Terjadi kesalahan server';
                if (err.json) {
                    const e = await err.json();
                    msg = e.message ?? msg;
                }
                Swal.fire('Error', msg, 'error');
                console.error(err);
            });
    });
</script>