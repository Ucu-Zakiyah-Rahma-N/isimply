<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Perizinan;
use App\Models\Wilayah;
use App\Models\QuotationPerizinan;
use App\Models\KawasanIndustri;
use App\Models\Cabang;
use App\Models\SatuanPerizinan;
use App\Models\QuotationTemplate;
use App\Models\MstTahapan;
use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Process\Process;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;
use app\Helpers\Terbilang; // kalau pakai package
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        
        $user = auth()->user();

        $query = Quotation::with(['customer', 'perizinan', 'kawasan_industri', 'customer.marketing'])
            ->orderBy('tgl_sph', 'DESC')
            ->orderBy('no_sph', 'DESC')
            ->orderBy('created_at', 'DESC');

        if (
            $user->role === 'admin marketing' &&
            $user->cabang_id != 1
        ) {
            $query->where('cabang_id', $user->cabang_id);
        }
        

        // filter
        // 🔹 Filter Kabupaten
        if ($request->filled('kabupaten')) {
            $query->where('kabupaten_id', $request->kabupaten);
        }

        // 🔹 Filter Kawasan
        if ($request->filled('kawasan')) {
            $query->whereHas('kawasan_industri', function ($q) use ($request) {
                $q->where('nama_kawasan', $request->kawasan);
            });
        }

        // 🔹 Filter Perizinan
        if ($request->filled('perizinan')) {
            $query->whereHas('perizinan', function ($q) use ($request) {
                $q->where('jenis', $request->perizinan);
            });
        }

        // 🔹 Filter Cabang
        // if ($request->filled('cabang')) {
        //     $query->where('cabang_id', $request->cabang);
        // }

        if (
            $request->filled('cabang') &&
            !($user->role === 'admin marketing' && $user->cabang_id)
        ) {
            $query->where('cabang_id', $request->cabang);
        }
        
        // 🔹 Search No SPH
        if ($request->filled('search')) {
            $query->where('no_sph', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('searchPIC')) {
            $searchPIC = $request->searchPIC;

            $query->whereHas('customer.marketing', function ($q) use ($searchPIC) {
                $q->where('nama', 'LIKE', '%' . $searchPIC . '%');
            });
        }

        if ($request->filled('searchPT')) {
            $searchPT = $request->searchPT;

            $query->whereHas('customer', function ($q) use ($searchPT) {
                $q->where('nama_perusahaan', 'LIKE', '%' . $searchPT . '%');
            });
        }
    
        // ✅ paginate + simpan query string
        $quotation = $query->paginate(10)->withQueryString();

        $kabupatenList = Wilayah::where('jenis', 'kabupaten')->pluck('nama', 'kode');

        foreach ($quotation as $q) {
            $q->kabupaten_name = $kabupatenList[$q->kabupaten_id] ?? '-';
            $q->kawasan_name = $q->kawasan_industri->nama_kawasan ?? '-';

            // Olah data luas
            $luasList = [];

            if (!is_null($q->luas_slf)) {
                $formatted = (floor($q->luas_slf) == $q->luas_slf)
                    ? number_format($q->luas_slf, 0, ',', '.')
                    : number_format($q->luas_slf, 2, ',', '.');
                $luasList[] = "SLF: {$formatted} m²";
            }

            if (!is_null($q->luas_pbg)) {
                $formatted = (floor($q->luas_pbg) == $q->luas_pbg)
                    ? number_format($q->luas_pbg, 0, ',', '.')
                    : number_format($q->luas_pbg, 2, ',', '.');
                $luasList[] = "PBG: {$formatted} m²";
            }

            if (!is_null($q->luas_shgb)) {
                $formatted = (floor($q->luas_shgb) == $q->luas_shgb)
                    ? number_format($q->luas_shgb, 0, ',', '.')
                    : number_format($q->luas_shgb, 2, ',', '.');
                $luasList[] = "SHGB: {$formatted} m²";
            }

            $q->luas_info = count($luasList) > 0 ? implode(', ', $luasList) : null;
        }

        $data = [
            'title' => 'Quotation',
            'quotation' => $quotation,
            'customers' => Customer::all(['id', 'nama_perusahaan', 'provinsi_id', 'kabupaten_id', 'detail_alamat']),
            'wilayahs'  => Wilayah::all(),
            'perizinan' => Perizinan::all(),
            'kawasan_industri' => KawasanIndustri::all(), 
            'cabang' => Cabang::where('status', 1)->get(),
        ];

        return view('pages.quotation.index', $data);
    }


    public function create()
    {
        $user = auth()->user();
        
        // Filter CABANG SESUAI USER
        // if ($user->role === 'admin marketing' && $user->cabang_id != 1) {
        if ($user->role === 'admin marketing') {
            // hanya cabang dia sendiri
            $cabang = Cabang::where('id', $user->cabang_id)
                ->where('status', 1)
                ->get();
        } else {
            // HO / internal / superadmin
            $cabang = Cabang::where('status', 1)->get();
        }
        
        $customers = Customer::all();
        $quotations = Quotation::with(['customer', 'perizinan'])->get();
        $provinsiList = Wilayah::where('jenis', 'provinsi')->orderBy('nama')->get();
        $perizinan = Perizinan::all();
        $satuanPerizinans = SatuanPerizinan::all();

        $data = [
            'title' => 'Form Quotation',
            'customers' => $customers,
            'quotations' => $quotations,
            'provinsiList' => $provinsiList,
            'perizinan' => $perizinan,
            'cabang' => $cabang,
            'user' => $user,
            'satuan_perizinans' => $satuanPerizinans,
        ];

        return view('pages.quotation.create', $data);
    }

    public function previewSph($id)
    {
        $cabang = Cabang::findOrFail($id);

        $last = Quotation::where('cabang_id', $id)
            ->orderBy('counter', 'DESC')
            ->first();

        $startNumber = $cabang->start_number ?? 1;

        $counter = $last ? $last->counter + 1 : $startNumber;
        $romawi = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        $bulan = date('n');
        $tahun = date('Y');

        return response()->json([
            "no_sph" => "{$counter}/SP{$cabang->kode_sph}/{$romawi[$bulan]}/{$tahun}"
        ]);
    }

    public function getCustomer($id)
    {
        try {
            $customer = Customer::find($id);

            if (!$customer) {
                return response()->json(['message' => 'Customer tidak ditemukan'], 404);
            }

            return response()->json([
                'id' => $customer->id,
                'nama_perusahaan' => $customer->nama_perusahaan,
                'provinsi_id' => $customer->provinsi_id,
                'kabupaten_id' => $customer->kabupaten_id,
                'kawasan_id' => $customer->kawasan_id,
                'detail_alamat' => $customer->detail_alamat,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Quotation Store Dipanggil', $request->all());
        Log::info('REQUEST RAW', [
            'diskon_tipe'    => $request->input('diskon_tipe'),
            'diskon_persen'  => $request->input('diskon_persen'),
            'diskon_nominal' => $request->input('diskon_nominal'),
            'ALL'            => $request->all(),
        ]);


        // Validasi input
        $data = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'cabang_id'       => 'required|exists:cabang,id',
            // 'no_sph'          => 'required|string|unique:quotations,no_sph',
            'tgl_sph'         => 'required|date',
            'fungsi_bangunan' => 'required|in:-,Fungsi Hunian,Fungsi Keagamaan,Fungsi Usaha,Fungsi Sosial dan Budaya,Fungsi Khusus',
            'nama_bangunan'   => 'nullable|string|max:255',
            'provinsi_id'     => 'nullable|integer',
            'kabupaten_id'    => 'nullable|integer',
            'kawasan_id'      => 'nullable|integer',
            'detail_alamat'   => 'nullable|string|max:1000',
            'is_same_nama_bangunan' => 'nullable|boolean',
            'is_same_alamat' => 'nullable|boolean',
            'lama_pekerjaan'  => 'nullable|integer',
            'jumlah_termin'      => 'required|integer|min:1|max:5',
            'termin'             => 'required|array',
            'termin.*'           => 'numeric|min:1|max:100',
            'perizinan_id'    => 'required|array',
            'perizinan_id.*'  => 'integer|exists:perizinans,id',
            'harga_tipe'      => 'required|in:satuan,gabungan',
            'harga_gabungan'  => 'nullable|numeric|min:0',
            'diskon_tipe'   => 'nullable|in:persen,nominal',
            'diskon_persen'=> 'nullable|numeric|min:0|max:100',
            'diskon_nominal'=> 'nullable|numeric|min:0',
        ], [
            'customer_id.required' => 'Customer harus dipilih.',
            'customer_id.exists' => 'Customer tidak valid.',
            'no_sph.required' => 'Nomor SPH wajib diisi.',
            'no_sph.unique' => 'Nomor SPH sudah terdaftar, gunakan nomor lain.',
            'tgl_sph.required' => 'Tanggal SPH wajib diisi.',
            'tgl_sph.date' => 'Format tanggal tidak valid.',
            'fungsi_bangunan.required' => 'Fungsi Bangunan SPH wajib diisi.',
            'lama_pekerjaan.required' => 'Lama Pekerjaan wajib diisi.',
            'termin.required' => 'Termin wajib diisi.',
            'perizinan_id.required' => 'Pilih minimal satu jenis perizinan.',
            'harga_tipe.required' => 'Pilih tipe harga (satuan/gabungan).',
        ]);

        // Ambil cabang
        $cabang = Cabang::find($request->cabang_id);

        // Hitung counter terbaru cabang ini
        $last = Quotation::where('cabang_id', $cabang->id)
            ->orderBy('counter', 'DESC')
            ->first();

        // Gunakan start_number jika belum ada data
        $startNumber = $cabang->start_number ?? 1;

        $newCounter = $last ? $last->counter + 1 : $startNumber;
        $romawi = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        // Generate nomor SPH
        $bulan = date('n');
        $tahun = date('Y');

        $generatedSph = "{$newCounter}/SP{$cabang->kode_sph}/{$romawi[$bulan]}/{$tahun}";

        // 2️⃣ Ambil input dinamis dari form
        $luas_slf = $request->input('luas_slf', []);
        $luas_pbg = $request->input('luas_pbg', []);
        $luas_shgb = $request->input('luas_shgb', []);
        $harga_satuan = $request->input('harga_satuan', []);

        // 3️⃣ Hitung total luas masing-masing
        $luas_slf_total = !empty(array_filter($luas_slf)) ? array_sum($luas_slf) : null;
        $luas_pbg_total = !empty(array_filter($luas_pbg)) ? array_sum($luas_pbg) : null;
        $luas_shgb_total = !empty(array_filter($luas_shgb)) ? array_sum($luas_shgb) : null;

        //VALIDASI TERMIN HARUS = 100%
        if (array_sum($request->termin) != 100) {
            return back()->withErrors(['termin' => 'Total termin harus 100%'])->withInput();
        }
        
        // =======================
        // NORMALISASI DISKON
        // =======================
        $diskonTipe  = $request->diskon_tipe;
        $diskonNilai = 0;
        
        if ($diskonTipe === 'persen') {
            $diskonNilai = (int) $request->diskon_persen;
        }
        
        if ($diskonTipe === 'nominal') {
            $diskonNilai = (int) preg_replace('/\D/', '', $request->diskon_nominal);
        }


        // 4️⃣ Simpan ke tabel quotations
        $quotation = Quotation::create([
            ...$data,
            'no_sph' => $generatedSph,
            'counter' => $newCounter,
            'luas_slf' => $luas_slf_total,
            'luas_pbg' => $luas_pbg_total,
            'luas_shgb' => $luas_shgb_total,
            'harga_gabungan' => $data['harga_tipe'] === 'gabungan' ? $data['harga_gabungan'] : null,
            'diskon_tipe'  => $diskonTipe,
            'diskon_nilai' => $diskonNilai,
        ]);

        $qty       = $request->input('qty', []);
        $satuan_id = $request->input('satuan_id', []);

        // 5️⃣ Simpan harga perizinan ke pivot quotation_perizinan
        foreach ($data['perizinan_id'] as $perizinan_id) {
            QuotationPerizinan::create([
                'quotation_id' => $quotation->id,
                'perizinan_id' => $perizinan_id,

                'qty'       => $qty[$perizinan_id] ?? 1,
                'satuan_id' => $satuan_id[$perizinan_id] ?? null,

                'harga_satuan' => $data['harga_tipe'] === 'satuan'
                    ? ($harga_satuan[$perizinan_id] ?? 0)
                    : null,
            ]);
        }
        // simpan termin
        $terminList = [];

        foreach ($request->termin as $urutan => $persen) {
            $terminList[] = [
                'urutan' => $urutan,   // supaya mulai dari 1, bukan 0
                'persen' => $persen
            ];
        }
        // simpan ke kolom JSON
        $quotation->update([
            'termin_persentase' => json_encode($terminList)
        ]);


        return redirect()->route('quotation.index')->with('success', 'Quotation berhasil dibuat!');
    }
    
    public function show($id)
{
    $title = 'show';
    
    $formatDesimal = function ($angka) {
        if ($angka === null) return '-';
        return floor($angka) == $angka
            ? number_format($angka, 0, ',', '.')
            : number_format($angka, 2, ',', '.');
    };

    $quotation = Quotation::with([
        'customer',
        'provinsi',
        'kabupaten',
        'kawasan_industri',
        'perizinan'
    ])->findOrFail($id);

    // ======================
    // Siapkan detail perizinan untuk tabel
    // ======================
    $detail_perizinan = [];
    foreach ($quotation->perizinan as $index => $p) {
        $harga = $quotation->harga_tipe === 'satuan' 
            ? ($p->pivot->harga_satuan ?? 0) 
            : ($index === 0 ? $quotation->harga_gabungan ?? 0 : null);

        $detail_perizinan[] = [
            'index' => $index + 1,
            'nama'  => $p->nama_perizinan,
            'harga' => $harga,
        ];
    }

    // ======================
    // Total harga, diskon, grand total
    // ======================
    $total_harga  = $quotation->total_harga;
    $total_diskon = $quotation->total_diskon;
    $grand_total  = $quotation->grand_total;
    $has_diskon   = $total_diskon > 0;

    // ======================
    // Luas bangunan (array)
    // ======================
    $luas_bangunan = [];
    if($quotation->luas_slf) $luas_bangunan['SLF'] = $quotation->luas_slf;
    if($quotation->luas_pbg) $luas_bangunan['PBG'] = $quotation->luas_pbg;
    if($quotation->luas_shgb) $luas_bangunan['SHGB'] = $quotation->luas_shgb;

    // ======================
    // Siapkan data untuk view
    // ======================
    $data = [
        'quotation'        => $quotation,
        'formatDesimal'    => $formatDesimal,
        'detail_perizinan' => $detail_perizinan,
        'total_harga'      => $total_harga,
        'total_diskon'     => $total_diskon,
        'grand_total'      => $grand_total,
        'has_diskon'       => $has_diskon,
        'luas_bangunan'    => $luas_bangunan,
        'title'             => $title
    ];

    if($quotation->perizinan->contains('id', 1)) { // SLF
        return view('pages.quotation.templates.slf', $data);
    }

    // Default untuk yang lain
    return view('pages.quotation.templates.default', $data);
}


    // public function show($id)
    // {
    //     // Fungsi format angka
    //     $formatDesimal = function ($angka) {
    //         if ($angka === null) return '-';

    //         // Bilangan bulat → tanpa ,00
    //         if (floor($angka) == $angka) {
    //             return number_format($angka, 0, ',', '.');
    //         }

    //         // Ada desimal → pakai 2 digit
    //         return number_format($angka, 2, ',', '.');
    //     };

    //     $quotation = Quotation::with([
    //         'customer',
    //         'provinsi',
    //         'kabupaten',
    //         'kawasan_industri',
    //         'perizinan'
    //     ])->findOrFail($id);

    //     // Tentukan luas bangunan yang tersedia
    //     $luas_bangunan = $quotation->luas_slf ?? $quotation->luas_pbg ?? $quotation->luas_shgb ?? null;


    //     // Siapkan list perizinan dan harga per item
    //     $detail_perizinan = [];
    //     foreach ($quotation->perizinan as $p) {
    //         $detail_perizinan[] = [
    //             'nama'  => $p->nama_perizinan,
    //             'harga' => $quotation->harga_tipe === 'satuan' ? ($p->pivot->harga_satuan ?? 0) : null,
    //         ];
    //     }

    //     // Tentukan nominal total
    //     if ($quotation->harga_tipe === 'gabungan') {
    //         $total_harga = $quotation->harga_gabungan ?? 0;
    //     } else {
    //         $total_harga = collect($detail_perizinan)->sum('harga');
    //     }

    //     // Siapkan data untuk view
    //     $data = [
    //         'title'             => 'Detail Quotation / SPH',
    //         'quotation'         => $quotation,
    //         'formatDesimal' => $formatDesimal,
    //     ];

    //     return view('pages.quotation.show', $data);
    // }

     public function edit($id)
    {
        $user = auth()->user();
        
        $quotation = Quotation::with(['customer', 'perizinan', 'provinsi', 'kabupaten', 'kawasan_industri'])
            ->findOrFail($id);
            
        if ($user->role === 'admin marketing') {
        // admin marketing hanya cabangnya sendiri
        $cabang = Cabang::where('id', $user->cabang_id)
            ->where('status', 1)
            ->get();
        } else {
            // superadmin / internal / HO
            $cabang = Cabang::where('status', 1)->get();
        }
        
        // Tentukan parent dgn benar
        $parentId = $quotation->parent_id ?? $quotation->id;

        // Cari versi terbaru berdasarkan parent_id
        $latest = Quotation::where('parent_id', $parentId)
            ->orWhere('id', $parentId)
            ->orderBy('version', 'desc')
            ->first();
        // Jika id yang diminta bukan versi terbaru → redirect
        if ($latest->id != $id) {
            return redirect()->route('quotation.edit', $latest->id);
        }
        // Setelah ini: $latest adalah quotation terbaru
        $quotation = $latest;

        // Quotation detail (perizinan + harga + luas)
        $quotationPerizinan = QuotationPerizinan::where('quotation_id', $id)->with('perizinan', 'quotation')->get();

        $provinsiList = Wilayah::where('jenis', 'provinsi')->orderBy('nama')->get();
        $customers = Customer::all();
        $perizinan = Perizinan::all();
        $kawasanIndustri = KawasanIndustri::all();
        $satuanPerizinans = SatuanPerizinan::all();

        //ambil data termin
        // $terminLama = $quotation->termin_persentase
        //     ? json_decode($quotation->termin_persentase, true)
        //     : [];
        $terminLama = $quotation->termin_persentase ?? [];



        $title = 'Edit Quotation';

        return view('pages.quotation.edit', compact(
            'quotation',
            'customers',
            'perizinan',
            'provinsiList',
            'kawasanIndustri',
            'title',
            'quotationPerizinan',
            'terminLama',
            'cabang',
            'satuanPerizinans'
        ));
    }


       public function update(Request $request, $id)
{
    if (auth()->user()->role === 'admin marketing') {
        $request->merge([
            'cabang_id' => auth()->user()->cabang_id
        ]);
    }

    $mode = $request->mode_update ?? 'update'; // default update biasa

    $old = Quotation::findOrFail($id);

    // ==========================
    // VALIDASI TERMIN
    // ==========================
    if (is_array($request->termin) && array_sum($request->termin) != 100) {
        return back()->withErrors(['termin' => 'Total termin harus 100%'])->withInput();
    }

    // ==========================
    // BUILD TERMIN LIST
    // ==========================
    $terminList = [];

    if (is_array($request->termin)) {
        foreach ($request->termin as $urutan => $persen) {
            $terminList[] = [
                'urutan' => (int) $urutan,
                'persen' => (float) $persen,
            ];
        }
    }

    // ==========================
    // MODE: UPDATE BIASA
    // ==========================
    if ($mode === 'update') {

        $quotation = $old;

    } else {

        // ==========================
        // MODE: REVISI
        // ==========================
        $parentId = $old->parent_id ?? $old->id;
        $parent = Quotation::findOrFail($parentId);

        $quotation = $old->replicate();

        $newVersion = $old->version + 1;
        $quotation->version = $newVersion;
        $quotation->parent_id = $parentId;

        // generate no_sph
        $parentNo = $parent->no_sph;
        list($counter, $body) = explode('/', $parentNo, 2);

        $body = preg_replace('/SP\.Rev\d+-/', 'SP', $body);

        if ($newVersion === 1) {
            $quotation->no_sph = $parentNo;
        } else {
            $rev = $newVersion - 1;
            $body = preg_replace('/^SP/', "SP.Rev{$rev}", $body);
            $quotation->no_sph = "{$counter}/{$body}";
        }
    }

    // ==========================
    // UPDATE DATA UMUM
    // ==========================
    $quotation->customer_id = $request->customer_id;
    $quotation->tgl_sph = $request->tgl_sph;
    $quotation->nama_bangunan = $request->nama_bangunan;
    $quotation->is_same_nama_bangunan = $request->is_same_nama_bangunan;
    $quotation->provinsi_id = $request->provinsi_id;
    $quotation->kabupaten_id = $request->kabupaten_id;
    $quotation->kawasan_id = $request->kawasan_id;
    $quotation->detail_alamat = $request->detail_alamat;
    $quotation->is_same_alamat = $request->is_same_alamat;
    $quotation->lama_pekerjaan = $request->lama_pekerjaan;
    $quotation->harga_tipe = $request->harga_tipe;

    $quotation->harga_gabungan = ($request->harga_tipe == 'gabungan')
        ? ($request->harga_gabungan ?? 0)
        : 0;

    // ==========================
    // LUAS PERIZINAN
    // ==========================
    $luasMap = [
        'SLF'  => 'luas_slf',
        'PBG'  => 'luas_pbg',
        'SHGB' => 'luas_shgb',
    ];

    foreach ($luasMap as $jenis => $field) {
        $inputLuas = $request->input($field, []);
        $dipilih = false;

        foreach ($request->input('perizinan_id', []) as $pid) {
            if (isset($inputLuas[$pid])) {
                $quotation->$field = $inputLuas[$pid];
                $dipilih = true;
                break;
            }
        }

        if (!$dipilih) {
            $quotation->$field = null;
        }
    }

    // ==========================
    // DISKON
    // ==========================
    $diskonTipe  = $request->diskon_tipe;
    $diskonNilai = 0;

    if ($diskonTipe === 'persen') {
        $diskonNilai = (int) $request->diskon_persen;
    }

    if ($diskonTipe === 'nominal') {
        $diskonNilai = (int) preg_replace('/\D/', '', $request->diskon_nominal);
    }

    $quotation->diskon_tipe  = $diskonTipe;
    $quotation->diskon_nilai = $diskonNilai;

    // ==========================
    // TERMIN
    // ==========================
    $quotation->termin_persentase = $terminList;
    $quotation->jumlah_termin = count($terminList);

    $quotation->save();

    // ==========================
    // PIVOT
    // ==========================
    $perizinanIds = $request->input('perizinan_id', []);
    $pivotData = [];

    foreach ($perizinanIds as $pid) {
        $pivotData[$pid] = [
            'harga_satuan' => ($request->harga_tipe == 'satuan')
                ? ($request->input("harga_satuan.$pid") ?? 0)
                : 0,
        ];
    }

    $quotation->perizinan()->sync($pivotData);

    // ==========================
    // RESPONSE
    // ==========================
    $msg = ($mode === 'revisi')
        ? 'Quotation revisi versi ' . $quotation->version . ' berhasil dibuat!'
        : 'Quotation berhasil diupdate tanpa revisi';

    return redirect()->route('quotation.index')->with('success', $msg);
}

// ini yg versi jadi revisi aja
  // public function update(Request $request, $id)
    // {
    //     if (auth()->user()->role === 'admin marketing') {
    //         $request->merge([
    //             'cabang_id' => auth()->user()->cabang_id
    //         ]);
    //     }

    //     $old = Quotation::findOrFail($id);

    //     // ==========================
    //     // TENTUKAN PARENT
    //     // ==========================
    //     $parentId = $old->parent_id ?? $old->id;

    //     // ambil quotation versi 1 (parent asli)
    //     $parent = Quotation::findOrFail($parentId);

    //     // ==========================
    //     // DUPLIKASI DATA
    //     // ==========================
    //     $quotation = $old->replicate();

    //     // hitung versi baru
    //     $newVersion = $old->version + 1;
    //     $quotation->version = $newVersion;
    //     $quotation->parent_id = $parentId;

    //     // ==========================
    //     // GENERATE NO SPH (FIX)
    //     // ==========================
    //     $parentNo = $parent->no_sph;

    //     // pisahkan counter dan body
    //     list($counter, $body) = explode('/', $parentNo, 2);

    //     // bersihkan SP.RevX jika ada (antisipasi revisi ulang)
    //     $body = preg_replace('/SP\.Rev\d+-/', 'SP', $body);


    //     // versi 1 → tanpa -R
    //     if ($newVersion === 1) {
    //         // versi 1 → tetap tanpa revisi
    //         $quotation->no_sph = $parentNo;
    //     } else {
    //         // versi 2 = Rev1, versi 3 = Rev2, dst
    //         $rev = $newVersion - 1;

    //         // sisipkan Rev setelah SP
    //         $body = preg_replace('/^SP/', "SP.Rev{$rev}", $body);

    //         $quotation->no_sph = "{$counter}/{$body}";
    //     }


    //     // Update data umum
    //     $quotation->customer_id = $request->customer_id;
    //     $quotation->tgl_sph = $request->tgl_sph;
    //     // $quotation->fungsi_bangunan = $request->fungsi_bangunan;
    //     $quotation->nama_bangunan = $request->nama_bangunan;
    //     $quotation->is_same_nama_bangunan = $request->is_same_nama_bangunan;
    //     $quotation->provinsi_id = $request->provinsi_id;
    //     $quotation->kabupaten_id = $request->kabupaten_id;
    //     $quotation->kawasan_id = $request->kawasan_id;
    //     $quotation->detail_alamat = $request->detail_alamat;
    //     $quotation->is_same_alamat = $request->is_same_alamat;
    //     $quotation->lama_pekerjaan = $request->lama_pekerjaan;
    //     $quotation->harga_tipe = $request->harga_tipe;

    //     $quotation->harga_gabungan = ($request->harga_tipe == 'gabungan')
    //         ? ($request->harga_gabungan ?? 0)
    //         : 0;

    //     // ------------------------
    //     // Update Luas perizinan
    //     // ------------------------
    //     $luasMap = [
    //         'SLF'  => 'luas_slf',
    //         'PBG'  => 'luas_pbg',
    //         'SHGB' => 'luas_shgb',
    //     ];

    //     foreach ($luasMap as $jenis => $field) {
    //         // input luas untuk jenis ini
    //         $inputLuas = $request->input($field, []);
    //         // cek apakah jenis ini dipilih (ada di perizinan_id)
    //         $dipilih = false;
    //         foreach ($request->input('perizinan_id', []) as $pid) {
    //             if (isset($inputLuas[$pid])) {
    //                 $quotation->$field = $inputLuas[$pid];
    //                 $dipilih = true;
    //                 break;
    //             }
    //         }
    //         if (!$dipilih) {
    //             $quotation->$field = null; // jika tidak ada, set null
    //         }
    //     }

    //     $quotation->save(); // SIMPAN RECORD BARU (VERSI BARU)

    //     // ------------------------
    //     // Update pivot perizinan & harga satuan
    //     // ------------------------
    //     $perizinanIds = $request->input('perizinan_id', []);

    //     $pivotData = [];
    //     foreach ($perizinanIds as $pid) {
    //         $pivotData[$pid] = [
    //             'harga_satuan' => ($request->harga_tipe == 'satuan')
    //                 ? ($request->input("harga_satuan.$pid") ?? 0)
    //                 : 0,  // jika gabungan → set 0
    //         ];
    //     }
    //     $quotation->perizinan()->sync($pivotData);

    //     // =======================
    //     // NORMALISASI DISKON
    //     // =======================
    //     $diskonTipe  = $request->diskon_tipe;
    //     $diskonNilai = 0;
        
    //     if ($diskonTipe === 'persen') {
    //         $diskonNilai = (int) $request->diskon_persen;
    //     }
        
    //     if ($diskonTipe === 'nominal') {
    //         $diskonNilai = (int) preg_replace('/\D/', '', $request->diskon_nominal);
    //     }   
        
    //     $quotation->diskon_tipe  = $diskonTipe;
    //     $quotation->diskon_nilai = $diskonNilai;

    //     //termin
    //     if ($request->termin && array_sum($request->termin) != 100) {
    //         return back()->withErrors(['termin' => 'Total termin harus 100%'])
    //             ->withInput();
    //     }
    //     // ============================
    //     // SIMPAN TERMIN KE JSON
    //     // ============================
    //     $terminList = [];

    //     if ($request->termin) {
    //         foreach ($request->termin as $urutan => $persen) {
    //             $terminList[] = [
    //                 'urutan' => (int) $urutan,
    //                 'persen' => (float) $persen,
    //             ];
    //         }
    //     }

    //     // Simpan ke database
    //     // $quotation->termin_persentase = json_encode($terminList);

    //     $terminLama = $quotation->termin_persentase;

    //     if (is_string($terminLama)) {
    //         $terminLama = json_decode($terminLama, true) ?? [];
    //     }

    //     $terminLama = $terminLama ?? [];

    //     $quotation->termin_persentase = $terminList;
    //     $quotation->jumlah_termin = count($terminList);

    //     $quotation->save();

    //     return redirect()->route('quotation.index')->with('success', 'Quotation revisi versi ' . $quotation->version . ' berhasil dibuat!');
    // }



    public function destroy($id)
    {
        try {
            $quotation = Quotation::findOrFail($id);
            $quotation->delete();

            return redirect()->route('quotation.index')->with('success', 'Quotation berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus quotation: ' . $e->getMessage());
        }
    }

    public function getByCustomer($id)
    {
        $quotations = \App\Models\Quotation::where('customer_id', $id)
            ->get(['id', 'no_sph']); // cukup ambil id dan no_sph

        return response()->json($quotations);
    }

    //harus nya di atas show
    public function templateIndex()
    {
        $title = 'Template SPH';
        $templates = \App\Models\QuotationTemplate::all();
        $perizinans = \App\Models\Perizinan::all(); // ambil semua kode + jenis

        $path = 'public/templates'; // storage/app/public/templates
        $files = Storage::files($path);

        $quotations = Quotation::with('customer')->latest()->get();
        return view('pages.quotation.template_SPH', compact('quotations', 'title', 'templates', 'files', 'path', 'perizinans'));
    }

    public function storeTemplateSPH(Request $request)
    {
        $request->validate([
            // 'kode_template' => 'required|unique:quotation_templates,kode_template',
            'nama_template' => 'required|string',
            'file' => 'required|mimes:docx|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filePath = $file->storeAs('templates', $originalName);

        QuotationTemplate::create([
            'kode_template' => $request->kode_template ?: 'Default', // bisa null jika kosong
            'nama_template' => $request->nama_template,
            'file_path' => str_replace('', '', $filePath),
        ]);

        return redirect()->route('template.index')->with('success', 'Template berhasil ditambahkan!');
    }

    public function uploadTemplateSPH(Request $request, $id)
    {
        $template = QuotationTemplate::findOrFail($id);

        $request->validate([
            'kode_template' => 'required|unique:quotation_templates,kode_template,' . $template->id,
            'nama_template' => 'required|string',
            'file' => 'nullable|mimes:docx|max:10240', // file optional
        ]);

        // Jika ada file baru → hapus file lama + simpan baru
        if ($request->hasFile('file')) {
            if ($template->file_path && Storage::exists('' . $template->file_path)) {
                Storage::delete('' . $template->file_path);
            }

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filePath = $file->storeAs('templates', $originalName);
            $template->file_path = str_replace('', '', $filePath);
        }

        // Update data lainnya
        $template->kode_template = $request->kode_template;
        $template->nama_template = $request->nama_template;
        $template->save();

        return redirect()->route('template.index')->with('success', 'Template berhasil diperbarui!');
    }

    // ============================
    // DOWNLOAD TEMPLATE
    // ============================
    public function downloadTemplateSPH($id)
    {
        $template = QuotationTemplate::findOrFail($id);
        $file = storage_path('app/public/' . $template->file_path);

        if (file_exists($file)) {
            return response()->download($file, $template->nama_template . '.docx');
        }

        return redirect()->route('template.index')->with('error', 'File tidak ditemukan.');
    }


    // ini yg bener word nya
    public function download($id)
    {
        $quotation = Quotation::with([
            'customer',
            'perizinan',
            'kabupaten',
            'provinsi',
        ])->findOrFail($id);

    // Log::info('Quotation loaded', $quotation->toArray());
    // Log::info('DEBUG SPH LAMA', [
    //     'id' => $quotation->id,
    //     'nama_bangunan' => $quotation->nama_bangunan,
    //     'fungsi_bangunan' => $quotation->fungsi_bangunan,
    //     'jenis_perizinan' => $quotation->jenis_perizinan_text,
    //     'termin_raw' => $quotation->termin_persentase,
    // ]);

//     $kodeIzin = $quotation->perizinan
//     ->pluck('kode')
//     ->map(fn ($k) => strtoupper($k))
//     ->unique()   // ambil kode unik
//     ->values()   // reset index
//     ->toArray();

// if (count($kodeIzin) === 1) {
//     // hanya ada satu kode, pakai template sesuai kode
//     switch ($kodeIzin[0]) {
//         case 'SLF':
//             $jenisTemplate = 'slf';
//             break;
//         case 'PBG':
//             $jenisTemplate = 'pbg';
//             break;
//         case 'UJI_RIKSA':
//             $jenisTemplate = 'uji_riksa';
//             break;
//         default:
//             $jenisTemplate = 'default';
//             break;
//     }
// } else {
//     // banyak kode / campuran, pakai default
//     $jenisTemplate = 'default';
// }

    $kodeIzin = $quotation->perizinan
        ->pluck('kode')
        ->map(fn ($k) => strtoupper($k))
        ->toArray();

    if (in_array('SLF', $kodeIzin)) {
        $jenisTemplate = 'slf';
    } elseif (in_array('PBG', $kodeIzin)) {
        $jenisTemplate = 'pbg';
    } elseif (in_array('UJI_RIKSA', $kodeIzin)) {
        $jenisTemplate = 'uji_riksa';
    } else {
        $jenisTemplate = 'default';
    }

    //skrg pake defaurlt dl
    // Ambil template pertama dari quotation templates yang ada
    $templateFile = QuotationTemplate::whereIn(
        'kode_template',
        $quotation->perizinan->pluck('kode')
    )->value('file_path');
    
    $templatePath = null;
    
    /**
     * ======================================
     * 1. TEMPLATE KHUSUS DARI QUOTATION
     * ======================================
     */
    if (!empty($templateFile) && file_exists(storage_path('app/public/' . $templateFile))) {
    
        $templatePath = storage_path('app/public/' . $templateFile);
    
    } else {

    /**
     * ======================================
     * 2. TEMPLATE BERDASARKAN TIPE & DISKON
     * ======================================
     */
    $hasDiskon = !empty($quotation->diskon_tipe) && $quotation->total_diskon > 0;

    //tentukan folder
    $templateFolder = "templates/{$jenisTemplate}/";

// Tentukan nama file
if ($quotation->harga_tipe === 'satuan') {
    $fileName = $hasDiskon
        ? 'sph_satuan_diskon.docx'
        : 'sph_satuan.docx';
} else {
    $fileName = $hasDiskon
        ? 'sph_gabungan_diskon.docx'
        : 'sph_gabungan.docx';
}

$fullPath = storage_path('app/public/' . $templateFolder . $fileName);

if (file_exists($fullPath)) {
    $templatePath = $fullPath;
}

    /**
     * ======================================
     * 3. FALLBACK DEFAULT
     * ======================================
     */
    if (empty($templatePath)
        && file_exists(storage_path('app/public/templates/sph_default.docx'))
    ) {
        $templatePath = storage_path('app/public/templates/sph_default.docx');
    }

    if (empty($templatePath)) {
        return back()->with('error', 'Template Word tidak ditemukan');
    }
}

$template = new TemplateProcessor($templatePath);

        // ===============================
        // CLONE PERIZINAN
        // ===============================
        // ===============================
        // CLONE PERIZINAN + ISI HARGA
        // ===============================
        //$jumlahIzin = $quotation->perizinan->count();
       // $template->cloneRow('izin_no', $jumlahIzin);

     $no = 1;

/**
 * ======================================
 * HITUNG TOTAL HARGA DASAR
 * ======================================
 */
if ($quotation->harga_tipe === 'gabungan') {
    $totalHargaDasar = $quotation->harga_gabungan;
} else {
    $totalHargaDasar = $quotation->perizinan->sum(function ($izin) {
        $qty = (int) ($izin->pivot->qty ?? 0);
        $hargaSatuan = (float) ($izin->pivot->harga_satuan ?? 0);

        return $qty * $hargaSatuan;
    });
}

/**
 * ======================================
 * HITUNG TOTAL AKHIR (SETELAH DISKON)
 * ======================================
 */
if (!empty($quotation->diskon_tipe) && $quotation->total_diskon > 0) {
    $totalAkhir = max(0, $totalHargaDasar - $quotation->total_diskon);
} else {
    $totalAkhir = $totalHargaDasar;
}

$terbilangHargaDasar = Terbilang::convert($totalHargaDasar);
$terbilangTotalAkhir = Terbilang::convert($totalAkhir);
        
        Log::info('DEBUG TEMPLATE VALUES', [
    'tgl_sph' => $quotation->tgl_sph,
    'no_sph' => $quotation->no_sph,
    'nama_customer' => $quotation->customer->nama_perusahaan ?? null,
    'alamat_customer' => trim(
        ($quotation->customer->detail_alamat ?? '') . ', ' .
        Str::title(strtolower(optional($quotation->customer->kabupaten)->nama)) . ', ' .
        Str::title(strtolower(optional($quotation->customer->provinsi)->nama))
    ),
    'nama_bangunan' => $quotation->nama_bangunan,
    'fungsi_bangunan' => $quotation->fungsi_bangunan ?? '-',
    'lokasi' => trim(
        "{$quotation->detail_alamat}, " .
        Str::title(strtolower($quotation->kabupaten->nama)) . ", " .
        Str::title(strtolower($quotation->provinsi->nama))
    ),
    'jenis_perizinan' => $quotation->jenis_perizinan_text,
    'luas_bangunan' => $quotation->luas_bangunan_text,
    'izin_total' => $totalHargaDasar,
    'izin_total_terbilang' => $terbilangHargaDasar,
    'total_setelah_diskon' => $totalAkhir,
    'total_setelah_diskon_terbilang' => $terbilangTotalAkhir,
    'lama_pekerjaan' => $quotation->lama_pekerjaan,
]);


        // ===============================
        // SET DATA
        // ===============================
        $template->setValues([
            'tgl_sph' => Carbon::parse($quotation->tgl_sph)->translatedFormat('d F Y'),
            'no_sph'  => $quotation->no_sph,

            'nama_customer' => strtoupper($quotation->customer->nama_perusahaan),
        
            'alamat_customer' => trim(
                ($quotation->customer->detail_alamat ?? '') . ', ' .
                Str::title(strtolower(optional($quotation->customer->kabupaten)->nama)) . ', ' .
                Str::title(strtolower(optional($quotation->customer->provinsi)->nama))
            ),

            'nama_bangunan' => $quotation->nama_bangunan,
            'fungsi_bangunan' => $quotation->fungsi_bangunan ?? '-',

            'lokasi' => trim(
                "{$quotation->detail_alamat}, " .
                Str::title(strtolower($quotation->kabupaten->nama)) . ", " .
                Str::title(strtolower($quotation->provinsi->nama))
            ),
            
            'jenis_perizinan' => $quotation->jenis_perizinan_text,
            'luas_bangunan'   => $quotation->luas_bangunan_text,

            // 'total_harga' => number_format($totalAkhir, 0, ',', '.'),
            'izin_total' => number_format($totalHargaDasar, 0, ',', '.'),
            'izin_total_terbilang' => $terbilangHargaDasar,
    
            'total_setelah_diskon' => number_format($totalAkhir, 0, ',', '.'),
            'total_setelah_diskon_terbilang' => $terbilangTotalAkhir,

            'lama_pekerjaan' => $quotation->lama_pekerjaan
        ]);

/**
 * ======================================
 * TABEL PERIZINAN
 * ======================================
 */
 Log::info('DEBUG TEMPLATE HARGA_TIPE', [
    'harga_tipe' => $quotation->harga_tipe,
    'harga_gabungan' => $quotation->harga_gabungan,
    'perizinan_count' => $quotation->perizinan->count(),
]);

if ($quotation->harga_tipe === 'gabungan') {

    /**
     * WAJIB:
     * Word template harus punya 1 baris dengan:
     * ${izin_no} | ${izin_jenis} | ${izin_harga}
     * TANPA cloneRow
     */
        Log::info('TEMPLATE: Harga Gabungan', [
        'izin_no' => '',
        'izin_jenis' => 'Biaya Perizinan (Gabungan)',
        'izin_qty' => '-',
        'izin_satuan' => '-',
        'izin_harga' => $quotation->harga_gabungan
    ]);
    
    $template->setValue('izin_no', '');
    $template->setValue('izin_jenis', 'Biaya Perizinan (Gabungan)');
    $template->setValue('izin_qty', '-');
    $template->setValue('izin_satuan', '-');

    $template->setValue(
        'izin_harga',
        number_format($quotation->harga_gabungan, 0, ',', '.')
    );

} else {

    /**
     * Harga satuan ? cloneRow
     */
    $jumlahIzin = $quotation->perizinan->count();
    Log::info('TEMPLATE: Harga Satuan, jumlah izin', ['jumlah_izin' => $jumlahIzin]);

    // Pastikan minimal 1 data agar tidak error
    if ($jumlahIzin > 0) {
        $template->cloneRow('izin_no', $jumlahIzin);
    }
    
    $satuanMap = SatuanPerizinan::pluck('nama', 'id');
    $no = 1;
    // foreach ($quotation->perizinan as $izin) {
    //     $template->setValue("izin_no#{$no}", $no);
    //     $template->setValue("izin_jenis#{$no}", $izin->jenis);
    //     $template->setValue(
    //         "izin_harga#{$no}",
    //         number_format($izin->pivot->harga_satuan, 0, ',', '.')
    //     );
    //     $no++;
    // }
    $satuanMap = SatuanPerizinan::pluck('nama', 'id');

    foreach ($quotation->perizinan as $izin) {

    $qty    = $izin->pivot->qty ?? 1;
    $satuan = optional($izin->pivot->satuan)->nama ?? '-';
    $hargaSatuan  = (float) ($izin->pivot->harga_satuan ?? 0);
    $hargaTotal   = $qty * $hargaSatuan;
            Log::info("TEMPLATE: Set perizinan #{$no}", [
            'izin_no' => $no,
            'izin_jenis' => $izin->jenis,
            'izin_qty' => $qty,
            'izin_satuan' => $satuan,
            'izin_harga' => $hargaSatuan,
            'izin_harga_total' => $hargaTotal
        ]);

    $template->setValue("izin_no#{$no}", $no);
    $template->setValue("izin_jenis#{$no}", $izin->jenis);
    $template->setValue("izin_qty#{$no}", $qty);
    $template->setValue("izin_satuan#{$no}",$satuanMap[$izin->pivot->satuan_id] ?? '-');
    $template->setValue("izin_harga#{$no}",number_format($izin->pivot->harga_satuan, 0, ',', '.'));

    $template->setValue("izin_harga_total#{$no}",number_format($hargaTotal, 0, ',', '.'));

    $no++;
    }

}


/**
 * ======================================
 * TOTAL HARGA (DITAMPILKAN DI BAWAH TABEL)
 * ======================================
 * Gunakan placeholder:
 * ${izin_total}
 */
$template->setValue(
    'izin_total',
    number_format($totalHargaDasar, 0, ',', '.')
);

if (!empty($quotation->diskon_tipe) && $quotation->total_diskon > 0) {

    // Diskon
    $template->setValue(
        'diskon_nilai',
        number_format($quotation->total_diskon, 0, ',', '.')
    );

    // Total setelah diskon
    $template->setValue(
        'total_setelah_diskon',
        number_format($totalAkhir, 0, ',', '.')
    );
    $template->setValue(
        'total_setelah_diskon_terbilang',
        Terbilang::convert($totalAkhir)
    );

} else {

    // Hapus seluruh baris diskon dari Word
    $template->deleteBlock('diskon_block');
}

        // ===============================
        // CLONE TERMIN
        // ===============================
        
        
        Log::info('START PROCESS TERMIN', [
            'quotation_id' => $quotation->id,
            'termin_raw'   => $quotation->termin_persentase
        ]);
        // Ambil data termin
$termin = $quotation->termin_persentase ?? [];
Log::info('Raw termin_persentase from DB', [
            'quotation_id' => $quotation->id,
            'raw_value'    => $quotation->termin_persentase,
        ]);
        if (empty($termin)) {
            $termin = [(object)['urutan' => 1, 'persen' => 100]];
                Log::info('TERMIN kosong, menggunakan default', [
        'termin' => $termin
    ]);

        }

$huruf = range('A', 'Z');
$jenis_perizinan = $quotation->jenis_perizinan_text;
$totalTermin = count($termin);


Log::info('TOTAL TERMIN', [
    'totalTermin' => $totalTermin
]);

$terminPlaceholder = [];
$kumulatif = 0;

foreach ($termin as $i => $row) {

    // $kumulatif += $row->persen;
    $kumulatif += $row['persen'];
    $subPoin = [];

    // ===============================
    // TERMIN PERTAMA (DP)
    // ===============================
    if ($i === 0 && $totalTermin > 1) {

        $subPoin = [
            "1. Penawaran disetujui dan data lengkap diserahkan;",
            "2. Pembayaran senilai {$row['persen']}% dari nilai kontrak;",
            "3. Pekerjaan mulai diproses oleh PT Simply Dimensi Indonesia"
        ];
    }

    // ===============================
    // TERMIN TENGAH
    // ===============================
    elseif ($i < $totalTermin - 1) {

        $subPoin = [
            "1. Progres pekerjaan berjalan;",
            "2. Pembayaran senilai {$row['persen']}% dari nilai kontrak;",
            "3. Dokumen {$jenis_perizinan} dalam proses penerbitan"
        ];
    }

    // ===============================
    // TERMIN TERAKHIR (PELUNASAN)
    // ===============================
    else {

        $subPoin = [
            "1. Seluruh pekerjaan dinyatakan selesai;",
            "2. {$jenis_perizinan} telah diterbitkan;",
            "3. Pembayaran pelunasan {$row['persen']}% sehingga total menjadi {$kumulatif}%"
        ];
    }

    // ===============================
    // JUDUL TERMIN
    // ===============================
    $judul = "Pembayaran ke-{$row['urutan']}";
    if ($i === 0 && $totalTermin > 1) {
        $judul .= " (Down Payment)";
    }

    $terminPlaceholder[] = [
        'huruf' => $huruf[$i],
        'judul' => $judul,
        'sub'   => implode("\n", $subPoin),
    ];
    
        Log::info("TERMIN #{$i} processed", [
        'huruf'       => $huruf[$i],
        'judul'       => $judul,
        'sub_poin'    => $subPoin,
        'kumulatif'   => $kumulatif,
        'persen_row'  => $row['persen']
    ]);

    // $terminPlaceholder[] = [
    //     'huruf' => $huruf[$i],
    //     'judul' => htmlspecialchars($judul, ENT_QUOTES, 'UTF-8'),
    //     'sub'   => htmlspecialchars(implode('<w:br/>', $subPoin), ENT_QUOTES, 'UTF-8'),
    // ];

}

Log::info('TERMIN PLACEHOLDER FINAL', [
    'terminPlaceholder' => $terminPlaceholder
]);



Log::info('START CLONE ROW TERMIN', [
    'termin_count' => count($terminPlaceholder),
    'terminPlaceholder' => $terminPlaceholder
]);
        // Clone row sesuai jumlah termin
        $template->cloneRow('termin_huruf', count($terminPlaceholder));

        foreach ($terminPlaceholder as $i => $row) {
            $no = $i + 1;
            
            
            
    Log::info("SET TEMPLATE TERMIN #{$no}", [
        'termin_huruf'  => $row['huruf'],
        'termin_judul'  => $row['judul'],
        'termin_sub'    => $row['sub']
    ]);
    
            $template->setValue("termin_huruf#{$no}", $row['huruf']);
            $template->setValue("termin_judul#{$no}", $row['judul']);
            $template->setValue("termin_sub#{$no}", $row['sub']); // satu string dengan line break
        }


        // ===============================
        // DOWNLOAD
        // ===============================
        $fileName = 'SPH ' .
            str_replace('/', '_', $quotation->no_sph) .
            ' - ' . ($quotation->customer->nama_perusahaan) . '.docx';
            
            Log::info('READY TO DOWNLOAD TEMPLATE', [
    'fileName' => $fileName
]);

        return response()->streamDownload(fn() => $template->saveAs('php://output'), $fileName);
    }

}
