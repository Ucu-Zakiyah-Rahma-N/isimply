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
        Schema::create('quotation_perizinan', function (Blueprint $table) {
            $table->id();

            // Foreign key ke quotations
            $table->unsignedBigInteger('quotation_id');
            $table->foreign('quotation_id')
                  ->references('id')
                  ->on('quotations')
                  ->onDelete('cascade');

            // Foreign key ke perizinans
            $table->unsignedBigInteger('perizinan_id');
            $table->foreign('perizinan_id')
                  ->references('id')
                  ->on('perizinans')
                  ->onDelete('cascade');

            $table->integer('qty')
                ->default(1);
            // satuan
            $table->foreignId('satuan_id')
                ->nullable()
                ->constrained('satuan_perizinans')
                ->nullOnDelete()
                ->cascadeOnUpdate();
                  
            // Harga per izin
            $table->decimal('harga_satuan', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_perizinan');
    }
};
