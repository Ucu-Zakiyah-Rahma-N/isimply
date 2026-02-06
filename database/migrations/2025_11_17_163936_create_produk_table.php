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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('satuan_id');
            $table->decimal('harga_satuan', 15, 2)->nullable();
            $table->unsignedBigInteger('coa_id');
            $table->unsignedBigInteger('pajak_id')->nullable();
            $table->timestamps();

            $table->foreign('satuan_id')->references('id')->on('satuans');
            $table->foreign('coa_id')->references('id')->on('coa');
            $table->foreign('pajak_id')->references('id')->on('pajak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
