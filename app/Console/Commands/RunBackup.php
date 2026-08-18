<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RunBackup extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Export all database tables and the public storage folder into a single zip file in storage/app/backups, and prune backups older than the configured retention period.';

    public function handle(): int
    {
        $this->info('Starting backup...');

        $backupsDir = storage_path('app/backups');
        if (! is_dir($backupsDir)) {
            mkdir($backupsDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $zipPath = $backupsDir . '/backup_' . $timestamp . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $this->error('Could not create backup archive.');

            return self::FAILURE;
        }

        // ---- 1. Export every table's rows to JSON ----
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->reject(fn ($table) => $table === 'sessions'); // no value in backing up session rows

        foreach ($tables as $table) {
            $rows = DB::table($table)->get();
            $zip->addFromString('database/' . $table . '.json', $rows->toJson(JSON_PRETTY_PRINT));
        }

        // ---- 2. Include uploaded files (logos, QR codes, product images) ----
        $publicDisk = Storage::disk('public');
        foreach ($publicDisk->allFiles() as $file) {
            $zip->addFile($publicDisk->path($file), 'storage/' . $file);
        }

        $zip->close();

        $this->info('Backup created: ' . basename($zipPath));

        // ---- 3. Prune old backups beyond the retention window ----
        $keepDays = (int) Setting::get('keep_backup_for_days', 30);
        $cutoff = now()->subDays($keepDays)->timestamp;

        $deleted = 0;
        foreach (glob($backupsDir . '/backup_*.zip') as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Pruned {$deleted} backup(s) older than {$keepDays} days.");
        }

        return self::SUCCESS;
    }
}