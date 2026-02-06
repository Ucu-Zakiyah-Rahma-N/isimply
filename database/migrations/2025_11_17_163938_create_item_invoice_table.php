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
    Schema::create('item_invoice', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('invoice_id');
        $table->unsignedBigInteger('produk_id');
        $table->decimal('qty', 10, 2);
        $table->unsignedBigInteger('satuan_id');
        $table->decimal('harga_satuan', 15, 2);
        $table->decimal('subtotal', 15, 2);
        $table->timestamps();

        $table->foreign('invoice_id')->references('id')->on('invoice')->onDelete('cascade');
        $table->foreign('produk_id')->references('id')->on('produk');
        $table->foreign('satuan_id')->references('id')->on('satuan');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_invoice');
    }
};
