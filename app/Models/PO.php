<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

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
                'customer.nama_perusahaan',
                'customer.detail_alamat',
                'customer.npwp'
            )
            ->first();
    }
}
