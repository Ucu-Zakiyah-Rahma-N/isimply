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
    Schema::create('pajak', function (Blueprint $table) {
        $table->id();
        $table->string('nama_pajak');
        $table->string('kode')->unique();
        $table->decimal('tarif', 5, 2);
        $table->enum('jenis', ['penambah', 'pengurang']);
        $table->unsignedBigInteger('coa_id');
        $table->boolean('tampil_di_invoice')->default(true);
        $table->boolean('aktif')->default(true);
        $table->timestamps();

        $table->foreign('coa_id')->references('id')->on('coa');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak');
    }
};
