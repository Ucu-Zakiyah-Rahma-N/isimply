<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryInvetory extends Model
{
    protected $table = 'history_invetory';
    protected $primaryKey = 'id_history_invetory';

    protected $fillable = [
        'invetory_id',
        'jumlah',
        'status',
        'checked_at',
        'checked_by',
    ];

    public $timestamps = true;

    // 🔗 Relasi ke inventory
    public function inventory()
    {
        return $this->belongsTo(Invetory::class, 'invetory_id', 'id_invetory');
    }
}
