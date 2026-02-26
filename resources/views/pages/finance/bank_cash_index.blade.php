@extends('app.template')

@section('content')
<div class="card shadow-sm">

    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Bank Cash</h5>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Akun</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>

                <tbody>

                    @php $no = 1; @endphp

                    @foreach($parents as $parent)

                    {{-- PARENT --}}
                    <tr class="table-secondary fw-bold">
                        <td></td>
                        <td>{{ $parent->nama_akun }}</td>
                        <td></td>
                    </tr>

                    {{-- CHILD --}}
                    @foreach($parent->children as $child)
                    <tr>
                        <td>{{ $no++ }}</td>

                        <td class="ps-4">
                            <a href="{{ route('finance.bank_cash.ledger',['coaId'=>$child->id]) }}"
                                class="d-block text-dark text-decoration-none">
                                {{ $child->nama_akun }}
                            </a>
                        </td>

                        <td class="text-end">
                            {{ number_format($child->saldo ?? 0) }}
                        </td>
                    </tr>
                    @endforeach

                    @endforeach

                </tbody>

            </table>
        </div>

    </div>
</div>
@endsection