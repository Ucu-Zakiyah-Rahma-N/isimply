<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    use HasFactory;

    protected $table = 'coa';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'nilai_coa',
        'kategori_akun',
        'is_header_akun',
        'is_sub_akun',
        'saldo',
        'saldo',
        'parent_akun_id'
    ];

    public function children()
    {
        return $this->hasMany(Coa::class, 'parent_akun_id');
    }

    public function parent()
    {
        return $this->belongsTo(Coa::class, 'parent_akun_id');
    }
}
