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
                            <th>Keterangan</th>
                            <th class="text-end">Terima</th>
                            <th class="text-end">Kirim</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($details as $row)
                        <tr>

                            <td>{{ $row->journal->tanggal }}</td>

                            <td>
                                {{ $row->journal->no_jurnal }}
                            </td>

                            <td>{{ $row->journal->keterangan }}</td>

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
                    </tbody>

                </table>
            </div>

        </div>
    </div>
    @endsection