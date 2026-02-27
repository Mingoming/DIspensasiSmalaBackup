<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\AuditLog;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--type=full}';
    protected $description = 'Create a backup of the database';

    public function handle()
    {
        $type = $this->option('type');

        $this->info('Starting backup...');

        try {
            if ($type === 'full') {
                Artisan::call('backup:run');
            } else {
                Artisan::call('backup:run --only-db');
            }

            $this->info('Backup completed successfully!');
            $this->info('Backup location: storage/app/backups');

            // Log to audit
            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'backup',
                'description' => "Database backup created (Type: {$type})",
                'ip_address' => request()->ip() ?? 'CLI',
                'user_agent' => 'Console Command',
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'backup_failed',
                'description' => "Database backup failed: " . $e->getMessage(),
                'ip_address' => request()->ip() ?? 'CLI',
                'user_agent' => 'Console Command',
            ]);

            return Command::FAILURE;
        }
    }
}
