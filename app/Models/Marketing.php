<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    protected $table = 'marketing'; // pastikan nama tabel benar
    protected $guarded = ['id'];
    
    public function users() {
    return $this->hasMany(User::class, 'customer_id');
    }

    public function customers() {
        return $this->hasMany(Project::class, 'marketing_id');
    }
    
}

