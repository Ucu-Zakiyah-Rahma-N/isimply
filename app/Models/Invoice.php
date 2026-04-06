<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    protected $table = 'invoice';
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

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }
    public function journal()
    {
        return $this->hasOne(Journal::class);
                // return $this->hasOne(Journal::class, 'ref_id')
        // ->where('ref_type', 'invoice');

    }
    
}
