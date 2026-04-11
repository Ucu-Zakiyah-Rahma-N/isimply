    @extends('app.template')

    @section('content')
    <style>
.table tbody tr {
    border-bottom: 1.5px solid #aaaaaa;
}
</style>
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Bank Cash</h5>
        </div>

        <div class="card-body">

<form method="GET" action="">
    <div class="row mb-3">

        <div class="col-md-3">
            <label>Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control"
                value="{{ request('start_date') }}">
        </div>

        <div class="col-md-3">
            <label>Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control"
                value="{{ request('end_date') }}">
        </div>

        <div class="col-md-3">
            <label>Bulan</label>
            <input type="month" name="month" class="form-control"
                value="{{ request('month') }}">
        </div>

        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100">
                Filter
            </button>

            <!-- <a href="{{ url()->current() }}" class="btn btn-secondary w-100">
                Reset
            </a> -->
            <a href="{{ request()->fullUrlWithQuery([
                'start_date' => null,
                'end_date' => null,
                'month' => null
            ]) }}" 
            class="btn btn-secondary w-100">
                Reset
            </a>
        </div>

    </div>
</form>

<select id="perPage" class="form-select" style="width:90px;">
    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
</select>
<br>
            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-secondary">
                        <tr>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>TRX</th>
                            <th>Keterangan</th>
                            <th>Penerima</th>
                            <th>Kode Projek</th>
                            <!-- <th>Akun</th> -->
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="fw-bold">
                            <td colspan="8" class="text-start">SALDO AWAL</td>
                            <td class="text-end">
                                {{ number_format($coa->saldo_awal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @foreach($details as $row)
                        <tr>

                            <td>{{ \Carbon\Carbon::parse($row->journal->tanggal)->format('d-m-Y') }}</td>
                            <td>
                                {{ $row->journal->no_jurnal }}
                            </td>
                            <td>
                                {{ $row->coa->nama_akun }}
                            </td>
                            <td>{{ $row->journal->keterangan }}</td>

                            <td>{{ $row->penerima }}</td>
                            <td>{{ $row->journal->invoice?->po?->kode_project ?? '-' }}</td>
                            <!-- <td>{{ $row->akun_lawan }}</td> -->

                            <td class="text-end text-success">
                                @if($row->debit > 0)
                                {{ number_format($row->debit) }}
                                @endif
                            </td>

                            <td class="text-end text-danger">
                                @if($row->credit > 0)
                                {{ number_format($row->credit) }}
                                @endif
                            </td>

                            <td class="text-end fw-bold">
                                {{ number_format($row->saldo) }}
                            </td>

                        </tr>
                        @endforeach
                       @if ($details->currentPage() == $details->lastPage())

                        <tr class="fw-bold">
                            <td colspan="6" class="text-end">TOTAL MUTASI</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-between">
                                <span>Rp</span>&nbsp;
                                <span>{{ number_format($totalDebit, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-between">
                                        <span>Rp</span>
                                        <span>{{ number_format($totalCredit, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td></td>
                        </tr>

                        <tr class="fw-bold bg-light">
                            <td colspan="6" class="text-end">SALDO AKHIR</td>
                            <td colspan="3" class="text-end">
                                Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                            </td>
                        </tr>

                        @endif
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $details->links() }}
                </div>
            </div>

        </div>
    </div>

    <script>
document.getElementById('perPage').addEventListener('change', function () {

    let url = new URL(window.location.href);

    // set per_page
    url.searchParams.set('per_page', this.value);

    // reset ke page 1 biar aman
    url.searchParams.set('page', 1);

    window.location.href = url.toString();
});
</script>
    @endsection