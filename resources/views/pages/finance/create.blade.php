@extends('app.template')

@section('content')
<div class="card shadow-sm">
  <div class="card-header bg-primary text-white">
    <h5 class="mb-0">Form Create Invoice</h5>
  </div>

  <div class="card-body">
    @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form action="#" method="POST">
      @csrf
      <input type="hidden" name="po_id" value="{{ $po_id }}">

      {{-- HEADER --}}
      <div class="row mb-4">
        <div class="col-md-6">
          <label class="form-label">No Invoice</label>
          <input type="text" name="invoice_number" class="form-control" value="{{ $no_invoice }}" required readonly>
        </div>

        <div class="col-md-6 text-end">
          <div class="border rounded p-3 bg-light">
            <strong>Total:</strong>
            <h4 class="mb-1">Rp. <span id="grandTotal">0</span></h4>
            <small>Invoice sudah dibuat: 2</small><br>
            <small>Total tagihan: Rp 40.000.000</small><br>
            <small>Sisa tagihan: Rp 40.000.000</small>
          </div>
        </div>
      </div>

      {{-- INFORMASI PERUSAHAAN --}}
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="form-label">Nama Perusahaan</label>
          <input type="text" class="form-control" name="company_name" value="{{ $customer->nama_perusahaan ?? '-' }}" readonly>
        </div>

        <div class="col-md-3">
          <label class="form-label">Alamat Penagihan</label>
          <textarea class="form-control" name="alamat_penagihan" rows="3" readonly>{{ $customer->detail_alamat ?? '-' }}</textarea>
        </div>

        <div class="col-md-3">
          <label class="form-label">NPWP</label>
          <input type="text" class="form-control" name="npwp" value="{{ $customer->npwp ?? '-' }}" readonly>
        </div>

        <div class="col-md-3">
          <label class="form-label">Referensi Proyek (No PO)</label>
          <select class="form-control" name="po_reference">
            <option value="">Pilih PO</option>
            <option value="{{ $po_id }}">PO-{{ $po_id }}</option>
          </select>
        </div>
      </div>

      {{-- JENIS & TANGGAL --}}
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="form-label">Jenis Invoice</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="invoice_type" value="dp">
            <label class="form-check-label">DP</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="invoice_type" value="pelunasan">
            <label class="form-check-label">Pelunasan</label>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Keterangan</label>
          <input type="text" class="form-control" name="description">
        </div>

        <div class="col-md-3">
          <label class="form-label">Tgl Invoice</label>
          <input type="date" class="form-control" name="invoice_date">
        </div>

        <div class="col-md-3">
          <label class="form-label">Tgl Jatuh Tempo</label>
          <input type="date" class="form-control" name="due_date">
        </div>
      </div>

      <hr>

      {{-- PRODUK --}}
      <h6 class="mb-3">Produk</h6>

      <div id="items">
        <div class="row align-items-end mb-2 item-row">
          <div class="col-md-2">
            <label class="form-label">Produk</label>
            <select class="form-control" name="items[0][product]">
              <option>ERP</option>
              <option>PBG</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Deskripsi</label>
            <input type="text" class="form-control" name="items[0][description]">
          </div>

          <div class="col-md-1">
            <label class="form-label">Qty</label>
            <input type="number" class="form-control qty" name="items[0][qty]" value="1">
          </div>

          <div class="col-md-2">
            <label class="form-label">Harga</label>
            <input type="number" class="form-control price" name="items[0][price]">
          </div>

          <div class="col-md-2">
            <label class="form-label">Jumlah</label>
            <input type="text" class="form-control subtotal" readonly>
          </div>

          <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-item">−</button>
            <button type="button" class="btn btn-primary btn-sm add-item">+</button>
          </div>
        </div>
      </div>

      <hr>

      {{-- TOTAL --}}
      <div class="row justify-content-end">
        <div class="col-md-4">
          <div class="mb-2 d-flex justify-content-between">
            <span>Subtotal</span>
            <strong>Rp <span id="subtotal">0</span></strong>
          </div>

          <div class="mb-2">
            <label>Diskon</label>
            <input type="number" class="form-control" name="discount">
          </div>

          <div class="mb-2">
            <label>PPN</label>
            <select class="form-control" name="tax">
              <option value="11">PPN 11%</option>
              <option value="3">PPN 3%</option>
              <option value="2">PPN 2%</option>
            </select>
          </div>

          <hr>

          <h5>Total: Rp <span id="finalTotal">0</span></h5>
        </div>
      </div>

      {{-- ACTION --}}
      <div class="text-end mt-4">
        <button class="btn btn-success px-4">
          Buat Invoice
        </button>
      </div>

    </form>
  </div>
</div>

@endsection