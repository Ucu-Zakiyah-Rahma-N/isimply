@extends('app.template')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Detail Invoice: {{ $invoice->no_invoice }}</h5>
    </div>

    <div class="card-body">
        {{-- HEADER --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">No Invoice</label>
                <input type="text" class="form-control" value="{{ $invoice->no_invoice }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Referensi Proyek (No PO)</label>
                <select class="form-control" name="po_id">
                    <option value="{{ $invoice->po_id }}">
                        {{ $invoice->po->no_po ?? '-' }}
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kode Projek</label>
                <select class="form-control" name="kode_project">
                    <option value="{{ $invoice->kode_project }}">
                        {{ $invoice->po->kode_project ?? '-' }}
                    </option>
                </select>
            </div>
        </div>

        {{-- INFORMASI CUSTOMER --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Nama Perusahaan</label>
                <input type="text" class="form-control" value="{{ $invoice->customer->nama_perusahaan }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">NPWP</label>
                <input type="text" class="form-control" value="{{ $invoice->customer->npwp ?? '-' }}" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Alamat Penagihan</label>
                <textarea class="form-control" rows="3" readonly>{{ collect([
                        $invoice->po->quotation->detail_alamat,
                        $invoice->po->quotation->kawasan_industri->nama_kawasan ?? null,
                        isset($invoice->po->quotation->kabupaten->nama)
                            ? \Illuminate\Support\Str::title(strtolower($invoice->po->quotation->kabupaten->nama))
                            : null,
                        isset($invoice->po->quotation->provinsi->nama)
                            ? \Illuminate\Support\Str::title(strtolower($invoice->po->quotation->provinsi->nama))
                            : null,
                    ])->filter()->implode(', ') }}</textarea>
            </div>


        </div>

        {{-- JENIS & TERMIN --}}
        <div class="row mb-2">
            <div class="col-md-2">
                <label class="form-label">Jenis Invoice</label>
                <input type="text" class="form-control" value="{{ ucfirst($invoice->jenis_invoice) }}" readonly>
            </div>
            {{-- <div class="col-md-2">
                    <label class="form-label">Termin ke</label>
                    <input type="text" class="form-control" value="{{ $invoice->termin_ke }}" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label">Persentase Termin</label>
            <input type="text" class="form-control" value="{{ $invoice->persentase_termin }}%" readonly>
        </div> --}}
        <div class="col-md-2">
            <label class="form-label">Keterangan</label>
            <input type="text" class="form-control" value="{{ $invoice->keterangan }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal Invoice</label>
            <input type="date" class="form-control" value="{{ $invoice->tgl_inv }}" readonly>
        </div>
          <div class="col-md-2">
            <label class="form-label">Net</label>
            <input type="text" class="form-control" value="{{ $invoice->net_month . ' bulan' }}" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label">Tanggal Jatuh Tempo</label>
            <input type="date" class="form-control" value="{{ $invoice->tgl_jatuh_tempo }}" readonly>
        </div>

    </div>
    <hr>
    {{-- PRODUK --}}
    {{-- <h6 class="mt-4">Produk</h6> --}}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Deskripsi</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
       @php
        $isGabungan = !empty($invoice->harga_gabungan);
        $rowspan = $invoice->produk->count();
        @endphp

        <tbody>
        @foreach ($invoice->produk as $i => $item)
        <tr>

            <td>
                {{ $item->perizinan->jenis ?? $item->perizinan_lainnya ?? '-' }}
            </td>

            <td>
                {{ $item->deskripsi ?? '-' }}
            </td>

            <td class="text-end">
                {{ $item->qty }}
            </td>

            @if($isGabungan)

                @if($i === 0)
                <td class="text-end align-middle" rowspan="{{ $rowspan }}">
                    Rp {{ number_format($invoice->harga_gabungan,0,',','.') }}
                </td>

                <td class="text-end align-middle" rowspan="{{ $rowspan }}">
                    Rp {{ number_format($invoice->harga_gabungan,0,',','.') }}
                </td>
                @endif

            @else

                <td class="text-end">
                    Rp {{ number_format($item->harga_satuan,0,',','.') }}
                </td>

                <td class="text-end">
                    Rp {{ number_format(($item->qty ?? 0) * ($item->harga_satuan ?? 0),0,',','.') }}
                </td>

            @endif

        </tr>
        @endforeach
        </tbody>
    </table>

    {{-- TOTAL & DISKON --}}
    <div class="row justify-content-end mt-3">
        <div class="col-md-4">

            {{-- SUBTOTAL --}}
            <div class="d-flex justify-content-between">
                <span>Subtotal</span>
                <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
            </div>

            {{-- DISKON PO --}}
            @if ($diskonPO > 0)
            <div class="d-flex justify-content-between">
                {{-- diskon PO --}}
                <span>Diskon</span>
                <strong>- Rp {{ number_format($diskonPO, 0, ',', '.') }}</strong>
            </div>

            {{-- AFTER DISKON PO --}}
            <div class="d-flex justify-content-between">
                <span>Total setelah Diskon</span>
                <strong>Rp {{ number_format($nominalPO, 0, ',', '.') }}</strong>
            </div>

            {{-- NOMINAL TERMIN --}}
            <div class="d-flex justify-content-between">
                <span>Termin ({{ $invoice->persentase_termin }}%)</span>
                <strong>Rp {{ number_format($nominalTermin, 0, ',', '.') }}</strong>
            </div>
            @else
            {{-- TIDAK ADA DISKON PO --}}
            <div class="d-flex justify-content-between">
                <span>Termin ({{ $invoice->persentase_termin }}%)</span>
                <strong>Rp {{ number_format($nominalTermin, 0, ',', '.') }}</strong>
            </div>
            @endif

            {{-- DISKON INVOICE --}}
            @if ($diskonInvoice > 0)
            <div class="d-flex justify-content-between">
                <span>Diskon</span>
                <strong>- Rp {{ number_format($diskonInvoice, 0, ',', '.') }}</strong>
            </div>

            <div class="d-flex justify-content-between">
                <span>Total setelah Diskon</span>
                <strong>Rp {{ number_format($afterDiscount, 0, ',', '.') }}</strong>
            </div>
            @endif

            {{-- PAJAK --}}
            @if ($ppn > 0)
            <div class="d-flex justify-content-between">
                <span>DPP</span>
                <strong>Rp {{ number_format($dpp, 0, ',', '.') }}</strong>
            </div>

            <div class="d-flex justify-content-between">
                <span>PPN 11%</span>
                <strong>Rp {{ number_format($ppn, 0, ',', '.') }}</strong>
            </div>
            @endif

            <hr>

            {{-- GRAND TOTAL --}}
            <div class="d-flex justify-content-between">
                <span>Total</span>
                <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
            </div>

             <div class="d-flex justify-content-between">
            <strong>Sisa Tagihan</strong>
            <strong class="text-danger">
                Rp {{ number_format($sisaTagihan,0,',','.') }}
            </strong>
        </div>

        </div>
    </div>


    <div class="mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
        <a href="{{ route('finance.invoice.edit', $invoice->id) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('finance.invoice.terima_pembayaran', $invoice->id) }}" class="btn btn-success">Terima Pembayaran</a>

    </div>


<hr>

<h5 class="mb-3">History Pembayaran</h5>

<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th width="40">No</th>
            <th>Tanggal Pembayaran</th>
            <th>Metode Pembayaran</th>
            <th>Nominal Diterima</th>
            <th>PPh</th>
            <th>Total Piutang Tertutup</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>

        @forelse($payments as $i => $p)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ date('d-m-Y', strtotime($p->tanggal)) }}</td>
            <td>{{ ucfirst($p->metode_pembayaran) }}</td>

            <td class="text-end">
                Rp {{ number_format($p->nominal,0,',','.') }}
            </td>

            <td class="text-end">
                Rp {{ number_format($p->nilai_pph,0,',','.') }}
            </td>

            <td class="text-end">
                Rp {{ number_format($p->nominal + $p->nilai_pph,0,',','.') }}
            </td>

            <td>{{ $p->keterangan }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-muted">
                Belum ada pembayaran
            </td>
        </tr>
        @endforelse

    </tbody>
</table>

<div class="row justify-content-end mt-3">

    <div class="col-md-4">

        <div class="d-flex justify-content-between mb-1">
            <span>Total Ditagihkan</span>
            <span>
                Rp {{ number_format($invoice->grand_total,0,',','.') }}
            </span>
        </div>

        <div class="d-flex justify-content-between mb-1">
            <span>Total Diterima</span>
            <span>
                Rp {{ number_format($totalCash,0,',','.') }}
            </span>
        </div>

        <div class="d-flex justify-content-between mb-1">
            <span>Total PPH</span>
            <span>
                Rp {{ number_format($totalPph,0,',','.') }}
            </span>
        </div>

        <hr>

        <div class="d-flex justify-content-between mb-1">
            <strong>Total Piutang Tertutup</strong>
            <strong>
                Rp {{ number_format($totalTertutup,0,',','.') }}
            </strong>
        </div>

        <div class="d-flex justify-content-between">
            <strong>Sisa Tagihan</strong>
            <strong class="text-danger">
                Rp {{ number_format($sisaTagihan,0,',','.') }}
            </strong>
        </div>

    </div>

</div>
<br>
</div>
</div>
@endsection