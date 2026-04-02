<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoicePayment extends Model
{
    protected $table = 'invoice_payment';
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
        public function coaBank()
    {
        return $this->belongsTo(Coa::class, 'coa_bank_id');
    }
}
