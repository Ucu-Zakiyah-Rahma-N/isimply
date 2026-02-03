<?php

namespace App\Http\Controllers;

use App\Models\Tahapan;
use Illuminate\Http\Request;

class TahapanController extends Controller
{
     public function index()
    {

        $data = [
            'title' => 'Data Tahapan',
            'tahapan' => Tahapan::all(),
        ];

        return view('admin.jenis_tahapan', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tahapan' => 'required|string',
        ]);

        Tahapan::create([
            'nama_tahapan' => $request->nama_tahapan,
        ]);

        return redirect()->route('tahapan.index')->with('success', 'Jenis Tahapan berhasil ditambahkan!');
    }
    
        public function destroy($id)
    {
        $tahapan = Tahapan::findOrFail($id);
        $tahapan->delete();
    
        return redirect()->back()->with('success', 'Tahapan berhasil dihapus');
    }


}
