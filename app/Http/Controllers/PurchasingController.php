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

class PurchasingController extends Controller
{

    public function purchasingIndex(Request $request)
    {
        $title = 'Purchasing';
        $tab = $request->get('tab', 'waiting');

        $today = Carbon::today();

        $baseQuery = PengajuanBiaya::query();

        // =======================
        // COUNT NOTIFICATION
        // =======================

        // Waiting List
        $countWaiting = (clone $baseQuery)
            ->whereNull('approved_at')
            ->where('status', '!=', 'ditolak')
            ->count();

        // Hari Ini (ada scheduling & pembayaran hari ini)
        $countToday = (clone $baseQuery)
            ->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', $today);
            })
            ->whereNull('approved_at')
            ->count();

        // Dijadwalkan (bukan hari ini)
        $countScheduled = (clone $baseQuery)
            ->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '!=', $today);
            })
            ->whereNull('approved_at')
            ->count();

        // Pending (jadwal sudah lewat & belum approve)
        $countPending = (clone $baseQuery)
            ->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '<', $today);
            })
            ->whereNull('approved_at')
            ->count();

        // Ditolak
        $countReject = (clone $baseQuery)
            ->where('status', 'ditolak')
            ->count();

        // Disetujui
        $countApproved = (clone $baseQuery)
            ->whereNotNull('approved_at')
            ->count();


        // =======================
        // DATA TABLE
        // =======================

        $query = PengajuanBiaya::with(['items', 'scheduling'])
            ->leftJoin('kontak', 'pengajuan_biaya.kontak_id', '=', 'kontak.id')
            ->select('pengajuan_biaya.*', 'kontak.nama as penerima');

        if ($tab == 'today') {

            $query->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', $today);
            })
                ->whereNull('pengajuan_biaya.approved_at');
        } elseif ($tab == 'dijadwalkan') {

            $query->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '!=', $today);
            })
                ->whereNull('pengajuan_biaya.approved_at');
        } elseif ($tab == 'pending') {

            $query->whereHas('scheduling', function ($q) use ($today) {
                $q->whereDate('tgl_pembayaran', '<', $today);
            })
                ->whereNull('pengajuan_biaya.approved_at');
        } elseif ($tab == 'ditolak') {

            $query->where('pengajuan_biaya.status', 'ditolak');
        } elseif ($tab == 'disetujui') {

            $query->whereNotNull('pengajuan_biaya.approved_at');
        } else {
            // WAITING LIST
            $query->whereNull('pengajuan_biaya.approved_at')
                ->where('pengajuan_biaya.status', '!=', 'ditolak');
        }

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
