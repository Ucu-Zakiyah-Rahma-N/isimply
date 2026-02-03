<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\PO;
use App\Models\Cabang;
use App\Models\AchievementTarget;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class RekapController extends Controller
{
        public function rekapBulanan(Request $request)
        {
            $title = 'Rekap Marketing';
            
            $user = auth()->user();
            $isCabang = $user->role === 'admin marketing' && $user->cabang_id != 1;
    
            $currentYear = date('Y');
            
            if (!$request->has('tahun_rekap') || !$request->has('tahun_achievement')) {
                return redirect()->route('rekap.bulanan', [
                    'tahun_rekap' => $currentYear,
                    'tahun_achievement' => $currentYear,
                ]);
            }
    
            $tahunRekap = $request->get('tahun_rekap');
            $tahunAchievement = $request->get('tahun_achievement');
            
            $cabangId = $request->get('cabang_id'); // bisa null
            $cabang = Cabang::all();
            // ========================
            // REKAP QUOTATION
    
            // ambil quotation TERBARU per parent_id
            $latestQuotationIds = Quotation::select(DB::raw('MAX(id) as id'))
                ->when($isCabang, fn($q) =>
                    $q->where('cabang_id', $user->cabang_id)) //isCabang adalah HO dan top manajemen
                ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId)) // filter per cabang
                ->groupBy(DB::raw('COALESCE(parent_id, id)'))
                ->pluck('id');
    
            $rekapQuotation = Quotation::with('quotation_perizinan')
                ->whereIn('id', $latestQuotationIds)
                ->when($tahunRekap, fn($q) => $q->whereYear('tgl_sph', $tahunRekap))
                ->get()
                ->groupBy(fn($q) => Carbon::parse($q->tgl_sph)->format('F Y'))
                ->map(function ($quotations, $bulan) {
    
                    $jumlah_sph = $quotations->count();
                    $nominal_sph = $quotations->sum(fn($q) => $q->grand_total); // sudah termasuk diskon
                    
                    return [
                        'bulan' => $bulan,
                        'jumlah_sph' => $jumlah_sph,
                        'nominal_sph' => $nominal_sph,
                    ];
                })
                ->values();
    
            // ========================
            // REKAP PO
            $rekapPO = PO::with('quotation.quotation_perizinan')
                ->when($isCabang, function ($q) use ($user) {
                    $q->whereHas('quotation', function ($qq) use ($user) {
                        $qq->where('cabang_id', $user->cabang_id);
                    });
                })
                ->when($cabangId, fn($q) => $q->whereHas('quotation', fn($qq) => $qq->where('cabang_id', $cabangId)))
                ->when($tahunRekap, fn($q) => $q->whereYear('tgl_po', $tahunRekap))
                ->get()
                ->groupBy(fn($po) => Carbon::parse($po->tgl_po)->format('F Y'))
                ->map(function($pos, $bulan) {
                    $jumlah_spk = $pos->count();
                    $nominal_spk = $pos->sum(fn($po) => $po->quotation->grand_total ?? 0); // sudah termasuk diskon 
    
                    // foreach ($pos as $po) {
                    //     $q = $po->quotation;
                    //     if (!$q) continue;
                    //     $nominal_spk += $q->harga_tipe == 'gabungan'
                    //         ? $q->harga_gabungan
                    //         : $q->quotation_perizinan->sum('harga_satuan');
                    // }
    
                    return [
                        'bulan' => $bulan,
                        'jumlah_spk' => $jumlah_spk,
                        'nominal_spk' => $nominal_spk,
                    ];
                })
                ->values();
            
            $rekapPOAchievement = PO::with('quotation')
                ->when($isCabang, function ($q) use ($user) {
                    $q->whereHas('quotation', fn($qq) =>
                        $qq->where('cabang_id', $user->cabang_id)
                    );
                })
                ->when($cabangId, fn($q) =>
                    $q->whereHas('quotation', fn($qq) => $qq->where('cabang_id', $cabangId))
                )
                ->whereYear('tgl_po', $tahunAchievement)
                ->get()
                ->groupBy(fn($po) => Carbon::parse($po->tgl_po)->format('F Y'))
                ->map(fn($pos) => $pos->sum(fn($po) => $po->quotation->grand_total ?? 0));

            // ========================
            // GABUNGKAN QUOTATION + PO
            $rekapGabungan = collect();
            $semuaBulan = $rekapQuotation->pluck('bulan')
                ->merge($rekapPO->pluck('bulan'))
                ->unique()
                ->sortBy(fn($bulan) => Carbon::createFromFormat('F Y', $bulan));
    
            foreach ($semuaBulan as $bulan) {
                $dataQuotation = $rekapQuotation->firstWhere('bulan', $bulan);
                $dataPO = $rekapPO->firstWhere('bulan', $bulan);
                $carbonBulan = Carbon::createFromFormat('F Y', $bulan);
    
                $rekapGabungan->push([
                    'bulan' => $bulan,
                    'tahun' => $carbonBulan->year,
                    'jumlah_sph' => $dataQuotation['jumlah_sph'] ?? 0,
                    'nominal_sph' => $dataQuotation['nominal_sph'] ?? 0,
                    'jumlah_spk' => $dataPO['jumlah_spk'] ?? 0,
                    'nominal_spk' => $dataPO['nominal_spk'] ?? 0,
                ]);
            }

            // ========================
            // REKAP ACHIEVEMENT (12 bulan, pakai format Inggris juga)
            $rekapAchievement = collect();
            foreach (range(1, 12) as $i) {
                $carbon = Carbon::createFromDate($tahunAchievement, $i, 1);
                $bulanNama = $carbon->format('F Y');
    
                // $data = $rekapGabungan->firstWhere('bulan', $bulanNama);
                $actual = $rekapPOAchievement->get($bulanNama, 0);
                $target = AchievementTarget::where('bulan', $bulanNama)
                    ->where('tahun', $tahunAchievement)
                    ->first();
    
                $rekapAchievement->push([
                    'bulan' => $bulanNama,
                    'tahun' => $tahunAchievement,
                    'jumlah_sph' => $data['jumlah_sph'] ?? 0,
                    'nominal_sph' => $data['nominal_sph'] ?? 0,
                    'jumlah_spk' => $data['jumlah_spk'] ?? 0,
                    // 'nominal_spk' => $data['nominal_spk'] ?? 0,
                    'nominal_spk' => $actual,
                    'target' => $target->target ?? 0,
                ]);
            }
    
            return view('pages.rekap_marketing', compact(
                'rekapGabungan',
                'rekapAchievement',
                'title',
                'tahunRekap',
                'tahunAchievement',
                'cabang',
                'cabangId' //optional, supaya bisa default selected
            ));
        }

    public function saveTarget(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $target = str_replace('.', '', $request->target);

        AchievementTarget::updateOrCreate(
            ['bulan' => $bulan, 'tahun' => $tahun],
            ['target' => $target]
        );

        return response()->json(['success' => true]);
    }
}
