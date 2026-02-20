<?php

namespace App\Http\Controllers;

use App\Models\PO;
use App\Models\Wilayah;
use App\Models\invoice;
use App\Models\Kontak;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ProdukInvoice;
use App\Models\TaxInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\TotalInvoiceHelper;
use App\Helpers\InvoiceCalculatorHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class KontakController extends Controller
{
    public function kontak_index()
    {
        $title = 'Kontak';

        $kontak = Kontak::latest()->get();

        return view(
            'pages.finance.kontak.kontak_index',
            compact('title', 'kontak')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:100',
            'tipe_kontak' => 'required|in:customer,supplier,karyawan,lainnya',
            'email'       => 'nullable|email|max:100',
            'no_hp'       => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            'nama_bank'   => 'nullable|string|max:100',
            'no_rekening'      => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {

            $kontak = Kontak::create($validated);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Kontak berhasil ditambahkan.');
        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan kontak.');
        }
    }

    public function destroy($id)
    {
        try {

            $kontak = Kontak::findOrFail($id);
            $kontak->delete();

            return redirect()
                ->back()
                ->with('success', 'Kontak berhasil dihapus.');
        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus kontak.');
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $kontak = Kontak::findOrFail($id);

            $kontak->update([
                'nama'        => $request->nama,
                'tipe_kontak' => $request->tipe_kontak,
                'email'       => $request->email,
                'no_hp'       => $request->no_hp,
                'alamat'      => $request->alamat,
                'nama_bank'   => $request->nama_bank,
                'no_rekening'      => $request->no_rek,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Kontak berhasil diperbarui.');
        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui kontak.');
        }
    }
}
