<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru', 'siswa'])->default('siswa')->after('email');
            $table->string('nisn')->nullable()->after('role'); // Untuk siswa
            $table->string('nip')->nullable()->after('nisn'); // Untuk guru
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('set null')->after('nip');
            $table->string('no_telepon')->nullable()->after('kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['role', 'nisn', 'nip', 'kelas_id', 'no_telepon']);
        });
    }
};
