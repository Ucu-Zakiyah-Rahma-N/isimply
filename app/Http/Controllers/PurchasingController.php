<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\Kontak;
use App\Models\Coa;
use App\Models\SchedulingPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PengajuanBiaya;
use App\Models\FPengajuanBiayaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use Throwable;
use Exception;

class PurchasingController extends Controller
{

    public function purchasingIndex(Request $request)
    {
        $title = 'Purchasing';
        $tab   = $request->get('tab', 'waiting');
        $today = Carbon::today();

        $baseQuery = PengajuanBiaya::query();

        // =======================
        // COUNT NOTIFICATION
        // =======================

        // Waiting (belum ada scheduling)
        $countWaiting = (clone $baseQuery)
            ->whereDoesntHave('scheduling')
            ->where('status', '!=', 'ditolak')
            ->count();

        // Today
        $countToday = (clone $baseQuery)
            ->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', $today);
            })
            ->count();

        // Scheduled (> hari ini)
        $countScheduled = (clone $baseQuery)
            ->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '>', $today);
            })
            ->count();

        // Pending (< hari ini)
        $countPending = (clone $baseQuery)
            ->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '<', $today);
            })
            ->count();

        // Ditolak
        $countReject = (clone $baseQuery)
            ->where('status', 'ditolak')
            ->count();

        // Approved (SUDAH ada scheduling)
        $countApproved = (clone $baseQuery)
            ->whereHas('scheduling')
            ->count();


        // =======================
        // DATA TABLE
        // =======================

        $query = PengajuanBiaya::with(['items', 'scheduling'])
            ->leftJoin('kontak', 'pengajuan_biaya.kontak_id', '=', 'kontak.id')
            ->select(
                'pengajuan_biaya.*',
                'kontak.nama as penerima'
            );

        // =======================
        // FILTER BY TAB
        // =======================

        if ($tab === 'today') {

            $query->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', $today);
            });
        } elseif ($tab === 'dijadwalkan') {

            $query->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '>', $today);
            });
        } elseif ($tab === 'pending') {

            $query->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '<', $today);
            });
        } elseif ($tab === 'ditolak') {

            $query->where('pengajuan_biaya.status', 'ditolak');
        } elseif ($tab === 'disetujui') {

            // Approved = sudah punya scheduling
            $query->whereHas('scheduling');
        } else {

            // WAITING (belum ada scheduling)
            $query->whereDoesntHave('scheduling')
                ->where('pengajuan_biaya.status', '!=', 'ditolak');
        }

        // =======================
        // FINAL DATA
        // =======================

        $data = $query
            ->orderByDesc('pengajuan_biaya.tgl_pengajuan')
            ->get();

        return view(
            'pages.finance.purchasing.index',
            compact(
                'title',
                'data',
                'tab',
                'countWaiting',
                'countToday',
                'countScheduled',
                'countPending',
                'countReject',
                'countApproved'
            )
        );
    }

    private function transformPengajuan($pengajuan)
    {
        $totalDiskonItem = $pengajuan->items->sum(function ($item) {
            $total = $item->qty * $item->harga;
            return $item->diskon_type === 'percent'
                ? $total * ($item->diskon / 100)
                : $item->diskon;
        });

        $diskonGlobal = (float) ($pengajuan->diskon_global ?? 0);
        $totalPajakItem = $pengajuan->items->sum('nilai_pajak');
        $pajakGlobal = (float) ($pengajuan->nilai_pajak_global ?? 0);

        return [
            'header' => [
                'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                'tgl_pengajuan'   => optional($pengajuan->tgl_pengajuan)->format('Y-m-d'),
                'kontak'          => optional($pengajuan->kontak)->nama,
                'subtotal'        => $pengajuan->subtotal,
                'total_diskon'    => $totalDiskonItem + $diskonGlobal,
                'total_pajak'     => $totalPajakItem + $pajakGlobal,
                'grand_total'     => $pengajuan->grand_total,
            ],
            'items' => $pengajuan->items->map(function ($item) {
                return [
                    'deskripsi' => $item->deskripsi,
                    'qty'       => $item->qty,
                    'harga'     => $item->harga,
                    'jumlah'    => $item->jumlah,
                ];
            })
        ];
    }
    public function exportPdf(Request $request)
    {
        try {

            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $start = Carbon::parse($request->start_date)->startOfDay();
            $end   = Carbon::parse($request->end_date)->endOfDay();

            $pengajuans = PengajuanBiaya::with([
                'kontak',
                'items',
                'items.coa'
            ])
                ->whereBetween('tgl_pengajuan', [$start, $end])
                ->orderByDesc('tgl_pengajuan')
                ->get();

            // 🔥 pakai transform (SAMA seperti show)
            $data = $pengajuans->map(function ($p) {
                return $this->transformPengajuan($p);
            });

            $html = view('pdf.pengajuan', [
                'data'  => $data,
                'start' => $start->format('Y-m-d'),
                'end'   => $end->format('Y-m-d'),
            ])->render();

            file_put_contents(storage_path('logs/debug_pdf.html'), $html);

            $pdf = Browsershot::html($html)
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->timeout(120)
                ->setNodeBinary('C:\Program Files\nodejs\node.exe')
                ->setNpmBinary('C:\Program Files\nodejs\npm.cmd')
                ->setNodeModulePath(base_path('node_modules'))
                ->pdf();

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="laporan.pdf"');
        } catch (Throwable $e) {

            Log::error('Export PDF ERROR', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeScheduling(Request $request)
    {
        $request->validate([
            'nomor_pengajuan' => 'required',
            'coa_lawan_id'    => 'required|exists:coa,id',
            'coa_bank_id'     => 'required|exists:coa,id',
            'tgl_pembayaran'  => 'required|date',
        ]);

        DB::beginTransaction();

        try {

            // Ambil pengajuan berdasarkan nomor
            $pengajuan = PengajuanBiaya::where(
                'nomor_pengajuan',
                $request->nomor_pengajuan
            )->firstOrFail();

            SchedulingPengajuan::create([
                'pengajuan_biaya_pengadaan_id' => $pengajuan->id,
                'coa_id'        => $request->coa_lawan_id,
                'bank_coa_id'    => $request->coa_bank_id,
                'tgl_pembayaran' => $request->tgl_pembayaran,
                'is_akomodasi'  => $request->boolean('is_akomodasi'),
            ]);
            $today = Carbon::now()->format('Y-m-d');
            // Optional: update status
            $pengajuan->update([
                'status' => 'dijadwalkan',
                'approved_at' => $today
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil dijadwalkan'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menjadwalkan pembayaran'
            ], 500);
        }
    }

    public function getCoaKasBank(Request $request)
    {
        $search = $request->get('term');

        $data = Coa::where('kategori_akun', 'Kas & Bank')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_akun', 'like', '%' . $search . '%');
            })
            ->select('id', 'nama_akun')
            ->orderBy('nama_akun')
            ->get();

        return response()->json([
            'results' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->nama_akun
                ];
            })
        ]);
    }

    public function getAkunCoa(Request $request)
    {
        $search = $request->get('term');

        $data = Coa::where('kategori_akun', '!=', 'Kas & Bank')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_akun', 'like', '%' . $search . '%');
            })
            ->select('id', 'nama_akun')
            ->orderBy('nama_akun')
            ->get();

        return response()->json([
            'results' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->nama_akun
                ];
            })
        ]);
    }
}
