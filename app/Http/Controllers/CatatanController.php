<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Catatan;
use Illuminate\Support\Facades\Auth;

class CatatanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'tahapan_id' => 'required|exists:tahapan,id',
        ]);

        Catatan::create([
            'project_id' => $request->project_id,
            'tahapan_id' => $request->tahapan_id,
            'user_id' => Auth::id(),
            'isi_catatan' => $request->isi_catatan,
        ]);

        return redirect()->back()->with('success', 'Catatan berhasil dibuat!')->with('open_catatan_modal', true);;
    }
}
