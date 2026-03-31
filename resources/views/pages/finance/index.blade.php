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
                            <th>Tgl PO</th>
                            <th>Nama Customer</th>
                            <th>Lokasi</th>
                            <th>PIC Marketing</th>
                            <th>Kode Projek</th>
                            <th>No PO</th>
                            <th>Nama Perizinan</th>
                            <th>Nominal SPK</th>
                            <th>Lama Pekerjaan</th>
                            <th>Termin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                            <th>Tgl BAST</th>

                            <!-- <th>Nama Bangunan</th> -->
                            <!-- <th>Detail Alamat</th> -->
                            <!-- <th>Luasan</th> -->
                            <!-- <th>PIC Keuangan</th>
                            <th>Kontak</th> -->
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($po as $po)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($po->tgl_po)->format('d-m-Y') }}</td>
                                <td>{{ optional($po->customer)->nama_perusahaan ?? '-' }}</td>
                                <td>
                                    {{ \Illuminate\Support\Str::title(strtolower($po->quotation->kabupaten->nama ?? '-')) }}                                            
                                </td>
                                <td>{{ optional($po->customer->marketing)->nama ?? '-' }}</td>
                                <td>Kode Projek</td>
                                <td>{{ $po->no_po }}</td>
                                <!-- <td>
                                    @if (!empty($po->jenis_perizinan))
                                        @foreach (explode(',', $po->jenis_perizinan) as $izin)
                                            <span class="text-dark border me-1">
                                                {{ trim($izin) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td> -->
                                <td>
                                    @if (!empty($po->jenis_perizinan))
                                        {{ collect(explode(',', $po->jenis_perizinan))
                                            ->map(fn($izin) => trim($izin))
                                            ->join(', ') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format(optional($po->quotation)->grand_total ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    {{ optional($po->quotation)->lama_pekerjaan ? optional($po->quotation)->lama_pekerjaan . ' hari' : '-' }}
                                </td>
                                <td>
                                    @if (!empty($po->quotation?->termin_persentase))
                                        {{ collect($po->quotation->termin_persentase)
                                            ->pluck('persen')
                                            ->map(fn($p) => $p . '%')
                                            ->join(', ') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>   
                                <td>status</td>                   
                               
                    
                                <td class="text-center">
                                    @if ($po->sisa_termin > 0)
                                        <a href="{{ route('finance.create', $po->id) }}" class="btn btn-sm btn-primary">
                                            Buat Invoice ({{ $po->invoice_terbuat + 1 }}/{{ $po->total_termin }})
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            Invoice Lengkap
                                        </button>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($po->bast_verified_at)->format('d-m-Y H:i') }}</td>

                                <!-- <td>{{ optional($po->quotation)->nama_bangunan ?? '-' }}</td> -->
                                <!-- <td>
                                    {{ collect([
                                        $po->quotation->detail_alamat ?? null,
                                        optional($po->quotation->kawasan_industri)->nama_kawasan,
                                        optional($po->quotation->kabupaten)->nama
                                            ? \Illuminate\Support\Str::title(strtolower($po->quotation->kabupaten->nama))
                                            : null,
                                        optional($po->quotation->provinsi)->nama
                                            ? \Illuminate\Support\Str::title(strtolower($po->quotation->provinsi->nama))
                                            : null,
                                    ])->filter()->implode(', ') }}
                                </td> -->
                                <!-- <td>{{ $po->luasan ?? '-' }}</td> -->
                                <!-- <td>{{ $po->nama_pic_keuangan }}</td>
                                <td>{{ $po->kontak_pic_keuangan }}</td> -->            
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
