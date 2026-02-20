@extends('app.template')

@section('content')


<div class="card shadow-sm">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Total Biaya Bulan Ini</small>
                    <h5 class="fw-bold mb-0">
                        Rp {{ number_format($totalBulanIni ?? 0, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Biaya 30 Hari Ini</small>
                    <h5 class="fw-bold mb-0">
                        Rp {{ number_format($total30Hari ?? 0, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Biaya Belum Dibayar</small>
                    <h5 class="fw-bold mb-0 text-danger">
                        Rp {{ number_format($totalBelumBayar ?? 0, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Data Pembelian </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPembelian">
                <i class="bi bi-plus-circle"></i> Buat Pembelian
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. Pengajuan</th>
                        <th>Tanggal</th>
                        <th>Penerima</th>
                        <th>Metode</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Urgent</th>
                        <!-- <th width="160">Aksi</th> -->
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
@include('pages.finance.operasional.pembelian.modal-tambah-pembelian')
@include('pages.finance.operasional.biaya.modal-tambah-penerima')
@include('pages.finance.operasional.biaya.modal-detail-biaya')

<script>
    function rupiah(value) {
        return new Intl.NumberFormat('id-ID').format(value || 0);
    }

    document.addEventListener('click', function(e) {

        const btn = e.target.closest('.btnDetailPengajuan');
        if (!btn) return;

        e.preventDefault();
        const id = btn.dataset.id;

        const modal = new bootstrap.Modal(
            document.getElementById('modalDetailPengajuan')
        );

        // reset item
        document.getElementById('detailItemContainer').innerHTML =
            `<div class="text-center text-muted">Memuat data...</div>`;

        fetch(`/finance/pengajuan-biaya/detail/${id}`)
            .then(res => res.json())
            .then(res => {

                if (res.status !== 'success') {
                    alert(res.message || 'Gagal mengambil data');
                    return;
                }

                const {
                    header,
                    items
                } = res.data;

                // kirim nomor pengajuan ke button edit
                const btnEdit = document.getElementById('btnEditPengajuan');
                btnEdit.dataset.nomor = header.nomor_pengajuan;

                /* ========== HEADER ========== */
                document.getElementById('d_nomor').value = header.nomor_pengajuan;
                document.getElementById('d_tanggal').value = header.tgl_pengajuan;
                document.getElementById('d_metode').value = header.metode_pembayaran;
                document.getElementById('d_kontak').value = header.kontak_nama;

                document.getElementById('d_subtotal').textContent = rupiah(header.subtotal);
                document.getElementById('d_diskon').textContent = rupiah(header.total_diskon);
                document.getElementById('d_ppn').textContent = rupiah(header.total_ppn);
                document.getElementById('d_total').textContent = rupiah(header.grand_total);
                document.getElementById('d_grand_total').textContent =
                    'Rp ' + rupiah(header.grand_total);

                // urgent badge
                document.getElementById('d_urgent')
                    .classList.toggle('d-none', !header.is_urgent);

                // lampiran
                if (header.lampiran) {
                    document.getElementById('lampiranWrapper').style.display = 'block';
                    document.getElementById('d_lampiran').href = header.lampiran;
                } else {
                    document.getElementById('lampiranWrapper').style.display = 'none';
                }

                /* ========== ITEMS ========== */
                let html = '';

                items.forEach(item => {
                    html += `
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-md-3">
                                <input class="form-control" value="${item.deskripsi}" readonly>
                            </div>
                            <div class="col-md-1">
                                <input class="form-control" value="${item.qty}" readonly>
                            </div>
                            <div class="col-md-2">
                                <input class="form-control" value="${rupiah(item.harga)}" readonly>
                            </div>
                           <div class="col-md-2">
                                <input class="form-control text-end"
                                    value="${item.diskon ?? 0} %"
                                    readonly>
                            </div>
                            <div class="col-md-2">
                                <input class="form-control" value="${item.pajak_nama ?? '-'}" readonly>
                            </div>
                            <div class="col-md-2">
                                <input class="form-control text-end" value="${rupiah(item.jumlah)}" readonly>
                            </div>
                        </div>
                    `;
                });

                document.getElementById('detailItemContainer').innerHTML =
                    html || `<div class="text-center text-muted">Tidak ada item</div>`;

                modal.show();
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan');
            });
    });
</script>

@endsection