<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kontak;
use App\Models\Coa;
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
}
