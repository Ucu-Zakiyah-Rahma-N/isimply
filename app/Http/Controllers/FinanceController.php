<?php

namespace App\Http\Controllers;

use App\Models\PO;
use App\Models\Wilayah;
use App\Models\invoice;
use App\Models\Customer;
use App\Models\Perizinan;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProdukInvoice;
use App\Models\TaxInvoice;
use App\Models\InvoicePayment;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\QuotationPerizinan;

use App\Helpers\TotalInvoiceHelper;
use App\Helpers\InvoiceCalculatorHelper;
use App\Helpers\JournalHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

//finance itu seputar invoice, Data yang sudah invoice, Data Piutang, Data Outstanding, Data Penerimaan Bulan Ini
//Data PPN Bulanan dan tahunan, Data PPh Bulanan dan tahunan, Data PPN dan PPh

class FinanceController extends Controller
{

public function index(Request $request)
{
    $title = 'BAST Finance';
    $tahunSekarang = now()->year;
    $tahunDipilih = $request->tahun ?? 'all';

    $query = PO::with([
        'customer',
        'quotation.kabupaten',
        'quotation.kawasan_industri',
        'quotation.perizinan',
        'invoices'
    ])
    ->where('bast_verified', 1);

    // ✅ FILTER TAHUN (HARUS DI QUERY, BUKAN COLLECTION)
    if ($tahunDipilih !== 'all') {
        $query->whereYear('tgl_po', $tahunDipilih);
    }

    $po = $query->orderBy('tgl_po', 'desc')->get();

    $po->each(function ($po) {
        $po->total_termin = $po->quotation->jumlah_termin ?? 0;
        $po->invoice_terbuat = $po->invoices->count();
        $po->sisa_termin = $po->total_termin - $po->invoice_terbuat;
    });

    foreach ($po as $item) {
        $quotation = $item->quotation;

        $item->kabupaten_name = $quotation->kabupaten->nama ?? '-';

        $item->kawasan_name = $quotation && $quotation->kawasan_industri
            ? $quotation->kawasan_industri->nama_kawasan
            : '-';

        $item->detail_alamat = $quotation->detail_alamat ?? '-';

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

        $invoice = $item->invoices->first();
        $status = 'ongoing';

        if ($invoice) {
            if ($invoice->status === 'paid') {
                $status = 'done';
            } elseif (in_array($invoice->status, ['posted', 'partial'])) {
                $status = 'ongoing';
            }
        }

        // $statuses = $item->invoices->pluck('status');

        // if ($item->is_hold) {
        //     $status = 'hold';
        // } elseif ($statuses->isNotEmpty() && $statuses->every(fn($s) => $s === 'paid')) {
        //     $status = 'done';
        // } else {
        //     $status = 'ongoing';
        // }

        // $item->status_label = $status;
        $totalTermin = $item->quotation->jumlah_termin ?? 0;
        $totalInvoice = $item->invoices->count();

        $statuses = $item->invoices->pluck('status');

        if ($item->is_hold) {
            $status = 'hold';
        } elseif (
            $totalTermin > 0 &&
            $totalInvoice === $totalTermin &&
            $statuses->every(fn($s) => $s === 'paid')
        ) {
            $status = 'done';
        } else {
            $status = 'ongoing';
        }

        $item->status_label = $status;
        }

    return view('pages.finance.index', compact(
        'po',
        'title',
        'tahunDipilih',
        'tahunSekarang'
    ));
}
//     public function index()
// {
//     $title = 'BAST Finance';
//     $tahunSekarang = now()->year;
//     $tahunDipilih = $request->tahun ?? 'all';

//     // Ambil PO + relasi penting
//     $po = PO::with([
//         'customer',
//         'quotation.kabupaten',
//         'quotation.kawasan_industri',
//         'quotation.perizinan',
//         'invoices'
//     ])
//     ->where('bast_verified', 1)
//     ->orderBy('tgl_po', 'desc')
//     ->get();
//     if ($tahunDipilih !== 'all') {
//         $query->whereYear('tgl_po', $tahunDipilih);
//     }

//     $holdInvoice = session('hold_invoice', []); // array PO yang di-hold

//     $po->each(function ($po) {
//         $po->total_termin = $po->quotation->jumlah_termin ?? 0;
//         $po->invoice_terbuat = $po->invoices->count();
//         $po->sisa_termin = $po->total_termin - $po->invoice_terbuat;
//     });

//     foreach ($po as $item) {
//         $quotation = $item->quotation;

//         /* Kabupaten */
//         $item->kabupaten_name = $quotation->kabupaten->nama ?? '-';

//         /* Kawasan Industri */
//         $item->kawasan_name = $quotation && $quotation->kawasan_industri
//             ? $quotation->kawasan_industri->nama_kawasan
//             : '-';

//         /* Detail Alamat */
//         $item->detail_alamat = $quotation->detail_alamat ?? '-';

//         /* Luasan + Jenis Perizinan */
//         $luasList = [];
//         $izinList = [];

//         if (!is_null($quotation->luas_slf)) {
//             $luasList[] = 'SLF: ' . number_format($quotation->luas_slf, 2, ',', '.') . ' m²';
//             $izinList[] = 'SLF';
//         }
//         if (!is_null($quotation->luas_pbg)) {
//             $luasList[] = 'PBG: ' . number_format($quotation->luas_pbg, 2, ',', '.') . ' m²';
//             $izinList[] = 'PBG';
//         }
//         if (!is_null($quotation->luas_shgb)) {
//             $luasList[] = 'SHGB: ' . number_format($quotation->luas_shgb, 2, ',', '.') . ' m²';
//             $izinList[] = 'SHGB';
//         }

//         $item->luasan = count($luasList) ? implode(', ', $luasList) : '-';
//         $item->jenis_perizinan = $quotation?->perizinan->isNotEmpty()
//             ? $quotation->perizinan->pluck('jenis')->implode(', ')
//             : '-';

//         /* ============================
//            STATUS berdasarkan invoice
//         ============================ */
//         $invoice = $item->invoices->first(); // ambil invoice pertama
//         $status = 'ongoing'; // default

//         if ($invoice) {
//             if ($invoice->status === 'paid') {
//                 $status = 'done';
//             } elseif (in_array($invoice->status, ['posted', 'partial'])) {
//                 $status = 'ongoing';
//             }
//         }

//         // cek apakah PO sedang di-hold
//         if (in_array($item->id, $holdInvoice)) {
//             $status = 'hold';
//         }

//         $item->status_label = $status;
//     }

//     return view('pages.finance.index', compact('po', 'title'));
// }

    // public function holdInvoice($poId)
    // {
    //     $holdInvoice = session('hold_invoice', []);

    //     if (!in_array($poId, $holdInvoice)) {
    //         $holdInvoice[] = $poId;
    //     }

    //     session(['hold_invoice' => $holdInvoice]);

    //     return redirect()->back()->with('success', 'Invoice di-hold.');
    // }
    public function holdInvoice($poId)
    {
        $po = PO::findOrFail($poId);
        $po->is_hold = true;
        $po->save();

        return redirect()->back()->with('success', 'Invoice di-hold.');
    }

    public function unholdInvoice($poId)
    {
        $po = PO::findOrFail($poId);
        $po->is_hold = false;
        $po->save();

        return redirect()->back()->with('success', 'Invoice di-unhold.');
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
        $perizinan = Perizinan::all();
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
        $terminSchedule = $quotation->termin_persentase;
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
            'perizinan'  => $perizinan,
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
            'net_month' => 'required|integer|min:1',
            'items'             => 'required|array|min:1',
            'persentase_termin' => 'required|numeric|min:0|max:100',
        ]);

        // 2️⃣ Customer
        $customer = Customer::findOrFail($request->customer_id);

        // 3️⃣ Hitung nominal invoice
        // Ambil PO dulu
        $po        = PO::with('quotation', 'perizinan')->findOrFail($request->po_id);
        $quotation = $po->quotation;


        $tgl_invoice = Carbon::parse($request->tgl_invoice);
        $netMonth = (int) $request->net_month ?? 0;
        $tgl_jatuh_tempo = $tgl_invoice->copy()->addMonths($netMonth);


        $isGabungan = $po->harga_gabungan != null;

        // Ambil subtotal dari request
        $subtotal = $request->subtotal ?? 0;

        //validasi nominal subtotal manual harus sama dengan subtotal PO
        $subtotalPO = QuotationPerizinan::where('quotation_id', $po->quotation_id)
            ->sum(DB::raw('qty * harga_satuan'));

        // jika tidak ada item → pakai harga gabungan
        if ($subtotalPO == 0 && $po->quotation) {
            $subtotalPO = $po->quotation->harga_gabungan ?? 0;
        }

        // hanya validasi jika manual mode
        if (!$request->has('is_same_with_po')) {

            if ((float)$subtotal !== (float)$subtotalPO) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'subtotal' => 'Subtotal item manual harus sama dengan subtotal PO (Rp ' . number_format($subtotalPO, 0, ',', '.') . ')'
                    ]);
            }
        }

        // Diskon PO dari quotation
        $diskonPo = $quotation->diskon_nilai ?? 0;

        // Hitung nominal PO
        $nominalPo = max($subtotal - $diskonPo, 0);

        // Hitung nominal invoice
        $nominalInvoice = $nominalPo * $request->persentase_termin / 100;

        // $nilaiDiskon = $request->nilai_diskon ?? 0;
        $nilaiDiskon = (float) ($request->nilai_diskon ?? 0);
        $tipeDiskon  = $request->tipe_diskon ?? null;

        $diskonInvoice = 0;

        if ($nilaiDiskon > 0) {

            $diskonInvoice = $request->tipe_diskon === 'persen'
                ? $nominalInvoice * $nilaiDiskon / 100
                : $nilaiDiskon;
        }

        // if ($diskonInvoice > $nominalInvoice) {
        //     $diskonInvoice = $nominalInvoice;
        // }
        $diskonInvoice = min($diskonInvoice, $nominalInvoice);


        // ✅ SELALU ADA NILAI (buat hitungan)
        $totalAfterDiscountInv = $nominalInvoice - $diskonInvoice;

        // ======================
        // BASE (SELALU DARI TERMIN SETELAH DISKON)
        // ======================
        // $base = $totalAfterDiscountInv;

        // ======================
        // NILAI YANG DISIMPAN KE DB
        // ======================
        // $tipeDiskon = $request->tipe_diskon ?? null;

        $totalAfterDiscount = $nilaiDiskon > 0 ? $totalAfterDiscountInv : null;
        $nilaiDiskon        = $nilaiDiskon > 0 ? $nilaiDiskon : null;
        $tipeDiskon         = $nilaiDiskon > 0 ? $tipeDiskon : null;


        // ======================
        // CEK PPN SOURCE
        // ======================
        $isPpnAllPo = $request->has('ppn_all_po');

        // $ppnSource = null;
        $ppnSource = $request->has('ppn_all_po') ? 'all_po' : 'per_termin';

        if ($isPpnAllPo) {
            $ppnSource = 'all_po';
        } elseif ($request->filled('tax')) {
            $ppnCoaId = 1;
            if (in_array($ppnCoaId, $request->tax)) {
                $ppnSource = 'per_termin';
            }
        }


        // ======================
        // HITUNG PAJAK
        // ======================

        $base = $totalAfterDiscountInv;
        $dpp = 0;
        $ppn = 0;
        $grandTotal = $base; // default tanpa pajak


        $selectedTaxes = $request->tax ?? [];
        $ppnCoaId = 1;

        // ======================
        // PASTIKAN PPN MASUK TAX
        // ======================
        if ($ppnSource === 'all_po' && !in_array($ppnCoaId, $selectedTaxes)) {
            $selectedTaxes[] = $ppnCoaId;
        }

        // ======================
        // HITUNG PAJAK
        // ======================
        if (in_array($ppnCoaId, $selectedTaxes)) {

            // DPP selalu dari base
            $dpp = round(($base * 11) / 12);

            if ($ppnSource === 'all_po') {

                // ✅ PPN dari NOMINAL PO
                $ppn = round(($nominalPo * 11) / 100);
            } else {

                // ✅ PPN dari TERMIN
                $ppn = round(($dpp * 12) / 100);
            }

            $grandTotal = $base + $ppn;
        }

        // 4️⃣ Hitung termin
        $lastTermin = Invoice::where('po_id', $request->po_id)->max('termin_ke');
        $terminKe   = $lastTermin ? $lastTermin + 1 : 1;

        $tipeHarga     = $quotation->harga_tipe; // satuan / gabungan
        $hargaGabungan = null;

        // if ($tipeHarga === 'gabungan') {
        //     $hargaGabungan = $quotation->harga_gabungan;
        // }

        Log::info('TIPE HARGA DARI QUOTATION', [
            'harga_tipe'     => $tipeHarga,
            'harga_gabungan' => $hargaGabungan,
        ]);

        DB::beginTransaction();
        $coaPiutangId   = 13;
        $coaPpnId        = 1;
        $coaPendapatanId = 56;

        try {
            $isSameWithPo = $request->has('is_same_with_po') ? 1 : 0;
            $hargaGabungan = ($isSameWithPo && $quotation->harga_tipe === 'gabungan')
                ? $quotation->harga_gabungan
                : null;

            // 6️⃣ Simpan Invoice
            $invoice = Invoice::create([
                'coa_piutang_id' => $coaPiutangId,
                'no_invoice'        => $request->no_invoice,
                'po_id'             => $request->po_id,
                'customer_id'       => $customer->id,
                'jenis_invoice'     => $request->jenis_invoice,
                'termin_ke'         => $terminKe,
                'keterangan'        => $request->keterangan,
                'catatan'           => $request->catatan,
                'tgl_inv'           => $request->tgl_invoice,
                'net_month'         => $netMonth,
                'tgl_jatuh_tempo'   => $tgl_jatuh_tempo,
                'subtotal'             => $subtotal,
                'diskon_po'            => $diskonPo,
                'nominal_po'           => $nominalPo,
                'persentase_termin' => $request->persentase_termin,
                'nominal_invoice'   => $nominalInvoice,
                'tipe_diskon' => $tipeDiskon,
                'nilai_diskon' => $nilaiDiskon,
                'total_after_diskon_inv' => $totalAfterDiscountInv,
                'total_after_diskon' => $totalAfterDiscount,
                'dpp'                  => $dpp ?? NULL,
                'ppn'                   => $ppn ?? NULL,
                'grand_total'          => $grandTotal,

                'ppn_source' => $ppnSource,

                'harga_gabungan'    => $hargaGabungan,
                'is_same_with_po'   => $isSameWithPo,
            ]);

            Log::info('Invoice created', [
                'invoice_id'     => $invoice->id,
                'harga_gabungan' => $invoice->harga_gabungan,
            ]);

            Log::info('PPN SOURCE', [
                'ppn_source' => $ppnSource
            ]);

            // 7️⃣ Simpan Produk Invoice
            $isInvoiceGabungan = $request->has('is_same_with_po') && $quotation->harga_tipe === 'gabungan';

            foreach ($request->items as $item) {

                $perizinan_id = $item['perizinan_id'] ?? null; // dari PO langsung
                $perizinan_lainnya = $item['perizinan_lainnya'] ?? null;

                // Hanya jika manual input (is_same_with_po = 0)
                if (!$request->has('is_same_with_po')) {
                    $input = $item['perizinan_input'] ?? null;

                    if ($input && str_starts_with($input, 'id:')) {
                        $perizinan_id = str_replace('id:', '', $input);
                        $perizinan_lainnya = null;
                    } else {
                        $perizinan_lainnya = $input;
                        $perizinan_id = null;
                    }
                }

                ProdukInvoice::create([
                    'invoice_id'        => $invoice->id,
                    'perizinan_id'      => $perizinan_id,
                    'perizinan_lainnya' => $perizinan_lainnya,
                    'deskripsi'         => $item['deskripsi'] ?? null,
                    'qty'               => $item['qty'] ?? 1,
                    'harga_satuan'      => $item['harga_satuan'] ?? null,
                ]);
            }

            // 8️⃣ Pajak
            if (!empty($selectedTaxes)) {
                foreach ($selectedTaxes as $coaId) {
                    TaxInvoice::create([
                        'invoice_id' => $invoice->id,
                        'coa_id'     => $coaId,
                    ]);
                }
            }

            $journal = Journal::create([
                'tanggal'     => $request->tgl_invoice,
                'no_jurnal'   => Journal::generateNo(),
                'keterangan'  => 'Invoice ' . $invoice->no_invoice,
                'ref_type'    => 'invoice',
                'ref_id'      => $invoice->id,
                'invoice_id'  => $invoice->id,
            ]);

            // PIUTANG
            JournalDetail::create([
                'journal_id' => $journal->id,
                'coa_id'     => $coaPiutangId,
                'debit'      => $grandTotal,
                'credit'     => 0,
            ]);

            $pendapatan = $totalAfterDiscountInv ?? $nominalInvoice;

            JournalDetail::create([
                'journal_id' => $journal->id,
                'coa_id'     => $coaPendapatanId,
                'debit'      => 0,
                'credit'     => $pendapatan,
            ]);

            // PPN (optional)
            if ($ppn > 0) {
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'coa_id'     => $coaPpnId,
                    'debit'      => 0,
                    'credit'     => $ppn,
                ]);
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
            'payments'
        ])->orderBy('id', 'desc')->get();


        foreach ($invoice as $inv) {
            $inv->total_hitung = TotalInvoiceHelper::calculateTotal($inv);

            $inv->nilai_pph = $inv->payments->isNotEmpty()
                ? $inv->payments->last()->nilai_pph
                : 0;

            $inv->nominal = $inv->payments->isNotEmpty()
                ? $inv->payments->last()->nominal
                : 0;
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
        $diskonInvoice = 0;

        if (!empty($invoice->nilai_diskon)) {

            if ($invoice->tipe_diskon === 'persen') {
                $diskonInvoice = ($invoice->nominal_invoice * $invoice->nilai_diskon) / 100;
            } else {
                $diskonInvoice = $invoice->nilai_diskon;
            }
        }
        $afterDiscount      = $invoice->total_after_diskon_inv ?? $nominalTermin;
        $dpp                = $invoice->dpp;
        $ppn                = $invoice->ppn;
        $grandTotal         = $invoice->grand_total;


        $payments = $invoice->payments()->orderBy('tanggal')->get();

        $totalCash = $payments->sum('nominal');
        $totalPph  = $payments->sum('nilai_pph');
        $totalTertutup = $payments->sum(function ($p) {
            return $p->nominal + $p->nilai_pph;
        });

        $sisaTagihan = $invoice->grand_total - $totalTertutup;

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
            'grandTotal',
            'payments',
            'totalCash',
            'totalPph',
            'totalTertutup',
            'sisaTagihan'


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

        $invoiceItems = $invoice->produk()->get();

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


        $isSameWithPo = $invoice->is_same_with_po;

        $perizinans = $quotation ? $quotation->perizinan : collect();
        $perizinan = Perizinan::orderBy('jenis')->get();

        $ppnList = Coa::where('id', 1)->get();

        $isGabungan = $quotation && $quotation->harga_tipe === 'gabungan';

        if ($quotation && $quotation->harga_tipe === 'gabungan') {

            // 🔥 ambil dari invoice, bukan quotation
            $subtotal = (float) $invoice->subtotal;
        } else {

            // kalau bukan gabungan, boleh hitung dari produk invoice
            $subtotal = $invoice->produk->sum(function ($item) {
                return ($item->qty ?? 0) * ($item->harga_satuan ?? 0);
            });
        }
        // dd($invoice->harga_gabungan);   

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
            'perizinan',
            'ppnList',
            'invoiceData',
            'subtotal',
            'persentase_termin',
            'diskonQuotation',
            'nominalPO',
            'dppOld',
            'isSameWithPo',
            'isGabungan',
            'quotation',
            'invoiceItems'

        ));
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        DB::beginTransaction();

        try {

            Log::info("=== START UPDATE INVOICE ===", [
                'invoice_id' => $invoice->id,
                'request_raw' => $request->all()
            ]);

            // ===============================
            // VALIDASI
            // ===============================
            $validated = $request->validate([
                'tgl_inv' => 'required|date',
                'net_month' => 'required|integer|min:1',
                'jenis_invoice' => 'required',
                'persentase_termin' => 'required|numeric',

                'diskon_po' => 'nullable|numeric',
                'tipe_diskon' => 'nullable|string',
                'nilai_diskon' => 'nullable|numeric',

                'is_same_with_po' => 'nullable|boolean',

                'items' => 'required|array',
                'items.*.id' => 'nullable|exists:produk_invoice,id',
                'items.*.perizinan_input' => 'nullable|string',
                'items.*.qty' => 'required|numeric|min:1',
                // 'items.*.harga_satuan' => 'nullable|numeric|min:0',
                'items.*.harga_satuan' => $request->has('is_same_with_po')
                    ? 'nullable|numeric|min:0'
                    : 'required|numeric|min:0',

                'tax' => 'nullable|array',
            ]);

            $isSameWithPo = (int) $request->input('is_same_with_po', 0);
            $isPpnAllPo   = $request->has('ppn_all_po');

            // ===============================
            // SUBTOTAL
            // ===============================
            if ($isSameWithPo) {
                $subtotal = (float) $request->subtotal;
            } else {
                $subtotal = collect($validated['items'])->sum(function ($item) {
                    return ($item['qty'] ?? 1) * ($item['harga_satuan'] ?? 0);
                });
            }

            $diskonPo = $validated['diskon_po'] ?? 0;
            $nominalPo = max($subtotal - $diskonPo, 0);

            $persenTermin = $validated['persentase_termin'];
            $nominalInvoice = $nominalPo * $persenTermin / 100;

            // ===============================
            // DISKON
            // ===============================
            // $tipeDiskon = $request->tipe_diskon;
            // $nilaiDiskon = (float) ($request->nilai_diskon ?? 0);

            // $jumlahDiskon = 0;
            // $totalAfterDiskon = null;

            // if ($nilaiDiskon > 0) {
            //     $jumlahDiskon = $tipeDiskon === 'persen'
            //         ? ($nominalInvoice * $nilaiDiskon / 100)
            //         : $nilaiDiskon;

            //     $totalAfterDiskon = max($nominalInvoice - $jumlahDiskon, 0);
            // }

            // $base = $totalAfterDiskon ?? $nominalInvoice;


            // ===============================
            // DISKON (FIX FINAL)
            // ===============================
            $tipeDiskon = $request->tipe_diskon;
            $nilaiDiskonInput = $request->nilai_diskon;

            // default (anggap tidak ada diskon)
            $nilaiDiskon = null;
            $jumlahDiskon = 0;
            $totalAfterDiskon = null;

            // hanya proses kalau benar2 diisi (> 0)
            if ($nilaiDiskonInput !== null && $nilaiDiskonInput !== '' && $nilaiDiskonInput > 0) {

                $nilaiDiskon = (float) $nilaiDiskonInput;

                $jumlahDiskon = $tipeDiskon === 'persen'
                    ? ($nominalInvoice * $nilaiDiskon / 100)
                    : $nilaiDiskon;

                $totalAfterDiskon = max($nominalInvoice - $jumlahDiskon, 0);
            } else {

                // 🔥 reset kalau kosong / 0
                $tipeDiskon = null;
                $nilaiDiskon = null;
            }

            $base = $totalAfterDiskon ?? $nominalInvoice;
            // ===============================
            // TAX
            // ===============================
            $ppnCoaId = 1;
            $selectedTaxes = $request->tax ?? [];

            if ($isPpnAllPo && !in_array($ppnCoaId, $selectedTaxes)) {
                $selectedTaxes[] = $ppnCoaId;
            }

            $ppnSource = empty($selectedTaxes)
                ? null
                : ($isPpnAllPo ? 'all_po' : 'per_termin');

            $dpp = 0;
            $ppn = 0;
            $grandTotal = $base;

            if (in_array($ppnCoaId, $selectedTaxes)) {

                $dpp = round(($base * 11) / 12);

                if ($ppnSource === 'all_po') {
                    $ppn = round(($nominalPo * 11) / 100);
                } else {
                    $ppn = round(($dpp * 12) / 100);
                }

                $grandTotal = $base + $ppn;
            }

            Log::info("CALC RESULT", compact(
                'subtotal',
                'nominalPo',
                'nominalInvoice',
                'ppn',
                'grandTotal',
                'ppnSource'
            ));

            // ===============================
            // AMBIL NILAI LAMA (PALING AMAN)
            // ===============================
            $oldGrandTotal     = $invoice->grand_total;
            $oldNominalInvoice = $invoice->nominal_invoice;
            $oldPpn            = $invoice->ppn;


            $tgl_inv = Carbon::parse($validated['tgl_inv']);
            $netMonth = (int) $request->net_month;

            $tgl_jatuh_tempo = $tgl_inv->copy()->addMonths($netMonth);
            // ===============================
            // UPDATE HEADER
            // ===============================
            $invoice->update([
                'tgl_inv' => $validated['tgl_inv'],
                'net_month' => $netMonth,
                'tgl_jatuh_tempo' => $tgl_jatuh_tempo,
                'jenis_invoice' => $validated['jenis_invoice'],
                'persentase_termin' => $persenTermin,

                'subtotal' => $subtotal,
                'diskon_po' => $diskonPo,
                'nominal_po' => $nominalPo,
                'nominal_invoice' => $nominalInvoice,
                'tipe_diskon' => $tipeDiskon,
                'nilai_diskon' => $nilaiDiskon,
                'total_after_diskon_inv' => $totalAfterDiskon,
                'dpp' => $dpp,
                'ppn' => $ppn,
                'grand_total' => $grandTotal,
                'ppn_source' => $ppnSource,

                'is_same_with_po' => $isSameWithPo,
            ]);


            // ===============================
            // PRODUK (FIX FINAL)
            // ===============================
            $existingIds = $invoice->produk()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($validated['items'] as $item) {

                $perizinan_id = null;
                $perizinan_lainnya = null;

                $input = $item['perizinan_input'] ?? null;

                if ($input) {
                    if (str_starts_with($input, 'id:')) {
                        $perizinan_id = str_replace('id:', '', $input);
                    } else {
                        $perizinan_lainnya = $input;
                    }
                }

                $data = [
                    'perizinan_id' => $perizinan_id,
                    'perizinan_lainnya' => $perizinan_lainnya,
                    'qty' => $item['qty'] ?? 1,
                    'deskripsi' => $item['deskripsi'] ?? null,
                    'harga_satuan' => $item['harga_satuan'] ?? 0,
                ];

                if (!empty($item['id'])) {

                    $produk = $invoice->produk()->where('id', $item['id'])->first();

                    if ($produk) {
                        $produk->update($data);
                        $submittedIds[] = $produk->id;
                    }
                } else {

                    $produk = $invoice->produk()->create($data);
                    $submittedIds[] = $produk->id;
                }
            }

            // delete aman
            // if (!empty($submittedIds)) {
            $deletedIds = array_diff($existingIds, $submittedIds);

            if (!empty($deletedIds)) {
                $invoice->produk()->whereIn('id', $deletedIds)->delete();
            }
            // }

            Log::info("PRODUK SYNC", ['deleted' => $deletedIds ?? []]);

            // ===============================
            // PAJAK
            // ===============================
            $invoice->pajak()->delete();

            foreach ($selectedTaxes as $coaId) {
                $invoice->pajak()->create(['coa_id' => $coaId]);
            }

            $financeChanged =
                $oldGrandTotal != $grandTotal ||
                $oldNominalInvoice != $nominalInvoice ||
                $oldPpn != $ppn;

            // ===============================
            // JURNAL (SMART SYNC)
            // ===============================
            $journal = Journal::where('ref_type', 'invoice')
                ->where('ref_id', $invoice->id)
                ->first();

            if ($journal) {

                // ✅ selalu update header (tanggal & keterangan)
                $journal->update([
                    'tanggal' => $validated['tgl_inv'],
                    'keterangan' => 'Invoice ' . $invoice->no_invoice,
                ]);

                // ✅ hanya update detail jika finansial berubah
                if ($financeChanged) {

                    Log::info("FINANCE CHANGED → UPDATE JOURNAL DETAIL", [
                        'invoice_id' => $invoice->id
                    ]);

                    $journal->journaldetails()->delete();

                    $pendapatan = $totalAfterDiskon ?? $nominalInvoice;

                    // PIUTANG (DEBIT)
                    $journal->journaldetails()->create([
                        'coa_id' => 13,
                        'debit'  => $grandTotal,
                        'credit' => 0,
                    ]);

                    // PENDAPATAN (CREDIT)
                    $journal->journaldetails()->create([
                        'coa_id' => 56,
                        'debit'  => 0,
                        'credit' => $pendapatan,
                    ]);

                    // PPN (CREDIT kalau ada)
                    if ($ppn > 0) {
                        $journal->journaldetails()->create([
                            'coa_id' => 1,
                            'debit'  => 0,
                            'credit' => $ppn,
                        ]);
                    }
                } else {

                    Log::info("NO FINANCIAL CHANGE → SKIP JOURNAL DETAIL", [
                        'invoice_id' => $invoice->id
                    ]);
                }
            }

            DB::commit();

            Log::info("=== SUCCESS UPDATE INVOICE ===", [
                'invoice_id' => $invoice->id
            ]);

            return redirect()
                ->route('finance.invoice_index')
                ->with('success', 'Invoice berhasil diperbarui');
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error("=== UPDATE FAILED ===", [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
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

        // SIMPAN HASIL KE VARIABEL LAIN
        $calc = InvoiceCalculatorHelper::from($invoice)->calculate();
        $terbilang = \App\Helpers\Terbilang::convert($calc['totalAkhir']);
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
            'terbilang' => $terbilang,        // hasil helper
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
        $invoice = Invoice::with(['payments', 'journal.details'])->findOrFail($id);

        DB::transaction(function () use ($invoice) {

            // ===============================
            // 1️⃣ DRAFT → DELETE FISIK
            // ===============================
            if ($invoice->status === 'draft') {

                $invoice->produk()->delete();
                $invoice->pajak()->delete();
                $invoice->delete();

                return;
            }

            // ===============================
            // 2️⃣ SUDAH ADA PAYMENT → REFUND
            // ===============================
            if ($invoice->payments()->exists()) {

                foreach ($invoice->payments as $payment) {

                    $journal = Journal::where('ref_type', 'invoice_payment')
                        ->where('ref_id', $payment->id)
                        ->first();

                    if ($journal) {

                        $reversalJournal = Journal::create([
                            'tanggal'     => now(),
                            'no_jurnal'   => Journal::generateNo(),
                            'keterangan'  => 'Refund Invoice ' . $invoice->no_invoice,
                            'ref_type'    => 'invoice_refund',
                            'ref_id'      => $payment->id,
                            'reversal_of' => $journal->id
                        ]);

                        foreach ($journal->journaldetails as $detail) {
                            JournalDetail::create([
                                'journal_id' => $reversalJournal->id,
                                'coa_id'     => $detail->coa_id,
                                'debit'      => $detail->credit,
                                'credit'     => $detail->debit,
                            ]);
                        }
                    }
                }
            }

            // ===============================
            // 3️⃣ POSTED → VOID + REVERSAL
            // ===============================
            if ($invoice->status === 'posted' || $invoice->status === 'paid') {

                $originalJournal = $invoice->journal;

                if ($originalJournal) {

                    $reversalJournal = Journal::create([
                        'invoice_id'  => $invoice->id,
                        'tanggal'     => now(),
                        'no_jurnal'   => Journal::generateNo(),
                        'status'      => 'posted',
                        'reversal_of' => $originalJournal->id,
                        'keterangan'  => 'Void Invoice ' . $invoice->no_invoice
                    ]);

                    foreach ($originalJournal->details as $detail) {
                        JournalDetail::create([
                            'journal_id' => $reversalJournal->id,
                            'coa_id'     => $detail->coa_id,
                            'debit'      => $detail->credit,
                            'credit'     => $detail->debit,
                        ]);
                    }
                }

                $invoice->update([
                    'status' => 'void',
                    'void_at' => now(),
                    'void_reason' => 'PO Cancelled'
                ]);
            }
        });

        return back()->with('success', 'Invoice berhasil diproses sesuai kondisi.');
    }






    
    // public function destroy($id)
    // {
    //     DB::transaction(function () use ($id) {
    //         $invoice = Invoice::findOrFail($id);

    //         // Cek apakah sudah ada pembayaran
    //         if ($invoice->payments()->exists()) {
    //             return redirect()
    //                 ->route('finance.invoice_index')
    //                 ->with('error', 'Invoice sudah memiliki pembayaran dan tidak dapat dihapus.');
    //         }


    //         $invoice->produk()->delete();
    //         $invoice->pajak()->delete();
    //         $invoice->delete();
    //     });

    //     return redirect()
    //         ->route('finance.invoice_index')
    //         ->with('success', 'Invoice berhasil dihapus');
    // }

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
            'saldo_awal'         => 'nullable|numeric|min:0',
            'is_header_akun'  => 'nullable|boolean',
            'is_sub_account'  => 'nullable|boolean',
            'parent_akun_id'  => 'nullable|exists:coa,id'
        ]);

        Coa::create([
            'kode_akun'     => $validated['kode_akun'],
            'nama_akun'     => $validated['nama_akun'],
            'nilai_coa'     => $validated['nilai_coa'] ?? 0,
            'kategori_akun' => $validated['kategori_akun'],
            'saldo_awal'         => $validated['saldo_awal'] ?? 0,
            'is_header_akun' => $request->has('is_header_akun'),
            'is_sub_account' => $request->has('is_sub_account'),
            'parent_akun_id' => $validated['parent_akun_id'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Akun berhasil ditambahkan');
    }



    public function terima_pembayaran($id)
    {
        $title = 'Terima Pembayaran';

        $invoice = Invoice::with([
            'customer',
            'quotations.kawasan_industri',
            'quotations.kabupaten',
            'quotations.provinsi',
            'po',
            'produk.perizinan',
            'pajak',
            'po.quotation',
            'payments'
        ])->findOrFail($id);

        $coaPendapatan = Coa::find(56);

        // ambil akun kas & bank
        $banks = Coa::whereIn('parent_akun_id', [3, 9])->get();

        // total sudah dibayar
        // $totalPaid = $invoice->payments()->sum('nominal');
        $totalPaid = $invoice->payments()
            ->sum(DB::raw('nominal + nilai_pph'));

        // $sisaTagihan = $invoice->grand_total - $totalPaid;

        // $pphSudahDibayar = $invoice->payments()->sum('nilai_pph');

        $lastPayment = $invoice->payments()->latest()->first();

        // Hitung sisa PPH dan PPH rate terakhir
        // $pphRateTerakhir = ($lastPayment && $lastPayment->nominal > 0)
        //     ? round(($lastPayment->nilai_pph / $lastPayment->nominal) * 100, 1)
        //     : 0;

        $lastPayment = $invoice->payments()->latest()->first();

        $pphRateTerakhir = 0;

        if ($lastPayment && $lastPayment->coa_pph_id) {
            switch ($lastPayment->coa_pph_id) {
                case 103:
                    $pphRateTerakhir = 2;
                    break;
                case 104:
                    $pphRateTerakhir = 3.5;
                    break;
                default:
                    $pphRateTerakhir = 0;
            }
        }
        $pphSudahDibayar = $invoice->payments()->sum('nilai_pph');

        $sisaTagihan = $invoice->grand_total - $invoice->payments()->sum(DB::raw('nominal + nilai_pph'));

        return view('pages.finance.terima_pembayaran', [
            'title' => $title,
            'invoice' => $invoice,
            'coaPendapatan' => $coaPendapatan,
            'banks' => $banks,
            'totalPaid' => $totalPaid,
            // 'sisaPiutang' => $sisaPiutang,
            'sisaTagihan' => $sisaTagihan,
            'pphRateTerakhir' => $pphRateTerakhir,
            'pphSudahDibayar' => $pphSudahDibayar,
        ]);
    }

    public function storePembayaran(Request $request)
{
    Log::info('=== START STORE PEMBAYARAN ===', [
        'request' => $request->all()
    ]);

    try {

        // =========================
        // NORMALISASI INPUT
        // =========================
        $request->merge([
            'nominal'   => preg_replace('/[^0-9]/', '', $request->nominal),
            'nilai_pph' => preg_replace('/[^0-9]/', '', $request->nilai_pph),
        ]);

        // =========================
        // VALIDASI
        // =========================
        $validated = $request->validate([
            'invoice_id'   => 'required|exists:invoice,id',
            'coa_bank_id'  => 'required|exists:coa,id',
            'nominal'      => 'required|numeric|min:1',
            'nilai_pph'    => 'nullable|numeric|min:0',
            'tanggal'      => 'required|date',
            'pph_rate'     => 'required|numeric'
        ]);

        DB::beginTransaction();

        // =========================
        // AMBIL DATA
        // =========================
        $invoice = Invoice::findOrFail($validated['invoice_id']);

        $nominalCash = (int) $validated['nominal'];
        $nilaiPph    = (int) ($validated['nilai_pph'] ?? 0);
        $pphRate     = (float) $validated['pph_rate'];

        Log::info('PARSED INPUT', compact('nominalCash', 'nilaiPph', 'pphRate'));

        // =========================
        // MAPPING COA PPH
        // =========================
        $coaPphId = null;

        if ($pphRate == 2) {
            $coaPphId = 103;
        } elseif ($pphRate == 3.5) {
            $coaPphId = 104;
        }

        Log::info('COA PPH', ['coa_pph_id' => $coaPphId]);

        // =========================
        // HITUNG TOTAL
        // =========================
        $totalMenutupPiutang = $nominalCash + $nilaiPph;

        $totalPaid = $invoice->payments()
            ->sum(DB::raw('nominal + nilai_pph'));

        $sisaPiutang = $invoice->grand_total - $totalPaid;

        Log::info('PIUTANG CHECK', compact(
            'totalMenutupPiutang',
            'totalPaid',
            'sisaPiutang'
        ));

        if ($totalMenutupPiutang > $sisaPiutang) {
            throw new \Exception('Pembayaran melebihi sisa piutang.');
        }

        // =========================
        // SIMPAN PEMBAYARAN
        // =========================
        $payment = InvoicePayment::create([
            'invoice_id'        => $invoice->id,
            'coa_bank_id'       => $validated['coa_bank_id'],
            'nominal'           => $nominalCash,
            'nilai_pph'         => $nilaiPph,
            'coa_pph_id'        => $coaPphId,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal'           => $validated['tanggal'],
            'keterangan'        => $request->keterangan,
        ]);

        Log::info('PAYMENT CREATED', ['payment_id' => $payment->id]);

        // =========================
        // BUAT JURNAL
        // =========================
        $journal = Journal::create([
            'tanggal'    => $validated['tanggal'],
            'no_jurnal'  => Journal::generateNo(),
            'keterangan' => 'Penerimaan Invoice ' . $invoice->no_invoice,
            'invoice_id' => $invoice->id,
            'ref_type'   => 'invoice_payment',
            'ref_id'     => $payment->id,
        ]);

        Log::info('JOURNAL CREATED', ['journal_id' => $journal->id]);

        // Debit Bank
        JournalDetail::create([
            'journal_id' => $journal->id,
            'coa_id'     => $validated['coa_bank_id'],
            'debit'      => $nominalCash,
            'credit'     => 0
        ]);

        // Debit PPH
        if ($nilaiPph > 0 && $coaPphId) {
            JournalDetail::create([
                'journal_id' => $journal->id,
                'coa_id'     => $coaPphId,
                'debit'      => $nilaiPph,
                'credit'     => 0
            ]);
        }

        // Credit Piutang
        JournalDetail::create([
            'journal_id' => $journal->id,
            'coa_id'     => $invoice->coa_piutang_id,
            'debit'      => 0,
            'credit'     => $totalMenutupPiutang
        ]);

        Log::info('JOURNAL DETAIL CREATED');

        // =========================
        // UPDATE STATUS INVOICE
        // =========================
        $totalPaidAfter = $invoice->payments()
            ->sum(DB::raw('nominal + nilai_pph'));

        $status = $totalPaidAfter >= $invoice->grand_total ? 'paid' : 'partial';

        $invoice->update(['status' => $status]);

        Log::info('INVOICE UPDATED', [
            'status' => $status,
            'total_paid_after' => $totalPaidAfter
        ]);

        DB::commit();

        Log::info('=== SUCCESS STORE PEMBAYARAN ===');

        return redirect()
            ->route('finance.invoice_index')
            ->with('success', 'Pembayaran berhasil disimpan');

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('=== ERROR STORE PEMBAYARAN ===', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return back()->withErrors([
            'error' => $e->getMessage()
        ]);
    }
}

    // public function storePembayaran(Request $request)
    // {
    //     $request->merge([
    //         'nominal'   => str_replace('.', '', $request->nominal),
    //         'nilai_pph' => str_replace('.', '', $request->nilai_pph),
    //     ]);

    //     $request->validate([
    //         'invoice_id'   => 'required|exists:invoice,id',
    //         'coa_bank_id'  => 'required|exists:coa,id',
    //         'nominal'   => 'required|numeric|min:1',
    //         'nilai_pph' => 'nullable|numeric|min:0',
    //         'tanggal'      => 'required|date',
    //         'pph_rate'     => 'required|numeric'
    //     ]);

    //     DB::transaction(function () use ($request) {

    //         $invoice = Invoice::findOrFail($request->invoice_id);

    //         // =========================
    //         // AMBIL NILAI DARI FORM
    //         // =========================

    //         // $nominalCash = (int) str_replace('.', '', $request->nominal);
    //         // $nilaiPph    = (int) str_replace('.', '', $request->nilai_pph);

    //         $nominalCash = (int) $request->nominal;
    //         $nilaiPph    = (int) ($request->nilai_pph ?? 0);

    //         $pphRate = (float) $request->pph_rate;

    //         $coaPphId = null;

    //         if ($pphRate == 2) {
    //             $coaPphId = 103;
    //         }

    //         if ($pphRate == 3.5) {
    //             $coaPphId = 104;
    //         }

    //         // total yang menutup piutang
    //         $totalMenutupPiutang = $nominalCash + $nilaiPph;

    //         // =========================
    //         // HITUNG SISA PIUTANG
    //         // =========================

    //         $totalPaid = $invoice->payments()
    //             ->sum(DB::raw('nominal + nilai_pph'));


    //         $sisaPiutang = $invoice->grand_total - $totalPaid;

    //         if ($totalMenutupPiutang > $sisaPiutang) {
    //             throw new \Exception('Pembayaran melebihi sisa piutang.');
    //         }

    //         // =========================
    //         // SIMPAN PEMBAYARAN
    //         // =========================

    //         $payment = InvoicePayment::create([
    //             'invoice_id'        => $invoice->id,
    //             'coa_bank_id'       => $request->coa_bank_id,
    //             'nominal'           => $nominalCash,
    //             'nilai_pph'         => $nilaiPph,
    //             'coa_pph_id'        => $coaPphId,
    //             'metode_pembayaran' => $request->metode_pembayaran,
    //             'tanggal'           => $request->tanggal,
    //             'keterangan'        => $request->keterangan,
    //         ]);

    //         // update status invoice
    //         $totalPaid = $invoice->payments()
    //             ->sum(DB::raw('nominal + nilai_pph'));

    //         $invoice->update([
    //             'status' => $totalPaid >= $invoice->grand_total ? 'paid' : 'partial'
    //         ]);

    //         // $totalPaid = $invoice->payments()->sum(DB::raw('nominal + nilai_pph'));
    //         // $invoice->update(['status' => $totalPaid >= $invoice->grand_total ? 'paid' : 'partial']);

    //         // =========================
    //         // BUAT JURNAL
    //         // =========================

    //         $journal = Journal::create([
    //             'tanggal'    => $request->tanggal,
    //             'no_jurnal'  => Journal::generateNo(),
    //             'keterangan' => 'Penerimaan Invoice ' . $invoice->no_invoice,
    //             'invoice_id' => $invoice->id,
    //             'ref_type'   => 'invoice_payment',
    //             'ref_id'     => $payment->id,
    //         ]);

    //         // Debit Bank
    //         JournalDetail::create([
    //             'journal_id' => $journal->id,
    //             'coa_id'     => $request->coa_bank_id,
    //             'debit'      => $nominalCash,
    //             'credit'     => 0
    //         ]);

    //         // Debit PPH
    //         if ($nilaiPph > 0 && $coaPphId) {
    //             JournalDetail::create([
    //                 'journal_id' => $journal->id,
    //                 'coa_id'     => $coaPphId,
    //                 'debit'      => $nilaiPph,
    //                 'credit'     => 0
    //             ]);
    //         }

    //         // Credit Piutang
    //         JournalDetail::create([
    //             'journal_id' => $journal->id,
    //             'coa_id'     => $invoice->coa_piutang_id,
    //             'debit'      => 0,
    //             'credit'     => $totalMenutupPiutang
    //         ]);

    //         // =========================
    //         // UPDATE STATUS INVOICE
    //         // =========================

    //         $totalPaid = $invoice->payments()
    //             ->sum(DB::raw('nominal + nilai_pph'));

    //         // $totalPaid = $invoice->payments()
    //         //     ->sum(DB::raw('nominal + nilai_pph')) + $totalMenutupPiutang;

    //         if ($totalPaid >= $invoice->grand_total) {
    //             $invoice->update(['status' => 'paid']);
    //         } else {
    //             $invoice->update(['status' => 'partial']);
    //         }
    //     });

    //     return redirect()
    //         ->route('finance.invoice_index')
    //         ->with('success', 'Pembayaran berhasil disimpan');
    // }

    public function updateTanggal(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'tgl_rencana_pembayaran' => $request->tgl_rencana_pembayaran
        ]);

        return response()->json([
            'success' => true
        ]);
    }
    public function uploadInvoice(Request $request, $id)
    {
        $request->validate([
            // 'file_invoice' => 'required|file|mimes:pdf|max:10240',
            'file_invoice' => 'required|file|max:10240',
        ]);

        $invoice = Invoice::findOrFail($id);
        $file = $request->file('file_invoice');

        // ⭐ ubah no_invoice jadi filename aman
        $noInvoiceSafe = preg_replace('/[^A-Za-z0-9\-]/', '-', $invoice->no_invoice);

        // ⭐ nama file
        $filename = 'invoice_' . $noInvoiceSafe . '.' . $file->getClientOriginalExtension();

        // ⭐ hapus file lama
        if ($invoice->file_invoice && Storage::disk('public')->exists($invoice->file_invoice)) {
            Storage::disk('public')->delete($invoice->file_invoice);
        }

        // ⭐ simpan tanpa folder per invoice
        $path = $file->storeAs('invoice_pdfs', $filename, 'public');

        // ⭐ save DB
        $invoice->update([
            'file_invoice' => $path
        ]);

        return back()->with('success', 'File Invoice berhasil diupload');
    }

    public function uploadFaktur(Request $request, $id)
    {
        $request->validate([
            // 'file_faktur' => 'required|mimes:pdf|max:10240',
            'file_faktur' => 'required|file|max:10240',
        ]);

        $invoice = Invoice::findOrFail($id);
        $file = $request->file('file_faktur');


        $noInvoiceSafe = preg_replace('/[^A-Za-z0-9\-]/', '-', $invoice->no_invoice);

        // ambil nama file asli tanpa extension
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // sanitize nama file
        $originalSafe = Str::slug($originalName);

        // gabungkan
        $filename = 'faktur_' . $noInvoiceSafe . '_' . $originalSafe . '.' . $file->getClientOriginalExtension();

        // hapus file lama
        if ($invoice->file_faktur && Storage::disk('public')->exists($invoice->file_faktur)) {
            Storage::disk('public')->delete($invoice->file_faktur);
        }

        // simpan (tanpa folder)
        $path = $file->storeAs('faktur_pajak_pdfs', $filename, 'public');

        // save DB
        $invoice->update([
            'file_faktur' => $path
        ]);

        return back()->with('success', 'File Faktur Pajak berhasil diupload');
    }

    //untuk outstanding
    // Pastikan laporan outstanding hanya ambil invoice aktif:
    // Invoice::where('status', 'posted')
    //     ->whereColumn('total', '>', 'paid_amount')
    //     ->get();
    //     Jangan ambil status void.




public function laporanPiutang()
{
    $title = 'Laporan Piutang';

    $today = Carbon::today();
    $end30 = $today->copy()->addDays(30);

    $data = Invoice::with(['payments', 'customer', 'produk', 'po'])
        ->whereIn('status', ['posted', 'partial'])
        ->whereHas('po', fn($q) => $q->where('is_hold', false))
        ->get();

    // =========================
    // FILTER DATA + HITUNG SISA
    // =========================
    $filteredData = $data->map(function ($row) {

        $nominalSpk = $row->total_after_diskon_inv > 0
            ? $row->total_after_diskon_inv
            : $row->nominal_invoice;

        $totalTagihan = $nominalSpk + ($row->ppn ?? 0);

        $totalBayar = $row->payments->sum(
            fn($p) => $p->nominal + $p->nilai_pph
        );

        $sisa = $totalTagihan - $totalBayar;

        if ($sisa <= 0) return null;

        // cache tanggal biar gak parse berulang
        $row->tgl_inv_parsed = Carbon::parse($row->tgl_inv);

        // inject ke object
        $row->sisa_tagihan = $sisa;
        $row->keterangan = $row->keterangan ?? 'Termin ' . ($row->termin_ke ?? 1);

        return $row;

    })->filter()->values();

    // =========================
    // TOTAL PIUTANG
    // =========================
    $totalPiutang = $filteredData->sum('sisa_tagihan');

    // =========================
    // BULAN INI
    // =========================
    $piutangBulanIni = $filteredData->filter(function ($row) use ($today) {
        return $row->tgl_inv_parsed->month == $today->month
            && $row->tgl_inv_parsed->year == $today->year;
    })->sum('sisa_tagihan');

    // =========================
    // 30 HARI KE DEPAN (BERDASARKAN TGL INVOICE)
    // =========================
    $piutang30Hari = $filteredData->filter(function ($row) use ($today, $end30) {
        return $row->tgl_inv_parsed->between($today, $end30);
    })->sum('sisa_tagihan');

    return view('pages.finance.laporan_piutang', [
        'data' => $filteredData,
        'totalPiutang' => $totalPiutang,
        'piutangBulanIni' => $piutangBulanIni,
        'piutang30Hari' => $piutang30Hari,
        'title' => $title
    ]);
}


// public function laporanPiutang()
// {
//     $title = 'Laporan Piutang';

//     $data = Invoice::with(['payments', 'customer', 'produk', 'po'])
//         ->whereIn('status', ['posted', 'partial'])
//         ->whereHas('po', function ($q) {
//             $q->where('is_hold', false);
//         })
//         ->get();

//     $filteredData = $data->map(function ($row) {

//         $nominalSpk = $row->total_after_diskon_inv > 0
//             ? $row->total_after_diskon_inv
//             : $row->nominal_invoice;

//         $totalTagihan = $nominalSpk + ($row->ppn ?? 0);

//         $totalBayar = $row->payments->sum(fn($p) => $p->nominal + $p->nilai_pph);

//         $sisa = $totalTagihan - $totalBayar;

//         // Hanya tampilkan kalau ada sisa tagihan
//         if ($sisa <= 0) {
//             return null;
//         }

//         // Tambahkan info termin & status
//         $row->sisa_tagihan = $sisa;
//         $row->termin_status = 'invoice'; // karena ini invoice
//         $row->keterangan = $row->keterangan ?? 'Termin ' . ($row->termin_ke ?? 1);

//         return $row;
//     })->filter()->values(); // buang null dan reset index

//     // Hitung total piutang
//     $totalPiutang = $filteredData->sum(fn($row) => $row->sisa_tagihan);

//     return view('pages.finance.laporan_piutang', [
//         'data' => $filteredData,
//         'totalPiutang' => $totalPiutang,
//         'title' => $title
//     ]);
// }

public function laporanOutstanding(Request $request)
{
    $title = 'Laporan Outstanding';
    $tahunSekarang = now()->year;
    $tahunDipilih = $request->tahun ?? 'all';

    $query = Po::with([
        'customer',
        'invoices.payments',
        'invoices.produk.perizinan',
        'quotation.perizinan',
        'quotation.quotation_perizinan',
        'quotation.kabupaten'
    ])->where('bast_verified', 1)
    ->orderBy('tgl_po', 'desc');

    if ($tahunDipilih !== 'all') {
        $query->whereYear('tgl_po', $tahunDipilih);
    }

    $pos = $query->get();
    $data = collect();

    foreach ($pos as $po) {
        //skip kalo hold
        if ($po->is_hold) continue;

        $quotation = $po->quotation;
        if (!$quotation) continue;

        // =========================
        // HITUNG NOMINAL SPK
        // =========================
        $subtotal = $quotation->harga_tipe === 'gabungan'
            ? (float) $quotation->harga_gabungan
            : $quotation->perizinan->sum(fn($item) => ($item->pivot->qty ?? 0) * ($item->pivot->harga_satuan ?? 0));

        $diskon = (float) ($quotation->diskon_nilai ?? 0);
        if ($diskon > $subtotal) $diskon = $subtotal;
        $nominalSPK = $subtotal - $diskon;

        // =========================
        // HITUNG TERMIN
        // =========================
        $terminList = collect();
        $terminPersentase = $quotation->termin_persentase ?? [];

        foreach ($terminPersentase as $termin) {
            $persen = (float) ($termin['persen'] ?? 0);
            $urutan = $termin['urutan'] ?? null;
            $nominalTermin = ($persen / 100) * $nominalSPK;

            // cek invoice berdasarkan termin
            $invoice = $po->invoices->firstWhere('termin_ke', $urutan);

            if ($invoice) {
                $totalBayar = $invoice->payments->sum(fn($p) => $p->nominal + $p->nilai_pph);
                $sisa = $invoice->grand_total - $totalBayar;

                if ($sisa > 0) {
                    $terminList->push([
                        'keterangan' => $invoice->keterangan ?? "Termin {$urutan}",
                        'nominal' => $sisa,
                        'status' => 'invoice'
                    ]);
                }
            } else {
                if ($nominalTermin > 0) {
                    $terminList->push([
                        'keterangan' => $termin['keterangan'] ?? "Termin {$urutan} ({$persen}%)",
                        'nominal' => $nominalTermin,
                        'status' => 'belum'
                    ]);
                }
            }
        }

        // =========================
        // PRODUK
        // =========================
        if ($po->invoices->isNotEmpty()) {
            $po->all_produk = $po->invoices->flatMap(fn($inv) => $inv->produk->map(fn($item) => $item->perizinan?->jenis ?? $item->perizinan_lainnya ?? '-'));
        } else {
            $po->all_produk = $quotation->quotation_perizinan->map(fn($item) => $item->perizinan?->jenis ?? $item->perizinan_lainnya ?? '-');
        }

        $po->nominal_spk = $nominalSPK;
        $po->termin_list = $terminList;

        // =========================
        // STATUS
        // =========================
        $statuses = $po->invoices->pluck('status');

        if ($statuses->isNotEmpty() && $statuses->every(fn($s) => $s === 'paid')) {
            $status = 'done';
        } else {
            $status = 'ongoing';
        }

        $po->status_label = $status;

        // hanya tampil kalau masih ada outstanding
        if ($terminList->isNotEmpty()) {
            $data->push($po);
        }
    }

    // =========================
    // TOTALS
    // =========================
    $totalNominalSPK = $data->sum('nominal_spk');
    $totalNominalTermin = $data->sum(fn($po) => $po->termin_list->sum('nominal'));

    // =========================
    // SUMMARY
    // =========================
    $allPos = Po::with(['invoices.payments','quotation.perizinan'])
        ->where('bast_verified',1)
        ->get();

    $summaryData = collect();
    foreach ($allPos as $po) {
         if ($po->is_hold) continue; 
    
        $quotation = $po->quotation;
        if (!$quotation) continue;

        $subtotal = $quotation->harga_tipe === 'gabungan'
            ? (float)$quotation->harga_gabungan
            : $quotation->perizinan->sum(fn($item) => ($item->pivot->qty ?? 0) * ($item->pivot->harga_satuan ?? 0));

        $diskon = (float)($quotation->diskon_nilai ?? 0);
        if ($diskon > $subtotal) $diskon = $subtotal;

        $nominalSPK = $subtotal - $diskon;
        $terminList = collect();
        $terminPersentase = $quotation->termin_persentase ?? [];

        foreach ($terminPersentase as $termin) {
            $persen = (float)($termin['persen'] ?? 0);
            $urutan = $termin['urutan'] ?? null;
            $nominalTermin = ($persen / 100) * $nominalSPK;

            $invoice = $po->invoices->firstWhere('termin_ke', $urutan);
            if ($invoice) {
                $totalBayar = $invoice->payments->sum(fn($p) => $p->nominal + $p->nilai_pph);
                $sisa = $invoice->grand_total - $totalBayar;
                if ($sisa > 0) $terminList->push(['nominal'=>$sisa]);
            } else {
                if ($nominalTermin > 0) $terminList->push(['nominal'=>$nominalTermin]);
            }
        }

        $po->termin_list = $terminList;
        $summaryData->push($po);
    }

    $totalOutstandingKeseluruhan = $summaryData->sum(fn($po) => $po->termin_list->sum('nominal'));
    $outstandingPerTahun = $summaryData->groupBy(fn($po) => \Carbon\Carbon::parse($po->tgl_po)->year)
        ->map(fn($items) => $items->sum(fn($po) => $po->termin_list->sum('nominal')))
        ->filter(fn($total) => $total > 0)
        ->sortKeysDesc();

    return view('pages.finance.laporan_outstanding', compact(
        'data','title','totalNominalSPK','totalNominalTermin',
        'totalOutstandingKeseluruhan','outstandingPerTahun',
        'tahunSekarang','tahunDipilih'
    ));
}


public function exportPdf(Request $request)
{
    $tahun = $request->tahun ?? 'all';

    $query = Po::with([
        'customer',
        'invoices.payments',
        'invoices.produk.perizinan',
        'quotation.perizinan',
        'quotation.quotation_perizinan',
        'quotation.kabupaten'
    ])->where('bast_verified', 1);

    if ($tahun !== 'all') {
        $query->whereYear('tgl_po', $tahun);
    }

    $pos = $query->get();
    $data = collect();

    foreach ($pos as $po) {

        if ($po->is_hold) continue;

        $quotation = $po->quotation;
        if (!$quotation) continue;

        // =========================
        // NOMINAL SPK
        // =========================
        $subtotal = $quotation->harga_tipe === 'gabungan'
            ? (float) $quotation->harga_gabungan
            : $quotation->perizinan->sum(fn($item) =>
                ($item->pivot->qty ?? 0) * ($item->pivot->harga_satuan ?? 0)
            );

        $diskon = (float) ($quotation->diskon_nilai ?? 0);
        if ($diskon > $subtotal) $diskon = $subtotal;

        $nominalSPK = $subtotal - $diskon;

        // =========================
        // TERMIN
        // =========================
        $terminList = collect();
        $terminPersentase = $quotation->termin_persentase ?? [];

        foreach ($terminPersentase as $termin) {

            $persen = (float) ($termin['persen'] ?? 0);
            $urutan = $termin['urutan'] ?? null;

            $nominalTermin = ($persen / 100) * $nominalSPK;

            $invoice = $po->invoices->firstWhere('termin_ke', $urutan);

            if ($invoice) {
                $totalBayar = $invoice->payments->sum(fn($p) =>
                    $p->nominal + $p->nilai_pph
                );

                $sisa = $invoice->grand_total - $totalBayar;

                if ($sisa > 0) {
                    $terminList->push([
                        'keterangan' => $invoice->keterangan ?? "Termin {$urutan}",
                        'nominal' => $sisa,
                        'status' => 'invoice'
                    ]);
                }

            } else {
                if ($nominalTermin > 0) {
                    $terminList->push([
                        'keterangan' => $termin['keterangan'] ?? "Termin {$urutan} ({$persen}%)",
                        'nominal' => $nominalTermin,
                        'status' => 'belum'
                    ]);
                }
            }
        }

        // PRODUK
        if ($po->invoices->isNotEmpty()) {
            $po->all_produk = $po->invoices->flatMap(fn($inv) =>
                $inv->produk->map(fn($item) =>
                    $item->perizinan?->jenis ?? $item->perizinan_lainnya ?? '-'
                )
            );
        } else {
            $po->all_produk = $quotation->quotation_perizinan->map(fn($item) =>
                $item->perizinan?->jenis ?? $item->perizinan_lainnya ?? '-'
            );
        }

        $po->nominal_spk = $nominalSPK;
        $po->termin_list = $terminList;

        if ($terminList->isNotEmpty()) {
            $data->push($po);
        }
    }

    // if ($tahun === 'all') {
    //     $data = $data->groupBy(function ($item) {
    //         return \Carbon\Carbon::parse($item->tgl_po)->format('Y');
    //     })
    //     ->sortKeysDesc();
    // }
    // // TOTAL PER TAHUN
    // $outstandingPerTahun = $data->map(function ($items) {
    //     return collect($items)->sum(function ($po) {
    //         return collect($po->termin_list)->sum('nominal');
    //     });
    // });
    if ($tahun === 'all') {

    $data = $data->groupBy(function ($item) {
        return \Carbon\Carbon::parse($item->tgl_po)->format('Y');
    })->sortKeysDesc();

    // ✅ PER TAHUN (BENAR)
    $outstandingPerTahun = $data->map(function ($items) {
        return $items->sum(function ($po) {
            return collect($po->termin_list)->sum('nominal');
        });
    });

} else {

    // ✅ HANYA 1 TAHUN
    $outstandingPerTahun = collect([
        $tahun => $data->sum(function ($po) {
            return collect($po->termin_list)->sum('nominal');
        })
    ]);
}

    // TOTAL KESELURUHAN
    $totalOutstandingKeseluruhan = $outstandingPerTahun->sum();

    // =========================
    // 🔥 RENDER VIEW KE HTML
    // =========================
    $html = view('pages.finance.outstanding_pdf', [
        'data' => $data,
        'tahun' => $tahun,
        'outstandingPerTahun' => $outstandingPerTahun,
        'totalOutstandingKeseluruhan' => $totalOutstandingKeseluruhan
    ])->render();

    // =========================
    // 🔥 GENERATE PDF VIA CHROME
    // =========================
    // $pdf = Browsershot::html($html)
    //     ->format('A4')
    //     ->landscape()
    //     ->margins(10, 10, 10, 10)
    //     ->showBackground()
    //     ->pdf();

    // $pdf = Browsershot::html($html)
    // ->setNodeBinary('/home/u576953852/.nvm/versions/node/v24.13.1/bin/node')
    // ->setNpmBinary('/home/u576953852/.nvm/versions/node/v24.13.1/bin/npm')
    // ->setChromePath('/usr/bin/chromium-browser')
    // ->noSandbox()
    // ->format('A4')
    // ->landscape()
    // ->margins(10, 10, 10, 10)
    // ->showBackground()
    // ->pdf();

    $filename = $tahun == 'all'
        ? 'laporan-outstanding-all.pdf'
        : "laporan-outstanding-{$tahun}.pdf";

    return response($pdf)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
}

// public function penerimaanKas(Request $request)
// {
//     $title = 'Penerimaan Kas';

//     // =========================
//     // FILTER TAHUN
//     // =========================
//     $tahun = $request->tahun ?? now()->year;

//     $start = Carbon::create($tahun, 1, 1)->startOfDay();
//     $end   = Carbon::create($tahun, 12, 31)->endOfDay();

    
//    $kas = Journal::with(['journaldetails.coa', 'payment.invoice.customer', 'payment.coaBank'])
//     ->where('ref_type', 'invoice_payment')
//     ->whereYear('tanggal', $tahun)
//     ->get()
//     ->flatMap(function ($jurnal) {

//         return $jurnal->journaldetails
//             ->filter(function ($d) use ($jurnal) {
//                 return $jurnal->payment 
//                     && $d->coa_id == $jurnal->payment->coa_bank_id
//                     && $d->debit > 0;
//             })
//             ->map(function ($d) use ($jurnal) {

//                 return (object)[
//                     'journal_id' => $jurnal->id,
//                     'tanggal' => $jurnal->tanggal,
//                     'nominal' => $d->debit, // ✅ ini yg bener
//                     'coa' => $d->coa->nama_akun ?? '-',
//                     'ref_type' => $jurnal->ref_type,
//                     'keterangan' => $jurnal->keterangan,
//                     'termin_inv' => $jurnal->invoice->termin_ke,
//                     'customer' => $jurnal->invoice->customer->nama_perusahaan,

//                     // 🔥 ambil dari relasi payment
//                     'no_invoice' => $jurnal->payment->invoice->no_invoice ?? '-',
//                     'bank' => $jurnal->payment->coaBank->nama_akun ?? '-',
//                 ];
//             });

//     });

// // dd($kas);

//     // =========================
//     // MONTHLY (GROUP PER BULAN)
//     // =========================
//     $monthly = $kas->groupBy(function ($item) {
//         return Carbon::parse($item->tanggal)->format('Y-m');
//     });

//     // =========================
//     // REKAP (12 BULAN FIX)
//     // =========================
//     $rekap = collect();

//     for ($i = 1; $i <= 12; $i++) {

//         $bulanKey = Carbon::create($tahun, $i, 1)->format('Y-m');

//         $total = isset($monthly[$bulanKey])
//             ? collect($monthly[$bulanKey])->sum('nominal')
//             : 0;

//         $rekap->push((object)[
//             'bulan' => Carbon::create($tahun, $i, 1)->translatedFormat('F Y'),
//             'total' => $total
//         ]);
//     }

//     // =========================
//     // LIST TAHUN (FIX RANGE)
//     // =========================
//     $listTahun = collect(range(2023, now()->year))->sortDesc();
    
//     return view('pages.finance.penerimaan_kas', [
//         'title' => $title,
//         'kas' => $kas,
//         'monthly' => $monthly,
//         'rekap' => $rekap,
//         'tahun' => $tahun,
//         'listTahun' => $listTahun,
//         'countKas' => $kas->count(),
//         'countMonthly' => $monthly->count(),
//         'countRekap' => $rekap->count(),
        
//     ]);
// }
public function penerimaanKas(Request $request)
{
    $title = 'Penerimaan Dana Masuk ';
    $tahun = $request->tahun ?? now()->year;
    
$kas = Journal::with([
    'journaldetails.coa',
    'payment.invoice.customer',
    'payment.coaBank',
    'payment.invoice.payments'
])
->where('ref_type', 'invoice_payment')
->whereYear('tanggal', $tahun)
->get()
->flatMap(function ($jurnal) {

    $invoice = $jurnal->payment->invoice ?? null;

    return $jurnal->journaldetails
        ->filter(fn($d) => $jurnal->payment
                    && $d->coa_id == $jurnal->payment->coa_bank_id
                    && $d->debit > 0)
        ->map(function ($d) use ($jurnal, $invoice) {

            $isPartial = false;

            if($invoice) {
                $terminKe = $jurnal->invoice->termin_ke ?? 1;

                $terminPayments = $invoice->payments
                    ->where('termin_ke', $terminKe)
                    ->sortBy('id'); // atau 'tanggal'

                // Tandai partial kalau ini bukan pembayaran terakhir di termin
                $lastPaymentId = $terminPayments->last()->id ?? null;
                if($jurnal->id != $lastPaymentId) {
                    $isPartial = true;
                }
            }

            return (object)[
                'journal_id' => $jurnal->id,
                'tanggal' => $jurnal->tanggal,
                'nominal' => $d->debit,
                'coa' => $d->coa->nama_akun ?? '-',
                'ref_type' => $jurnal->ref_type,
                'keterangan' => $jurnal->keterangan,
                'termin_inv' => $jurnal->invoice->termin_ke ?? null,
                'customer' => $invoice->customer->nama_perusahaan ?? '-',
                'no_invoice' => $invoice->no_invoice ?? '-',
                'bank' => $jurnal->payment->coaBank->nama_akun ?? '-',
                'is_partial' => $isPartial,
            ];
        });
});
    // =========================
    // MONTHLY (GROUP PER BULAN)
    // =========================
    $monthly = $kas->groupBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m'));

    // =========================
    // REKAP 12 BULAN
    // =========================
    $rekap = collect();
    for ($i = 1; $i <= 12; $i++) {
        $bulanKey = Carbon::create($tahun, $i, 1)->format('Y-m');
        $total = isset($monthly[$bulanKey])
            ? collect($monthly[$bulanKey])->sum('nominal')
            : 0;

        $rekap->push((object)[
            'bulan' => Carbon::create($tahun, $i, 1)->translatedFormat('F Y'),
            'total' => $total
        ]);
    }

    $listTahun = collect(range(2023, now()->year))->sortDesc();

    return view('pages.finance.penerimaan_kas', [
        'title' => $title,
        'kas' => $kas,
        'monthly' => $monthly,
        'rekap' => $rekap,
        'tahun' => $tahun,
        'listTahun' => $listTahun,
        'countKas' => $kas->count(),
        'countMonthly' => $monthly->count(),
        'countRekap' => $rekap->count(),
    ]);
}
}
