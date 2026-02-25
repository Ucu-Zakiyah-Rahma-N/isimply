<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\PengajuanBiaya;
use App\Models\PengajuanPembelian;
use App\Models\Coa;

use Carbon\Carbon;

class SchedulingPengajuan extends Model
{
    protected $table = 'scheduling_pengajuan';

    protected $fillable = [
        'pengajuan_biaya_pengadaan_id',
        'pengajuan_pembelian_id',
        'coa_id',
        'bank_coa_id',
        'tgl_pembayaran',
        'is_akomodasi',
    ];

    protected $casts = [
        'tgl_pembayaran' => 'date',
        'is_akomodasi'   => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Relasi ke pengajuan biaya
    public function pengajuanBiaya()
    {
        return $this->belongsTo(PengajuanBiaya::class, 'pengajuan_biaya_pengadaan_id');
    }

    // Relasi ke pengajuan pembelian
    // public function pengajuanPembelian()
    // {
    //     return $this->belongsTo(PengajuanPembelian::class, 'pengajuan_pembelian_id');
    // }

    // COA lawan transaksi
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }

    // COA sumber bank
    public function coaBank()
    {
        return $this->belongsTo(Coa::class, 'bank_coa_id');
    }
}
