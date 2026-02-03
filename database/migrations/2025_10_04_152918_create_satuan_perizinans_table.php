<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('satuan_perizinans', function (Blueprint $table) {
            $table->id();
            // Kode satuan (dipakai di sistem)
            $table->string('nama', 30)->unique();
            // Status aktif/nonaktif
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan_perizinans');
    }
};
