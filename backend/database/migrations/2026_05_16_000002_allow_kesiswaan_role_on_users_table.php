<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','guru','siswa','kesiswaan') DEFAULT 'siswa'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'kesiswaan')->update(['role' => 'guru']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','guru','siswa') DEFAULT 'siswa'");
        }
    }
};
