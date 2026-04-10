<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Journal;
use App\Models\Coa;
use App\Models\JournalDetail;
use App\Models\Kontak;

class AccountingController extends Controller
{
    public function bankCashIndex()
    {
        $title = "bank cash";
        $parents = Coa::where('kategori_akun', 'Kas & Bank')
            ->where('is_header_akun', 1)
            ->with(['children' => function ($q) {
                $q->orderBy('nama_akun');
            }])
            ->orderBy('nama_akun')
            ->get();

        // HITUNG SALDO REAL
        foreach ($parents as $parent) {
            foreach ($parent->children as $child) {

                $debit = JournalDetail::where('coa_id', $child->id)->sum('debit');
                $credit = JournalDetail::where('coa_id', $child->id)->sum('credit');

                $saldoAwal = $child->saldo_awal ?? 0;

                $child->saldo = $saldoAwal + $debit - $credit;
            }
        }

        return view('pages.finance.bank_cash_index', compact('parents', 'title'));
    }



public function bankCashLedger(Request $request, $coaId)
{
    $title = "bank cash";
    $coa = Coa::findOrFail($coaId);

    // =========================
    // BASE QUERY (TANPA JOIN)
    // =========================
    $baseQuery = JournalDetail::with([
        'journal.journaldetails.coa',
        'journal.invoice.po'
    ])
    ->where('coa_id', $coaId)
    ->whereHas('journal');

    // =========================
    // FILTER
    // =========================
    if ($request->month) {

        $month = date('m', strtotime($request->month));
        $year  = date('Y', strtotime($request->month));

        $baseQuery->whereHas('journal', function ($q) use ($month, $year) {
            $q->whereMonth('tanggal', $month)
              ->whereYear('tanggal', $year);
        });

    } elseif ($request->start_date && $request->end_date) {

        $baseQuery->whereHas('journal', function ($q) use ($request) {
            $q->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        });
    }

    // =========================
    // TOTAL GLOBAL (PAKAI BASE QUERY)
    // =========================
    $totalDebit = (clone $baseQuery)->sum('debit');
    $totalCredit = (clone $baseQuery)->sum('credit');

    $saldoAkhir = $coa->saldo_awal +
        (clone $baseQuery)->sum(DB::raw('debit - credit'));

    // =========================
    // DATA QUERY (PAKAI JOIN)
    // =========================
    $dataQuery = (clone $baseQuery)
        ->join('journals', 'journals.id', '=', 'journal_details.journal_id')
        ->orderBy('journals.tanggal')
        ->orderBy('journal_details.id')
        ->select('journal_details.*');

    $perPage = $request->per_page ?? 10;

    // =========================
    // HITUNG LAST PAGE
    // =========================
$totalRows = (clone $baseQuery)->count();
$perPage = $request->per_page ?? 10;

$lastPage = ceil($totalRows / $perPage);

// ✅ hanya redirect kalau:
// - belum ada page
// - dan bukan dari aksi user (per_page/filter)
if (!$request->has('page') && !$request->has('per_page')) {
    return redirect()->to(request()->fullUrlWithQuery([
        'page' => $lastPage
    ]));
}

    // =========================
    // PAGINATION
    // =========================
    $details = $dataQuery->paginate($perPage)->withQueryString();
    
    // =========================
    // SALDO BERJALAN
    // =========================
    $offset = ($details->currentPage() - 1) * $perPage;

    $saldo = $coa->saldo_awal +
        (clone $baseQuery)
            ->take($offset)
            ->get()
            ->sum(fn($x) => $x->debit - $x->credit);

    foreach ($details as $d) {

        $saldo += $d->debit - $d->credit;
        $d->saldo = $saldo;

        // akun lawan
        $lawan = $d->journal->journaldetails
            ->where('coa_id', '!=', $coaId)
            ->sortByDesc('credit')
            ->first();

        $d->akun_lawan = $lawan?->coa?->nama_akun ?? '-';

        // penerima
        $penerima = '-';

        if ($d->journal->ref_type == 'invoice_payment') {
            $penerima = $coa->nama_akun;
        }

        if ($d->journal->ref_type == 'purchase') {
            $purchase = Kontak::find($d->journal->ref_id);
            $penerima = $purchase?->contact?->nama ?? '-';
        }

        $d->penerima = $penerima;
    }

    return view('pages.finance.bank_cash', compact(
        'coa',
        'details',
        'title',
        'totalDebit',
        'totalCredit',
        'saldoAkhir'
    ));
}

//     public function bankCashLedger(Request $request, $coaId)
//     {
//         $title = "bank cash";
//         $coa = Coa::findOrFail($coaId);

//   $query = JournalDetail::with([
//         'journal.journaldetails.coa'
//     ])
//     ->where('coa_id', $coaId)
//     ->whereHas('journal');

    

//     // =========================
//     // FILTER BULAN (PRIORITAS)
//     // =========================
//     if ($request->month) {

//         $month = date('m', strtotime($request->month));
//         $year  = date('Y', strtotime($request->month));

//         $query->whereHas('journal', function ($q) use ($month, $year) {
//             $q->whereMonth('tanggal', $month)
//               ->whereYear('tanggal', $year);
//         });

//     }
//     // =========================
//     // FILTER RANGE TANGGAL
//     // =========================
//     elseif ($request->start_date && $request->end_date) {

//         $query->whereHas('journal', function ($q) use ($request) {
//             $q->whereBetween('tanggal', [
//                 $request->start_date,
//                 $request->end_date
//             ]);
//         });
//     }

//     $details = $query->get()->sortBy([
//         fn($a, $b) => strcmp($a->journal->tanggal, $b->journal->tanggal),
//         fn($a, $b) => $a->id <=> $b->id
//     ]);


//         $saldoAwal = $coa->saldo_awal ?? 0;

//         $saldo = $saldoAwal;
//         $totalDebit = 0;
//         $totalCredit = 0;

//         foreach ($details as $d) {

//             $totalDebit += $d->debit;
//             $totalCredit += $d->credit;

//             $saldo += $d->debit;
//             $saldo -= $d->credit;

//             $d->saldo = $saldo;

//             // Ambil akun lawan (selain bank yang sedang dibuka)
//             $lawan = $d->journal->journaldetails
//                 ->where('coa_id', '!=', $coaId)
//                 ->sortByDesc('credit')
//                 ->first();

//             $d->akun_lawan = $lawan?->coa?->nama_akun ?? '-';

//             //penerima berdasarkan ref_type di jurnal
//             $penerima = '-';

//             if ($d->journal->ref_type == 'invoice_payment') {
//                 $penerima = $coa->nama_akun; // nama bank
//             }

//             if ($d->journal->ref_type == 'purchase') {
//                 $purchase = Kontak::find($d->journal->ref_id);
//                 $penerima = $purchase?->contact?->nama ?? '-';
//             }

//             $d->penerima = $penerima;
//         }

//         $saldoAkhir = $saldo;

//         return view('pages.finance.bank_cash', compact(
//             'coa',
//             'details',
//             'title',
//             'totalDebit',
//             'totalCredit',
//             'saldoAwal',
//             'saldoAkhir'
//         ));
//     }
 
}
