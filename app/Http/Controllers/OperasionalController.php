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
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
            'jenis_pengajuan'   => 'required|in:biaya,pengeluaran',
            'tanggal_pengajuan' => 'required|date',
            'metode_pembayaran' => 'required|in:cash,transfer',
            'project_id'        => 'nullable',
            'jenis_project'     => 'nullable|string',
            'kontak_id'         => 'required|integer',

            'deskripsi.*'       => 'required|string',
            'qty.*'             => 'required|numeric|min:1',
            'harga.*'           => 'required|numeric|min:0',
            'diskon.*'          => 'nullable|numeric|min:0',
            'diskon_type.*'     => 'nullable|in:percent,nominal',
            'pajak_id.*'        => 'nullable|integer',

            // GLOBAL
            'use_diskon_global' => 'nullable',
            'diskon_global'     => 'nullable|numeric|min:0',
            'diskon_global_type' => 'nullable|in:percent,nominal',

            'use_pajak_global'  => 'nullable',
            'pajak_global_id'   => 'nullable|integer',

            'lampiran'          => 'nullable|file|max:2048'
        ]);

        DB::beginTransaction();

        try {

            /** ================== FILE ================== */
            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $request->file('lampiran')
                    ->store('pengajuan/lampiran', 'public');
            }

            /** ================== NOMOR ================== */
            $nomorPengajuan = 'PB-' . now()->format('YmdHis');

            /** ================== INIT ================== */
            $subtotal = 0;
            $totalDiskonItem = 0;
            $totalPajakItem = 0;
            $items = [];

            /** ================== LOOP ITEM ================== */
            foreach ($request->deskripsi as $i => $deskripsi) {

                $qty    = (float) $request->qty[$i];
                $harga  = (float) $request->harga[$i];

                $diskon     = (float) ($request->diskon[$i] ?? 0);
                $diskonType = $request->diskon_type[$i] ?? 'percent';
                $pajakId    = $request->pajak_id[$i] ?? null;

                $total = $qty * $harga;

                /** ===== DISKON ITEM ===== */
                $nilaiDiskon = $diskonType === 'percent'
                    ? $total * ($diskon / 100)
                    : $diskon;

                if ($nilaiDiskon > $total) {
                    $nilaiDiskon = $total;
                }

                $setelahDiskon = $total - $nilaiDiskon;

                /** ===== PAJAK ITEM ===== */
                $nilaiPajak = 0;

                if (!empty($pajakId) && $pajakId != 0) {

                    $coa = Coa::findOrFail($pajakId);

                    $persen   = (float) $coa->nilai_coa;
                    $kategori = strtoupper($coa->kategori_pajak ?? '');

                    $nilaiPajak = $setelahDiskon * ($persen / 100);

                    if ($kategori === 'PPH') {
                        $nilaiPajak *= -1;
                    }
                }

                $jumlah = $setelahDiskon + $nilaiPajak;

                /** ===== AKUMULASI ===== */
                $subtotal += $total;
                $totalDiskonItem += $nilaiDiskon;
                $totalPajakItem += $nilaiPajak;

                /** ===== SIMPAN ITEM ===== */
                $items[] = [
                    'deskripsi'    => $deskripsi,
                    'qty'          => $qty,
                    'harga'        => $harga,
                    'diskon'       => $diskon,
                    'diskon_type'  => $diskonType,
                    'pajak_id'     => $pajakId ?: null,
                    'nilai_pajak'  => $nilaiPajak,
                    'jumlah'       => $jumlah
                ];
            }

            /** ================== GLOBAL DISKON ================== */
            $diskonGlobal = 0;

            if ($request->boolean('use_diskon_global')) {

                $diskonGlobalInput = (float) $request->diskon_global;
                $type = $request->diskon_global_type ?? 'percent';

                $diskonGlobal = $type === 'percent'
                    ? $subtotal * ($diskonGlobalInput / 100)
                    : $diskonGlobalInput;

                if ($diskonGlobal > $subtotal) {
                    $diskonGlobal = $subtotal;
                }
            }

            /** ================== GLOBAL PAJAK ================== */
            $pajakGlobal = 0;

            if ($request->boolean('use_pajak_global') && $request->pajak_global_id) {

                $coa = Coa::findOrFail($request->pajak_global_id);

                $persen   = (float) $coa->nilai_coa;
                $kategori = strtoupper($coa->kategori_pajak ?? '');

                $dasar = $subtotal - $totalDiskonItem - $diskonGlobal;

                $pajakGlobal = $dasar * ($persen / 100);

                if ($kategori === 'PPH') {
                    $pajakGlobal *= -1;
                }
            }

            /** ================== FINAL TOTAL ================== */
            $grandTotal =
                $subtotal
                - $totalDiskonItem
                - $diskonGlobal
                + $totalPajakItem
                + $pajakGlobal;

            /** ================== HEADER ================== */
            $pengajuan = PengajuanBiaya::create([
                'jenis_pengajuan'   => $request->jenis_pengajuan,
                'project_id'        => $request->project_id,
                'jenis_project'     => $request->jenis_project,
                'nomor_pengajuan'   => $nomorPengajuan,
                'tgl_pengajuan'     => $request->tanggal_pengajuan,
                'metode_pembayaran' => $request->metode_pembayaran,
                'kontak_id'         => $request->kontak_id,
                'is_urgent'         => $request->boolean('is_urgent'),

                'subtotal'          => $subtotal,
                'total_diskon'      => $totalDiskonItem + $diskonGlobal,
                'total_pajak'       => $totalPajakItem + $pajakGlobal,
                'grand_total'       => $grandTotal,

                'use_diskon_global' => $request->boolean('use_diskon_global'),
                'diskon_global'     => $diskonGlobal,
                'diskon_global_type' => $request->diskon_global_type,

                'use_pajak_global'  => $request->boolean('use_pajak_global'),
                'pajak_global_id'   => $request->pajak_global_id,
                'nilai_pajak_global' => $pajakGlobal,

                'user_id' => auth()->id(),

                'lampiran'          => $lampiranPath,
                'status'            => 'dipurchasing'
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

            /** ================= HITUNG ULANG ================= */
            $totalDiskonItem = $pengajuan->items->sum(function ($item) {
                $total = $item->qty * $item->harga;
                if ($item->diskon_type === 'percent') {
                    return $total * ($item->diskon / 100);
                }
                return $item->diskon;
            });

            $diskonGlobal = (float) ($pengajuan->diskon_global ?? 0);

            $totalPajakItem = $pengajuan->items->sum('nilai_pajak');
            $pajakGlobal = (float) ($pengajuan->nilai_pajak_global ?? 0);

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

                /** ===== TOTAL ===== */
                'subtotal'          => (float) ($pengajuan->subtotal ?? 0),

                // 🔥 DIPISAH
                'diskon_item'       => $totalDiskonItem,
                'diskon_global'     => $diskonGlobal,
                'total_diskon'      => $totalDiskonItem + $diskonGlobal,

                'pajak_item'        => $totalPajakItem,
                'pajak_global'      => $pajakGlobal,
                'total_pajak'       => $totalPajakItem + $pajakGlobal,
                'grand_total'       => (float) ($pengajuan->grand_total ?? 0),
                'use_diskon_global' => (bool) $pengajuan->use_diskon_global,
                'diskon_global_type' => $pengajuan->diskon_global_type ?? 'percent',
                'use_pajak_global'  => (bool) $pengajuan->use_pajak_global,
                'pajak_global_id'   => $pengajuan->pajak_global_id,
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
                    'diskon_type'    => $item->diskon_type ?? 'percent',
                    'pajak_id'       => $item->pajak_id ?? 0,
                    'nama_pajak' => optional($item->coa)->nama_akun
                        ? optional($item->coa)->nama_akun . ' (' . rtrim(rtrim($item->coa->nilai_coa, '0'), '.') . '%)'
                        : null,

                    'nilai_pajak'    => (float) ($item->nilai_pajak ?? 0),
                    'jumlah'         => (float) ($item->jumlah ?? 0),
                ];
            })->values()->toArray();

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
                    'diskon_type'    => $item->diskon_type,

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
        $pajak = Coa::select('id', 'nama_akun', 'nilai_coa', 'kategori_pajak')
            ->where('kategori_akun', 'Kewajiban Lancar Lainnya')
            ->where('id', 2)
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
        $data = DB::table('po')
            ->leftJoin('customers', 'po.customer_id', '=', 'customers.id')
            ->select(
                DB::raw("CONCAT('P-', po.id) as id"),
                DB::raw("CONCAT(po.no_po,' - ', customers.nama_perusahaan) as label"),
                DB::raw("'PO' as jenis_project")
            )
            ->get();

        return response()->json($data);
    }
}
