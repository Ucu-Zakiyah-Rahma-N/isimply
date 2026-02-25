<?php

use App\Models\Journal;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\DB;

if (!function_exists('createJournal')) {

    /**
     * Create journal + details
     *
     * @param array $header
     * @param array $details
     * @return Journal
     */
    function createJournal(array $header, array $details)
    {
        return DB::transaction(function () use ($header, $details) {

            // ⭐ create header
            $journal = Journal::create([
                'tanggal' => $header['tanggal'] ?? now(),
                'ref_type' => $header['ref_type'] ?? null,
                'ref_id' => $header['ref_id'] ?? null,
                'keterangan' => $header['keterangan'] ?? null,
            ]);

            // ⭐ create details
            foreach ($details as $row) {
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $row['coa_id'],
                    'debit' => $row['debit'] ?? 0,
                    'credit' => $row['credit'] ?? 0,
                ]);
            }

            return $journal;
        });
    }
}