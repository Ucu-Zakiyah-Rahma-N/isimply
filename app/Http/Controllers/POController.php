<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Perizinan;
use App\Models\PO;
use App\Models\Wilayah;
use App\Models\Quotation;
use App\Models\Cabang;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


use Illuminate\Http\Request;

class POController extends Controller
{
    public function index(Request $request)
    {
        
        $user = auth()->user();
        $query = PO::with(['customer', 'perizinan', 'quotation.kawasan_industri', 'quotation.perizinan'])
            ->orderBy('tgl_po', 'DESC')
            ->orderBy('created_at', 'DESC');
        
        if (
            $user->role === 'admin marketing' &&
            $user->cabang_id != 1
        ) {
            $query->whereHas('quotation', function ($q) use ($user) {
                $q->where('cabang_id', $user->cabang_id);
            });
        }

    
        // 🔹 Filter Kabupaten
        if ($request->filled('kabupaten')) {
            $query->whereHas('quotation', function ($q) use ($request) {
                $q->where('kabupaten_id', $request->kabupaten);
            });
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
        if ($request->filled('cabang')) {
            $query->whereHas('quotation', function ($q) use ($request) {
                $q->where('cabang_id', $request->cabang);
            });
        }

        // 🔹 Search No SPH
        if ($request->filled('sph')) {
            $query->whereHas('quotation', function ($q) use ($request) {
                $q->where('no_sph', 'like', '%' . $request->sph . '%');
            });
        }

        if ($request->filled('po')) {
            $query->where('no_po', 'like', '%' . $request->po . '%');
        }

        $po = $query->paginate(500)->withQueryString(); // ganti jadi brp yg mau di tampilkan

        $kabupatenList = Wilayah::where('jenis', 'kabupaten')->pluck('nama', 'kode');

        foreach ($po as $item) {

            // ========== ambil PIC utama dari customer ==========
            $pics = $item->customer->pic_perusahaan ?? [];

            if (is_string($pics)) {
                $decoded = json_decode($pics, true);
                $pics = is_array($decoded) ? $decoded : [];
            }

            $picsCollection = collect($pics);

            // cari PIC utama
            $primary = $picsCollection->firstWhere('utama', true);
            if (!$primary) {
                $primary = $picsCollection->first(); // fallback ke PIC pertama
            }

            // simpan di properti model (biar bisa langsung dipanggil di Blade)
            $item->primary_pic = $primary;
            $item->pic_perusahaan = $picsCollection->all();

            // ===== Tambahkan kabupaten dan kawasan =====
            $item->kabupaten_name = $kabupatenList[$item->quotation->kabupaten_id ?? null] ?? '-';
            $item->kawasan_name   = $item->quotation->kawasan_industri->nama_kawasan ?? '-';

            // ===== Buat format luas seperti di quotation =====
            $luasList = [];

            if (!is_null($item->quotation->luas_slf)) {
                $luasList[] = 'SLF: ' . number_format($item->quotation->luas_slf, 2, ',', '.') . ' m²';
            }
            if (!is_null($item->quotation->luas_pbg)) {
                $luasList[] = 'PBG: ' . number_format($item->quotation->luas_pbg, 2, ',', '.') . ' m²';
            }
            if (!is_null($item->quotation->luas_shgb)) {
                $luasList[] = 'SHGB: ' . number_format($item->quotation->luas_shgb, 2, ',', '.') . ' m²';
            }

            $item->luas_info = count($luasList) > 0 ? implode(', ', $luasList) : '-';
        }

        $data = [
            'title' => 'PO',
            'po' => $po,
            'customers' => Customer::all(['id', 'nama_perusahaan']),
            'quotation' => Quotation::all(['id', 'provinsi_id', 'kabupaten_id', 'kawasan_id', 'detail_alamat']),
            'perizinan' => Perizinan::all(),
            'wilayahs' => Wilayah::all(),
            'cabang' => Cabang::where('status', 1)->get(),
        ];

        return view('pages.PO.index', $data);
    }

    public function create()
    {
        $customers = Customer::all();
        $PO = PO::with(['customer', 'quotation'])->get();
        $wilayahs = Wilayah::all();

        $data = [
            'title' => 'Form PO',
            'customers' => $customers,
            'PO' => $PO,
            'wilayahs' => $wilayahs,
        ];

        return view('pages.PO.create', $data);
    }


    public function store(Request $request)
    {
    Log::info('=== START STORE PO ===', [
        'user_id' => auth()->id(),
        'request_all' => $request->except(['file']),
        'has_file' => $request->hasFile('file'),
    ]);

    try {
        
         $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_id' => 'required|exists:quotations,id',
            'file' => 'nullable|mimes:pdf|max:20480', // 20MB max
            'no_po' => 'nullable|string|unique:po,no_po',
            'tgl_po' => 'required|date',
            'nama_pic_keuangan' => 'nullable|string|max:255',
            'kontak_pic_keuangan' => 'nullable|string|max:20',
        ]);
    
        Log::info('VALIDATION PASSED', $validated);

    $filePath = null;

    //  CEK ADA FILE ATAU TIDAK
    if ($request->hasFile('file')) {

        $file = $request->file('file');
        
        
        Log::info('FILE DETECTED', [
                'original_name' => $file->getClientOriginalName(),
                'size_kb' => round($file->getSize() / 1024, 2),
                'mime' => $file->getMimeType(),
            ]);


        $originalName = $file->getClientOriginalName();
        $cleanName = time() . '_' . str_replace(['(', ')', ' '], '_', $originalName);

        $filePath = $file->storeAs('po', $cleanName, 'public');
        
                    Log::info('FILE STORED', [
                'file_path' => $filePath,
            ]);

    }
        PO::create([
            'customer_id' => $request->customer_id,
            'quotation_id' => $request->quotation_id,
            'file_path' => $filePath,
            'no_po' => $request->no_po,
            'tgl_po' => $request->tgl_po,
            'nama_pic_keuangan' => $request->nama_pic_keuangan,
            'kontak_pic_keuangan' => $request->kontak_pic_keuangan,
            'bast_verified' => 0,
            'bast_verified_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
           Log::info('PO INSERT SUCCESS', [
            'no_po' => $validated['no_po'],
        ]);

        return redirect()->route('PO.index')->with('success', 'Data PO berhasil disimpan.');
        
        
    } catch (\Throwable $e) {

        Log::error('STORE PO FAILED', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()
            ->withInput()
            ->with('error', 'Terjadi kesalahan saat menyimpan PO. Silakan hubungi admin.');
    }
    }

    private function generateSingkatan($nama)
    {
        $nama = strtoupper($nama);

        // hapus kata umum
        $remove = ['PT', 'CV', '&', 'AND'];
        $words = explode(' ', $nama);

        $singkatan = '';

        foreach ($words as $word) {
            if (!in_array($word, $remove) && !empty($word)) {
                $singkatan .= substr($word, 0, 1);
            }
        }

        return $singkatan ?: 'UNK';
    }
   private function generateKodeProject($po)
{
    $tahun = now()->format('Y');
    // $bulan = now()->format('m');
    $bulan = \Carbon\Carbon::parse($po->tgl_po)->format('m');

    // ambil nama customer (sesuaikan relasi kamu)
    $namaCustomer = $po->customer->nama_perusahaan ?? 'UNKNOWN';

    // singkatan
    $singkatan = $this->generateSingkatan($namaCustomer);

    // hitung urutan project customer di tahun ini
    $count = PO::whereYear('bast_verified_at', $tahun)
        ->where('customer_id', $po->customer_id)
        ->whereNotNull('kode_project')
        ->count();

    $urutan = $count + 1;

    return "{$singkatan}{$urutan}-{$bulan}-{$tahun}";
}
    public function verifyBast($id)
    {
        $user = auth()->user();
        if (
            !($user->role === 'superadmin' ||
            ($user->role === 'admin marketing' && $user->cabang_id == 1))
        ) {
            return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki hak verifikasi BAST'
            ], 403);
        }

        $po = PO::find($id);

        if (!$po) {
            return response()->json(['success' => false, 'message' => 'Data PO tidak ditemukan.'], 404);
        }

        if ($po->bast_verified) {
            return response()->json(['success' => false, 'message' => 'BAST sudah diverifikasi sebelumnya.']);
        }

         // 🔥 Cegah double generate
        if ($po->kode_project) {
            return response()->json([
                'success' => false,
                'message' => 'Kode project sudah dibuat sebelumnya.'
            ]);
        }

        $kodeProject = $this->generateKodeProject($po);

        $po->update([
            'bast_verified' => 1,
            'bast_verified_at' => now(),
             'kode_project' => $kodeProject
        ]);

        return response()->json(['success' => true, 'message' => 'BAST berhasil diverifikasi.',  'kode_project' => $kodeProject]);
    }
    
    
    public function edit($id)
    {
        $title = 'edit PO';
        
        
        $po = PO::with(['quotation.customer'])->findOrFail($id);
    
        $customers = Customer::all();
    
        // Ambil SPH milik customer terkait (untuk preload select)
        $quotations = Quotation::where('customer_id', $po->customer_id)->get();
    
        return view('pages.PO.edit', compact(
            'po',
            'customers',
            'quotations',
            'title'
        ));
    }


public function update(Request $request, $id)
{
    $po = PO::findOrFail($id);

    // Ambil semua data kecuali file
    $data = $request->except('file');

    // ======================
    // HANDLE FILE PO
    // ======================
    if ($request->hasFile('file')) {

        // 🔹 Hapus file lama kalau ada
        if ($po->file_path && Storage::disk('public')->exists($po->file_path)) {
            Storage::disk('public')->delete($po->file_path);
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $cleanName = time() . '_' . str_replace(['(', ')', ' '], '_', $originalName);

        // 🔹 Simpan file baru di folder storage/app/public/po
        $data['file_path'] = $file->storeAs('po', $cleanName, 'public');
    }

    // ======================
    // UPDATE DATA PO
    // ======================
    $po->update($data);

    return redirect()
        ->route('PO.index')
        ->with('success', 'PO berhasil diperbarui');
}

}
