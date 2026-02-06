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
    Schema::create('invoice', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('po_id');
        $table->unsignedBigInteger('customer_id');
        $table->unsignedBigInteger('marketing_id')->nullable();
        $table->enum('jenis_invoice', ['dp', 'pelunasan']);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->text('keterangan')->nullable();
        $table->date('tgl_inv');
        $table->date('tgl_jatuh_tempo')->nullable();
        $table->string('lampiran')->nullable();
        $table->decimal('subtotal', 15, 2)->default(0);
        $table->decimal('total', 15, 2)->default(0);
        $table->timestamps();

        $table->foreign('po_id')->references('id')->on('po');
        $table->foreign('customer_id')->references('id')->on('customers');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
