    @extends('app.template')

    @section('content')
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Bank Cash</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>TRX</th>
                            <th>Keterangan</th>
                            <th>Penerima</th>
                            <th>Kode Projek</th>
                            <th>Akun</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="fw-bold">
                            <td colspan="9" class="text-start">SALDO AWAL</td>
                            <td class="text-end">
                                Rp {{ number_format($coa->saldo_awal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @foreach($details as $row)
                        <tr>

                            <td>{{ $row->journal->tanggal }}</td>
                            <td>
                                {{ $row->journal->no_jurnal }}
                            </td>
                            <td>
                                {{ $row->coa->nama_akun }}
                            </td>
                            <td>{{ $row->journal->keterangan }}</td>

                            <td>{{ $row->penerima }}</td>
                            <td>-</td>
                            <td>{{ $row->akun_lawan }}</td>

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
                        <tr class="fw-bold">
                            <td colspan="7" class="text-end">TOTAL MUTASI</td>
                            <td class="text-end">
                                Rp {{ number_format($totalDebit, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($totalCredit, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                        <tr class="fw-bold bg-light">
                            <td colspan="7" class="text-end">SALDO AKHIR</td>
                            <td colspan="3" class="text-end">
                                Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>

        </div>
    </div>
    @endsection