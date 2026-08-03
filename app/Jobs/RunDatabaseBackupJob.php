<?php

namespace App\Jobs;

use App\Models\BackupRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;
use Throwable;

class RunDatabaseBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public BackupRun $backupRun)
    {
        $this->onQueue('longtask');
    }

    public function handle(): void
    {
        Log::info('[RunDatabaseBackupJob] Starting database backup...', [
            'backup_run_id' => $this->backupRun->id,
            'triggered_by' => $this->backupRun->triggered_by_user_id,
        ]);

        try {
            $config = $this->createBackupConfig();
            $backupJob = BackupJobFactory::createFromConfig($config);
            $backupJob->dontBackupFilesystem();
            $backupJob->onlyBackupTo('danger-backups');
            $backupJob->disableSignals();
            $backupJob->run();

            $file = $this->findLatestBackupFile();

            $this->backupRun->update([
                'completed_at' => now(),
                'status' => 'completed',
                'filename' => $file['filename'],
                'file_path' => $file['path'],
                'file_size' => $file['size'],
            ]);

            Log::info('[RunDatabaseBackupJob] Database backup completed successfully', [
                'backup_run_id' => $this->backupRun->id,
                'filename' => $file['filename'],
            ]);
        } catch (Throwable $exception) {
            $this->backupRun->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            Log::error('[RunDatabaseBackupJob] Database backup failed', [
                'backup_run_id' => $this->backupRun->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function createBackupConfig(): Config
    {
        $configData = config('backup');
        $configData['backup']['destination']['disks'] = ['danger-backups'];
        $configData['backup']['source']['files']['include'] = [];

        return Config::fromArray($configData);
    }

    /**
     * @return array{filename: string, path: string, size: int}
     */
    protected function findLatestBackupFile(): array
    {
        $disk = Storage::disk('danger-backups');
        $backupName = config('backup.backup.name', 'laravel-backup');
        $prefix = config('backup.backup.destination.filename_prefix', '');

        $files = $disk->files($backupName);

        $zipFiles = array_filter($files, function (string $file) use ($prefix): bool {
            return str_ends_with($file, '.zip') && str_starts_with(basename($file), $prefix);
        });

        if (empty($zipFiles)) {
            throw new \RuntimeException('No backup zip file was found after backup completed.');
        }

        rsort($zipFiles);

        $latestFile = reset($zipFiles);

        return [
            'filename' => basename($latestFile),
            'path' => $latestFile,
            'size' => $disk->size($latestFile),
        ];
    }
}
