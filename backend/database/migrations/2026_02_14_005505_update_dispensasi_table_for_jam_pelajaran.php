<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            // Ganti jam_mulai dan jam_selesai jadi jam_pelajaran_mulai dan jam_pelajaran_selesai
            $table->dropColumn(['jam_mulai', 'jam_selesai']);
        });

        Schema::table('dispensasi', function (Blueprint $table) {
            $table->integer('jam_pelajaran_mulai')->after('tanggal'); // 1-8
            $table->integer('jam_pelajaran_selesai')->after('jam_pelajaran_mulai'); // 1-8
        });
    }

    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->dropColumn(['jam_pelajaran_mulai', 'jam_pelajaran_selesai']);
        });

        Schema::table('dispensasi', function (Blueprint $table) {
            $table->time('jam_mulai')->after('tanggal');
            $table->time('jam_selesai')->after('jam_mulai');
        });
    }
};
