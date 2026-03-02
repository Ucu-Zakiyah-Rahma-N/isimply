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
    public function bankCashLedger($coaId)
    {
        $title = "bank cash";
        $coa = Coa::findOrFail($coaId);

        $details = JournalDetail::with([
            'journal.journaldetails.coa' // eager load sampai coa
        ])
            ->where('coa_id', $coaId)
            ->whereHas('journal')
            ->get()
            ->sortBy([
                fn($a, $b) => strcmp($a->journal->tanggal, $b->journal->tanggal),
                fn($a, $b) => $a->id <=> $b->id
            ]);

        $saldoAwal = $coa->saldo_awal ?? 0;

        $saldo = $saldoAwal;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($details as $d) {

            $totalDebit += $d->debit;
            $totalCredit += $d->credit;

            $saldo += $d->debit;
            $saldo -= $d->credit;

            $d->saldo = $saldo;

            // Ambil akun lawan (selain bank yang sedang dibuka)
            $lawan = $d->journal->journaldetails
                ->where('coa_id', '!=', $coaId)
                ->sortByDesc('credit')
                ->first();

            $d->akun_lawan = $lawan?->coa?->nama_akun ?? '-';

            //penerima berdasarkan ref_type di jurnal
            $penerima = '-';

            if ($d->journal->ref_type == 'invoice_payment') {
                $penerima = $coa->nama_akun; // nama bank
            }

            if ($d->journal->ref_type == 'purchase') {
                $purchase = Kontak::find($d->journal->ref_id);
                $penerima = $purchase?->contact?->nama ?? '-';
            }

            $d->penerima = $penerima;
        }

        $saldoAkhir = $saldo;

        return view('pages.finance.bank_cash', compact(
            'coa',
            'details',
            'title',
            'totalDebit',
            'totalCredit',
            'saldoAwal',
            'saldoAkhir'
        ));
    }
    // public function bankCashLedger($coaId)
    // {

    //     $title = "bank cash";
    //     $coa = Coa::findOrFail($coaId);

    //     $details = JournalDetail::with('journal')
    //         ->where('coa_id', $coaId)
    //         ->join('journals', 'journals.id', '=', 'journal_details.journal_id')
    //         ->orderBy('journals.tanggal')
    //         ->orderBy('journal_details.id')
    //         ->select('journal_details.*')
    //         ->get();

    //     $saldoAwal = $coa->saldo_awal ?? 0;

    //     $saldo = $saldoAwal;
    //     $totalDebit = 0;
    //     $totalCredit = 0;

    //     foreach ($details as $d) {
    //         $totalDebit += $d->debit;
    //         $totalCredit += $d->credit;

    //         $saldo += $d->debit;
    //         $saldo -= $d->credit;

    //         $d->saldo = $saldo;

    //         $lawan = $d->journal->details
    //             ->where('coa_id', '!=', $coaId)
    //             ->first();

    //         $d->akun_lawan = $lawan?->coa?->nama_akun ?? '-';
    //     }

    //     $saldoAkhir = $saldoAwal + $totalDebit - $totalCredit;

    //     return view('pages.finance.bank_cash', compact(
    //         'coa',
    //         'details',
    //         'title',
    //         'totalDebit',
    //         'totalCredit',
    //         'saldoAwal',
    //         'saldoAkhir'
    //     ));
    // }
}
