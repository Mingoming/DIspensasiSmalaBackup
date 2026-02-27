<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, kesiswaan, guru_mapel
            $table->string('display_name'); // Admin, Kesiswaan, Guru Mata Pelajaran
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tabel pivot user_roles (many-to-many)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        // Tambah kolom mata_pelajaran untuk guru
        Schema::table('users', function (Blueprint $table) {
            $table->string('mata_pelajaran')->nullable()->after('nip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mata_pelajaran');
        });

        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
