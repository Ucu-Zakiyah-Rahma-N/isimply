<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PO extends Model
{
    protected $table = 'po';
    protected $guarded = ['id'];


    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function perizinan()
    {
        return $this->hasManyThrough(
            Perizinan::class,
            QuotationPerizinan::class,
            'quotation_id',  // foreign key di quotation_perizinan
            'id',            // foreign key di perizinan
            'quotation_id',  // foreign key di po
            'perizinan_id'   // local key di quotation_perizinan
        );
    }

    public function provinsi()
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_id', 'kode')->where('jenis', 'provinsi');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Wilayah::class, 'kabupaten_id', 'kode')->where('jenis', 'kabupaten');
    }


    public function kawasan_industri()
    {
        return $this->belongsTo(KawasanIndustri::class, 'kawasan_id', 'id');
    }

    public static function getCustomerData($poId)
    {
        return DB::table('po as po')
            ->leftJoin('customers as customer', 'po.customer_id', '=', 'customer.id')
            ->where('po.id', $poId)
            ->select(
                'customer.id',
                'customer.nama_perusahaan',
                'customer.detail_alamat',
                'customer.npwp'
            )
            ->first();
            return $customerRaw ? Customer::find($customerRaw->id) : null;
    }

public function projects()
{
    return $this->hasMany(Project::class, 'po_id', 'id');
}
//     public function getUmurAttribute()
//     {
//         // dd($this->id);
//         // Pastikan ada relasi project
 
//     if (!$this->tgl_po) {
//         return null;
//     }

//     $project = $this->projects->first();

//     // ======================
//     // KALAU BELUM ADA PROJECT
//     // ======================
//     if (!$project) {
//         return round(
//             \Carbon\Carbon::parse($this->tgl_po)
//                 ->addDay()
//                 ->diffInHours(now()) / 24
//         );
//     }

//     // ======================
//     // CEK STATUS PROJECT
//     // ======================
//     $tahapan = $project->project_tahapan;
//     $total = $tahapan->count();
//     $start = $tahapan->whereNotNull('actual_start')->count();
//     $end   = $tahapan->whereNotNull('actual_end')->count();
//     $verifiedAll = ($project->jumlah_unverified == 0);

//     $selesai = (
//         $total > 0 &&
//         $start == $total &&
//         $end == $total &&
//         $verifiedAll
//     );

//     // ======================
//     // KALAU SUDAH SELESAI → STOP
//     // ======================
//     if ($selesai) {
//         return 0;
//     }

//     // ======================
//     // SELAIN ITU → HITUNG
//     // ======================
//     return round(
//         \Carbon\Carbon::parse($this->tgl_po)
//             ->addDay()
//             ->diffInHours(now()) / 24
//     );
// }


public function getUmurAttribute()
{
    if (!$this->tgl_po) {
        return null;
    }

    $project = $this->projects->first();

    // ======================
    // TANGGAL MULAI HITUNG
    // ======================
    $startDate = Carbon::parse($this->tgl_po)->addDay();

    // ======================
    // KALAU BELUM ADA PROJECT
    // ======================
    if (!$project) {
        return round($startDate->diffInHours(now()) / 24);
    }

    $tahapan = $project->project_tahapan;

    $total = $tahapan->count();
    $start = $tahapan->whereNotNull('actual_start')->count();
    $end   = $tahapan->whereNotNull('actual_end')->count();
    $verifiedAll = ($project->jumlah_unverified == 0);

    $selesai = (
        $total > 0 &&
        $start == $total &&
        $end == $total &&
        $verifiedAll
    );

    // ======================
    // JIKA SELESAI → AMBIL TANGGAL END TERAKHIR
    // ======================
    if ($selesai) {
        $tanggalSelesai = $tahapan->max('actual_end');

        if ($tanggalSelesai) {
            return round(
                $startDate->diffInHours(Carbon::parse($tanggalSelesai)) / 24
            );
        }
    }

    // ======================
    // JIKA BELUM SELESAI
    // ======================
    return round($startDate->diffInHours(now()) / 24);
}

public function getStatusProjectAttribute()
{
    $project = $this->projects->first();

    if (!$project) return null;

    $tahapan = $project->project_tahapan;

    $total = $tahapan->count();
    $start = $tahapan->whereNotNull('actual_start')->count();
    $end   = $tahapan->whereNotNull('actual_end')->count();
    $verifiedAll = ($project->jumlah_unverified == 0);

    if ($total > 0 && $start == $total && $end == $total && $verifiedAll) {
        return 'Selesai';
    }

    return 'On Progress';
}
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'po_id');
    }

}
