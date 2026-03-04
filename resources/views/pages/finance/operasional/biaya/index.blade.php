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
            <h5 class="mb-0 fw-semibold">Data Biaya </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPengajuanBiaya">
                <i class="bi bi-plus-circle"></i> Buat Biaya Baru
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
                    @forelse($data as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-bold">
                            <!-- <button
                                class="btn btn-info btn-sm"
                                onclick="showDetailPengajuan({{ $row->id }})">
                                Detail
                            </button> -->
                            <a href="javascript:void(0)"
                                class="text-decoration-none text-primary btnDetailPengajuan"
                                data-id="{{ $row->id }}">
                                {{ $row->nomor_pengajuan }}
                            </a>
                        </td>
                        <td>{{ $row->tgl_pengajuan->format('d/m/Y') }}</td>
                        <td>{{ optional($row->kontak)->nama ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst($row->metode_pembayaran) }}
                            </span>
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($row->grand_total, 0, ',', '.') }}
                        </td>
                        @php
                        $statusClass = [
                        'proses di purchasing' => 'bg-primary',
                        'dijadwalkan' => 'bg-warning',
                        'disetujui' => 'bg-success',
                        'ditolak' => 'bg-danger'
                        ];
                        @endphp

                        <td>
                            <span class="badge {{ $statusClass[$row->status] ?? 'bg-secondary' }}">
                                {{ $row->status ?? 'proses di purcashasing' }}
                            </span>
                        </td>
                        <td>
                            @if($row->is_urgent)
                            <span class="badge text-danger fw-bold">Urgent</span>
                            @else
                            <span class="badge text-secondary">Normal</span>
                            @endif
                        </td>
                        <td>
                            <!-- <button class="btn btn-sm btn-info btnDetail"
                                data-id="{{ $row->id }}">
                                Detail
                            </button>

                            @if($row->lampiran) -->
                            <!-- <a href="{{ asset('storage/'.$row->lampiran) }}"
                                target="_blank"
                                class="btn btn-sm btn-secondary">
                                Lampiran
                            </a>
                            @endif -->
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Belum ada pengajuan biaya
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('pages.finance.operasional.biaya.modal-tambah-biaya')
@include('pages.finance.operasional.biaya.modal-tambah-penerima')

<script>
    window.appRoutes = {
        storeKontak: "{{ route('finance.kontak.store') }}",
        storePengajuan: "{{ route('finance.pengajuan-biaya.store') }}"
    };
</script>
@vite (['resources/js/operasional/pengajuan.js'])

<script>
    // window.pajakList = @json($pajakList ?? []);
    $(document).on('click', '.btnDetailPengajuan', function() {

        const id = $(this).data('id');

        $.ajax({
            url: `/finance/pengajuan-biaya/detail/${id}`,
            type: 'GET',
            success: function(response) {

                if (!response || response.status !== 'success') {
                    console.error('Response invalid:', response);
                    return;
                }

                const header = response.data?.header || {};
                const items = response.data?.items || [];

                const modal = $('#modalDetailPengajuan');

                /* ================= HEADER ================= */

                modal.find('[name="jenis_pengajuan"]').val(header.jenis_pengajuan ?? '');
                modal.find('[name="metode_pembayaran"]').val(header.metode_pembayaran ?? '');
                modal.find('[name="tanggal_pengajuan"]').val(header.tgl_pengajuan ?? '');
                modal.find('[name="kontak_id"]').val(header.kontak_id ?? '').trigger('change');
                modal.find('[name="project_id"]').val(header.project_id ?? '').trigger('change');
                modal.find('#jenis_project').val(header.jenis_project ?? '');

                modal.find('[name="is_urgent"]').prop('checked', !!header.is_urgent);

                // Set kontak (Select2)
                const kontakSelect = modal.find('#kontakSelect');
                kontakSelect.empty(); // hindari duplicate
                if (header.kontak_id && header.kontak_nama) {
                    const option = new Option(
                        header.kontak_nama,
                        header.kontak_id,
                        true,
                        true
                    );
                    kontakSelect.append(option).trigger('change');
                }

                /* ================= ITEMS ================= */

                const container = modal.find('#itemContainer');
                container.empty();

                // pastikan pajakList ada
                const pajakData = (typeof pajakList !== 'undefined' && Array.isArray(pajakList)) ?
                    pajakList : [];

                items.forEach(item => {

                    const row = `
                    <div class="row g-2 align-items-center mb-2 item-row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="deskripsi[]" value="${item.deskripsi ?? ''}">
                        </div>
                        <div class="col-md-1">
                            <input type="number" class="form-control qty" name="qty[]" value="${item.qty ?? 0}">
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control harga" name="harga[]" value="${item.harga ?? 0}">
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control diskon" name="diskon[]" value="${item.diskon ?? 0}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select pajak" name="pajak_id[]">
                                <option value="0">Non Pajak</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <input type="text" class="form-control jumlah text-end" value="${item.jumlah ?? 0}" readonly>
                            <button type="button" class="btn btn-outline-danger btnRemove">−</button>
                        </div>
                    </div>
                `;

                    container.append(row);

                    const lastRow = container.find('.item-row').last();
                    const pajakSelect = lastRow.find('.pajak');

                    // isi ulang pajak dengan aman
                    pajakData.forEach(pajak => {
                        const opt = new Option(
                            `${pajak.nama_akun} (${pajak.nilai_coa}%)`,
                            pajak.id,
                            false,
                            pajak.id == item.pajak_id
                        );
                        pajakSelect.append(opt);
                    });
                });

                /* ================= TOTAL ================= */

                modal.find('#subtotal').text(
                    Number(header.subtotal ?? 0).toLocaleString('id-ID')
                );

                modal.find('#totalDiskon').text(
                    Number(header.total_diskon ?? 0).toLocaleString('id-ID')
                );

                modal.find('#summaryTotal').text(
                    Number(header.grand_total ?? 0).toLocaleString('id-ID')
                );

                modal.find('#grandTotal').text(
                    'Rp ' + Number(header.grand_total ?? 0).toLocaleString('id-ID')
                );

                /* ================= SHOW MODAL ================= */

                const bsModal = new bootstrap.Modal(
                    document.getElementById('modalDetailPengajuan')
                );
                bsModal.show();
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
            }
        });

    });
</script>

@endsection