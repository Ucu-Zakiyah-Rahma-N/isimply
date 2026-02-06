@extends('app.template')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Data BAST Finance</h5>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr class="text-center align-middle">
                        <th>No</th>
                        <th>No PO</th>
                        <th>Tgl PO</th>
                        <th>Nama Perusahaan</th>
                        <th>Nama Bangunan</th>
                        <th>Kabupaten</th>
                        <th>Kawasan</th>
                        <th>Detail Alamat</th>
                        <th>Luasan</th>
                        <th>Jenis Perizinan</th>
                        <th>Lama Pekerjaan</th>
                        <th>Nominal PO</th>
                        <th>Status</th>
                        <th>PIC Keuangan</th>
                        <th>Kontak</th>
                        <th>Aksi</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $po)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $po->no_po }}</td>
                        <td>{{ $po->tgl_po }}</td>

                        <td>{{ optional($po->customer)->nama_perusahaan ?? '-' }}</td>
                        <td>{{ optional($po->quotation)->nama_bangunan ?? '-' }}</td>
                        <td>{{ $po->kabupaten_name ?? '-' }}</td>
                        <td>{{ $po->kawasan_name ?? '-' }}</td>
                        <td>{{ $po->detail_alamat ?? '-' }}</td>
                        <td>{{ $po->luasan ?? '-' }}</td>

                        <td>
                            @if (!empty($po->jenis_perizinan))
                            @foreach (explode(',', $po->jenis_perizinan) as $izin)
                            <span class="badge bg-primary-subtle text-dark border me-1">
                                {{ trim($izin) }}
                            </span>
                            @endforeach
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            {{ optional($po->quotation)->lama_pekerjaan
                                    ? optional($po->quotation)->lama_pekerjaan . ' hari'
                                    : '-' }}
                        </td>

                        <td class="text-end">
                            Rp {{ number_format(optional($po->quotation)->grand_total ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="text-center">
                            {{ $po->status ?? '-' }}
                        </td>

                        <td>{{ $po->nama_pic_keuangan }}</td>
                        <td>{{ $po->kontak_pic_keuangan }}</td>
                        <td class="text-center">
                            @if ($po->sisa_termin > 0)
                                <a href="{{ route('finance.create', $po->id) }}"
                                class="btn btn-sm btn-primary">
                                    Buat Invoice ({{ $po->invoice_terbuat + 1 }}/{{ $po->total_termin }})
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    Invoice Lengkap
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center text-muted">
                            Data BAST belum tersedia
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection