<!-- MODAL Pembelian -->
<div class="modal fade" id="modalPembelian" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Form Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formPembelian" enctype="multipart/form-data">
                    @csrf
                    <div class="container-fluid">
                        <!-- ================= HEADER INFO ================= -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Jenis Pengajuan</label>
                                <input type="text" class="form-control" value="Pembelian" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select class="form-select" name="metode_pembayaran">
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Jenis Aset</label>
                                <select class="form-select" name="jenis_aset">
                                    <option value="">Pilih</option>
                                    <option value="asset">Asset</option>
                                    <option value="non_asset">Non Asset</option>
                                </select>
                            </div>

                            <div class="col-md-3 text-end">
                                <label class="form-label fw-bold">Sisa Tagihan</label>
                                <div class="fs-5 fw-bold text-danger">
                                    Rp. <span id="sisaTagihan">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- ================= SUPPLIER ================= -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Supplier</label>
                                        <select class="form-select" name="supplier_id">
                                            <option value="">Pilih Supplier</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Alamat</label>
                                        <input type="text" class="form-control" name="alamat">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">No HP</label>
                                        <input type="text" class="form-control" name="no_hp">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= TANGGAL ================= -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Tgl Pengajuan</label>
                                <input type="date" class="form-control" name="tgl_pengajuan">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tgl Jatuh Tempo</label>
                                <input type="date" class="form-control" name="tgl_jatuh_tempo">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Syarat Pembayaran</label>
                                <select class="form-select" name="syarat_pembayaran">
                                    <option value="">Pilih</option>
                                    <option value="lunas">Lunas</option>
                                    <option value="tempo">Tempo</option>
                                </select>
                            </div>
                        </div>

                        <!-- ================= PRODUK ================= -->
                        <div class="card mb-3">
                            <div class="card-header fw-bold d-flex justify-content-between">
                                Detail Produk
                                <button type="button" class="btn btn-sm btn-primary">+ Tambah</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Deskripsi</th>
                                            <th>Qty</th>
                                            <th>Satuan</th>
                                            <th>Harga Satuan</th>
                                            <th>Diskon</th>
                                            <th>Pajak</th>
                                            <th>Jumlah</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-select">
                                                    <option>Pilih</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control"></td>
                                            <td><input type="number" class="form-control text-end"></td>
                                            <td><input type="text" class="form-control"></td>
                                            <td><input type="number" class="form-control text-end"></td>
                                            <td><input type="number" class="form-control text-end"></td>
                                            <td>
                                                <select class="form-select">
                                                    <option>PPN</option>
                                                    <option>Non PPN</option>
                                                </select>
                                            </td>
                                            <td class="text-end fw-bold">Rp 0</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-danger">✕</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ================= RINGKASAN ================= -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Lampiran</label>
                                <input type="file" class="form-control" name="lampiran">
                            </div>

                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th class="text-end">Subtotal</th>
                                        <td class="text-end">Rp. <span id="subtotal">0</span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-end">Diskon</th>
                                        <td class="text-end">Rp. <span id="diskon">0</span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-end">PPN</th>
                                        <td class="text-end">Rp. <span id="ppn">0</span></td>
                                    </tr>
                                    <tr class="fw-bold fs-5">
                                        <th class="text-end">Total</th>
                                        <td class="text-end">Rp. <span id="total">0</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- ================= UANG MUKA ================= -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="uangMuka">
                                            <label class="form-check-label fw-bold">
                                                Uang Muka
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Nominal</label>
                                        <input type="number" class="form-control" name="uang_muka">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Sumber Bank</label>
                                        <select class="form-select" name="bank">
                                            <option>Saldo Mandiri</option>
                                            <option>Saldo BCA</option>
                                            <option>Saldo BRI</option>
                                            <option>Saldo Petty Cash</option>
                                            <option>Saldo Paybank</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 text-end fw-bold fs-5">
                                        Sisa Tagihan<br>
                                        Rp. <span id="sisaAkhir">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </form>
            </div>

            <div class="modal-footer justify-content-end">
                <button type="submit" form="formPembelian" class="btn btn-success px-4">
                    Buat
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('input', function(e) {

        if (
            e.target.classList.contains('qty') ||
            e.target.classList.contains('harga') ||
            e.target.classList.contains('diskon') ||
            e.target.classList.contains('pajak') ||
            e.target.id === 'uangMukaNominal'
        ) {
            hitungSemua();
        }

    });

    function hitungSemua() {
        let subtotal = 0;
        let totalPPN = 0;

        document.querySelectorAll('tbody tr').forEach(row => {

            const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
            const harga = parseFloat(row.querySelector('.harga')?.value) || 0;
            const diskon = parseFloat(row.querySelector('.diskon')?.value) || 0;
            const pajak = row.querySelector('.pajak')?.value;

            let jumlah = (qty * harga) - diskon;
            if (jumlah < 0) jumlah = 0;

            subtotal += jumlah;

            if (pajak === 'ppn') {
                totalPPN += jumlah * 0.11;
            }

            row.querySelector('.item-jumlah').innerText = formatRupiah(jumlah);
        });

        const total = subtotal + totalPPN;
        const uangMuka = parseFloat(document.getElementById('uangMukaNominal')?.value) || 0;
        const sisa = total - uangMuka;

        // Inject ke UI
        setText('subtotal', subtotal);
        setText('ppn', totalPPN);
        setText('total', total);
        setText('sisaTagihan', sisa);
        setText('sisaAkhir', sisa);
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.innerText = formatRupiah(value);
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(Math.round(angka));
    }
</script>

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
    document.getElementById('formPembelian').addEventListener('submit', function(e) {
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