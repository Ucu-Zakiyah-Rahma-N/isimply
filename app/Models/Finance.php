<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $table = 'invoice';
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'id');
    }


    public function tracking()
    {
        return $this->hasMany(Tracking::class);
    }

    public function po()
    {
            return $this->belongsTo(PO::class, 'po_id', 'id');

        // return $this->hasOne(PO::class, 'quotation_id', 'quotation_id');
    }
    
    public function project_perizinan()
    {
        return $this->hasMany(ProjectPerizinan::class, 'project_id', 'id');
    }

    public function project_tahapan()
    {
    return $this->hasMany(ProjectTahapan::class, 'project_id', 'id');
    }

    public function tahapan()
    {
        return $this->belongsToMany(Tahapan::class, 'project_tahapan'); // sesuaikan nama tabel pivot
    }


    public function ceklis_perizinan() { 
        return $this->hasMany(CeklisPerizinan::class); 
    }
        public function catatan()
    {
        return $this->hasMany(Catatan::class);
    }
    public function ceklisExclude()
{
    return $this->hasMany(ProjectCeklisExclude::class);
}

public function marketing()
{
    return $this->belongsTo(Marketing::class, 'marketing_id', 'id');
}

// Project.php
public function verifikasi_project()
{
    return $this->hasMany(VerifikasiProject::class, 'project_id', 'id');
}


    public function perizinan()
    {
        return $this->hasManyThrough(
            Perizinan::class,
            QuotationPerizinan::class,
            'quotation_id',  // foreign key di quotation_perizinan
            'id',            // foreign key di perizinan
            'quotation_id',  // foreign key di po
            'perizinan_id'   // local key di quotation_perizinan
        );
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


}
