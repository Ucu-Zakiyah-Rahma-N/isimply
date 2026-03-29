<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanBiayaItem extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_biaya_items';

    protected $fillable = [
        'pengajuan_biaya_id',
        'deskripsi',
        'qty',
        'harga',
        'diskon',
        'diskon_type',
        'pajak_id',
        'nilai_pajak',
        'jumlah'
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanBiaya::class);
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class, 'pajak_id');
    }
}
