<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kontak;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PengajuanBiaya;
use App\Models\PengajuanBiayaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;
use \Illuminate\Database\Eloquent\ModelNotFoundException;

class OperasionalController extends Controller
{
    public function biayaIndex()
    {
        $title = 'Biaya';
        $data = PengajuanBiaya::with(['items'])
            ->orderByDesc('tgl_pengajuan')
            ->get();
        $pajakList = Coa::whereNotNull('kategori_pajak')->get();

        return view(
            'pages.finance.operasional.biaya.index',
            compact('title', 'data', 'pajakList')
        );
    }

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'nama'        => 'required|string|max:100',
                'tipe_kontak' => 'required|in:customer,supplier,karyawan,lainnya',
                'email'       => 'nullable|email|max:100',
                'no_hp'       => 'nullable|string|max:20',
                'alamat'      => 'nullable|string',
                'nama_bank'   => 'nullable|string|max:100',
                'no_rekening' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $kontak = Kontak::create($request->all());

            return response()->json([
                'success' => true,
                'id'      => $kontak->id,
                'nama'    => $kontak->nama,
            ]);
        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }
    public function store_pengajuan_biaya(Request $request)
    {
        $request->validate([
            'jenis_pengajuan' => 'required|in:biaya,pengeluaran',
            'tanggal_pengajuan'       => 'required|date',
            'metode_pembayaran'   => 'required|in:cash,transfer',
            'project_id' => 'nullable',
            'jenis_project' => 'nullable|string',
            'kontak_id'         => 'required|integer',
            'deskripsi.*'         => 'required|string',
            'qty.*'               => 'required|numeric|min:1',
            'harga.*'             => 'required|numeric|min:0',
            'diskon.*'            => 'nullable|numeric|min:0',
            'pajak_id.*'          => 'nullable|integer',
            'lampiran'            => 'nullable|file|max:2048'
        ]);

        DB::beginTransaction();

        try {
            /** ================== UPLOAD FILE ================== */
            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $request->file('lampiran')
                    ->store('pengajuan/lampiran', 'public');
            }

            /** ================== NOMOR PENGAJUAN ================== */
            $nomorPengajuan = 'PB-' . now()->format('YmdHis');

            /** ================== HITUNG ULANG ================== */
            $subtotal = 0;
            $totalDiskon = 0;
            $totalPPN = 0;
            $items = [];

            foreach ($request->deskripsi as $i => $deskripsi) {

                $qty    = $request->qty[$i];
                $harga = $request->harga[$i];
                $diskon = $request->diskon[$i] ?? 0;
                $pajakId = $request->pajak_id[$i] ?? null;

                $total = $qty * $harga;
                $nilaiDiskon = $total * ($diskon / 100);
                $setelahDiskon = $total - $nilaiDiskon;

                $nilaiPajak = 0;
                if (!empty($pajakId) && $pajakId != 0) {
                    $coa = Coa::findOrFail($pajakId);
                    $nilaiPajak = $setelahDiskon * ($coa->nilai_coa / 100);
                }

                $jumlah = $setelahDiskon + $nilaiPajak;

                $subtotal += $total;
                $totalDiskon += $nilaiDiskon;
                $totalPPN += $nilaiPajak;

                $items[] = [
                    'deskripsi'    => $deskripsi,
                    'qty'          => $qty,
                    'harga'        => $harga,
                    'diskon'       => $diskon,
                    'pajak_id'     => $pajakId ?: null,
                    'nilai_pajak'  => $nilaiPajak,
                    'jumlah'       => $jumlah
                ];
            }

            /** ================== HEADER ================== */
            $pengajuan = PengajuanBiaya::create([
                'jenis_pengajuan'        => $request->jenis_pengajuan,
                'project_id'             => $request->project_id,
                'jenis_project'          => $request->jenis_project,
                'nomor_pengajuan'        => $nomorPengajuan,
                'tgl_pengajuan'          => $request->tanggal_pengajuan,
                'metode_pembayaran'      => $request->metode_pembayaran,
                'kontak_id'              => $request->kontak_id,
                // 'referensi_proyek_id'    => $request->referensi_proyek_id,
                'is_urgent'              => $request->boolean('is_urgent'),
                'subtotal'               => $subtotal,
                'total_diskon'           => $totalDiskon,
                'total_ppn'              => $totalPPN,
                'grand_total'            => $subtotal - $totalDiskon + $totalPPN,
                'lampiran'               => $lampiranPath,
                'status'                => 'proses di purchasing'
            ]);

            /** ================== DETAIL ================== */
            $pengajuan->items()->createMany($items);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan biaya berhasil dibuat'
            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('STORE PENGAJUAN BIAYA ERROR', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan pengajuan biaya'
            ], 500);
        }
    }

    public function update_pengajuan_biaya(Request $request, $id)
    {
        $request->validate([
            'jenis_pengajuan' => 'required|in:biaya,pengeluaran',
            'tanggal_pengajuan' => 'required|date',
            'metode_pembayaran' => 'required|in:cash,transfer',
            'project_id' => 'nullable',
            'kontak_id' => 'required|integer',

            'item_id' => 'array',
            'deskripsi.*' => 'required|string',
            'qty.*' => 'required|numeric|min:1',
            'harga.*' => 'required|numeric|min:0',
            'diskon.*' => 'nullable|numeric|min:0|max:100',
            'pajak_id.*' => 'nullable|integer'
        ]);

        DB::beginTransaction();

        try {

            $pengajuan = PengajuanBiaya::findOrFail($id);

            // ================== LOAD PAJAK SEKALI ==================
            $pajakIds = collect($request->pajak_id)->filter()->unique();
            $coaList = Coa::whereIn('id', $pajakIds)->get()->keyBy('id');

            // ================== EXISTING ITEMS ==================
            $existingItems = $pengajuan->items()->get()->keyBy('id');
            $usedItemIds = [];

            $subtotal = 0;
            $totalDiskon = 0;
            $totalPPN = 0;

            // ================== LOOP INPUT ==================
            foreach ($request->deskripsi as $i => $deskripsi) {

                $itemId = $request->item_id[$i] ?? null;

                $qty = $request->qty[$i];
                $harga = $request->harga[$i];
                $diskon = $request->diskon[$i] ?? 0;
                $pajakId = $request->pajak_id[$i] ?? null;

                $total = $qty * $harga;

                // ✅ DISKON PERSEN
                $nilaiDiskon = round($total * ($diskon / 100));
                $afterDiskon = $total - $nilaiDiskon;

                $nilaiPajak = 0;
                if (!empty($pajakId) && isset($coaList[$pajakId])) {
                    $nilaiPajak = round($afterDiskon * ($coaList[$pajakId]->nilai_coa / 100));
                }

                $jumlah = $afterDiskon + $nilaiPajak;

                // ================== AKUMULASI ==================
                $subtotal += $total;
                $totalDiskon += $nilaiDiskon;
                $totalPPN += $nilaiPajak;

                // ================== UPDATE ATAU CREATE ==================
                if ($itemId && isset($existingItems[$itemId])) {

                    $item = $existingItems[$itemId];

                    $item->update([
                        'deskripsi' => $deskripsi,
                        'qty' => $qty,
                        'harga' => $harga,
                        'diskon' => $diskon,
                        'pajak_id' => $pajakId ?: null,
                        'nilai_pajak' => $nilaiPajak,
                        'jumlah' => $jumlah
                    ]);

                    $usedItemIds[] = $itemId;
                } else {

                    $newItem = $pengajuan->items()->create([
                        'deskripsi' => $deskripsi,
                        'qty' => $qty,
                        'harga' => $harga,
                        'diskon' => $diskon,
                        'pajak_id' => $pajakId ?: null,
                        'nilai_pajak' => $nilaiPajak,
                        'jumlah' => $jumlah
                    ]);

                    $usedItemIds[] = $newItem->id;
                }
            }

            // ================== DELETE ITEM YANG DIHAPUS DI UI ==================
            $pengajuan->items()
                ->whereNotIn('id', $usedItemIds)
                ->delete();

            // ================== UPDATE HEADER ==================
            $pengajuan->update([
                'jenis_pengajuan' => $request->jenis_pengajuan,
                'tgl_pengajuan' => $request->tanggal_pengajuan,
                'metode_pembayaran' => $request->metode_pembayaran,
                'project_id' => $request->project_id,
                'kontak_id' => $request->kontak_id,
                'subtotal' => $subtotal,
                'total_diskon' => $totalDiskon,
                'total_ppn' => $totalPPN,
                'grand_total' => $subtotal - $totalDiskon + $totalPPN
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan berhasil diperbarui'
            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('UPDATE PENGAJUAN ERROR', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal update pengajuan'
            ], 500);
        }
    }

    public function show_pengajuan_biaya($id)
    {
        try {

            $pengajuan = PengajuanBiaya::with([
                'kontak:id,nama',
                'items',
                'items.coa:id,nama_akun,nilai_coa,kategori_pajak'
            ])->findOrFail($id);

            /** ================= HEADER ================= */

            $header = [
                'id'                => $pengajuan->id,
                'nomor_pengajuan'   => $pengajuan->nomor_pengajuan,
                'jenis_pengajuan'   => $pengajuan->jenis_pengajuan,

                'tgl_pengajuan'     => $pengajuan->tgl_pengajuan
                    ? Carbon::parse($pengajuan->tgl_pengajuan)->format('Y-m-d')
                    : null,

                'metode_pembayaran' => $pengajuan->metode_pembayaran,
                'project_id'        => $pengajuan->project_id,
                'jenis_project'     => $pengajuan->jenis_project,

                'kontak_id'         => $pengajuan->kontak_id,
                'kontak_nama'       => optional($pengajuan->kontak)->nama,

                'is_urgent'         => (bool) $pengajuan->is_urgent,

                'subtotal'          => $pengajuan->subtotal ?? 0,
                'total_diskon'      => $pengajuan->total_diskon ?? 0,
                'total_ppn'         => $pengajuan->total_ppn ?? 0,
                'grand_total'       => $pengajuan->grand_total ?? 0,

                'lampiran'          => $pengajuan->lampiran
                    ? asset('storage/' . $pengajuan->lampiran)
                    : null,

                'status'            => $pengajuan->status,
            ];

            /** ================= ITEMS ================= */

            $items = $pengajuan->items->map(function ($item) {

                return [

                    'item_id'        => $item->id,

                    'deskripsi'      => $item->deskripsi ?? '',

                    'qty'            => (float) ($item->qty ?? 0),

                    'harga'          => (float) ($item->harga ?? 0),

                    'diskon'         => (float) ($item->diskon ?? 0),

                    'pajak_id'       => $item->pajak_id ?? 0,

                    'nama_pajak' => optional($item->coa)->nama_akun
                        ? optional($item->coa)->nama_akun . ' (' . rtrim(rtrim($item->coa->nilai_coa, '0'), '.') . '%)'
                        : null,
                    'pajak_persen'   => (float) (optional($item->coa)->nilai_coa ?? 0),

                    'kategori_pajak' => optional($item->coa)->kategori_pajak,

                    'nilai_pajak'    => (float) ($item->nilai_pajak ?? 0),

                    'jumlah'         => (float) ($item->jumlah ?? 0),
                ];
            })->values()->toArray();

            /** ================= RESPONSE ================= */

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'header' => $header,
                    'items'  => $items
                ]
            ]);
        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data pengajuan tidak ditemukan'
            ], 404);
        } catch (Throwable $e) {

            Log::error('SHOW PENGAJUAN BIAYA ERROR', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data'
            ], 500);
        }
    }
    public function get_edit_pengajuan_biaya($id)
    {
        try {

            $pengajuan = PengajuanBiaya::with([
                'kontak:id,nama',
                'items',
                'items.coa:id,nama_akun,nilai_coa,kategori_pajak'
            ])->findOrFail($id);

            /** ================= HEADER ================= */

            $header = [
                'id'                => $pengajuan->id,
                'nomor_pengajuan'   => $pengajuan->nomor_pengajuan,
                'jenis_pengajuan'   => $pengajuan->jenis_pengajuan,

                'tgl_pengajuan'     => $pengajuan->tgl_pengajuan
                    ? Carbon::parse($pengajuan->tgl_pengajuan)->format('Y-m-d')
                    : null,

                'metode_pembayaran' => $pengajuan->metode_pembayaran,
                'project_id'        => $pengajuan->project_id,
                'jenis_project'     => $pengajuan->jenis_project,

                'kontak_id'         => $pengajuan->kontak_id,
                'kontak_nama'       => optional($pengajuan->kontak)->nama,

                'is_urgent'         => (bool) $pengajuan->is_urgent,

                'subtotal'          => $pengajuan->subtotal ?? 0,
                'total_diskon'      => $pengajuan->total_diskon ?? 0,
                'total_ppn'         => $pengajuan->total_ppn ?? 0,
                'grand_total'       => $pengajuan->grand_total ?? 0,

                'lampiran'          => $pengajuan->lampiran
                    ? asset('storage/' . $pengajuan->lampiran)
                    : null,

                'status'            => $pengajuan->status,
            ];

            /** ================= ITEMS ================= */

            $items = $pengajuan->items->map(function ($item) {

                return [

                    'item_id'        => $item->id,

                    'deskripsi'      => $item->deskripsi ?? '',

                    'qty'            => (float) ($item->qty ?? 0),

                    'harga'          => (float) ($item->harga ?? 0),

                    'diskon'         => (float) ($item->diskon ?? 0),

                    'pajak_id'       => $item->pajak_id ?? 0,

                    'nama_pajak' => optional($item->coa)->nama_akun
                        ? optional($item->coa)->nama_akun . ' (' . rtrim(rtrim($item->coa->nilai_coa, '0'), '.') . '%)'
                        : null,
                    'pajak_persen'   => (float) (optional($item->coa)->nilai_coa ?? 0),

                    'kategori_pajak' => optional($item->coa)->kategori_pajak,

                    'nilai_pajak'    => (float) ($item->nilai_pajak ?? 0),

                    'jumlah'         => (float) ($item->jumlah ?? 0),
                ];
            })->values()->toArray();

            /** ================= RESPONSE ================= */

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'header' => $header,
                    'items'  => $items
                ]
            ]);
        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data pengajuan tidak ditemukan'
            ], 404);
        } catch (Throwable $e) {

            Log::error('Get PENGAJUAN BIAYA ERROR', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data'
            ], 500);
        }
    }

    public function delete_item($id)
    {
        try {

            $item = PengajuanBiayaItem::find($id);

            // ❗ Jika tidak ditemukan
            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item tidak ditemukan'
                ], 404);
            }

            // (Optional tapi bagus) ambil parent untuk validasi
            $pengajuanId = $item->pengajuan_biaya_id;

            // =====================
            // HAPUS ITEM
            // =====================
            $item->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Item berhasil dihapus',
                'data' => [
                    'pengajuan_biaya_id' => $pengajuanId
                ]
            ]);
        } catch (Throwable $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPajakCoa()
    {
        $pajak = Coa::where('kategori_akun', 'Kewajiban Lancar Lainnya')
            ->select('id', 'nama_akun', 'nilai_coa', 'kategori_pajak')
            ->orderBy('nama_akun')
            ->get();

        return response()->json($pajak);
    }

    public function getKontak()
    {
        $kontak = Kontak::select(
            'id',
            'nama',
            'tipe_kontak',
            'no_rekening'
        )
            ->orderBy('nama')
            ->get();

        return response()->json($kontak);
    }

    public function getProjectGabungan()
    {
        $marketing = DB::table('marketing')
            ->select(
                DB::raw("CONCAT('M-', id) as id"),
                DB::raw("nama COLLATE utf8mb4_unicode_ci as label"),
                DB::raw("'MARKETING' as jenis_project")
            );

        $po = DB::table('po')
            ->leftJoin('customers', 'po.customer_id', '=', 'customers.id')
            ->select(
                DB::raw("CONCAT('P-', po.id) as id"),
                DB::raw("CONCAT(po.no_po,' - ', customers.nama_perusahaan) COLLATE utf8mb4_unicode_ci as label"),
                DB::raw("'PO' as jenis_project")
            );

        $data = $marketing->unionAll($po)->get();

        return response()->json($data);
    }
}
