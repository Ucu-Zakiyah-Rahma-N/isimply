<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SatuanPerizinan extends Model
{
    use HasFactory;

    protected $table = 'satuan_perizinans'; // pastikan nama tabel benar
    protected $guarded = ['id'];

        public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function quotation_perizinan()
    {
        return $this->hasMany(QuotationPerizinan::class, 'satuan_perizinans_id');
    }

}

