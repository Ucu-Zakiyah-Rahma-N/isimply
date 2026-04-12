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
        'metode_pembayaran',
        'kontak_id',
        'referensi_proyek_id',
        'is_urgent',

        // TOTAL
        'subtotal',
        'total_diskon',
        'total_ppn',
        'grand_total',

        // GLOBAL DISKON
        'use_diskon_global',
        'diskon_global',
        'diskon_global_type',

        // GLOBAL PAJAK
        'use_pajak_global',
        'pajak_global_id',
        'nilai_pajak_global',
        'user_id',

        // LAINNYA
        'lampiran',
        'status',
        'note'
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'tgl_pengajuan' => 'date'
    ];

    public function kontak()
    {
        return $this->belongsTo(Kontak::class, 'kontak_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

    public function po()
    {
        return $this->belongsTo(PO::class, 'project_id', 'id');
    }
}
