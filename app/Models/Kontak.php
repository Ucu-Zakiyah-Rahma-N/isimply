<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kontak extends Model
{
    protected $table = 'kontak';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'tipe_kontak',
        'alamat',
        'email',
        'no_hp',
        'nama_bank',
        'no_rekening',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
