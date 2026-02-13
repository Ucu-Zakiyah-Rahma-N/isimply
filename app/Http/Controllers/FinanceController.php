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
            'quotation.perizinan',
            'quotation.kawasan_industri',
            'quotation.kabupaten',
            'quotation.provinsi',
            'invoices'
        ])->findOrFail($po_id);

        $quotation = $po->quotation;
        $perizinans = $quotation->perizinan;
        $ppnList = Coa::select('id', 'nama_akun', 'nilai_coa')->get();

        $items = $quotation->perizinan; // contoh relasi

        $subtotal = 0;
        $hasNonGabungan = false;

        foreach ($items as $item) {
            if ($item->pivot->harga_tipe !== 'gabungan') {
                $qty   = $item->pivot->qty ?? 0;
                $harga = $item->pivot->harga_satuan ?? 0;
                $subtotal += $qty * $harga;
                $hasNonGabungan = true;
            }
        }

        // JIKA SEMUA ITEM GABUNGAN
        if (!$hasNonGabungan) {
            $subtotal = (float) $quotation->harga_gabungan;
        }

        Log::info('DEBUG SUBTOTAL', [
            'subtotal' => $subtotal
        ]);

        // dd($subtotal);

        // $totalTermin = $po->quotation->jumlah_termin ?? 0;
        // $invoiceTerbuat = $po->invoice->count();

        // if ($invoiceTerbuat >= $totalTermin) {
        //     abort(403, 'Invoice sudah lengkap');
        // }
        // $terminKe = $invoiceTerbuat + 1;
        // $sisaTermin = $totalTermin - $invoiceTerbuat;

        $po = PO::with('customer')->findOrFail($po_id);

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

        // Ambil schedule termin dari quotation
        $terminSchedule = json_decode($quotation->termin_persentase);

        // Tentukan invoice berikutnya
        $invoiceTerbuat = $po->invoice_terbuat ?? 0; // invoice yang sudah ada
        $terminKe = $invoiceTerbuat;                 // index berikutnya

        $persentaseTermin = $terminSchedule[$terminKe]->persen ?? 0; // default 0% jika tidak ada

        // Ambil invoice sebelumnya
        $invoice_sebelumnya = $po->invoices()->orderBy('termin_ke', 'desc')->first();
        $invoiceTerbuat = $po->invoices->count(); // jumlah invoice yang sudah dibuat           
        $terminKe = $invoiceTerbuat + 1;          // termin berikutnya
        // Ambil schedule termin dari quotation (misal [50,50] atau [30,40,30])
        $terminSchedule = json_decode($quotation->termin_persentase, true);

        $persentaseTermin = $terminSchedule[$invoiceTerbuat]['persen'] ?? 0;

        // Hitung nominal invoice
        $subtotal = 0;
        $hasNonGabungan = false;

        foreach ($quotation->perizinan as $item) {
            if ($item->pivot->harga_tipe !== 'gabungan') {
                $qty   = $item->pivot->qty ?? 0;
                $harga = $item->pivot->harga_satuan ?? 0;
                $subtotal += $qty * $harga;
                $hasNonGabungan = true;
            }
        }

        // Jika semua gabungan
        if (!$hasNonGabungan) {
            $subtotal = (float) $quotation->harga_gabungan;
        }

        $nominalInvoice = $subtotal * $persentaseTermin / 100;
        return view('pages.finance.create', [
            'title' => $title,
            'customer' => $customer,
            'po_id' => $po_id,
            'po' => $po,
            'no_po' => $po->no_po,
            'quotation' => $quotation,
            'perizinans'  => $perizinans,
            'no_invoice' => $noInvoice,
            'customer' => $customer,
            'termin_ke' => $terminKe,
            'persentaseTermin' => $persentaseTermin,
            // 'sisa_termin' => $sisaTermin,
            'invoice_sebelumnya' => $invoice_sebelumnya,
            'ppnList' => $ppnList,
            'subtotal' => $subtotal,
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
        $nominalInvoice = $request->subtotal * $request->persentase_termin / 100;

        // 4️⃣ Hitung termin
        $lastTermin = Invoice::where('po_id', $request->po_id)->max('termin_ke');
        $terminKe   = $lastTermin ? $lastTermin + 1 : 1;

        // 5️⃣ Ambil PO + Quotation (INI PENTING)
        $po        = PO::with('quotation')->findOrFail($request->po_id);
        $quotation = $po->quotation;

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
                'tipe_diskon'       => $request->tipe_diskon ?? 'nominal',
                'nilai_diskon'      => $request->nilai_diskon ?? 0,
                'persentase_termin' => $request->persentase_termin,
                'nominal_invoice'   => $nominalInvoice,

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
        $title = 'detail invoice';
        $invoice = Invoice::with([
            'produk.perizinan',
            'customer',
            'po',
            'po.quotation',
            'pajak.coa'
        ])->findOrFail($id);
        $poList = Po::all();

        // ======================
        // 1️⃣ Subtotal
        // ======================
        $subtotal = $invoice->produk->sum(function ($item) {
            return ($item->qty ?? 0) * ($item->harga_satuan ?? 0);
        });

        // Jika semua gabungan -> pakai harga_gabungan
        if ($subtotal == 0 && isset($invoice->po->quotation->harga_gabungan)) {
            $subtotal = $invoice->po->quotation->harga_gabungan;
        }

        // ======================
        // 2️⃣ Nominal sesuai termin
        // ======================
        $nominalTermin = $subtotal * ($invoice->persentase_termin / 100);

        // ======================
        // 3️⃣ Diskon
        // ======================
        $diskon = 0;
        if ($invoice->tipe_diskon && $invoice->nilai_diskon) {
            if ($invoice->tipe_diskon === 'nominal') {
                $diskon = $invoice->nilai_diskon;
            } else {
                $diskon = $nominalTermin * $invoice->nilai_diskon / 100;
            }

            if ($diskon > $nominalTermin) $diskon = $nominalTermin;
        }

        $afterDiscount = $nominalTermin - $diskon;

        // ======================
        // 4️⃣ Pajak
        // ======================
        $totalPPN = 0;
        $totalPPH = 0;
        $taxes_detail = [];
        foreach ($invoice->pajak as $tax) {
            $rate = $tax->coa->nilai_coa ?? 0;
            $name = strtolower($tax->coa->nama_akun ?? '');
            $amount = $afterDiscount * $rate / 100;

            if (str_contains($name, 'pph')) $totalPPH += $amount;
            else $totalPPN += $amount;

            $taxes_detail[] = [
                'name' => $tax->coa->nama_akun,
                'rate' => $rate,
                'amount' => $amount
            ];
        }

        // ======================
        // 5️⃣ Total akhir
        // ======================
        $totalAkhir = $afterDiscount + $totalPPN - $totalPPH;

        return view('pages.finance.show', compact(
            'title',
            'invoice',
            'subtotal',
            'nominalTermin',
            'diskon',
            'afterDiscount',
            'totalPPN',
            'totalPPH',
            'totalAkhir',
            'taxes_detail'
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
        $ppnList = COA::all(); // list pajak untuk checkbox

        $invoiceData = InvoiceCalculatorHelper::from($invoice)->calculate();

        return view('pages.finance.edit', compact(
            'title',
            'invoice',
            'invoice_sebelumnya',
            'perizinans',
            'ppnList',
            'invoiceData',
            'subtotal',
            'persentase_termin',
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
}
