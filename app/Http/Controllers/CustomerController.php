<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Marketing;
use App\Models\Wilayah;
use App\Models\KawasanIndustri;
use App\Models\CeklisPerizinan;
use App\Models\ProjectCeklisExclude;
use App\Models\VerifikasiProject;
use App\Models\ProjectTahapanProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CustomerController extends Controller
{
    /**
     * Tampilkan semua data customer
     */
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $title = 'Data Customer';
        $query = Customer::with(['marketing', 'kawasan_industri'])
        ->orderBy('id', 'DESC');
        //$no = 1;
        
        if ($user->role === 'admin marketing' && $user->cabang_id != 1) {
        $query->where('cabang_id', $user->cabang_id);
        }
        
            if ($request->filled('search')) {
            $query->where('nama_perusahaan', 'like', '%' . $request->search . '%');
        }

        
        $customer = $query->paginate(10)->withQueryString();
        $marketing = Marketing::all();

        // Ambil semua wilayah dari tabel lokal
        $provinsiList = Wilayah::where('jenis', 'provinsi')->pluck('nama', 'kode');
        $kabupatenList = Wilayah::where('jenis', 'kabupaten')->pluck('nama', 'kode');

        // Tambahkan nama provinsi, kabupaten, dan kawasan ke customer
        foreach ($customer as $c) {
            $c->provinsi_name = $provinsiList[$c->provinsi_id] ?? '-';
            $c->kabupaten_name = $kabupatenList[$c->kabupaten_id] ?? '-';
            $c->kawasan_name = $c->kawasan_industri->nama_kawasan ?? '-';

            // Konversi JSON PIC ke array
            $pics = is_string($c->pic_perusahaan)
                ? json_decode($c->pic_perusahaan, true)
                : $c->pic_perusahaan;

            $picsCollection = collect($pics ?? []);
            $primary = $picsCollection->firstWhere('utama', true) ?? $picsCollection->first();
            $c->primary_pic = $primary;
            $c->pic_perusahaan = $picsCollection->all();
        }

        return view('admin.customer_view', compact('customer', 'marketing', 'title'));
    }

    /**
     * Form tambah customer
     */
    public function create(Request $request)
    {
        // Jika permintaan AJAX untuk marketing berdasarkan status
        if ($request->ajax() && $request->has('status')) {
            $status = $request->get('status');
            $marketing = Marketing::where('status', $status)->get(['id', 'nama']);
            return response()->json($marketing);
        }

        $statusList = Marketing::select('status')->distinct()->pluck('status');
        $title = 'Tambah Data Customer';

        // Ambil daftar provinsi dari tabel lokal
        $provinsiList = Wilayah::where('jenis', 'provinsi')->orderBy('nama')->get();

        return view('admin.customer_create', compact('statusList', 'title', 'provinsiList'));
    }

    public function cekNama(Request $request)
    {
        $exists = Customer::where('nama_perusahaan', $request->nama)->exists();
    
        return response()->json([
            'exists' => $exists
        ]);
    }

    /**
     *  Simpan data customer baru
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'provinsi_id' => 'nullable|string',
            'kabupaten_id' => 'nullable|string',
            'kawasan_id' => 'nullable|integer|exists:kawasan_industri,id',
            'detail_alamat' => 'nullable|string|max:255',
            'marketing_id' => 'nullable|integer|exists:marketing,id',
            'utama' => 'nullable|integer',
            'pic_perusahaan' => 'nullable|array',
            'pic_perusahaan.*.nama' => 'nullable|string|max:255',
            'pic_perusahaan.*.kontak' => 'nullable|string|max:255',
            'pic_perusahaan.*.email' => 'nullable|email|max:255',

        ]);
        

        // Tandai PIC utama
        if (!empty($data['pic_perusahaan'])) {
            foreach ($data['pic_perusahaan'] as $i => &$pic) {
                $pic['utama'] = isset($data['utama']) && (int)$data['utama'] === $i;
            }
            unset($pic);
        }

        Customer::create([
            'nama_perusahaan' => $data['nama_perusahaan'],
            'provinsi_id' => $data['provinsi_id'] ?? null,
            'kabupaten_id' => $data['kabupaten_id'] ?? null,
            'kawasan_id' => $data['kawasan_id'] ?? null,
            'detail_alamat' => $data['detail_alamat'] ?? null,
            'marketing_id' => $data['marketing_id'] ?? null,
            'pic_perusahaan' => $data['pic_perusahaan'] ?? [],
            'cabang_id' => auth()->user()->cabang_id,
        ]);

        return redirect()->route('customer.index')->with('success', 'Data customer berhasil disimpan.');
    }

    /**
     * Form edit customer
     */ public function edit($id, Request $request)
    {
        $customer = Customer::findOrFail($id);

        $title = 'Edit Customer';

        // Ambil status & marketing terkait
        $statusList = Marketing::select('status')->distinct()->pluck('status');
        $currentMarketing = Marketing::find($customer->marketing_id);
        $currentStatus = $currentMarketing ? $currentMarketing->status : null;
        $marketingList = $currentStatus
            ? Marketing::where('status', $currentStatus)->get(['id', 'nama'])
            : collect();

        // Jika AJAX untuk ubah marketing berdasarkan status
        if ($request->ajax() && $request->has('status')) {
            $status = $request->get('status');
            $marketing = Marketing::where('status', $status)->get(['id', 'nama']);
            return response()->json($marketing);
        }

        // Ambil wilayah & kawasan
        $provinsiList = Wilayah::where('jenis', 'provinsi')->orderBy('nama')->get();
        $kabupatenList = $customer->provinsi_id
            ? Wilayah::where('parent_kode', $customer->provinsi_id)->where('jenis', 'kabupaten')->orderBy('nama')->get()
            : collect();

        $kawasanList = $customer->kabupaten_id
            ? DB::table('kawasan_industri as k')
            ->join('wilayahs as w', 'k.kabupaten_kode', '=', 'w.kode')
            ->where('k.kabupaten_kode', $customer->kabupaten_id)
            ->select('k.id', 'k.nama_kawasan', 'k.kabupaten_kode', 'w.nama as kabupaten_nama')
            ->orderBy('k.nama_kawasan')
            ->get()
            : collect();

        return view('admin.customer_update', compact(
            'customer',
            'title',
            'statusList',
            'currentStatus',
            'marketingList',
            'provinsiList',
            'kabupatenList',
            'kawasanList'
        ));
    }


    /**
     * 🔁 Update data customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $data = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'provinsi_id' => 'nullable|string',
            'kabupaten_id' => 'nullable|string',
            'kawasan_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && !KawasanIndustri::where('id', $value)->where('kabupaten_kode', $request->kabupaten_id)->exists()) {
                        $fail('Kawasan tidak valid untuk kabupaten yang dipilih.');
                    }
                }
            ],
            'detail_alamat' => 'nullable|string',
            'marketing_id' => 'required|exists:marketing,id',
            'pic_perusahaan' => 'nullable',
            'utama' => 'nullable',
        ]);

        // Proses PIC
        $picData = $request->pic_perusahaan;
        if (is_string($picData)) {
            $decoded = json_decode($picData, true);
            $picData = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (is_array($picData)) {
            foreach ($picData as $i => &$pic) {
                $pic['utama'] = ($request->utama == $i);
            }
        }

        $customer->update([
            'nama_perusahaan' => $data['nama_perusahaan'],
            'provinsi_id' => $data['provinsi_id'],
            'kabupaten_id' => $data['kabupaten_id'],
            'kawasan_id' => $data['kawasan_id'] ?? null,
            'detail_alamat' => $data['detail_alamat'],
            'marketing_id' => $data['marketing_id'],
            'pic_perusahaan' => json_encode($picData ?? []),
        ]);

    // Sinkronisasi ke Quotation yang ikut Customer
        $quotations = $customer->quotations()
            ->where(function ($q) {
                $q->where('is_same_nama_bangunan', true)
                    ->orWhere('is_same_alamat', true);
            })
            ->get();

        foreach ($quotations as $quo) {
            $updateData = [];

            // Jika ikut nama bangunan
            if ($quo->is_same_nama_bangunan) {
                $updateData['nama_bangunan'] = $customer->nama_perusahaan;
            }

            // Jika ikut alamat
            if ($quo->is_same_alamat) {
                $updateData['provinsi_id'] = $customer->provinsi_id;
                $updateData['kabupaten_id'] = $customer->kabupaten_id;
                $updateData['kawasan_id'] = $customer->kawasan_industri?->id;
                $updateData['detail_alamat'] = $customer->detail_alamat;
            }

            $quo->update($updateData);
        }


        return redirect('customer')->with('success', 'Data customer berhasil diperbarui.');
    }

    /**
     * 🧾 Set PIC utama via AJAX
     */
    public function setPicUtama(Request $request, $id)
    {
        $index = $request->get('index');
        $customer = Customer::findOrFail($id);
        $pics = json_decode($customer->pic_perusahaan, true);

        if (!$pics || !isset($pics[$index])) {
            return response()->json(['success' => false, 'message' => 'PIC tidak ditemukan']);
        }

        foreach ($pics as &$pic) $pic['utama'] = false;
        $pics[$index]['utama'] = true;

        $customer->update(['pic_perusahaan' => json_encode($pics)]);
        return response()->json(['success' => true, 'message' => 'PIC utama berhasil diperbarui']);
    }

    /**
     * 📦 Tracking proyek milik customer login
     */
    //index nya customer
public function tracking()
{   

    $user = Auth::user();
    $customerId = $user->customer_id;
    $title = 'Menu Projek';

    // Ambil semua project milik customer via PO → Project
    $projects = Project::with([
        'po.quotation',
        'project_perizinan.perizinan',
        'project_tahapan.tahapan',
        'catatan',
        'ceklisExclude'
    ])
    ->whereHas('po', function($q) use ($customerId) {
        $q->where('customer_id', $customerId);
    })
    ->get();

    // Hitung persentase dokumen dan catatan terakhir
    $projects->transform(function($project) {
        // Catatan terakhir
        $lastNote = $project->catatan->sortByDesc('created_at')->first();
        $project->catatan_terakhir = $lastNote->isi_catatan ?? '-';

        // Gabungkan jenis perizinan
        $project->jenis_perizinan = $project->project_perizinan->pluck('perizinan.jenis')->implode(', ');

        // Persentase dokumen
        $perizinanIds = $project->project_perizinan->pluck('perizinan_id')->toArray();
        $ceklist = CeklisPerizinan::whereIn('perizinan_id', $perizinanIds)->pluck('id')->toArray();
        $excluded = $project->ceklisExclude->where('is_active', 0)->pluck('ceklis_perizinan_id')->toArray();
        $activeDokumen = array_diff($ceklist, $excluded);

        $verified = VerifikasiProject::where('project_id', $project->id)
            ->whereIn('ceklis_perizinan_id', $activeDokumen)
            ->where('verified', 1)
            ->pluck('ceklis_perizinan_id')->toArray();

        $project->jumlah_dokumen = count($activeDokumen);
        $project->jumlah_verified = count($verified);
        $project->jumlah_unverified = $project->jumlah_dokumen - $project->jumlah_verified;

        $actual = $project->jumlah_dokumen > 0 
            ? ($project->jumlah_verified / $project->jumlah_dokumen) * 100 
            : 0;    

        // Ambil target collect
        $tahapanCollect = $project->project_tahapan->first(function($pt){
            return str_contains(strtolower($pt->tahapan->nama_tahapan), 'collect');
        });

        $project->persentase_target = $tahapanCollect->persentase_target ?? 0;
        $project->persentase_actual = round(($actual * $project->persentase_target) / 100, 2);


                /* =========================
         * FLAG PROJECT
         * ========================= */
        $project->sudah_buat_project = true;
        $project->punya_collect_dokumen = (bool) $tahapanCollect;

        /* =========================
         * LAMA PEKERJAAN
         * ========================= */
        $project->lama_pekerjaan = $project->po->quotation->lama_pekerjaan ?? null;

    // ======================
    // STATUS PROJECT
    // ======================
    $tahapanProject = DB::table('project_tahapan')
        ->where('project_id', $project->id)
        ->get();

    $totalTahapan = $tahapanProject->count();
    $jumlahStart = $tahapanProject->whereNotNull('actual_start')->count();
    $jumlahEnd   = $tahapanProject->whereNotNull('actual_end')->count();

    $semuaDokumenVerified = ($project->jumlah_unverified == 0);

    // Belum mulai
    $belumMulai = (
        $totalTahapan == 0 ||
        (
            $jumlahStart == 0 &&
            $jumlahEnd == 0 &&
            !$semuaDokumenVerified &&
            $project->jumlah_verified == 0
        )
    );

    // Selesai
    $selesai = (
        $jumlahStart == $totalTahapan &&
        $jumlahEnd == $totalTahapan &&
        $semuaDokumenVerified
    );

    if ($belumMulai) {
        $project->status_project = 'Belum Mulai';
    } else if ($selesai) {
        $project->status_project = 'Selesai';
    } else {
        $project->status_project = 'On Progress';
    }


        /* =========================
         * DEADLINE / SISA HARI
         * ========================= */
        $deadlineProject = null;
        $lama = (int) ($project->lama_pekerjaan ?? 0);

        if ($tahapanCollect && !$tahapanCollect->actual_end) {
            // Collect belum selesai
            $project->sisaHari = null;
        } else {
            if ($tahapanCollect && $tahapanCollect->actual_end) {
                $deadlineProject = \Carbon\Carbon::parse($tahapanCollect->actual_end)
                    ->addDays($lama);
            } else {
                $firstTahapan = $project->project_tahapan
                    ->sortBy('rencana_start')
                    ->first();

                if ($firstTahapan && $firstTahapan->rencana_start) {
                    $deadlineProject = \Carbon\Carbon::parse($firstTahapan->rencana_start)
                        ->addDays($lama);
                }
            }

            $project->sisaHari = $deadlineProject
                ? round(now()->diffInDays($deadlineProject, false))
                : null;
        }

        /* =========================
 * VERIFIKASI PER TAHAPAN
 * sumber: verifikasi_project
 * ========================= */
$project->verified_tahapan = VerifikasiProject::where('project_id', $project->id)
    ->where('verified', 1)
    ->get()
    ->groupBy('tahapan_id')
    ->map(function ($rows) {
        // ambil verified_at TERAKHIR per tahapan
        return $rows->max('verified_at');
    });

        return $project;
    });

    $customer = Customer::find($customerId);

    return view('pages.customer.index_customer', compact('customer', 'projects', 'title'));
}

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
    
        if ($customer->quotations()->exists()) {
            return back()->with('error', 'Customer masih memiliki SPH.');
        }
    
        $customer->delete();
    
        return back()->with('success', 'Customer berhasil dihapus.');
    }

    /**
     * 🔍 Detail proyek customer
     */
public function show_customer($id)
{
    $project = Project::with([
        'po.customer',
        'po.quotation',
        'project_perizinan.perizinan',
        'project_tahapan.tahapan',
        'marketing',
        'verifikasi_project'
    ])->findOrFail($id);
    
    $title = 'customer';
    $tahapanProject = $project->project_tahapan->sortBy('urutan')->values(); // koleksi ProjectTahapan
    $perizinanIds   = $project->project_perizinan->pluck('perizinan_id')->toArray();

    // daftar ceklis perizinan (semua), lalu filter exclude
    $allCeklis = CeklisPerizinan::whereIn('perizinan_id', $perizinanIds)
        ->with('perizinan')
        ->get();

    $excludeList = ProjectCeklisExclude::where('project_id', $project->id)
        ->where('is_active', 0)
        ->pluck('ceklis_perizinan_id')
        ->toArray();

    $listDokCollect = $allCeklis->filter(fn($d) => !in_array($d->id, $excludeList))->values();

    // verifikasi dokumen project
    $verifikasiList = VerifikasiProject::where('project_id', $project->id)->get();

    // ambil definisi sub tahapan untuk setiap tahapan project (jika ada)
    // SubTahapan punya kolom tahapan_id (referensi ke tabel tahapan master)
    $subTahapanMap = [];
    foreach ($tahapanProject as $pt) {
        $masterTahapanId = $pt->tahapan_id; // id di tabel tahapan master
        $subs = \App\Models\SubTahapan::where('tahapan_id', $masterTahapanId)->get();
        if ($subs->isNotEmpty()) {
            $subTahapanMap[$pt->id] = $subs; // gunakan key = project_tahapan.id
        }
    }

    $results = [];

    foreach ($tahapanProject as $tahapan) {
        $nama    = strtolower(optional($tahapan->tahapan)->nama_tahapan ?? '');
        $target  = (float) ($tahapan->persentase_target ?? 0);

        // Inisialisasi
        $percentActual = 0; // 0..100
        $realisasi     = 0; // percentActual * target / 100

        // 1) COLLECT DOKUMEN
        if ($nama === 'collect dokumen') {
            $dokRencana = $listDokCollect->count();
            $dokActual  = $verifikasiList
                ->where('tahapan_id', $tahapan->tahapan_id) // pastikan ambil verifikasi utk tahapan collect
                ->where('verified', 1)
                ->whereIn('ceklis_perizinan_id', $listDokCollect->pluck('id'))
                ->count();

            $percentActual = $dokRencana > 0 ? ($dokActual / $dokRencana) * 100 : 0;
            $realisasi     = ($percentActual * $target) / 100;
        }
        // 2) SURVEY & GAMBAR -> punya sub-tahapan
        elseif (isset($subTahapanMap[$tahapan->id]) && in_array($nama, ['survey', 'gambar'])) {
            $subs = $subTahapanMap[$tahapan->id]; // koleksi SubTahapan model

            $vals = [];
            foreach ($subs as $sub) {
                // Ambil persentase_actual terbaru khusus untuk project_tahapan ini + sub.id
                $last = \App\Models\ProjectTahapanProgress::where('project_tahapan_id', $tahapan->id)
                    ->where('sub_tahapan_id', $sub->id)
                    ->orderByDesc('tanggal_update')
                    ->orderByDesc('id')
                    ->value('persentase_actual');

                $vals[] = $last !== null ? (float) $last : 0.0;
            }

            // rata-rata latest per sub (jika jumlah sub > 0)
            $percentActual = count($vals) ? (collect($vals)->avg()) : 0;
            // jika semua sub = 100 maka treat tahapan = 100 (opsional, tapi berguna)
            if (count($vals) && collect($vals)->every(fn($v) => $v == 100)) {
                $percentActual = 100;
            }

            $realisasi = ($percentActual * $target) / 100;
        }
        // 3) TAHAPAN LAINNYA (tanpa sub)
        else {
            $last = \App\Models\ProjectTahapanProgress::where('project_tahapan_id', $tahapan->id)
                ->orderByDesc('tanggal_update')
                ->orderByDesc('id')
                ->first();

            $percentActual = $last ? (float) $last->persentase_actual : (float) ($tahapan->persentase_actual ?? 0);
            $realisasi = ($percentActual * $target) / 100;
        }

        // simpan hasil
        $results[$tahapan->id] = [
            'percent_actual' => round($percentActual, 2),
            'realisasi'      => round($realisasi, 2),
            'target'         => $target,
        ];
    }

    // total progress (opsional)
    $totalProgress = collect($results)->sum('realisasi');

    return view('pages.customer.show_customer', compact(
        'project', 'tahapanProject', 'listDokCollect', 'verifikasiList',
        'excludeList', 'results', 'totalProgress', 'subTahapanMap', 'title',
    ));
}
}
