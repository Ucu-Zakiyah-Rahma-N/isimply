<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdukInvoice extends Model
{
    protected $table = 'produk_invoice';
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
}
