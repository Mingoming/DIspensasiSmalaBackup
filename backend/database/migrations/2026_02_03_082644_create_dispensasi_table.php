<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Siswa yang mengajukan
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->date('tanggal'); // Tanggal dispensasi
            $table->time('jam_mulai'); // Jam mulai
            $table->time('jam_selesai'); // Jam selesai
            $table->string('mata_pelajaran'); // Mapel yang ditinggalkan
            $table->text('keperluan'); // Alasan dispensasi
            $table->string('surat_dispensasi')->nullable(); // File upload (opsional)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Guru/admin yang approve
            $table->text('catatan')->nullable(); // Catatan dari guru/admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensasi');
    }
};
