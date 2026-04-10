<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invetory extends Model
{
    protected $table = 'invetory';
    protected $primaryKey = 'id_invetory';

    protected $fillable = [
        'produk_id',
        'jumlah',
    ];

    public $timestamps = true;

    // 🔗 Relasi ke produk
    public function produk()
    {
        return $this->belongsTo(ProdukPengadaan::class, 'produk_id', 'id_produk');
    }

    // 🔗 Relasi ke history
    public function histories()
    {
        return $this->hasMany(HistoryInvetory::class, 'invetory_id', 'id_invetory');
    }
}
