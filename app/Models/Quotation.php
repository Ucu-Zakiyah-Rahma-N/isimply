<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;
    
    protected $table = 'quotations'; // pastikan nama tabel benar
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function provinsi()
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_id', 'kode')->where('jenis', 'provinsi');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Wilayah::class, 'kabupaten_id', 'kode')->where('jenis', 'kabupaten');
    }

    public function kawasan_industri()
    {
        return $this->belongsTo(KawasanIndustri::class, 'kawasan_id', 'id');
    }

    public function perizinan()
    {
        return $this->belongsToMany(Perizinan::class, 'quotation_perizinan', 'quotation_id', 'perizinan_id')
                    ->withPivot('harga_satuan', 'qty', 'satuan_id')
                    ->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(Quotation::class, 'parent_id');
    }

    public function versions()
    {
        return $this->hasMany(Quotation::class, 'parent_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function quotation_perizinan()
    {
        return $this->hasMany(QuotationPerizinan::class, 'quotation_id');
    }

    public function po()
    {
        return $this->hasOne(PO::class);
    }
    
    public function marketing()
    {
        return $this->belongsTo(Marketing::class, 'marketing_id');
    }


// public static function get_data_template($id)
// {
//     return self::select(
//         'quotations.id as quotation_id',
//         'quotations.no_sph',
//         'quotations.tgl_sph',
//         'quotations.nama_bangunan',
//         'quotations.fungsi_bangunan',
//         'quotations.luas_slf',
//         'quotations.luas_pbg',
//         'quotations.luas_shgb',
//         'quotations.harga_gabungan',
//         'quotations.detail_alamat',
//         'quotation_perizinan.id as quotation_perizinan_id',
//         'quotation_perizinan.harga_satuan',
//         'perizinans.id as perizinan_id',
//         'perizinans.kode as perizinan_kode',
//         'perizinans.jenis as perizinan_jenis',
//         'quotation_templates.file_path',
//         'quotation_templates.kode_template'
//     )
//     ->leftJoin('quotation_perizinan', 'quotation_perizinan.quotation_id', '=', 'quotations.id')
//     ->leftJoin('perizinans', 'perizinans.id', '=', 'quotation_perizinan.perizinan_id')
//     ->leftJoin('quotation_templates', 'quotation_templates.kode_template', '=', 'perizinans.kode')
//     ->where('quotations.id', $id)
//     ->first(); // ambil satu record
// }

//untuk template (nnti kalo sudah punya semua wajib di hidupkan lg)

// public function getTemplatePathAttribute()
    // {
    //     return QuotationTemplate::whereIn(
    //         'kode_template',
    //         $this->perizinan->pluck('kode')
    //     )->value('file_path');
    // }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR (LOGIC BISNIS)
    |--------------------------------------------------------------------------
    */

    /**
     * Total harga (gabungan / satuan)
     */
    public function getTotalHargaAttribute()
    {
        if ($this->harga_tipe === 'gabungan') {
            return (float) ($this->harga_gabungan ?? 0);
        }

        // SATUAN: qty × harga_satuan
        return $this->perizinan->sum(function ($izin) {
            $qty   = (int) ($izin->pivot->qty ?? 1);
            $harga = (float) ($izin->pivot->harga_satuan ?? 0);
            return $qty * $harga;
        });
    }
    
    public function getTotalDiskonAttribute()
    {
        if ($this->diskon_tipe === 'nominal') {
            return $this->diskon_nilai;
        }
    
        if ($this->diskon_tipe === 'persen') {
            return ($this->total_harga * $this->diskon_nilai) / 100;
        }
    
        return 0;
    }
    
    public function getGrandTotalAttribute()
    {
        return max(0, $this->total_harga - $this->total_diskon);
    }
    /**
     * Text jenis perizinan (SLF, PBG, dst)
     */
    public function getJenisPerizinanTextAttribute()
    {
        return (
            $this->perizinan->pluck('jenis')->implode(', ')
        );
    }

    /**
     * Luas bangunan utama (numeric)
     */
    public function getLuasBangunanAttribute()
    {
        return $this->luas_slf
            ?? $this->luas_pbg
            ?? $this->luas_shgb
            ?? 0;
    }

    /**
     * Luas bangunan text (untuk Blade / Word)
     */
    public function getLuasBangunanTextAttribute()
    {
        $luas = [];

        if ($this->luas_slf) {
            $luas[] = "SLF: " . number_format($this->luas_slf, 2, ',', '.') . " m²";
        }

        if ($this->luas_pbg) {
            $luas[] = "PBG: " . number_format($this->luas_pbg, 2, ',', '.') . " m²";
        }

        if ($this->luas_shgb) {
            $luas[] = "SHGB: " . number_format($this->luas_shgb, 2, ',', '.') . " m²";
        }

        return $luas ? implode(', ', $luas) : '-';
    }

    /**
     * Detail perizinan (untuk Word / PDF)
     */
    public function getDetailPerizinanAttribute()
    {
        return $this->perizinan->map(function ($izin) {
            return [
                'nama'  => $izin->jenis,
                'harga' => $this->harga_tipe === 'satuan'
                    ? ($izin->pivot->harga_satuan ?? 0)
                    : 0
            ];
        })->toArray();
    }
    
    // public function customer() { return $this->belongsTo(Customer::class, 'customer_id'); }
    // public function perizinan() { return $this->belongsToMany(Perizinan::class, 'quotation_perizinan', 'quotation_id', 'perizinan_id')->withPivot('harga_satuan'); }
    // public function kabupaten() { return $this->belongsTo(Kabupaten::class, 'kabupaten_id'); }
    // public function provinsi() { return $this->belongsTo(Provinsi::class, 'provinsi_id'); }

}

