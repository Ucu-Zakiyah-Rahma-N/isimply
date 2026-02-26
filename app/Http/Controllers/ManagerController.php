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
        $tab = $request->get('tab', 'disetujui');

        $countApprovedToday = PengajuanBiaya::whereHas('scheduling', function ($q) {
            $q->whereDate('tgl_pembayaran', Carbon::today());
        })->count();

        $countScheduled = PengajuanBiaya::whereHas('scheduling', function ($q) {
            $q->whereDate('tgl_pembayaran', '!=', Carbon::today());
        })->count();

        $countReject = PengajuanBiaya::where('status', 'ditolak')->count();

        $query = PengajuanBiaya::with(['items', 'scheduling'])
            ->leftJoin('kontak', 'pengajuan_biaya.kontak_id', '=', 'kontak.id')
            ->select('pengajuan_biaya.*', 'kontak.nama as penerima');

        if ($tab == 'disetujui') {

            // pembayaran hari ini
            $query->whereHas('scheduling', function ($q) {
                $q->whereDate('tgl_pembayaran', Carbon::today());
            });
        } elseif ($tab == 'dijadwalkan') {

            // pembayaran bukan hari ini
            $query->whereHas('scheduling', function ($q) {
                $q->whereDate('tgl_pembayaran', '!=', Carbon::today());
            });
        } elseif ($tab == 'ditolak') {

            $query->where('pengajuan_biaya.status', 'ditolak');
        }

        $data = $query
            ->orderByDesc('pengajuan_biaya.tgl_pengajuan')
            ->get();

        return view(
            'pages.finance.manager.index',
            compact(
                'title',
                'data',
                'tab',
                'countApprovedToday',
                'countScheduled',
                'countReject'
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
            'note' => 'required|string|max:1000'
        ]);

        $pengajuan = PengajuanBiaya::findOrFail($id);

        $pengajuan->update([
            'status' => 'ditolak',
            'note_manager' => $request->note
        ]);

        return redirect()->back()->with('success', 'Pengajuan berhasil ditolak.');
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
