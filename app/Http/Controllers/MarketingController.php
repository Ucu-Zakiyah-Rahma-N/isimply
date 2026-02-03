<?php

namespace App\Http\Controllers;

use App\Models\Marketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Gunakan paginate() agar bisa tampilkan pagination di view
        $marketing = Marketing::paginate(10);

        $data = [
            'title' => 'Marketing',
            'marketing' => $marketing,
        ];

        return view('admin.marketing', $data);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'nama' => 'required|string',
        ]);

        Marketing::create([
            'status' => $request->status,
            'nama' => $request->nama,
        ]);

        return redirect()->route('marketing.index')->with('success', 'Data marketing berhasil ditambahkan!');
    }
    
    public function destroy($id)
    {
        $marketing = Marketing::findOrFail($id);
        $marketing->delete();
    
        return redirect()->back()->with('success', 'Staff marketing berhasil dihapus');
    }
}
