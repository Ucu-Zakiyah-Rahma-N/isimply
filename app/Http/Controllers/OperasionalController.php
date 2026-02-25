<?php

namespace App\Http\Controllers;

use App\Models\Kontak;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PengajuanBiaya;
use App\Models\FPengajuanBiayaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OperasionalController extends Controller
{
    public function biayaIndex()
    {
        $title = 'Biaya';
        $data = PengajuanBiaya::with(['items'])
            ->orderByDesc('tgl_pengajuan')
            ->get();

        return view(
            'pages.finance.operasional.biaya.index',
            compact('title', 'data')
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
        } catch (\Throwable $e) {

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
        } catch (\Throwable $e) {

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

    public function show_pengajuan_biaya($id)
    {
        try {

            $rows = DB::table('pengajuan_biaya as pb')
                ->leftJoin('kontak as p', 'p.id', '=', 'pb.kontak_id')
                ->leftJoin('pengajuan_biaya_items as item', 'item.pengajuan_biaya_id', '=', 'pb.id')
                ->leftJoin('coa', 'coa.id', '=', 'item.pajak_id')
                ->where('pb.id', $id)
                ->select(
                    'pb.id as pengajuan_id',
                    'pb.nomor_pengajuan',
                    'pb.tgl_pengajuan',
                    'pb.metode_pembayaran',
                    'pb.referensi_proyek_id',
                    'pb.is_urgent',
                    'pb.subtotal',
                    'pb.total_diskon',
                    'pb.total_ppn',
                    'pb.grand_total',
                    'pb.lampiran',

                    'p.nama as kontak_nama',

                    'item.id as item_id',
                    'item.deskripsi',
                    'item.qty',
                    'item.harga',
                    'item.diskon',
                    'item.pajak_id',
                    'item.nilai_pajak',
                    'item.jumlah',

                    'coa.nama_akun as pajak_nama',
                    'coa.nilai_coa as pajak_persen'
                )
                ->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            /** ================== FORMAT HEADER ================== */
            $header = [
                'id'                  => $rows[0]->pengajuan_id,
                'nomor_pengajuan'     => $rows[0]->nomor_pengajuan,
                'tgl_pengajuan'       => $rows[0]->tgl_pengajuan,
                'metode_pembayaran'   => $rows[0]->metode_pembayaran,
                'kontak_nama'         => $rows[0]->kontak_nama,
                'referensi_proyek_id' => $rows[0]->referensi_proyek_id,
                'is_urgent'           => (bool) $rows[0]->is_urgent,
                'subtotal'            => $rows[0]->subtotal,
                'total_diskon'        => $rows[0]->total_diskon,
                'total_ppn'           => $rows[0]->total_ppn,
                'grand_total'         => $rows[0]->grand_total,
                'lampiran'            => $rows[0]->lampiran
                    ? asset('storage/' . $rows[0]->lampiran)
                    : null,
            ];

            /** ================== FORMAT ITEMS ================== */
            $items = $rows->map(function ($row) {
                return [
                    'item_id'      => $row->item_id,
                    'deskripsi'    => $row->deskripsi,
                    'qty'          => $row->qty,
                    'harga'        => $row->harga,
                    'diskon'       => $row->diskon,
                    'pajak_id'     => $row->pajak_id,
                    'pajak_nama'   => $row->pajak_nama,
                    'pajak_persen' => $row->pajak_persen ?? 0,
                    'nilai_pajak'  => $row->nilai_pajak,
                    'jumlah'       => $row->jumlah,
                ];
            })->filter(fn($i) => $i['item_id'] !== null)->values();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'header' => $header,
                    'items'  => $items
                ]
            ]);
        } catch (\Throwable $e) {

            Log::error('SHOW PENGAJUAN BIAYA ERROR', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data'
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
