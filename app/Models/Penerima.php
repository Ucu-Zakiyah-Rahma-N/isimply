<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kontak extends Model
{
    protected $table = 'kontak';

    protected $fillable = [
        'nama',
        'tipe_kontak',
        'alamat',
        'email',
        'no_hp',
        'no_rekening',
    ];
}
