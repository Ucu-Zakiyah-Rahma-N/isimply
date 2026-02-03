<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Perizinan;
use App\Models\MstTahapan;
use App\Models\Tracking;
use App\Models\Wilayah;
use App\Models\Marketing;
use App\Models\Tahapan;
use App\Models\ProjectSubTahapan;
use App\Models\ProjectPerizinan;
use App\Models\ProjectTahapan;
use App\Models\SubTahapan;
use App\Models\CeklisPerizinan;
use App\Models\ProjectCeklisExclude;
use App\Models\VerifikasiProject;
use App\Models\Quotation;
use Carbon\Carbon;
use App\Models\ProjectTahapanProgress;
use App\Models\PO;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\DashboardHelper;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;



class ProjectController extends Controller
{

    public function index(Request $request)
    {
        
$query = DB::table('po')
    ->join('quotations', 'po.quotation_id', '=', 'quotations.id')
    ->join('customers', 'po.customer_id', '=', 'customers.id')
    ->leftJoin('projects', 'projects.po_id', '=', 'po.id')
    ->leftJoin('marketing', 'projects.marketing_id', '=', 'marketing.id')
    ->leftJoin('quotation_perizinan', 'quotations.id', '=', 'quotation_perizinan.quotation_id')
    ->leftJoin('perizinans', 'quotation_perizinan.perizinan_id', '=', 'perizinans.id')
    ->leftJoin('kawasan_industri', 'quotations.kawasan_id', '=', 'kawasan_industri.id')
    ->leftJoin('wilayahs', 'quotations.kabupaten_id', '=', 'wilayahs.kode')
    ->where('po.bast_verified', 1);

// Kabupaten
if ($request->filled('kabupaten')) {
    $query->where('quotations.kabupaten_id', $request->kabupaten);
}

// Kawasan
if ($request->filled('kawasan')) {
    $query->where('kawasan_industri.nama_kawasan', $request->kawasan);
}

// Jenis Perizinan
if ($request->filled('perizinan')) {
    $query->where('perizinans.jenis', $request->perizinan);
}

// Cari No PO
if ($request->filled('searchPO')) {
    $query->where('po.no_po', 'LIKE', '%' . $request->searchPO . '%');
}

// GLOBAL SEARCH (Perusahaan / Bangunan)
if ($request->filled('searchGlobal')) {
    $search = $request->searchGlobal;

    $query->where(function ($q) use ($search) {
        $q->where('customers.nama_perusahaan', 'LIKE', "%{$search}%")
          ->orWhere('quotations.nama_bangunan', 'LIKE', "%{$search}%");
    });
}


$projects = $query
    ->orderBy('po.tgl_po', 'ASC')
    ->select(
        'po.id as po_id',
        'po.no_po',
        'po.tgl_po',
        'po.bast_verified',
        'po.bast_verified_at',
        'customers.nama_perusahaan',
        'wilayahs.nama as kabupaten_name',
        'kawasan_industri.nama_kawasan as kawasan_name',
        'quotations.detail_alamat',
        'quotations.nama_bangunan',
        'quotations.luas_slf',
        'quotations.luas_pbg',
        'quotations.luas_shgb',
        DB::raw("GROUP_CONCAT(perizinans.jenis SEPARATOR ', ') AS jenis_perizinan"),
        'quotations.lama_pekerjaan',
        'marketing.nama'
    )
    ->groupBy(
        'po.id',
        'po.no_po',
        'po.tgl_po',
        'po.bast_verified',
        'po.bast_verified_at',
        'customers.nama_perusahaan',
        'wilayahs.nama',
        'kawasan_industri.nama_kawasan',
        'quotations.detail_alamat',
        'quotations.nama_bangunan',
        'quotations.luas_slf',
        'quotations.luas_pbg',
        'quotations.luas_shgb',
        'quotations.lama_pekerjaan',
        'marketing.nama'
    )
    ->get();
        // ======================
        // FORMAT LUAS (SLF, PBG, SHGB)
        // ======================
        $projects->transform(function ($item) {

            $luasList = [];

            // SLF
            if (!is_null($item->luas_slf)) {
                $formatted = (floor($item->luas_slf) == $item->luas_slf)
                    ? number_format($item->luas_slf, 0, ',', '.')
                    : number_format($item->luas_slf, 2, ',', '.');
                $luasList[] = "SLF: {$formatted} m²";
            }

            // PBG
            if (!is_null($item->luas_pbg)) {
                $formatted = (floor($item->luas_pbg) == $item->luas_pbg)
                    ? number_format($item->luas_pbg, 0, ',', '.')
                    : number_format($item->luas_pbg, 2, ',', '.');
                $luasList[] = "PBG: {$formatted} m²";
            }

            // SHGB
            if (!is_null($item->luas_shgb)) {
                $formatted = (floor($item->luas_shgb) == $item->luas_shgb)
                    ? number_format($item->luas_shgb, 0, ',', '.')
                    : number_format($item->luas_shgb, 2, ',', '.');
                $luasList[] = "SHGB: {$formatted} m²";
            }

            $item->luasan = count($luasList) > 0 ? implode(', ', $luasList) : '-';
            $item->status = $item->bast_verified ? 'BAST Verified' : 'Pending';

            return $item;
        });



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
            $item->jumlah_unverified = 0;
            $item->jumlah_verified   = 0;
            $item->jumlah_dokumen    = 0;

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

            if ($collectTahapan && !$collectTahapan->actual_end) {
                $item->sisaHari = null;
                // JANGAN return, biarkan proses lanjut
            } else {
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
                    $item->sisaHari = round(now()->diffInDays($deadlineProject, false));
                } else {
                    $item->sisaHari = null;
                }
            }



            // ======================
            // CATATAN TERAKHIR
            // ======================
            $lastNote = DB::table('catatan')
                ->where('project_id', $project->id)
                ->latest()
                ->first();

            $item->catatan_terakhir = $lastNote->isi_catatan ?? null;

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
        
        $collection = $projects;

        if (request()->filled('status')) {
            $collection = $collection->filter(function ($item) {
                return $item->status_project === request('status');
            })->values();
        }
        
        $page = request()->get('page', 1);
        $perPage = 100;
        
        $projects = new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $allProjects = DashboardHelper::getAllProjects();

        // REKAP PROJECT BERDASARKAN STATUS
        $rekap = DashboardHelper::getProjectRekap($allProjects);

        $wilayahs = DB::table('wilayahs')
            ->where('jenis', 'kabupaten')
            ->orderBy('nama')
            ->get();
        $perizinan = DB::table('perizinans')
            ->orderBy('jenis')
            ->get();

        return view('pages.projects.index', [
            'title' => 'Projects (BAST Verified)',
            'projects' => $projects,
            'rekap' => $rekap,
            'wilayahs' => $wilayahs,
            'perizinan' => $perizinan,
        ]);
    }

    public function create($id)
    {
        $po = PO::findOrFail($id);

        $marketingInternal = Marketing::where('status', 'internal')->get(['id', 'nama']);
        // contoh: array ID perizinan yang ada di PO
        $poPerizinan = $po->quotation->perizinan->pluck('id')->toArray();

        $customers = Customer::all();
        $projects = Project::with(['customer', 'perizinan'])->get();
        $perizinans = Perizinan::orderBy('jenis')->get();
        $tahapanOpsional = Tahapan::orderBy('nama_tahapan')->get();

        $data = [
            'title' => 'Form Project',
            'customers' => $customers,
            'projects' => $projects,
            'marketingInternal' => $marketingInternal,
            'perizinan' => $perizinans,
            'tahapanOpsional' => $tahapanOpsional,
            'po_id' => $po->id,
            'po' => $po,
            'poPerizinan'   => $poPerizinan,

        ];

        return view('pages.projects.create', $data);
    }



    // public function store(Request $request)
    // {
    //     // dd($request->all());

    //     // 🔒 Validasi input dasar
    //     $validated = $request->validate([
    //         'po_id'         => 'required|exists:po,id',
    //         'marketing_id'  => 'required|exists:marketing,id',
    //     ]);

    //     // 1️⃣ SIMPAN PROJECT
    //     $project = Project::create([
    //         'po_id'        => $validated['po_id'],
    //         'marketing_id' => $validated['marketing_id'],
    //         'status'       => 'draft', // sesuai enum
    //     ]);

    //     // 2️⃣ Simpan relasi project_perizinan
    //     if ($request->has('perizinan')) {
    //         foreach ($request->perizinan as $perizinan_id) {
    //             ProjectPerizinan::create([
    //                 'project_id'    => $project->id,
    //                 'perizinan_id'  => $perizinan_id,
    //             ]);
    //         }
    //     }

    //     // urutan klik admin
    //     $urutanTahapan = $request->tahapan_input ?? [];

    //     $urutan = 1;

    //     foreach ($urutanTahapan as $slug) {

    //         // cek apakah ini survey
    //         if ($slug === 'survey') {
    //             $tahapanSurvey = Tahapan::whereRaw('LOWER(nama_tahapan) = ?', ['survey'])->first();

    //             $petugas = $request->personil['survey'] ?? null;
    //             $petugasArray = $petugas ? array_map('trim', explode(',', $petugas)) : [];

    //             ProjectTahapan::create([
    //                 'project_id'        => $project->id,
    //                 'tahapan_id'        => $tahapanSurvey->id,
    //                 'urutan'            => $urutan++,
    //                 'rencana_start'     => $request->rencana_mulai['survey'] ?? null,
    //                 'rencana_end'       => $request->rencana_selesai['survey'] ?? null,
    //                 'persentase_target' => $request->persentase_survey ?? 0,
    //                 'petugas'           => json_encode($petugasArray),
    //             ]);

    //             continue;
    //         }

    //         // selain survey = tahapan opsional
    //         $tahapan = Tahapan::whereRaw('LOWER(REPLACE(nama_tahapan, " ", "_")) = ?', [$slug])->first();
    //         if (!$tahapan) continue;

    //         ProjectTahapan::create([
    //             'project_id'        => $project->id,
    //             'tahapan_id'        => $tahapan->id,
    //             'urutan'            => $urutan++,
    //             'rencana_start'     => $request->rencana_mulai_opsional[$slug] ?? null,
    //             'rencana_end'       => $request->rencana_selesai_opsional[$slug] ?? null,
    //             'persentase_target' => $request->persentase_opsional[$slug] ?? 0,
    //         ]);
    //     }

    //     // ✅ Redirect dengan pesan sukses
    //     return redirect()->route('projects.index')->with('success', 'Project berhasil dibuat!');
    // }

public function store(Request $request)
{
    // ===============================
    // LOG AWAL (DEBUG)
    // ===============================
    \Log::info('STORE PROJECT - REQUEST MASUK', [
        'user_id' => auth()->id(),
        'payload' => $request->all(),
    ]);

    // ===============================
    // VALIDASI DASAR
    // ===============================
    $validated = $request->validate([
        'po_id'        => 'required|exists:po,id',
        'marketing_id' => 'required|exists:marketing,id',

        'tahapan_input'        => 'required|array|min:1',
        'tahapan_input.*'      => 'string',

        'perizinan_input'      => 'nullable|array',
        'perizinan_input.*'    => 'exists:perizinans,id',

        'persentase_survey'    => 'nullable|numeric|min:0|max:100',
        'persentase_opsional'  => 'nullable|array',
        
        'rencana_mulai_opsional.*'   => 'nullable|date',
        'rencana_selesai_opsional.*'=> 'nullable|date|after_or_equal:rencana_mulai_opsional.*',

    ]);

    DB::beginTransaction();

    try {

        // ===============================
        // 1. SIMPAN PROJECT
        // ===============================
        $project = Project::create([
            'po_id'        => $validated['po_id'],
            'marketing_id' => $validated['marketing_id'],
            'status'       => 'draft',
        ]);

        // ===============================
        // 2. SIMPAN PERIZINAN (URUTAN KLIK)
        // ===============================
        if (!empty($validated['perizinan_input'])) {
            foreach ($validated['perizinan_input'] as $index => $perizinanId) {
                ProjectPerizinan::create([
                    'project_id'   => $project->id,
                    'perizinan_id' => $perizinanId,
                    'urutan'       => $index + 1,
                ]);
            }
        }

        // ===============================
        // 3. SIMPAN TAHAPAN (URUTAN KLIK)
        // ===============================
        $urutan = 1;

        foreach ($validated['tahapan_input'] as $slug) {

            // ===============================
            // SURVEY
            // ===============================
            if ($slug === 'survey') {

                $tahapan = Tahapan::whereRaw('LOWER(nama_tahapan) = ?', ['survey'])->firstOrFail();

                $petugas = $request->personil['survey'] ?? null;
                $petugasArray = $petugas
                    ? array_map('trim', explode(',', $petugas))
                    : [];

                ProjectTahapan::create([
                    'project_id'        => $project->id,
                    'tahapan_id'        => $tahapan->id,
                    'urutan'            => $urutan++,
                    'rencana_start'     => $request->rencana_mulai['survey'] ?? null,
                    'rencana_end'       => $request->rencana_selesai['survey'] ?? null,
                    'persentase_target' => $request->persentase_survey ?? 0,
                    'petugas'           => json_encode($petugasArray),
                ]);

                continue;
            }

            // ===============================
            // TAHAPAN OPSIONAL
            // ===============================
            $namaTahapan = str_replace('_', ' ', $slug);

            $tahapan = Tahapan::whereRaw(
                'LOWER(nama_tahapan) = ?',
                [strtolower($namaTahapan)]
            )->first();

            if (!$tahapan) {
                \Log::warning('Tahapan tidak ditemukan', ['slug' => $slug]);
                continue;
            }

            ProjectTahapan::create([
                'project_id'        => $project->id,
                'tahapan_id'        => $tahapan->id,
                'urutan'            => $urutan++,
                'rencana_start'     => $request->rencana_mulai_opsional[$slug] ?? null,
                'rencana_end'       => $request->rencana_selesai_opsional[$slug] ?? null,
                'persentase_target' => $request->persentase_opsional[$slug] ?? 0,
            ]);
        }

        DB::commit();

        // ===============================
        // REDIRECT SUCCESS
        // ===============================
        return redirect()
            ->route('projects.index')
            ->with('success', 'Project berhasil dibuat');

    } catch (\Throwable $e) {

        DB::rollBack();

        \Log::error('STORE PROJECT - GAGAL', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()
            ->withInput()
            ->with('error', 'Gagal menyimpan project. Silakan cek log.');
    }
}


    public function verifikasi($po_id)
    {
        $title = 'Verifikasi Dokumen';

        $project = Project::where('po_id', $po_id)
            ->with(['project_tahapan.tahapan', 'project_perizinan.perizinan', 'quotation'])
            ->firstOrFail();

        $verifikasiList = VerifikasiProject::where('project_id', $project->id)->get();

        $currentDate = now()->toDateString();

        /* ===========================
       1. HITUNG COLLECT DOKUMEN
    ============================ */
        $excludedIds = ProjectCeklisExclude::where('project_id', $project->id)
            ->where('is_active', 0)
            ->pluck('ceklis_perizinan_id')
            ->toArray();

        $results = [];
        $collectDone = false;

        foreach ($project->project_tahapan as $tahapan) {

            $target = $tahapan->persentase_target ?? 0;

            $total = CeklisPerizinan::whereIn(
                'perizinan_id',
                $project->project_perizinan->pluck('perizinan_id')
            )
                ->when(count($excludedIds), fn($q) => $q->whereNotIn('id', $excludedIds))
                ->count();

            $verified = VerifikasiProject::where('project_id', $project->id)
                ->where('tahapan_id', $tahapan->tahapan_id)
                ->where('verified', 1)
                ->when(count($excludedIds), fn($q) => $q->whereNotIn('ceklis_perizinan_id', $excludedIds))
                ->count();

            $persenActual = $total > 0 ? ($verified / $total) * 100 : 0;
            $nilaiRealisasi = ($persenActual * $target) / 100;

            if (strtolower($tahapan->tahapan->nama_tahapan) === 'collect dokumen') {
                $collectDone = $persenActual >= $target;
            }

            $results[] = [
                'nama_tahapan'   => $tahapan->tahapan->nama_tahapan ?? '-',
                'target'          => $target,
                'total'           => $total,
                'verified'        => $verified,
                'persenActual'    => round($persenActual, 2),
                'nilaiRealisasi'  => round($nilaiRealisasi, 2),
            ];
        }

        $totalProgress = collect($results)->sum('nilaiRealisasi');
        $projectStartAllowed = $collectDone;

        /* ===========================
       2. HITUNG DEADLINE PROJECT
    ============================ */

        $po = Po::find($po_id);

        $quotation = Quotation::find($po->quotation_id);

        $lamaPekerjaan = (int) ($quotation->lama_pekerjaan ?? 0);

        // CASE 1 — COLLECT DOKUMEN ADA & SUDAH 100%
        $collectTahapan = ProjectTahapan::where('project_id', $project->id)
            ->whereHas('tahapan', fn($q) => $q->where('nama_tahapan', 'LIKE', '%Collect Dokumen%'))
            ->first();

        $deadlineProject = null;

        //dd
        // if ($collectTahapan) {
        //     dd([
        //         'actual_end' => $collectTahapan->actual_end,
        //         'lama_pekerjaan' => $lamaPekerjaan
        //     ]);
        // }
        if ($collectTahapan && $collectTahapan->actual_end) {
            // Hanya hitung deadline jika actual_end ada
            $deadlineProject = Carbon::parse($collectTahapan->actual_end)->addDays($lamaPekerjaan);
        }



        // CASE 2 — JIKA TIDAK ADA COLLECT / BELUM 100% → PAKAI RENCANA_START TERAWAL
        // Hanya hitung dari rencana_start jika Collect Dokumen TIDAK ADA sama sekali
        if (!$collectTahapan) {
            $firstTahapan = ProjectTahapan::where('project_id', $project->id)
                ->orderBy('rencana_start', 'asc')
                ->first();

            if ($firstTahapan) {
                $deadlineProject = Carbon::parse($firstTahapan->rencana_start)->addDays($lamaPekerjaan);
            }
        }


        /* ===========================
       3. HITUNG SISA HARI
    ============================ */
        $sisaHari = null;
        if ($deadlineProject) {
            $sisaHari = now()->diffInDays($deadlineProject, false);

            $sisaHari = round($sisaHari);
        }

        /* ===========================
       4. DATA TAMBAHAN UNTUK VIEW
    ============================ */
        $tahapanProject = ProjectTahapan::with(['tahapan', 'subTahapan'])
            ->where('project_id', $project->id)
            ->orderBy('urutan', 'asc')
            ->get();

        //untuk menambabhkan tahapan baru
        $existingIds = ProjectTahapan::where('project_id', $project->id)
            ->pluck('tahapan_id')
            ->toArray();

        $opsionalTahapan = \App\Models\Tahapan::whereNotIn('id', $existingIds)
            ->orderBy('nama_tahapan')
            ->get();


        $perizinanIds = $project->project_perizinan->pluck('perizinan_id');

        $subTahapanMap = [];
        foreach ($tahapanProject as $tahapan) {
            $nama = strtolower($tahapan->tahapan->nama_tahapan);
            if (stripos($nama, 'survey') !== false || stripos($nama, 'gambar') !== false) {
                //kalo mau pake id
                // if (in_array($tahapan->tahapan_id, [6, 29])) {
                $subTahapan = \App\Models\SubTahapan::where('tahapan_id', $tahapan->tahapan_id)->get();

                if ($subTahapan->isNotEmpty()) {
                    $subTahapanMap[$tahapan->id] = $subTahapan;
                }
            }
        }

        //group dokumen per perizinan
        $ceklistDokumen = CeklisPerizinan::whereIn('perizinan_id', $perizinanIds)
            ->with('perizinan')
            ->get()
            ->groupBy('perizinan_id');

        //timeline tahapan
        $tahapanList = ProjectTahapan::with('tahapan')
            ->where('project_id', $project->id)
            ->orderBy('rencana_start', 'asc')
            ->get();

        //tahapan yg aktif per hari ini
        $tahapanAktif = ProjectTahapan::with('tahapan')
            ->where('project_id', $project->id)
            ->where('rencana_start', '<=', $currentDate)
            ->where('rencana_end', '>=', $currentDate)
            ->first()
            ?? ProjectTahapan::with('tahapan')
            ->where('project_id', $project->id)
            ->where('rencana_start', '>', $currentDate)
            ->orderBy('rencana_start', 'asc')
            ->first();


        /** Cari Collect Dokumen */
        $collect = \App\Models\ProjectTahapan::where('project_id', $project->id)
            ->whereHas('tahapan', fn($q) => $q->where('nama_tahapan', 'LIKE', '%collect dokumen%'))
            ->first();

        if ($collect) {

            // ambil progress collect
            $excluded = \App\Models\ProjectCeklisExclude::where('project_id', $project->id)
                ->where('is_active', 0)
                ->pluck('ceklis_perizinan_id')
                ->toArray();

            $totalDoc = \App\Models\CeklisPerizinan::whereIn('perizinan_id', $project->project_perizinan->pluck('perizinan_id'))
                ->whereNotIn('id', $excluded)
                ->count();

            $verifiedDoc = \App\Models\VerifikasiProject::where('project_id', $project->id)
                ->where('tahapan_id', $collect->tahapan_id)
                ->where('verified', 1)
                ->whereNotIn('ceklis_perizinan_id', $excluded)
                ->count();

            $persen = $totalDoc > 0 ? ($verifiedDoc / $totalDoc) * 100 : 0;
        }

        // dd($deadlineProject);
        // exit;

        return view('pages.projects.verifikasi', compact(
            'title',
            'project',
            'tahapanProject',
            'ceklistDokumen',
            'results',
            'totalProgress',
            'verifikasiList',
            'tahapanList',
            'tahapanAktif',
            'sisaHari',
            'collectDone',
            'projectStartAllowed',
            'deadlineProject',
            'subTahapanMap',
            'opsionalTahapan'
        ));
    }

    public function update(Request $request, $id)
    {
        $role = strtolower(trim(Auth::user()->role));

        if (!in_array($role, ['admin 1', 'admin 2'])) {
            abort(403, 'Anda tidak memiliki akses');
        }

        $request->validate([
            'rencana_start' => 'required|date',
            'rencana_end' => 'required|date',
            'persentase_target' => 'required|numeric|min:0|max:100',
        ]);

        ProjectTahapan::where('id', $id)->update([
            'rencana_start' => $request->rencana_start,
            'rencana_end' => $request->rencana_end,
            'persentase_target' => $request->persentase_target,
        ]);

        return back()->with('success', 'Detail Tahapan berhasil diperbarui');
    }

    public function updateProgress(Request $request, $projectTahapanId)
    {
  
    \Log::info('UPDATE PROGRESS MASUK', [
        'project_tahapan_id' => $projectTahapanId,
        'request' => $request->all()
    ]);

  
        $projectTahapan = \App\Models\ProjectTahapan::findOrFail($projectTahapanId);
        $persentaseTarget = $projectTahapan->persentase_target ?? 0;
        $now = now();
        
        $persentaseActual = str_replace(',', '.', $request->persentase_actual ?? 0);
        $persentaseActual = floatval($persentaseActual);

        if ($request->filled('sub_tahapan_id')) {

            $subTahapanId = $request->sub_tahapan_id;
            // $persentaseActual = $request->persentase_actual ?? 0;

            // Simpan progress baru
            $progress = \App\Models\ProjectTahapanProgress::create([
                'project_tahapan_id' => $projectTahapanId,
                'sub_tahapan_id'     => $subTahapanId,
                'tanggal_update'     => $now,
                'persentase_actual'  => $persentaseActual,
            ]);

            // Ambil semua progress sub
            $subList = SubTahapan::where('tahapan_id', $projectTahapan->tahapan_id)->get();
            $values = [];
            $allDone = true;

            foreach ($subList as $sub) {
                $last = ProjectTahapanProgress::where('project_tahapan_id', $projectTahapanId)
                    ->where('sub_tahapan_id', $sub->id)
                    ->latest('tanggal_update')
                    ->value('persentase_actual') ?? 0;
                    
                $values[] = $last;
                
            if ($last < 100) {
                $allDone = false;
            }
            
            }

            $persentaseActualTahapan = $allDone ? 100 : round(collect($values)->avg(), 2);
            $realisasi = round(($persentaseActualTahapan / 100) * $persentaseTarget, 2);

            // Hitung actual_start & actual_end
            // $actual_start = ProjectTahapanProgress::where('project_tahapan_id', $projectTahapanId)
            //     ->orderBy('tanggal_update')
            //     ->value('tanggal_update');

            // $actual_end = $allDone
            //     ? ProjectTahapanProgress::where('project_tahapan_id', $projectTahapanId)
            //     ->where('persentase_actual', 100)
            //     ->latest('tanggal_update')
            //     ->value('tanggal_update')
            //     : null;
            $actual_start = $projectTahapan->actual_start ?? $now;
            $actual_end   = $allDone ? $now : null;

            $projectTahapan->update([
                'persentase_actual' => $persentaseActualTahapan,
                'realisasi_persentase' => $realisasi,
                'actual_start' => $actual_start,
                'actual_end' => $actual_end,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Progress sub-tahapan berhasil disimpan!',
                'data' => [
                    'persentase_actual' => $persentaseActualTahapan,
                    'realisasi' => $realisasi,
                    'actual_start' => $actual_start,
                    'actual_end' => $actual_end,
                ]
            ]);
        }

        // ===== Jika tanpa SUB-TAHAPAN =====
        // $persentaseActual = $request->persentase_actual ?? 0;
        // $realisasi = ($persentaseActual / 100) * $persentaseTarget;

        // Simpan progress
        $progress = ProjectTahapanProgress::create([
            'project_tahapan_id' => $projectTahapanId,
            'tanggal_update' => $now,
            'persentase_actual' => $persentaseActual,
        ]);
        
        $realisasi = round(($persentaseActual / 100) * $persentaseTarget, 2);


        // Hitung actual_start & actual_end
        // $actual_start = ProjectTahapanProgress::where('project_tahapan_id', $projectTahapanId)
        //     ->orderBy('tanggal_update')
        //     ->value('tanggal_update');

        // $actual_end = $persentaseActual == 100 ? $progress->tanggal_update : null;

        $actual_start = $projectTahapan->actual_start ?? $now;
        $actual_end   = $persentaseActual >= 100 ? $now : null;
    

        $projectTahapan->update([
            'persentase_actual' => $persentaseActual,
            'realisasi_persentase' => $realisasi,
            'actual_start' => $actual_start,
            'actual_end' => $actual_end,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress tahapan berhasil diperbarui!',
            'data' => [
                'persentase_actual' => $persentaseActual,
                'realisasi' => $realisasi,
                'actual_start' => $actual_start,
                'actual_end' => $actual_end
            ]
        ]);
    }

    // ini yang bener untk tahapa tanpa sub
    // public function updateProgress(Request $request, $projectTahapanId)
    // {
    //     $projectTahapan = ProjectTahapan::findOrFail($projectTahapanId);
    //     $persentaseTarget = $projectTahapan->persentase_target ?? 0;

    //     // --- Jika Punya Sub Tahapan ---
    //     $subTahapan = ProjectSubTahapan::where('project_tahapan_id', $projectTahapanId)->get();

    //     if ($subTahapan->count() > 0) {
    //         // Update masing-masing sub-tahapan
    //         foreach ($request->sub as $subId => $value) {
    //             ProjectSubTahapan::where('id', $subId)
    //                 ->update(['persentase_actual' => $value]);
    //         }

    //         // Hitung rata-rata sub
    //         $avg = ProjectSubTahapan::where('project_tahapan_id', $projectTahapanId)->avg('persentase_actual');
    //         $persentaseActual = $avg;
    //     } else {
    //         // --- Jika tidak punya sub tahapan ---
    //         $persentaseActual = $request->persentase_actual;
    //     }

    //     // Simpan ke tabel project_tahapan_progress (log setiap update)
    //     ProjectTahapanProgress::create([
    //         'project_tahapan_id' => $projectTahapanId,
    //         'tanggal_update' => now(),
    //         'persentase_actual' => $persentaseActual,
    //     ]);

    //     // Update juga ke project_tahapan (supaya progress terakhir tersimpan)
    //     $projectTahapan->update([
    //         'persentase_actual' => $persentaseActual,
    //         'realisasi_persentase' => ($persentaseActual * $persentaseTarget) / 100,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Progress berhasil diperbarui!',
    //         'data' => [
    //             'persentase_actual' => $persentaseActual,
    //             'realisasi' => ($persentaseActual * $persentaseTarget) / 100,
    //         ]
    //     ]);
    // }

    public function tambahTahapanOpsional(Request $req, $projectId)
    {
        // Ambil tahapan existing project
        $existing = DB::table('project_tahapan')
            ->where('project_id', $projectId)
            ->orderBy('urutan')
            ->get();

        // Loop tahapan opsional yg dipilih
        foreach ($req->tahapan_opsional as $tahapId) {

            $afterOrder = $req->sisip_setelah[$tahapId] ?? null;

            if ($afterOrder === null) {
                continue; // tidak memilih posisi → skip
            }

            // Buat slug key input
            $slug = Str::slug($req->nama[$tahapId], '_');

            // Geser tahapan setelah posisi sisipan (increment urutan)
            DB::table('project_tahapan')
                ->where('project_id', $projectId)
                ->where('urutan', '>', $afterOrder)
                ->increment('urutan', 1);

            // Insert tahapan baru
            DB::table('project_tahapan')->insert([
                'project_id'        => $projectId,
                'tahapan_id'        => $tahapId,
                'rencana_start'     => $req->rencana_mulai_opsional[$slug] ?? null,
                'rencana_end'       => $req->rencana_selesai_opsional[$slug] ?? null,
                'persentase_target' => $req->persentase_opsional[$slug] ?? 0,
                'urutan'            => $afterOrder + 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        return back()->with('success', 'Tahapan opsional berhasil ditambahkan!');
    }

    // public function timeline(Request $request)
    // {
    //     $title = 'timeline';

    //     $allowedRoles = ['admin 1', 'admin 2', 'admin marketing', 'superadmin', 'CEO', 'direktur', 'manager projek', 'manager finance', 'manager marketing'];

    //     if (!in_array(Auth::user()->role, $allowedRoles)) {
    //         abort(403);
    //     }

    //     $projects = \App\Models\Project::with([
    //         'po.quotation.customer',
    //         'project_tahapan' => function ($q) {
    //             $q->orderBy('rencana_start', 'asc');
    //         }
    //     ])->get();

    //     $resources = [];
    //     $events = [];

    //     foreach ($projects as $project) {
    //         $ptName = optional($project->po->quotation->customer)->nama_perusahaan
    //             ?? ('Project #' . $project->id);

    //         // RESOURCE -> per perusahaan
    //         $resources[] = [
    //             "id" => $project->id,
    //             "title" => $ptName,
    //             "eventColor" => "#" . substr(md5($ptName), 0, 6)
    //         ];

    //         $tahap = $project->project_tahapan;

    //         if ($tahap->isEmpty()) continue;

    //         // PLAN
    //         $planStart = $tahap->first()->rencana_start;
    //         $planEnd   = $tahap->last()->rencana_end;

    //         $events[] = [
    //             "id"       => "plan-{$project->id}",
    //             "resourceId" => $project->id,
    //             'start' => Carbon::parse($planStart)->toDateString(),
    //             'end'   => Carbon::parse($planEnd)->addDay()->toDateString(),
    //             'allDay' => true,
    //             "title"    => "PLAN",
    //             "color"    => "#21b3dc",
    //             "extendedProps" => [
    //                 "type" => "plan",
    //                 "order" => 1
    //             ]
    //         ];

    //         // ACTUAL
    //         $actualStart = $tahap->whereNotNull('actual_start')->min('actual_start');
    //         $actualEnd   = $tahap->whereNotNull('actual_end')->max('actual_end');

    //         if ($actualStart && $actualEnd) {
    //             $events[] = [
    //                 "id"       => "actual-{$project->id}",
    //                 "resourceId" => $project->id,
    //                 'start' => Carbon::parse($actualStart)->toDateString(),
    //                 'end'   => Carbon::parse($actualEnd)->addDay()->toDateString(),
    //                 'allDay' => true,
    //                 "title"    => "ACTUAL",
    //                 "color"    => "#5eb56e",
    //                 "extendedProps" => [
    //                     "type" => "actual",
    //                     "order" => 2
    //                 ]
    //             ];
    //         }
    //     }

    //     return view('pages.projects.timeline', [
    //         'resources' => $resources,
    //         'events'    => $events,
    //         'title' => $title,
    //         'mode' => Auth::user()->role //apakah hanya untuk admin 1 atau bisa untuk semua role kecuali customer?

    //     ]);
    // }

    public function timeline(Request $request)
    {
        $title = 'timeline';
        $user = Auth::user();

        $allowedRoles = [
            'admin 1',
            'admin 2',
            'admin marketing',
            'superadmin',
            'CEO',
            'direktur',
            'manager projek',
            'manager finance',
            'manager marketing'
        ];

        if (!in_array(Auth::user()->role, $allowedRoles)) {
            abort(403);
        }
        
            // =========================
        // LOGIKA CABANG MARKETING
        // =========================
        $isAdminMarketingCabang =
            $user->role === 'admin marketing'
            && $user->cabang_id != 1;
    
        $query = \App\Models\Project::with([
            'po.quotation.customer',
            'project_tahapan.tahapan'
        ]);
    
        // 🔥 FILTER KHUSUS ADMIN MARKETING CABANG
        if ($isAdminMarketingCabang) {
            $query->whereHas('po.quotation', function ($q) use ($user) {
                $q->where('cabang_id', $user->cabang_id);
            });
        }
    
        $projects = $query->get();

        // $projects = \App\Models\Project::with([
        //     'po.quotation.customer',
        //     'project_tahapan.tahapan'
        // ])->get();

        $resources = [];
        $events = [];

        foreach ($projects as $project) {

            $ptName = optional($project->po->quotation->customer)->nama_perusahaan
                ?? ('Project #' . $project->id);

            // =====================
            // RESOURCE = 1 PROJECT
            // =====================
            $resources[] = [
                'id'    => $project->id,
                'title' => $ptName
            ];

            foreach ($project->project_tahapan as $tahap) {

                $namaTahapan = $tahap->tahapan->nama_tahapan;
                $warna       = tahapan_color($namaTahapan);

                // =====================
                // PLAN
                // =====================
                if ($tahap->rencana_start && $tahap->rencana_end) {
                    $events[] = [
                        'id'         => "plan-{$tahap->id}",
                        'resourceId' => $project->id,
                        'start'     => Carbon::parse($tahap->rencana_start)->toDateString(),
                        'end'       => Carbon::parse($tahap->rencana_end)->addDay()->toDateString(),
                        'allDay'    => true,
                        'title'     => $namaTahapan,
                        'color'     => $warna,
                        'classNames' => ['plan'],
                        'extendedProps' => [
                            'type' => 'plan',
                            'order' => 1
                        ]
                    ];
                }

                // =====================
                // ACTUAL (DINAMIS)
                // =====================
                if ($tahap->actual_start) {

                    $actualStart = Carbon::parse($tahap->actual_start);
                    $actualEnd   = $tahap->actual_end
                        ? Carbon::parse($tahap->actual_end)
                        : Carbon::today();

                    $events[] = [
                        'id'         => "actual-{$tahap->id}",
                        'resourceId' => $project->id,
                        'start'     => $actualStart->toDateString(),
                        'end'       => $actualEnd->addDay()->toDateString(),
                        'allDay'    => true,
                        'title'     => $namaTahapan,
                        'color'     => $warna,
                        'classNames' => ['actual'],
                        'extendedProps' => [
                            'type'    => 'actual',
                            'order'   => 2,
                            'running' => $tahap->actual_start && !$tahap->actual_end,
                            'done'    => (bool) $tahap->actual_end

                        ]
                    ];
                }
            }
        }

        return view('timeline', compact(
            'resources',
            'events',
            'title'
        ));
    }


    public function timelineCustomer()
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) abort(403);

        $title = 'Timeline';

        // FILTER PROJECT KHUSUS CUSTOMER LOGIN
        $projects = Project::whereHas('po.quotation', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->with([
            'po.quotation.customer',
            'project_tahapan.tahapan'
        ])->get();

        $resources = [];
        $events = [];

        foreach ($projects as $project) {

            $ptName = optional($project->po->quotation->customer)->nama_perusahaan
                ?? ('Project #' . $project->id);

            // =====================
            // RESOURCE = 1 PROJECT
            // =====================
            $resources[] = [
                'id'    => $project->id,
                'title' => $ptName . ' - PO ' . optional($project->po)->no_po
            ];

            foreach ($project->project_tahapan as $tahap) {

                if (!$tahap->tahapan) continue;

                $namaTahapan = $tahap->tahapan->nama_tahapan;
                $warna       = tahapan_color($namaTahapan);

                // =====================
                // PLAN
                // =====================
                if ($tahap->rencana_start && $tahap->rencana_end) {
                    $events[] = [
                        'id'         => "plan-{$tahap->id}",
                        'resourceId' => $project->id,
                        'start'      => Carbon::parse($tahap->rencana_start)->toDateString(),
                        'end'        => Carbon::parse($tahap->rencana_end)->addDay()->toDateString(),
                        'allDay'     => true,
                        'title'      => $namaTahapan,
                        'color'      => $warna,
                        'classNames' => ['plan'],
                        'extendedProps' => [
                            'type'  => 'plan',
                            'order' => 1
                        ]
                    ];
                }

                // =====================
                // ACTUAL
                // =====================
                if ($tahap->actual_start) {

                    $actualStart = Carbon::parse($tahap->actual_start);
                    $actualEnd   = $tahap->actual_end
                        ? Carbon::parse($tahap->actual_end)
                        : Carbon::today();

                    $events[] = [
                        'id'         => "actual-{$tahap->id}",
                        'resourceId' => $project->id,
                        'start'      => $actualStart->toDateString(),
                        'end'        => $actualEnd->addDay()->toDateString(),
                        'allDay'     => true,
                        'title'      => $namaTahapan,
                        'color'      => $warna,
                        'classNames' => ['actual'],
                        'extendedProps' => [
                            'type'    => 'actual',
                            'order'   => 2,
                            'running' => $tahap->actual_start && !$tahap->actual_end,
                            'done'    => (bool) $tahap->actual_end
                        ]
                    ];
                }
            }
        }

        return view('timeline', [
            'resources' => $resources,
            'events'    => $events,
            'title'     => $title,
            'mode'      => 'customer'
        ]);
    }


    public function updateEvent(Request $request)
    {
        $eventId = $request->id;
        $start   = $request->start;
        $end     = $request->end;

        // Jika EVENT PLAN
        if (str_starts_with($eventId, "plan-")) {
            $projectId = intval(str_replace("plan-", "", $eventId));

            \App\Models\ProjectTahapan::where('project_id', $projectId)
                ->orderBy('rencana_start')
                ->first()
                ->update(['rencana_start' => $start]);

            \App\Models\ProjectTahapan::where('project_id', $projectId)
                ->orderBy('rencana_end', 'desc')
                ->first()
                ->update(['rencana_end' => $end]);
        }

        // Jika EVENT ACTUAL
        if (str_starts_with($eventId, "actual-")) {
            $projectId = intval(str_replace("actual-", "", $eventId));

            \App\Models\ProjectTahapan::where('project_id', $projectId)
                ->orderBy('actual_start')
                ->first()
                ->update(['actual_start' => $start]);

            \App\Models\ProjectTahapan::where('project_id', $projectId)
                ->orderBy('actual_end', 'desc')
                ->first()
                ->update(['actual_end' => $end]);
        }

        return response()->json(['success' => true]);
    }

public function exportTimelinePdf(Request $request)
{
    $user = Auth::user();

    // =====================
    // FILTER ROLE
    // =====================
    $allowedRoles = [
        'admin 1',
        'admin 2',
        'admin marketing',
        'superadmin',
        'CEO',
        'direktur',
        'manager projek',
        'manager finance',
        'manager marketing'
    ];

    $isCustomer = $user->role === 'customer';

    if (!$isCustomer && !in_array($user->role, $allowedRoles)) {
        abort(403);
    }

    // =====================
    // QUERY PROJECT (SAMA DENGAN TIMELINE)
    // =====================
    $projects = Project::with([
        'po.quotation.customer',
        'project_tahapan.tahapan'
    ])
    ->when($isCustomer, function ($q) use ($user) {
        $q->whereHas('po.quotation', function ($qq) use ($user) {
            $qq->where('customer_id', $user->customer->id);
        });
    })
    ->get();

    // =====================
    // BUILD DATA PDF
    // =====================
    $data = [];

    foreach ($projects as $project) {

        $ptName = optional($project->po->quotation->customer)->nama_perusahaan
            ?? 'Project #' . $project->id;

        foreach ($project->project_tahapan as $tahap) {

            // PLAN WAJIB ADA
            if (!$tahap->rencana_start || !$tahap->rencana_end) {
                continue;
            }

            // =====================
            // STATUS
            // =====================
            if ($tahap->actual_end) {
                $status = 'Selesai';
            } elseif ($tahap->actual_start) {
                $status = 'Sedang Berjalan';
            } else {
                $status = 'Belum Mulai';
            }

            $data[] = [
                'perusahaan' => $ptName,
                'tahapan' => $tahap->tahapan->nama_tahapan ?? '-',

                'rencana_mulai' =>
                    Carbon::parse($tahap->rencana_start)->format('d/m/Y'),

                'rencana_selesai' =>
                    Carbon::parse($tahap->rencana_end)->format('d/m/Y'),

                'actual_mulai' =>
                    $tahap->actual_start
                        ? Carbon::parse($tahap->actual_start)->format('d/m/Y')
                        : '-',

                'actual_selesai' =>
                    $tahap->actual_end
                        ? Carbon::parse($tahap->actual_end)->format('d/m/Y')
                        : ($tahap->actual_start ? 'Sedang berjalan' : '-'),

                'status' => $status
            ];
        }
    }

    // =====================
    // GENERATE PDF
    // =====================
    $pdf = Pdf::loadView('timeline_pdf', [
        'data' => $data,
        'tanggal' => now()->format('d/m/Y H:i')
    ])->setPaper('A4', 'landscape');

    return $pdf->download('timeline-project.pdf');
}
}
