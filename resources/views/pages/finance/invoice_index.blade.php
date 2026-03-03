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
                            <th>PPH</th>
                            <th>Nominal Pembayaran</th>
                            <th>Tgl Pembayaran</th>
                            <th>Bulan</th>
                            <th>Status Payment</th>
                            <th>Status</th>
                            <th>File Invoice</th>
                            <th>File Faktur</th>
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
                                @if ($inv->produk->isNotEmpty())
                                @foreach ($inv->produk as $item)
                                <span class="badge bg-primary-subtle text-dark border me-1">
                                    {{ $item->perizinan?->jenis ?? $item->perizinan_lainnya ?? '-' }}
                                </span>
                                @endforeach
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $inv->keterangan ?? '-' }}</td>
                            <td class="text-end fw-bold">
                                @php
                                $totalFinal = $inv->total_after_diskon_inv > 0
                                ? $inv->total_after_diskon_inv
                                : $inv->nominal_invoice;
                                @endphp

                                Rp {{ number_format($totalFinal, 0, ',', '.') }}

                                <!-- Rp {{ number_format($inv->nominal_invoice, 0, ',', '.') }} ini kalo tanpa kondisi total after diskon -->
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
                            <td>
                                {{ $inv->nilai_pph > 0 ? 'Rp ' . number_format($inv->nilai_pph,0,',','.') : '-' }}
                            </td>

                            <td>
                                {{ $inv->nominal > 0 ? 'Rp ' . number_format($inv->nominal,0,',','.') : '-' }}
                            </td>
                            <td>{{ $inv->payments->isNotEmpty() 
                                    ? $inv->payments->pluck('tanggal')
                                        ->map(fn($t) => \Carbon\Carbon::parse($t)->format('d-m-Y'))
                                        ->implode(', ') 
                                    : '-' 
                                }}
                            </td>
                            <td>
                                {{ $inv->payments->isNotEmpty() 
                                    ? \Carbon\Carbon::parse($inv->payments->last()->tanggal)->translatedFormat('F') 
                                    : '-' 
                                }}
                            </td>

                            <td>
                                @if($inv->payments->count() > 0)
                                <span class="badge bg-success">Done</span>
                                @else
                                <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                                @endif
                            </td>
                            <td>
                                @if($inv->status === 'posted')
                                <span class="badge bg-primary">Posted</span>

                                @elseif($inv->status === 'paid')
                                <span class="badge bg-success">Paid</span>

                                @elseif($inv->status === 'void')
                                <span class="badge bg-danger">Void</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">

                                    @if(!$inv->file_invoice)
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#uploadInvoiceModal{{ $inv->id }}"
                                        title="Upload">
                                        <i class="bi bi-upload"></i>
                                    </button>
                                    @else
                                    <button class="btn btn-sm btn-danger"
                                        onclick="openPdf('{{ route('files.view', $inv->file_invoice) }}')"
                                        title="Lihat">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </button>

                                    <button type="button"
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#uploadInvoiceModal{{ $inv->id }}"
                                        title="Edit / Ganti File">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>


                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    @if(!$inv->file_faktur)
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#uploadFakturModal{{ $inv->id }}"
                                        title="Upload">
                                        <i class="bi bi-upload"></i>
                                    </button>
                                    @else
                                    <button class="btn btn-sm btn-danger"
                                        onclick="openPdf('{{ route('files.view', $inv->file_faktur) }}')"
                                        title="Lihat">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </button>

                                    <button type="button"
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#uploadFakturModal{{ $inv->id }}"
                                        title="Edit / Ganti File">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>

                            <!-- Modal Invoice -->
                            <div class="modal fade" id="uploadInvoiceModal{{ $inv->id }}" tabindex="-1" aria-labelledby="uploadInvoiceLabel{{ $inv->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('finance.invoice.uploadInvoice', $inv->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="uploadInvoiceLabel{{ $inv->id }}">Upload Invoice PDF</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="file" name="file_invoice" accept="application/pdf" class="form-control" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Faktur Pajak -->
                            <div class="modal fade" id="uploadFakturModal{{ $inv->id }}" tabindex="-1" aria-labelledby="uploadFakturLabel{{ $inv->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('finance.invoice.uploadFaktur', $inv->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="uploadFakturLabel{{ $inv->id }}">Upload Faktur Pajak PDF</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="file" name="file_faktur" accept="application/pdf" class="form-control" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <a href="{{ route('finance.invoice.invoice_print', $inv->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <form action="{{ route('finance.invoice.invoice_destroy', $inv->id) }}"
                                        method="POST"
                                        class="form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
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
                <div class="modal fade" id="pdfViewerModal" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">Preview PDF</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-0">
                                <iframe id="pdfViewerFrame"
                                    src=""
                                    width="100%"
                                    height="650px"
                                    style="border:none;">
                                </iframe>
                            </div>

                        </div>
                    </div>
                </div>

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

    function openPdf(fileUrl) {
        document.getElementById('pdfViewerFrame').src = fileUrl;

        var modal = new bootstrap.Modal(document.getElementById('pdfViewerModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.btn-delete').forEach(function(button) {
            button.addEventListener('click', function() {

                let form = this.closest('form');

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data invoice tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

            });
        });

    });
</script>
@endsection