<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationPerizinan extends Model
{
    use HasFactory;

    protected $table = 'quotation_perizinan';

    protected $fillable = [
        'quotation_id',
        'perizinan_id',
        'qty',
        'satuan_id',
        'harga_satuan',
    ];

    /**
     * Relasi ke Quotation
     * Banyak quotation_perizinan dimiliki oleh satu quotation
     */
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    // Relasi ke perizinan
    public function perizinan()
    {
        return $this->belongsTo(Perizinan::class, 'perizinan_id');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanPerizinan::class, 'satuan_id');
    }
}
