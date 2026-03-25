<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreDatabase extends Command
{
    protected $signature = 'db:restore {filename? : Backup filename (uses latest if omitted)}';
    protected $description = 'Restore the PostgreSQL database from a backup in storage/backups/';

    public function handle(): int
    {
        $dir = storage_path('backups');

        if (! File::isDirectory($dir)) {
            $this->error('No backups directory found.');
            return self::FAILURE;
        }

        $filename = $this->argument('filename');

        if (! $filename) {
            $backups = collect(File::files($dir))
                ->filter(fn ($f) => str_ends_with($f->getFilename(), '.sql'))
                ->sortByDesc(fn ($f) => $f->getMTime());

            if ($backups->isEmpty()) {
                $this->error('No backups found.');
                return self::FAILURE;
            }

            $this->info('Available backups:');
            $backups->each(fn ($f) => $this->line('  ' . $f->getFilename() . '  (' . number_format($f->getSize() / 1024, 1) . ' KB)'));

            $filename = $backups->first()->getFilename();
            $this->newLine();
            $this->info("Latest: {$filename}");
        }

        $path = "{$dir}/{$filename}";

        if (! File::exists($path)) {
            $this->error("Backup not found: {$filename}");
            return self::FAILURE;
        }

        if (! $this->confirm("Restore database from {$filename}? This will OVERWRITE the current database.")) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $db   = config('database.connections.pgsql.database');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');

        $cmd = sprintf(
            'PGPASSWORD=%s pg_restore -h %s -p %s -U %s -d %s --clean --if-exists %s 2>&1',
            escapeshellarg($pass),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($db),
            escapeshellarg($path),
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            // pg_restore often returns non-zero for warnings; check output
            $errors = array_filter($output, fn ($l) => stripos($l, 'error') !== false);
            if (! empty($errors)) {
                $this->error('Restore had errors:');
                foreach ($errors as $line) $this->line("  {$line}");
                return self::FAILURE;
            }
        }

        $this->info("Database restored from {$filename}.");
        return self::SUCCESS;
    }
}
