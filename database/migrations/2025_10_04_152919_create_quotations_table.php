<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            // Relasi ke customer
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();
                        
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable()->index();

            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->integer('counter')->nullable();

            // Nomor dan tanggal SPH
            $table->string('no_sph')->unique();
            $table->date('tgl_sph')->nullable();
            
            $table->enum('fungsi_bangunan', ['-','Fungsi Hunian','Fungsi Keagamaan','Fungsi Usaha','Fungsi Sosial dan Budaya','Fungsi Khusus']);

            // Informasi bangunan
            $table->string('nama_bangunan')->nullable();

            // Wilayah (provinsi, kabupaten, kawasan)
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->unsignedBigInteger('kawasan_id')->nullable();

            // Alamat detail
            $table->text('detail_alamat')->nullable();

            // Lama pekerjaan (dalam satuan hari)
            $table->integer('lama_pekerjaan')->nullable();
            $table->integer('jumlah_termin')->nullable();
            // JSON untuk menyimpan daftar termin & persentasenya
            $table->json('termin_persentase')->nullable();

            
            // Luasan (opsional, hanya dipakai kalau izinnya relevan)
            $table->decimal('luas_pbg', 10, 2)->nullable();
            $table->decimal('luas_slf', 10, 2)->nullable();
            $table->decimal('luas_shgb', 10, 2)->nullable();

            // Harga gabungan atau per-satuan
            $table->enum('harga_tipe', ['satuan', 'gabungan'])->default('satuan');
            $table->decimal('harga_gabungan', 15, 2)->nullable();
            
            $table->enum('diskon_tipe', ['persen', 'nominal'])->nullable();
            $table->unsignedBigInteger('diskon_nilai')->default(0);
    

            $table->boolean('is_same_nama_bangunan')->default(false);
            $table->boolean('is_same_alamat')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
