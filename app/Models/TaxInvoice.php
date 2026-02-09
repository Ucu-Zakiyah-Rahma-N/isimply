<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxInvoice extends Model
{
    protected $table = 'tax_invoice';
    protected $guarded = ['id'];

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id');
    }
        public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function coa()
    {
        return $this->belongsTo(Pajak::class, 'coa_id');
    }

}
