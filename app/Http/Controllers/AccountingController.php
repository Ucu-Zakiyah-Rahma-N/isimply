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

class AccountingController extends Controller
{
    public function bankCash()
    {
        $title = "bank cash";
        // 1. ambil semua akun kas & bank
        $bankCoaIds = Coa::where('kategori_akun', 'Kas & bank')
            ->pluck('id');

        // 2. ambil transaksi jurnal yang pakai akun tsb
        $details = JournalDetail::with(['journal', 'coa'])
            ->whereIn('coa_id', $bankCoaIds)
            ->join('journals', 'journals.id', '=', 'journal_details.journal_id')
            ->orderBy('journals.tanggal', 'desc')
            ->select('journal_details.*')
            ->get();

        // 3. running balance GLOBAL (optional)
        $saldo = 0;
        foreach ($details as $d) {
            $saldo += $d->debit;
            $saldo -= $d->credit;
            $d->saldo = $saldo;
        }

        return view('pages.finance.bank_cash', compact('details', 'title'));
    }
    // public function bankCash($coaId)
    // {
    //     $coa = Coa::findOrFail($coaId);

    //     $details = JournalDetail::with('journal')
    //         ->where('coa_id', $coaId)
    //         ->whereHas('journal') // pastikan ada journal
    //         ->get()
    //         ->sortBy([
    //             fn($a, $b) => strcmp($a->journal->tanggal, $b->journal->tanggal),
    //             fn($a, $b) => $a->id <=> $b->id,
    //         ])
    //         ->values();

    //     // running balance
    //     $saldo = $coa->saldo_awal ?? 0;

    //     foreach ($details as $d) {
    //         $saldo += $d->debit;
    //         $saldo -= $d->credit;
    //         $d->saldo = $saldo;
    //     }

    //     return view('finance.bank_cash', compact('coa', 'details', 'saldo'));
    // }
}
