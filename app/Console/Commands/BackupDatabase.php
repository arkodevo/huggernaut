<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Create a timestamped PostgreSQL backup in storage/backups/';

    public function handle(): int
    {
        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir);

        $timestamp = now()->format('Y-m-d_His');
        $filename = "huggernaut-{$timestamp}.sql";
        $path = "{$dir}/{$filename}";

        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $db   = config('database.connections.pgsql.database');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');

        $cmd = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -Fc %s > %s 2>&1',
            escapeshellarg($pass),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($db),
            escapeshellarg($path),
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup failed: ' . implode("\n", $output));
            return self::FAILURE;
        }

        $size = number_format(filesize($path) / 1024, 1);
        $this->info("Backup created: {$filename} ({$size} KB)");

        // Keep only last 10 backups
        $backups = collect(File::files($dir))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.sql'))
            ->sortByDesc(fn ($f) => $f->getMTime());

        $backups->slice(10)->each(fn ($f) => File::delete($f->getPathname()));

        $deleted = max(0, $backups->count() - 10);
        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old backup(s).");
        }

        return self::SUCCESS;
    }
}
