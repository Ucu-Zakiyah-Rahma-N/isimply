@extends('app.template')

@section('content')
    <style>
        .icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-wrapper i {
            font-size: 22px;
            line-height: 1;

            .print-area {
                display: none;
            }

            @media print {
                body * {
                    visibility: hidden;
                }

                .print-area,
                .print-area * {
                    visibility: visible;
                }

                .print-area {
                    display: block;
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
            }
        }
    </style>
    <div class="card-body">

        {{-- Rekap Card --}}
        <div class="d-flex justify-content-center mb-3">
            <div class="d-flex flex-nowrap gap-5" style="max-width: 1000px;">

                {{-- Penagihan belum dibayar --}}
                <a href="{{ route('projects.index', ['status' => 'Belum Mulai']) }}" class="text-decoration-none text-dark">
                    <div class="card shadow border-0" style="width: 350px; height: 110px;">
                        <div class="card-body d-flex align-items-center">

                            <div class="icon-wrapper bg-primary text-white flex-shrink-0">
                                <i class="ti ti-clock"></i>
                            </div>

                            <div class="ms-3">
                                <h6 class="text-muted mb-1">Penagihan belum dibayar</h6>
                                <h4 class="fw-bold mb-0">{{ $rekap['belum_mulai'] ?? 0 }}</h4>
                            </div>

                        </div>
                    </div>
                </a>

                {{-- Penagihan telat dibayar --}}
                <a href="{{ route('projects.index', ['status' => 'On Progress']) }}" class="text-decoration-none text-dark">
                    <div class="card shadow border-0" style="width: 350px; height: 110px;">
                        <div class="card-body d-flex align-items-center">

                            <div class="icon-wrapper bg-warning text-dark flex-shrink-0">
                                <i class="ti ti-loader"></i>
                            </div>

                            <div class="ms-3">
                                <h6 class="text-muted mb-1">Penagihan telat dibayar</h6>
                                <h4 class="fw-bold mb-0">{{ $rekap['on_progress'] ?? 0 }}</h4>
                            </div>
                            t
                        </div>
                    </div>
                </a>

            </div>
        </div>


        {{-- Table --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Sudah Invoice</h5>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr class="text-center align-middle">
                                <th>No</th>
                                <th>Nama Perusahaan</th>
                                <th>No Invoice</th>
                                <th>No PO</th>
                                <th>Jenis Perizinan</th>
                                <th>Termin</th>
                                <th>Nominal PO</th>
                                <th>DPP</th>
                                <th>PPN</th>
                                <th>Total Tagihan</th>
                                <th>Tgl Pembayaran</th>
                                <th>Bulan</th>
                                <th>Status</th>
                                <th>Aksi</th>


                                {{-- <th>Tgl Invoice</th>
                                <th>Tgl Jatuh Tempo</th>
                                <th>Nama Bangunan</th>
                                <th>Alamat</th>
                                <th>Status</th>
                                <th>Sisa Tagihan</th>
                                <th>Upload Inv</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice as $inv)
                                @php
                                    $terminData = \App\Helpers\TotalInvoiceHelper::calculateTerminBreakdown($inv);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $inv->po->quotation->customer->nama_perusahaan ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('finance.invoice.show', $inv->id) }}" class="text-primary">
                                            {{ $inv->no_invoice }}
                                        </a>
                                    </td>
                                    <td>{{ $inv->po->no_po ?? '-' }}</td>
                                    <td>
                                        @php
                                            $perizinans = $inv->po->quotation->perizinan ?? collect();
                                        @endphp

                                        @if ($perizinans->isNotEmpty())
                                            @foreach ($perizinans as $izin)
                                                <span class="badge bg-primary-subtle text-dark border me-1">
                                                    {{ $izin->jenis }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $inv->keterangan ?? '-' }}</td>
                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($inv->nominal_invoice, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{ $inv->dpp > 0 ? 'Rp ' . number_format($inv->dpp, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ $inv->ppn > 0 ? 'Rp ' . number_format($inv->ppn, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($inv->grand_total, 0, ',', '.') ?? '-' }}
                                    </td>


                                    <td></td>
                                    <td></td>
                                    <td></td>

                                    <td class="text-center">
                                        <a href="{{ route('finance.invoice.invoice_print', $inv->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        {{-- <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="printInvoice({{ $inv->id }})">
                                            <i class="bi bi-printer"></i>
                                        </button> --}}
                                        {{-- <iframe id="print-frame" style="display:none;"></iframe> --}}
                                        <form action="{{ route('finance.invoice.invoice_destroy', $inv->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus invoice ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                    {{-- <td>{{ $inv->tgl_inv }}</td>
                                    <td>{{ $inv->tgl_jatuh_tempo }}</td>
                                    <td>{{ $inv->po->quotation->nama_bangunan ?? '-' }}</td>
                                    <td>
                                        {{ collect([
                                            $inv->po->quotation->detail_alamat,
                                            $inv->po->quotation->kawasan_industri->nama_kawasan ?? null,
                                            isset($inv->po->quotation->kabupaten->nama)
                                                ? \Illuminate\Support\Str::title(strtolower($inv->po->quotation->kabupaten->nama))
                                                : null,
                                            isset($inv->po->quotation->provinsi->nama)
                                                ? \Illuminate\Support\Str::title(strtolower($inv->po->quotation->provinsi->nama))
                                                : null,
                                        ])->filter()->implode(', ') }}
                                    </td> --}}
                                </tr>
                            @endforeach

                        </tbody>
                    </table>


                </div>
            </div>
        </div>

    </div>

    <script>
        function printInvoice(id) {
            const iframe = document.getElementById('print-frame');

            // paksa reload walaupun ID sama
            iframe.src = '';

            setTimeout(() => {
                iframe.src = `/finance/invoice/${id}/print`;

                iframe.onload = function() {
                    setTimeout(() => {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }, 300); // tunggu render
                };
            }, 50);
        }
    </script>
@endsection
