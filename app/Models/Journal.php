<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Journal extends Model
{
    protected $table = 'journals';
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function quotations()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'id');
    }

    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function produk()
    {
        return $this->hasMany(ProdukInvoice::class, 'invoice_id');
    }

    public function pajak()
    {
        return $this->hasMany(TaxInvoice::class, 'invoice_id');
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
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // ⭐ 1 journal punya banyak detail
    public function journaldetails()
    {
        return $this->hasMany(JournalDetail::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    // total debit
    public function totalDebit()
    {
        return $this->details()->sum('debit');
    }

    // total credit
    public function totalCredit()
    {
        return $this->details()->sum('credit');
    }

    public static function generateNo()
    {
        $date = now()->format('Ymd');

        $last = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $seq = $last ? ((int) substr($last->no_jurnal, -4)) + 1 : 1;

        return 'JRN-' . $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
    public function getPphAttribute()
    {
        return $this->details()
            ->where('coa_id', 2) // sementara pakai ini dulu
            ->value('debit') ?? 0;
    }
}
