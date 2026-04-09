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

class ManagerController extends Controller
{
    public function managerIndex(Request $request)
    {
        $title = 'Manager';

        // Default tab
        $tab = $request->get('tab', 'hari_ini');

        // ================= BASE QUERY =================
        $baseQuery = PengajuanBiaya::query();

        // ================= COUNT =================

        $countToday = (clone $baseQuery)
            ->where('status', '!=', 'dipending')
            ->whereHas('scheduling', function ($q) {
                $q->whereDate('tgl_pembayaran', Carbon::today());
            })
            ->count();

        $countPending = (clone $baseQuery)
            ->where('status', 'dipending')
            ->count();

        $countReject = (clone $baseQuery)
            ->where('status', 'ditolak')
            ->count();

        $countHistory = (clone $baseQuery)
            ->where('status', 'disetujui')
            ->count();

        // ================= MAIN QUERY =================

        $query = PengajuanBiaya::with(['items', 'scheduling', 'user'])
            ->leftJoin('kontak', 'pengajuan_biaya.kontak_id', '=', 'kontak.id')
            ->select('pengajuan_biaya.*', 'kontak.nama as penerima');

        // ================= FILTER TAB =================

        if ($tab == 'hari_ini') {

            $query->where('pengajuan_biaya.status', '!=', 'dipending')
                ->whereHas('scheduling', function ($q) {
                    $q->whereDate('tgl_pembayaran', Carbon::today());
                });
        } elseif ($tab == 'pending') {

            $query->where('pengajuan_biaya.status', 'dipending');
        } elseif ($tab == 'reject') {

            $query->where('pengajuan_biaya.status', 'ditolak');
        } elseif ($tab == 'history') {

            $query->where('pengajuan_biaya.status', 'disetujui');
        }

        // ================= GET DATA =================

        $data = $query
            ->orderByDesc('pengajuan_biaya.tgl_pengajuan')
            ->get();

        // ================= RETURN =================

        return view(
            'pages.finance.manager.index',
            compact(
                'title',
                'data',
                'tab',
                'countToday',
                'countPending',
                'countReject',
                'countHistory'
            )
        );
    }

    public function approve($id)
    {
        $pengajuan = PengajuanBiaya::findOrFail($id);

        $pengajuan->update([
            'status' => 'disetujui'
        ]);

        return redirect()->back()->with('success', 'Pembayaran berhasil disetujui.');
    }

    public function tolak(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string'
        ]);

        $pengajuan = PengajuanBiaya::findOrFail($id);

        $pengajuan->update([
            'status' => 'ditolak',
            'note' => $request->note
        ]);

        return redirect()->back()->with('success', 'Pengajuan berhasil ditolak.');
    }
    public function pending(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string'
        ]);

        $pengajuan = PengajuanBiaya::findOrFail($id);

        $pengajuan->update([
            'status' => 'dipending',
            'note' => $request->note
        ]);

        return redirect()->back()->with('success', 'Pengajuan berhasil dipending.');
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
