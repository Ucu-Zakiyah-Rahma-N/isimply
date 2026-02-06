<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $fillable = [
        'no_invoice',
        'po_id',
        'customer_id',
        'marketing_id',
        'jenis_invoice',
        'parent_id',
        'keterangan',
        'tgl_inv',
        'tgl_jatuh_tempo',
        'lampiran',
        'subtotal',
        'total',
        'created_at',
        'updated_at'
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

}
