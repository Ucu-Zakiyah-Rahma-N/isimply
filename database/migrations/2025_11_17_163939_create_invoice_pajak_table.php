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
    Schema::create('invoice_pajak', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('invoice_id');
        $table->unsignedBigInteger('pajak_id');
        $table->decimal('tarif', 5, 2);
        $table->decimal('nominal', 15, 2);
        $table->timestamps();

        $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        $table->foreign('pajak_id')->references('id')->on('pajaks');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_pajak');
    }
};
