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
        $tab = $request->get('tab', 'proses di purchasing');

        $query = PengajuanBiaya::with('items')
            ->leftJoin('kontak', 'pengajuan_biaya.kontak_id', '=', 'kontak.id')
            ->select('pengajuan_biaya.*', 'kontak.nama as penerima');

        if ($tab == 'today') {

            $query->where('pengajuan_biaya.status', 'proses di purchasing')
                ->whereDate('pengajuan_biaya.tgl_pengajuan', Carbon::today());
        } elseif ($tab == 'diajukan') {

            $query->where('pengajuan_biaya.status', 'diajukan')
                ->whereNotNull('pengajuan_biaya.tgl_pengajuan')
                ->whereDate('pengajuan_biaya.tgl_pengajuan', '!=', Carbon::today());
        } elseif ($tab == 'ditolak') {

            $query->where('pengajuan_biaya.status', 'ditolak');
        } else {
            // Waiting List (default)
            $query->where('pengajuan_biaya.status', 'proses di purchasing');
        }

        $data = $query->orderByDesc('pengajuan_biaya.tgl_pengajuan')
            ->get();

        return view(
            'pages.finance.purchasing.index',
            compact('title', 'data', 'tab')
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

            // Optional: update status
            $pengajuan->update([
                'status' => 'dijadwalkan'
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
