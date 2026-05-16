<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('dispensasi_kehadiran_guru');
    }

    public function down(): void
    {
        Schema::create('dispensasi_kehadiran_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensasi_id')->constrained('dispensasi')->onDelete('cascade');
            $table->foreignId('jadwal_mengajar_id')->constrained('jadwal_mengajar')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
            $table->enum('status_kehadiran', ['izin'])->default('izin');
            $table->text('catatan_akademik')->nullable();
            $table->timestamps();

            $table->unique(['dispensasi_id', 'jadwal_mengajar_id', 'guru_id'], 'disp_kehadiran_guru_unique');
            $table->index(['guru_id', 'status_kehadiran']);
        });
    }
};
