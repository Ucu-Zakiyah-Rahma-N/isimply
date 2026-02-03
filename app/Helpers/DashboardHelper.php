<?php

namespace App\Helpers;

use App\Models\PO;
use App\Models\AchievementTarget;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class DashboardHelper
{
    //dahboard untuk marketing
    public static function getMarketingSummary($bulan = null, $tahun = null)
    {
        $user = auth()->user();
        $now = Carbon::now();

        $tahun = $tahun ?? $now->year;  // default tahun sekarang
        $bulan = $bulan ?: null;        // null berarti semua bulan

        $isCabang = $user->role === 'admin marketing' && $user->cabang_id != 1;

        // Query PO
        $poQuery = PO::with('quotation.quotation_perizinan')
            ->whereYear('tgl_po', $tahun)
            ->when($bulan, fn($q) => $q->whereMonth('tgl_po', $bulan))
            ->when($isCabang, fn($q) => $q->whereHas('quotation', fn($qq) => $qq->where('cabang_id', $user->cabang_id)));

        $poFiltered = $poQuery->get();

        // Jumlah & Nilai PO
        $jumlahPO = $poFiltered->count();
        $nilaiPO = $poFiltered->sum(fn($po) => $po->quotation->grand_total ?? 0);

        // $nilaiPO = $poFiltered->sum(function($po){
        //     if (!$po->quotation) return 0;
        //     $q = $po->quotation;
        //     return $q->harga_tipe === 'gabungan'
        //         ? $q->harga_gabungan
        //         : $q->quotation_perizinan->sum('harga_satuan');
        // });

        // Target & Achievement (Hanya HO)
        $target = $achievement = 0;
        if (!$isCabang) {
            if ($bulan) {
                // Per bulan
                $bulanNama = Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');
                $target = AchievementTarget::where('bulan', $bulanNama)
                    ->where('tahun', $tahun)
                    ->first()
                    ->target ?? 0;
                $achievement = $target > 0 ? round(($nilaiPO / $target) * 100, 1) : 0;
            } else {
                // Total tahun
                $targets = AchievementTarget::where('tahun', $tahun)->get();
                $target = $targets->sum('target');
                $achievement = $target > 0 ? round(($nilaiPO / $target) * 100, 1) : 0;
            }
        }

        // Grafik 12 bulan
        $bulanList = [];
        $nilaiList = [];
        for ($i=1; $i<=12; $i++) {
            $bulanList[] = Carbon::createFromDate($tahun, $i, 1)->format('M');

            $nilai = PO::with('quotation.quotation_perizinan')
                ->whereYear('tgl_po', $tahun)
                ->whereMonth('tgl_po', $i)
                ->when($isCabang, fn($q) => $q->whereHas('quotation', fn($qq) => $qq->where('cabang_id', $user->cabang_id)))
                ->get()
                ->sum(fn($po) => $po->quotation->grand_total ?? 0);

                // ->sum(function($po){
                //     if (!$po->quotation) return 0;
                //     $q = $po->quotation;
                //     return $q->harga_tipe === 'gabungan'
                //         ? $q->harga_gabungan
                //         : $q->quotation_perizinan->sum('harga_satuan');
                // });

            $nilaiList[] = $nilai;
        }

        return [
            'jumlahPO' => $jumlahPO,
            'nilaiPO' => $nilaiPO,
            'target' => $target,
            'achievement' => $achievement,
            'bulan_list' => $bulanList,
            'nilai_list' => $nilaiList,
            'tahun' => $tahun,
            'bulan' => $bulan, // null jika semua bulan
        ];
    }

    //  * ==============================
    //  *  REKAP PROJECT BERDASARKAN STATUS
    //  * ==============================
    //  */

    public static function getAllProjects()
{
    $projects = DB::table('po')
        ->join('quotations', 'po.quotation_id', '=', 'quotations.id')
        ->join('customers', 'po.customer_id', '=', 'customers.id')
        ->leftJoin('projects', 'projects.po_id', '=', 'po.id')
        ->leftJoin('marketing', 'projects.marketing_id', '=', 'marketing.id')
        ->leftJoin('quotation_perizinan', 'quotations.id', '=', 'quotation_perizinan.quotation_id')
        ->leftJoin('perizinans', 'quotation_perizinan.perizinan_id', '=', 'perizinans.id')
        ->leftJoin('kawasan_industri', 'quotations.kawasan_id', '=', 'kawasan_industri.id')
        ->leftJoin('wilayahs', 'quotations.kabupaten_id', '=', 'wilayahs.kode')
        ->where('po.bast_verified', 1)
        ->select(
            'po.id as po_id',
            'po.tgl_po',
            'po.bast_verified',
            'customers.nama_perusahaan',
            'wilayahs.nama as kabupaten_name',
            'kawasan_industri.nama_kawasan as kawasan_name',
            'quotations.detail_alamat',
            'quotations.luas_slf',
            'quotations.luas_pbg',
            'quotations.luas_shgb',
            DB::raw("
                GROUP_CONCAT(
                    perizinans.jenis 
                    ORDER BY quotation_perizinan.id 
                    SEPARATOR ', '
                ) AS jenis_perizinan
            "),
            'quotations.lama_pekerjaan',
            'marketing.nama'
        )
        ->groupBy(
            'po.id',
            'po.tgl_po',
            'po.bast_verified',
            'customers.nama_perusahaan',
            'wilayahs.nama',
            'kawasan_industri.nama_kawasan',
            'quotations.detail_alamat',
            'quotations.luas_slf',
            'quotations.luas_pbg',
            'quotations.luas_shgb',
            'quotations.lama_pekerjaan',
            'marketing.nama'
        )
        ->get();

    // COPY SEMUA TRANSFORM() PERTAMA

// ======================
// HITUNG DOKUMEN, TAHAPAN, STATUS PROJECT
// ======================
$projects->transform(function ($item) {

    // ====== CARI PROJECT BERDASARKAN PO ======
    $project = DB::table('projects')->where('po_id', $item->po_id)->first();
    $item->project_id = $project->id ?? null;

    // ====== BELUM ADA PROJECT → STATUS DEFAULT ======
    if (!$project) {

        $item->sudah_buat_project = false;
        $item->punya_collect_dokumen = false;

        // NILAI DEFAULT AGAR TIDAK ERROR DI BLADE
        $item->status_project = 'Belum Mulai';
        $item->jumlah_dokumen = 0;
        $item->jumlah_verified = 0;
        $item->jumlah_unverified = 0;
        $item->persentase_actual = 0;
        $item->persentase_target = 0;
        $item->sisaHari = null;
        $item->catatan_terakhir = null;

        return $item;
    }


    // ====== PROJECT ADA ======
    $item->sudah_buat_project = true;

    // CEK COLLECT DOKUMEN
    $collectExist = DB::table('project_tahapan')
        ->join('tahapan', 'project_tahapan.tahapan_id', '=', 'tahapan.id')
        ->where('project_tahapan.project_id', $project->id)
        ->whereRaw('LOWER(REPLACE(tahapan.nama_tahapan," ","")) LIKE ?', ['%collectdokumen%'])
        ->exists();

    $item->punya_collect_dokumen = $collectExist;

    // ======================
    // HITUNG DEADLINE (SISA HARI)
    // ======================
    $po = DB::table('po')->where('id', $item->po_id)->first();
    $quotation = DB::table('quotations')->where('id', $po->quotation_id)->first();
    $lamaPekerjaan = (int) ($quotation->lama_pekerjaan ?? 0);

    $collectTahapan = DB::table('project_tahapan')
        ->join('tahapan', 'project_tahapan.tahapan_id', '=', 'tahapan.id')
        ->where('project_tahapan.project_id', $project->id)
        ->where('tahapan.nama_tahapan', 'LIKE', '%Collect Dokumen%')
        ->first();

    $deadlineProject = null;

    if ($collectTahapan && $collectTahapan->actual_end) {
        $deadlineProject = \Carbon\Carbon::parse($collectTahapan->actual_end)->addDays($lamaPekerjaan);
    } else {
        $firstTahapan = DB::table('project_tahapan')
            ->where('project_id', $project->id)
            ->orderBy('rencana_start', 'asc')
            ->first();

        if ($firstTahapan) {
            $deadlineProject = \Carbon\Carbon::parse($firstTahapan->rencana_start)->addDays($lamaPekerjaan);
        }
    }

    if ($deadlineProject) {
        $item->sisaHari = round (now()->diffInDays($deadlineProject, false));
    } else {
        $item->sisaHari = null;
    }

    // // ======================
    // // CATATAN TERAKHIR
    // // ======================
    // $lastNote = DB::table('catatan')
    //     ->where('project_id', $project->id)
    //     ->latest()
    //     ->first();

    // $item->catatan_terakhir = $lastNote->isi_catatan ?? null;

    // ======================
    // HITUNG CEKLIS DOKUMEN
    // ======================
    $perizinan = DB::table('project_perizinan')
        ->where('project_id', $project->id)
        ->pluck('perizinan_id');

    if ($perizinan->isEmpty()) {
        $item->jumlah_dokumen = 0;
        $item->jumlah_verified = 0;
        $item->jumlah_unverified = 0;
        $item->persentase_actual = 0;
        $item->persentase_target = 0;
        $item->status_project = 'Belum Mulai';
        return $item;
    }

    $ceklist = DB::table('ceklis_perizinan')
        ->whereIn('perizinan_id', $perizinan)
        ->pluck('id')
        ->toArray();

    $excluded = DB::table('project_ceklis_exclude')
        ->where('project_id', $project->id)
        ->where('is_active', 0)
        ->pluck('ceklis_perizinan_id')
        ->toArray();

    $activeDokumen = array_diff($ceklist, $excluded);

    $item->jumlah_dokumen = count($activeDokumen);

    $verified = DB::table('verifikasi_project')
        ->where('project_id', $project->id)
        ->whereIn('ceklis_perizinan_id', $activeDokumen)
        ->where('verified', 1)
        ->pluck('ceklis_perizinan_id')
        ->toArray();

    $item->jumlah_verified = count($verified);
    $item->jumlah_unverified = $item->jumlah_dokumen - $item->jumlah_verified;

    // ======================
    // STATUS PROJECT
    // ======================
    $tahapanProject = DB::table('project_tahapan')
        ->where('project_id', $project->id)
        ->get();

    $totalTahapan = $tahapanProject->count();
    $jumlahStart = $tahapanProject->whereNotNull('actual_start')->count();
    $jumlahEnd   = $tahapanProject->whereNotNull('actual_end')->count();

    $semuaDokumenVerified = ($item->jumlah_unverified == 0);

    // Belum mulai
    $belumMulai = (
        $totalTahapan == 0 ||
        (
            $jumlahStart == 0 &&
            $jumlahEnd == 0 &&
            !$semuaDokumenVerified &&
            $item->jumlah_verified == 0
        )
    );

    // Selesai
    $selesai = (
        $jumlahStart == $totalTahapan &&
        $jumlahEnd == $totalTahapan &&
        $semuaDokumenVerified
    );

    if ($belumMulai) {
        $item->status_project = 'Belum Mulai';
    } else if ($selesai) {
        $item->status_project = 'Selesai';
    } else {
        $item->status_project = 'On Progress';
    }

    // ======================
    // PERSENTASE ACTUAL
    // ======================
    $actual = $item->jumlah_dokumen > 0
        ? ($item->jumlah_verified / $item->jumlah_dokumen) * 100
        : 0;

    $persentaseTarget = DB::table('project_tahapan')
        ->join('tahapan', 'project_tahapan.tahapan_id', '=', 'tahapan.id')
        ->where('project_tahapan.project_id', $project->id)
        ->whereRaw('LOWER(REPLACE(tahapan.nama_tahapan," ","")) LIKE ?', ['%collect%'])
        ->value('project_tahapan.persentase_target') ?? 0;

    $item->persentase_target = $persentaseTarget;
    $item->persentase_actual = round(($actual * $persentaseTarget) / 100, 2);

    return $item;
});

    return $projects;
}


    public static function getProjectRekap(Collection $projects): array
    {
        return [
            'belum_mulai' => $projects->where('status_project', 'Belum Mulai')->count(),
            'on_progress' => $projects->where('status_project', 'On Progress')->count(),
            'selesai'     => $projects->where('status_project', 'Selesai')->count(),
            'total'       => $projects->count(),    
        ];
    }

}
