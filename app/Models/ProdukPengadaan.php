<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukPengadaan extends Model
{
    protected $table = 'produk_pengadaan';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'nama_produk',
    ];

    public $timestamps = true;

    // 🔗 Relasi ke inventory
    public function inventory()
    {
        return $this->hasOne(Invetory::class, 'produk_id', 'id_produk');
    }
}
