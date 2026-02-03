<?php

namespace App\Http\Controllers;

use App\Models\Perizinan;
use Illuminate\Http\Request;

class PerizinanController extends Controller
{
     public function index()
    {

        $data = [
            'title' => 'Data Perizinan',
            'perizinan' => Perizinan::all(),
        ];

        return view('admin.jenis_perizinan', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'required|string',
            'kode' => 'required|string',
        ]);

        Perizinan::create([
            'jenis' => $request->jenis,
            'kode' => $request->kode,
        ]);

        return redirect()->route('perizinan.index')->with('success', 'Jenis Perizinan berhasil ditambahkan!');
    }
    
    public function destroy($id)
    {
        $perizinan = Perizinan::findOrFail($id);
        $perizinan->delete();
    
        return redirect()->back()->with('success', 'Perizinan berhasil dihapus');
    }


}
