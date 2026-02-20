<?php

namespace App\Http\Controllers;

use App\Models\PO;
use App\Models\Wilayah;
use App\Models\invoice;
use App\Models\Customer;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ProdukInvoice;
use App\Models\TaxInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\TotalInvoiceHelper;
use App\Helpers\InvoiceCalculatorHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceController extends Controller
{
    public function index()
    {
        $title = 'BAST Finance';

        // Ambil PO + relasi penting
        $po = PO::with([
            'customer',
            'quotation.kabupaten',
            'quotation.kawasan_industri',
            'quotation.perizinan',
            'invoices'
        ])
            ->where('bast_verified', 1)
            ->orderBy('tgl_po', 'desc')
            ->get();

        $po->each(function ($po) {
            $po->total_termin = $po->quotation->jumlah_termin ?? 0;
            $po->invoice_terbuat = $po->invoices->count();
            $po->sisa_termin = $po->total_termin - $po->invoice_terbuat;
        });

        // Mapping kabupaten
        $kabupatenList = Wilayah::where('jenis', 'kabupaten')
            ->pluck('nama', 'kode')
            ->toArray();

        foreach ($po as $item) {

            $quotation = $item->quotation;

            /* ===============================
             | Kabupaten
             =============================== */
            $item->kabupaten_name = $quotation->kabupaten->nama ?? '-';

            /* ===============================
             | Kawasan Industri
             =============================== */
            $item->kawasan_name = $quotation && $quotation->kawasan_industri
                ? $quotation->kawasan_industri->nama_kawasan
                : '-';

            /* ===============================
             | Detail Alamat
             =============================== */
            $item->detail_alamat = $quotation->detail_alamat ?? '-';

            /* ===============================
             | Luasan + Jenis Perizinan
             =============================== */
            $luasList = [];
            $izinList = [];

            if (!is_null($quotation->luas_slf)) {
                $luasList[] = 'SLF: ' . number_format($quotation->luas_slf, 2, ',', '.') . ' m²';
                $izinList[] = 'SLF';
            }

            if (!is_null($quotation->luas_pbg)) {
                $luasList[] = 'PBG: ' . number_format($quotation->luas_pbg, 2, ',', '.') . ' m²';
                $izinList[] = 'PBG';
            }

            if (!is_null($quotation->luas_shgb)) {
                $luasList[] = 'SHGB: ' . number_format($quotation->luas_shgb, 2, ',', '.') . ' m²';
                $izinList[] = 'SHGB';
            }

            $item->luasan = count($luasList) ? implode(', ', $luasList) : '-';


            $item->jenis_perizinan = $quotation?->perizinan->isNotEmpty()
                ? $quotation->perizinan->pluck('jenis')->implode(', ')
                : '-';
        }

        return view('pages.finance.index', compact('po', 'title'));
    }
    public function store_akun_coa(Request $request)
    {
        $validated = $request->validate([
            'nama'  => 'required|string|max:255',
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $ppn = Coa::create([
                'nama_akun' => $validated['nama'],
                'nilai_coa' => $validated['nilai'],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id'    => $ppn,
                    'nama'  => $validated['nama'],
                    'nilai' => $validated['nilai'],
                ],
                'message' => 'PPN berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan PPN'
            ], 500);
        }
    }

    public function create($po_id)
    {
        $title = 'Create Invoice';

        $noInvoice = $this->generateInvoiceNumber();

        $po = Po::with([
            'customer',
            'quotation.perizinan',
            'quotation.kawasan_industri',
            'quotation.kabupaten',
            'quotation.provinsi',
            'invoices'
        ])->findOrFail($po_id);


        $quotation = $po->quotation;
        $perizinans = $quotation->perizinan;
        //get PPN aja
        $ppnList = Coa::where('id', 1)->get();

        $items = $quotation->perizinan; // contoh relasi


        // Ambil customer dari relasi
        $customer = $po->customer;

        if (!$customer) {
            return back()->withErrors(['customer' => 'Customer tidak ditemukan']);
        }

        $po->kabupaten_name = $quotation->kabupaten->nama ?? '-';
        // Kawasan Industri
        $po->kawasan_name = $quotation && $quotation->kawasan_industri
            ? $quotation->kawasan_industri->nama_kawasan
            : '-';
        // Detail Alamat
        $po->detail_alamat = $quotation->detail_alamat ?? '-';

        // // Ambil schedule termin dari quotation & Ambil invoice sebelumnya
        $invoice_sebelumnya = $po->invoices()->orderBy('termin_ke', 'desc')->first();
        $invoiceTerbuat = $po->invoices->count(); // jumlah invoice yang sudah dibuat           
        $terminKe = $invoiceTerbuat + 1;          // termin berikutnya
        // Ambil schedule termin dari quotation (misal [50,50] atau [30,40,30])
        $terminSchedule = json_decode($quotation->termin_persentase, true);
        $persentaseTermin = $terminSchedule[$invoiceTerbuat]['persen'] ?? 0;

        // Hitung nominal invoice

        if ($quotation->harga_tipe === 'gabungan') {

            $subtotal = (float) $quotation->harga_gabungan;
        } else {

            $subtotal = $quotation->perizinan->sum(function ($item) {
                return ($item->pivot->qty ?? 0) * ($item->pivot->harga_satuan ?? 0);
            });
        }
        //diskon after subtotal
        $tipeDiskonQuotation  = $quotation->diskon_tipe ?? null;
        $nilaiDiskonQuotation = $quotation->diskon_nilai ?? 0;

        // Hitung diskon dari subtotal
        $diskonQuotation = 0;

        if ($tipeDiskonQuotation && $nilaiDiskonQuotation > 0) {
            if ($tipeDiskonQuotation === 'nominal') {
                $diskonQuotation = $nilaiDiskonQuotation;
            } else {
                $diskonQuotation = $subtotal * $nilaiDiskonQuotation / 100;
            }
        }

        // Pastikan diskon tidak melebihi subtotal
        if ($diskonQuotation > $subtotal) {
            $diskonQuotation = $subtotal;
        }

        // Nominal PO setelah diskon
        $nominalPO = $subtotal - $diskonQuotation;

        $nominalInvoice = $nominalPO * $persentaseTermin / 100;
        return view('pages.finance.create', [
            'title' => $title,
            'customer' => $customer,
            'po_id' => $po_id,
            'po' => $po,
            'no_po' => $po->no_po,
            'quotation' => $quotation,
            'perizinans'  => $perizinans,
            'no_invoice' => $noInvoice,
            'termin_ke' => $terminKe,
            'persentaseTermin' => $persentaseTermin,
            // 'sisa_termin' => $sisaTermin,
            'invoice_sebelumnya' => $invoice_sebelumnya,
            'ppnList' => $ppnList,
            'subtotal' => $subtotal,
            'diskonQuotation' => $diskonQuotation,
            'nominalPO' => $nominalPO,
            'nominalInvoice' => $nominalInvoice,
            'tipeDiskonQuotation' => $tipeDiskonQuotation,
            'nilaiDiskonQuotation' => $nilaiDiskonQuotation,
        ]);
    }

    private function generateInvoiceNumber()
    {
        $now = Carbon::now();
        $tahun = $now->year;
        $bulan = $now->month;

        // Convert bulan 
        $bulanRomawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$bulan];

        // Ambil invoice terakhir di tahun yang sama
        $lastInvoice = Invoice::whereYear('created_at', $tahun)
            ->orderBy('id', 'desc')
            ->first();

        // Start number
        $startNumber = 100;

        if ($lastInvoice && $lastInvoice->no_invoice) {
            // contoh: 001/II/INV-SDI/2026
            $lastRunning = (int) explode('/', $lastInvoice->no_invoice)[0];
            $startNumber = $lastRunning + 1;
        }
        $runningNumber = str_pad($startNumber, 3, '0', STR_PAD_LEFT);

        return "{$runningNumber}/{$bulanRomawi}/INV-SDI/{$tahun}";
    }

    public function store(Request $request)
    {
        Log::info('=== START STORE INVOICE ===');

        Log::info('Request All', $request->all());
        Log::info('RAW ITEMS FROM REQUEST', [
            'items' => $request->input('items')
        ]);

        // 1️⃣ Validasi
        $request->validate([
            'no_invoice'        => 'required',
            'po_id'             => 'required',
            'customer_id'       => 'required|exists:customers,id',
            'jenis_invoice'     => 'required',
            'tgl_invoice'       => 'required|date',
            'tgl_jatuh_tempo'   => 'required|date',
            'items'             => 'required|array|min:1',
            'persentase_termin' => 'required|numeric|min:0|max:100',
        ]);

        // 2️⃣ Customer
        $customer = Customer::findOrFail($request->customer_id);

        // 3️⃣ Hitung nominal invoice
        // Ambil PO dulu
        $po        = PO::with('quotation')->findOrFail($request->po_id);
        $quotation = $po->quotation;

        // Ambil subtotal dari request
        $subtotal = $request->subtotal ?? 0;

        // Diskon PO dari quotation
        $diskonPo = $quotation->diskon_nilai ?? 0;

        // Hitung nominal PO
        $nominalPo = max($subtotal - $diskonPo, 0);

        // Hitung nominal invoice
        $nominalInvoice = $nominalPo * $request->persentase_termin / 100;

        $nilaiDiskon = $request->nilai_diskon ?? 0;

        if ($nilaiDiskon > 0) {

            $diskonInvoice = $request->tipe_diskon === 'persen'
                ? $nominalInvoice * $nilaiDiskon / 100
                : $nilaiDiskon;

            $totalAfterDiscountInv = max($nominalInvoice - $diskonInvoice, 0);
        } else {

            $diskonInvoice = 0;
            $totalAfterDiscountInv = null; // 🔥 tidak disimpan
        }

        $base = ($totalAfterDiscountInv > 0 && $totalAfterDiscountInv != $nominalInvoice)
            ? $totalAfterDiscountInv
            : $nominalInvoice;
        // // DPP
        // $dpp = round(($base * 11) / 12);
        // $ppn = round(($dpp * 12) / 100);

        // // Grand total
        // $grandTotal = $base + $ppn;


        // =====================
        // DEFAULT (tanpa pajak)
        // =====================
        $dpp = 0;
        $ppn = 0;
        $grandTotal = $base; // default = base

        // =====================
        // CEK PAJAK
        // =====================
        if ($request->filled('tax')) {

            $selectedTaxes = $request->tax;

            // misal ID COA PPN kamu
            $ppnCoaId = 1; // ganti sesuai ID PPN kamu

            if (in_array($ppnCoaId, $selectedTaxes)) {

                $dpp = round(($base * 11) / 12);
                $ppn = round(($dpp * 12) / 100);

                $grandTotal = $base + $ppn;
            }
        }

        // 4️⃣ Hitung termin
        $lastTermin = Invoice::where('po_id', $request->po_id)->max('termin_ke');
        $terminKe   = $lastTermin ? $lastTermin + 1 : 1;

        $tipeHarga     = $quotation->harga_tipe; // satuan / gabungan
        $hargaGabungan = null;

        if ($tipeHarga === 'gabungan') {
            $hargaGabungan = $quotation->harga_gabungan;
        }

        Log::info('TIPE HARGA DARI QUOTATION', [
            'harga_tipe'     => $tipeHarga,
            'harga_gabungan' => $hargaGabungan,
        ]);

        DB::beginTransaction();
        try {

            // 6️⃣ Simpan Invoice
            $invoice = Invoice::create([
                'no_invoice'        => $request->no_invoice,
                'po_id'             => $request->po_id,
                'customer_id'       => $customer->id,
                'jenis_invoice'     => $request->jenis_invoice,
                'termin_ke'         => $terminKe,
                'keterangan'        => $request->keterangan,
                'catatan'           => $request->catatan,
                'tgl_inv'           => $request->tgl_invoice,
                'tgl_jatuh_tempo'   => $request->tgl_jatuh_tempo,
                'subtotal'             => $subtotal,
                'diskon_po'            => $diskonPo,
                'nominal_po'           => $nominalPo,
                'persentase_termin' => $request->persentase_termin,
                'nominal_invoice'   => $nominalInvoice,
                'tipe_diskon'       => $request->tipe_diskon ?? NULL,
                'nilai_diskon'      => $request->nilai_diskon ?? 0,
                'total_after_diskon_inv' => $totalAfterDiscountInv,
                'dpp'                  => $dpp ?? NULL,
                'ppn'                   => $ppn ?? NULL,
                'grand_total'          => $grandTotal,

                // 🔥 KUNCI
                'harga_gabungan'    => $hargaGabungan,
            ]);

            Log::info('Invoice created', [
                'invoice_id'     => $invoice->id,
                'harga_gabungan' => $invoice->harga_gabungan,
            ]);

            // 7️⃣ Simpan Produk Invoice
            foreach ($request->items as $index => $item) {

                Log::info("ITEM LOOP {$index}", [
                    'perizinan_id' => $item['perizinan_id'],
                    'tipe_harga'   => $tipeHarga,
                    'qty'          => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                ]);

                // 🔹 HARGA GABUNGAN
                if ($tipeHarga === 'gabungan') {

                    ProdukInvoice::create([
                        'invoice_id'   => $invoice->id,
                        'perizinan_id' => $item['perizinan_id'],
                        'deskripsi'    => $item['deskripsi'] ?? null,
                        'qty'          => $item['qty'],
                        'harga_satuan' => null,
                    ]);

                    continue;
                }

                // 🔹 SATUAN
                if (empty($item['qty']) || empty($item['harga_satuan'])) {
                    Log::warning("Item satuan skipped {$index}", $item);
                    continue;
                }

                ProdukInvoice::create([
                    'invoice_id'   => $invoice->id,
                    'perizinan_id' => $item['perizinan_id'],
                    'deskripsi'    => $item['deskripsi'] ?? null,
                    'qty'          => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                ]);
            }

            // 8️⃣ Pajak
            if ($request->filled('tax')) {
                foreach ($request->tax as $coaId) {
                    TaxInvoice::create([
                        'invoice_id' => $invoice->id,
                        'coa_id'     => $coaId,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('finance.invoice_index')
                ->with('success', 'Invoice berhasil dibuat');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('STORE INVOICE FAILED', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function invoice_index()
    {
        $title = 'Data Invoice';
        $invoice = Invoice::with([
            'produk',   // ambil semua produk_invoice terkait
            'pajak.coa', // ambil semua tax_invoice + nama pajak
            'po.quotation.customer',
        ])->orderBy('id', 'desc')->get();

        foreach ($invoice as $inv) {
            $inv->total_hitung = TotalInvoiceHelper::calculateTotal($inv);
        }

        //cek debug total di index_inv
        // foreach ($invoice as $invoice) {
        //     dd(\App\Helpers\TotalInvoiceHelper::calculateTotalDebug($invoice));
        // }
        return view('pages.finance.invoice_index', compact('title', 'invoice'));
    }

    public function show($id)
    {
        $title = 'Detail Invoice';

        $invoice = Invoice::with([
            'produk.perizinan',
            'customer',
            'po.quotation',
            'pajak.coa'
        ])->findOrFail($id);


        // ======================
        // Ambil langsung dari DB
        // ======================
        $subtotal           = $invoice->subtotal;
        $diskonPO           = $invoice->diskon_po;
        $nominalPO          = $invoice->nominal_po;
        $nominalTermin      = $invoice->nominal_invoice;
        $diskonInvoice      = $invoice->nilai_diskon ?? 0;
        $afterDiscount      = $invoice->total_after_diskon_inv ?? $nominalTermin;
        $dpp                = $invoice->dpp;
        $ppn                = $invoice->ppn;
        $grandTotal         = $invoice->grand_total;

        return view('pages.finance.show', compact(
            'title',
            'invoice',
            'subtotal',
            'diskonPO',
            'nominalPO',
            'nominalTermin',
            'diskonInvoice',
            'afterDiscount',
            'dpp',
            'ppn',
            'grandTotal'
        ));
    }


    public function edit($id)
    {
        $title = 'edit invoice';
        $invoice = Invoice::with([
            'customer',
            'quotations.kawasan_industri',
            'quotations.kabupaten',
            'quotations.provinsi',
            'po',
            'produk.perizinan',
            'pajak',
            'po.quotation'
        ])->findOrFail($id);

        $subtotal = 0;
        foreach ($invoice->produk as $item) {
            if ($item->harga_tipe !== 'gabungan') {
                $subtotal += $item->qty * $item->harga_satuan;
            }
        }
        if ($subtotal === 0) {
            $subtotal = $invoice->harga_gabungan ?? 0;
        }

        $persentase_termin = $invoice->persentase_termin;

        // Ambil quotation utama yang ingin digunakan
        $quotation = $invoice->po ? $invoice->po->quotation : null;

        $poList = Po::all();
        $invoice->kabupaten_name = $quotation->kabupaten->nama ?? '-';
        $invoice->kawasan_name = $quotation && $quotation->kawasan_industri
            ? $quotation->kawasan_industri->nama_kawasan
            : '-';
        $invoice->detail_alamat = $quotation->detail_alamat ?? '-';

        $invoice->harga_gabungan = $quotation->harga_gabungan ?? 0;

        $invoice_sebelumnya = Invoice::where('po_id', $invoice->po_id)
            ->where('id', '<', $invoice->id)
            ->orderBy('id', 'desc')
            ->first();

        $perizinans = $quotation ? $quotation->perizinan : collect();

        $ppnList = Coa::where('id', 1)->get();
        if ($quotation->harga_tipe === 'gabungan') {

            $subtotal = (float) $quotation->harga_gabungan;
        } else {

            $subtotal = $quotation->perizinan->sum(function ($item) {
                return ($item->pivot->qty ?? 0) * ($item->pivot->harga_satuan ?? 0);
            });
        }
        //diskon after subtotal
        $tipeDiskonQuotation  = $quotation->diskon_tipe ?? null;
        $nilaiDiskonQuotation = $quotation->diskon_nilai ?? 0;

        // Hitung diskon dari subtotal
        $diskonQuotation = 0;

        if ($tipeDiskonQuotation && $nilaiDiskonQuotation > 0) {
            if ($tipeDiskonQuotation === 'nominal') {
                $diskonQuotation = $nilaiDiskonQuotation;
            } else {
                $diskonQuotation = $subtotal * $nilaiDiskonQuotation / 100;
            }
        }

        // Pastikan diskon tidak melebihi subtotal
        if ($diskonQuotation > $subtotal) {
            $diskonQuotation = $subtotal;
        }

        // Nominal PO setelah diskon
        $nominalPO = $subtotal - $diskonQuotation;
        $invoiceData = InvoiceCalculatorHelper::from($invoice)->calculate();
        $dppOld = $invoice->dpp ?? 0;
        // dd([
        //     'invoice_id' => $invoice->id,
        //     'dpp_lama' => $invoice->dpp,
        // ]);
        return view('pages.finance.edit', compact(
            'title',
            'invoice',
            'invoice_sebelumnya',
            'perizinans',
            'ppnList',
            'invoiceData',
            'subtotal',
            'persentase_termin',
            'diskonQuotation',
            'nominalPO',
            'dppOld'
        ));
    }


    // Update invoice
    public function update(Request $request, $id)
    {
        Log::info('=== START UPDATE INVOICE ===', ['invoice_id' => $id]);

        $invoice = Invoice::findOrFail($id);

        Log::info('Invoice found', $invoice->toArray());

        $validated = $request->validate([
            'tgl_inv' => 'required|date',
            'tgl_jatuh_tempo' => 'required|date',
            'jenis_invoice' => 'required',
            'keterangan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'persentase_termin' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'nominal_invoice' => 'required|numeric',
            'tipe_diskon' => 'nullable|string',
            'nilai_diskon' => 'nullable|numeric',
            'total_after_discount' => 'required|numeric',
            'total' => 'required|numeric',
            'items' => 'required|array|min:1',
            'items.*.perizinan_id' => 'required|exists:perizinans,id',
            'items.*.qty' => 'nullable|numeric',
            'items.*.harga_satuan' => 'nullable|numeric',
            'items.*.description' => 'nullable|string',
            'items.*.harga_tipe' => 'nullable|string',
            'tax' => 'nullable|array'
        ]);

        Log::info('Validated data', $validated);

        DB::beginTransaction();
        try {

            /* ===============================
         | 1️⃣ Update invoice header
         =============================== */
            $invoice->update([
                'tgl_inv' => $validated['tgl_inv'],
                'tgl_jatuh_tempo' => $validated['tgl_jatuh_tempo'],
                'jenis_invoice' => $validated['jenis_invoice'],
                'keterangan' => $validated['keterangan'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'persentase_termin' => $validated['persentase_termin'],
                'subtotal' => $validated['subtotal'],
                'nominal_invoice' => $validated['nominal_invoice'],
                'tipe_diskon' => $validated['tipe_diskon'] ?? 'nominal',
                'nilai_diskon' => $validated['nilai_diskon'] ?? 0,
                'total_after_discount' => $validated['total_after_discount'],
                'total' => $validated['total'],
            ]);

            Log::info('Invoice header updated');

            /* ===============================
         | 2️⃣ Replace PRODUK invoice
         =============================== */
            Log::info('Deleting old produk invoice');
            $invoice->produk()->delete();

            foreach ($validated['items'] as $index => $item) {
                Log::info("Insert produk invoice {$index}", $item);

                $invoice->produk()->create([
                    'perizinan_id' => $item['perizinan_id'],
                    'qty' => $item['qty'] ?? 0,
                    'harga_satuan' => $item['harga_satuan'] ?? 0,
                    'description' => $item['description'] ?? null,
                    'harga_tipe' => $item['harga_tipe'] ?? 'satuan',
                ]);
            }

            Log::info('Produk invoice replaced');

            /* ===============================
         | 3️⃣ Replace PAJAK invoice
         =============================== */
            Log::info('Deleting old pajak');
            $invoice->pajak()->delete();

            if (!empty($validated['tax'])) {
                foreach ($validated['tax'] as $coaId) {
                    Log::info('Insert pajak', ['coa_id' => $coaId]);

                    $invoice->pajak()->create([
                        'coa_id' => $coaId
                    ]);
                }
            }

            Log::info('Pajak updated');

            DB::commit();
            Log::info('=== UPDATE INVOICE SUCCESS ===', ['invoice_id' => $invoice->id]);

            return redirect()
                ->route('finance.invoice_index')
                ->with('success', 'Invoice berhasil diperbarui');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('=== UPDATE INVOICE FAILED ===', [
                'invoice_id' => $id,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function print($id)
    {
        $invoice = Invoice::with([
            'customer',
            'produk.perizinan',
            'produk',
            'pajak.coa',
            'po',
            'po.quotation.kabupaten',
            'po.quotation.provinsi',
            'po.quotation.kawasan_industri',
            'po.quotation.perizinan',
            'po.quotation.customer',
        ])->findOrFail($id);

        // 🔥 SIMPAN HASIL KE VARIABEL LAIN
        $calc = InvoiceCalculatorHelper::from($invoice)->calculate();
        // dd($calc);
        // LOGO BASE64
        $path = public_path('assets/images/simply.png');

        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
        } else {
            $logo = null;
        }

        $pdf = Pdf::loadView('pages.finance.print', [
            'invoice' => $invoice,  // tetap object
            'logo' => $logo,
            'calc' => $calc,        // hasil helper
        ])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
            ]);
        // dd([
        //     'invoice_id'        => $invoice->id,
        //     'tipe_diskon'       => $invoice->tipe_diskon,
        //     'nilai_diskon'      => $invoice->nilai_diskon,
        //     'persentase_termin'=> $invoice->persentase_termin,
        // ]);

        return $pdf->stream('invoice.pdf');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $invoice = Invoice::findOrFail($id);

            $invoice->produk()->delete();
            $invoice->pajak()->delete();
            $invoice->delete();
        });

        return redirect()
            ->route('finance.invoice_index')
            ->with('success', 'Invoice berhasil dihapus');
    }

    public function akun_index()
    {
        $title = 'Akun';

        $akun = Coa::whereNull('parent_akun_id')
            ->with('children')
            ->orderBy('kode_akun')
            ->get();
        $akunHeader = Coa::where('is_header_akun', 1)
            ->orderBy('kode_akun')
            ->get();
        return view(
            'pages.finance.coa.akun_index',
            compact('title', 'akun', 'akunHeader')
        );
    }


    public function akun_store(Request $request)
    {
        $validated = $request->validate([
            'kode_akun'     => 'required|string|max:20|unique:coa,kode_akun',
            'nama_akun'     => 'required|string|max:50',
            'kategori_akun' => 'required|in:Kas & Bank,Akun Piutang,Persediaan,Aktiva Lancar Lainnya,Aktiva Tetap,Depresiasi & Amortisasi,Akun Hutang,Kewajiban Lancar Lainnya,Ekuitas,Pendapatan,Harga Pokok Penjualan,Beban,Pendapatan Lainnya,Beban Lainnya',
            'saldo'         => 'nullable|numeric|min:0',
            'is_header_akun'  => 'nullable|boolean',
            'is_sub_account'  => 'nullable|boolean',
            'parent_akun_id'  => 'nullable|exists:coa,id'
        ]);

        Coa::create([
            'kode_akun'     => $validated['kode_akun'],
            'nama_akun'     => $validated['nama_akun'],
            'nilai_coa'     => $validated['nilai_coa'] ?? 0,
            'kategori_akun' => $validated['kategori_akun'],
            'saldo'         => $validated['saldo'] ?? 0,
            'is_header_akun' => $request->has('is_header_akun'),
            'is_sub_account' => $request->has('is_sub_account'),
            'parent_akun_id' => $validated['parent_akun_id'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Akun berhasil ditambahkan');
    }
}
