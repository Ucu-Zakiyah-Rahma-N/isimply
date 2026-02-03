<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('cabang', function (Blueprint $table) {
        $table->id();
        $table->string('nama_cabang');   // HO, Bogor, Depok, dll
        $table->string('kode_sph');      // SDI, SDI-Bogor, SDI-Sukabumi
        $table->integer('start_number')->default(1);
        $table->boolean('status')->default(1); // 1 aktif, 0 tidak
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabang');
    }
};
