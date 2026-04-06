<?php

namespace App\Models;

use App\Http\Controllers\CatatanController;
use Illuminate\Database\Eloquent\Model;

class VerifikasiProject extends Model
{
    protected $table = 'verifikasi_project'; // pastikan nama tabel benar
    protected $fillable = [
    'project_id',
    'project_perizinan_id',
    'ceklis_perizinan_id',
    'tahapan_id',
    'verified',
    'verified_at',
    'verified_by',
];
    protected $dates = ['verified_at'];
    
        public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function perizinan() {
        return $this->belongsTo(Perizinan::class, 'perizinan_id');
    }

    public function tahapan() {
        return $this->belongsTo(MstTahapan::class, 'tahapan_id');
    }

    public function catatan() {
        return $this->hasMany(Catatan::class);
    }
}

