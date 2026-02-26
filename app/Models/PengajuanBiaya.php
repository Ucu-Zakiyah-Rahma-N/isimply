<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SchedulingPengajuan;

class PengajuanBiaya extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_biaya';

    protected $fillable = [
        'jenis_pengajuan',
        'tgl_pengajuan',
        'project_id',
        'jenis_project',
        'nomor_pengajuan',
        'tgl_pengajuan',
        'metode_pembayaran',
        'kontak_id',
        'referensi_proyek_id',
        'is_urgent',
        'subtotal',
        'total_diskon',
        'total_ppn',
        'grand_total',
        'lampiran',
        'status',
        'note'
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'tgl_pengajuan' => 'date'
    ];

    public function items()
    {
        return $this->hasMany(PengajuanBiayaItem::class);
    }
    public function scheduling()
    {
        return $this->hasOne(
            SchedulingPengajuan::class,
            'pengajuan_biaya_pengadaan_id', // foreign key di scheduling_pengajuan
            'id' // local key di pengajuan_biaya
        );
    }
}
