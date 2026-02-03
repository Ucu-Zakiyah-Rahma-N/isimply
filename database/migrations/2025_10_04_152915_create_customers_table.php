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
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED PRIMARY KEY
            $table->foreignId('cabang_id')
                ->default(1)
                ->constrained('cabang')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('nama_perusahaan');
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->unsignedBigInteger('kawasan_id')->nullable();
            $table->string('detail_alamat')->nullable();
            $table->foreignId('marketing_id')->nullable()->constrained('marketing')->nullOnDelete();
            $table->json('pic_perusahaan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
